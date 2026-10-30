<?php

namespace WCKalkulator;

/** Private storage and access control for customer uploads. */
class PrivateUploads
{
    public static function init()
    {
        add_action('admin_post_wck_private_upload', array(__CLASS__, 'download'));
        add_action('admin_post_nopriv_wck_private_upload', array(__CLASS__, 'download'));
    }

    public static function root()
    {
        $root = dirname(rtrim(ABSPATH, '/\\')) . '/wc-kalkulator-private/customer-data';
        if (class_exists(__NAMESPACE__ . '\\Settings')) {
            $configured_root = Settings::get('upload_customer_data_dir');
            if (is_string($configured_root) && $configured_root !== '') {
                $root = $configured_root;
            }
        }
        $root = apply_filters('wck_private_upload_directory', $root);
        if (!is_string($root) || $root === '') {
            return false;
        }
        $root = rtrim($root, '/\\');
        $public = rtrim(ABSPATH, '/\\') . DIRECTORY_SEPARATOR;
        $normalized = str_replace('\\', '/', $root . '/');
        $public_normalized = str_replace('\\', '/', $public);
        $document_root = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
        $document_root_normalized = $document_root ? trailingslashit(str_replace('\\', '/', rtrim($document_root, '/\\'))) : false;
        if (strpos($normalized, $public_normalized) === 0 || ($document_root_normalized && strpos($normalized, $document_root_normalized) === 0) || is_link($root)) {
            return false;
        }
        $probe = rtrim($root, '/\\');
        $suffix = '';
        while (!file_exists($probe) && dirname($probe) !== $probe) {
            $suffix = DIRECTORY_SEPARATOR . basename($probe) . $suffix;
            $probe = dirname($probe);
        }
        $resolved = realpath($probe);
        $canonical = $resolved ? rtrim($resolved, '/\\') . $suffix . DIRECTORY_SEPARATOR : '';
        $canonical_normalized = str_replace('\\', '/', $canonical);
        if ($canonical === '' || strpos($canonical_normalized, $public_normalized) === 0
            || ($document_root_normalized && strpos($canonical_normalized, $document_root_normalized) === 0)) {
            return false;
        }
        return rtrim($canonical, '/\\') . DIRECTORY_SEPARATOR;
    }

    public static function new_id()
    {
        try {
            return bin2hex(random_bytes(16));
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function file_path($id, $extension)
    {
        if (!is_string($id) || !preg_match('/^[a-f0-9]{32}$/', $id) || !self::allowed_extension($extension)) {
            return false;
        }
        $root = self::root();
        return $root ? $root . strtolower($id) . '.' . strtolower($extension) : false;
    }

    public static function temp_path($id, $extension)
    {
        if (!is_string($id) || !preg_match('/^[a-f0-9]{32}$/', $id) || !self::allowed_extension($extension)) {
            return false;
        }
        return rtrim(get_temp_dir(), '/\\') . DIRECTORY_SEPARATOR . 'wckalkulator_tmp_' . strtolower($id) . '.' . strtolower($extension);
    }

    /** Verify a staged cart upload by server-derived identity and recorded size. */
    public static function is_staged($data)
    {
        if (!is_array($data) || !isset($data['file_id'], $data['extension'], $data['size'])
            || !is_numeric($data['size']) || (float) $data['size'] <= 0) {
            return false;
        }
        $path = self::staged_path($data);
        $size = $path ? @filesize($path) : false;
        return $path && !is_link($path) && is_file($path) && is_readable($path)
            && $size !== false && (int) $size === (int) $data['size'];
    }

    /** Resolve a staged path, including the signed identity of a legacy cart file. */
    public static function staged_path($data)
    {
        if (!is_array($data) || !isset($data['file_id'], $data['extension'])) {
            return false;
        }
        if (!isset($data['legacy_upload_tmp'])) {
            return self::temp_path($data['file_id'], $data['extension']);
        }
        if (!isset($data['original_name'], $data['size']) || !is_string($data['legacy_upload_tmp'])
            || !is_string($data['original_name']) || !is_numeric($data['size'])) {
            return false;
        }
        $source = $data['legacy_upload_tmp'];
        $directory = realpath(get_temp_dir());
        if (!$directory || realpath(dirname($source)) !== $directory || is_link($source)
            || !preg_match('/^wckalkulator_tmp_[a-f0-9]{13}(?:-[0-9]+)?\.(pdf|jpg|jpeg|png|gif)$/i', basename($source), $matches)
            || strtolower($matches[1]) !== $data['extension']) {
            return false;
        }
        $id = substr(hash_hmac('sha256', $source . '|' . $data['size'] . '|' . $data['original_name'], wp_salt('auth')), 0, 32);
        return is_string($data['file_id']) && hash_equals($id, $data['file_id']) ? $source : false;
    }

    /** Adapt a 1.6.1 cart upload on demand without moving its original file. */
    public static function restore_legacy_staged($data)
    {
        if (self::is_staged($data)) {
            return $data;
        }
        if (!is_array($data) || isset($data['file_id']) || !isset($data['upload_tmp'], $data['original_name'], $data['size'])
            || !is_string($data['upload_tmp']) || !is_string($data['original_name'])
            || !is_numeric($data['size']) || (float) $data['size'] <= 0) {
            return false;
        }
        $directory = realpath(get_temp_dir());
        $source = $data['upload_tmp'];
        $basename = basename($source);
        if (!$directory || realpath(dirname($source)) !== $directory || is_link($source)
            || !preg_match('/^wckalkulator_tmp_[a-f0-9]{13}(?:-[0-9]+)?\.(pdf|jpg|jpeg|png|gif)$/i', $basename, $matches)
            || !is_file($source) || !is_readable($source) || filesize($source) !== (int) $data['size']) {
            return false;
        }
        $extension = strtolower($matches[1]);
        $id = substr(hash_hmac('sha256', $source . '|' . $data['size'] . '|' . $data['original_name'], wp_salt('auth')), 0, 32);
        $converted = $data;
        $converted['file_id'] = $id;
        $converted['extension'] = $extension;
        $converted['legacy_upload_tmp'] = $source;
        $converted['tmp_name'] = $source;
        unset($converted['upload_tmp'], $converted['upload_path'], $converted['upload_url']);
        return self::is_staged($converted) ? $converted : false;
    }

    /** Copy a staged upload into private storage without consuming the source. */
    public static function promote($data)
    {
        if (!isset($data['file_id'], $data['extension'])) {
            return false;
        }
        $source = self::staged_path($data);
        $destination = self::file_path($data['file_id'], $data['extension']);
        if (!$source || !$destination) {
            return false;
        }
        $root = dirname($destination);
        if (!is_dir($root) && !@mkdir($root, 0700, true) && !is_dir($root)) {
            return false;
        }
        @chmod($root, 0700);
        $registry = $root . DIRECTORY_SEPARATOR . '.registry';
        if (!is_dir($registry) && !@mkdir($registry, 0700, true) && !is_dir($registry)) {
            return false;
        }
        @chmod($registry, 0700);
        $record_path = $registry . DIRECTORY_SEPARATOR . $data['file_id'] . '.json';
        if (is_link($record_path)) {
            return false;
        }
        $record_handle = @fopen($record_path, 'c+');
        if (!$record_handle || !flock($record_handle, LOCK_EX)) {
            if ($record_handle) {
                fclose($record_handle);
            }
            return false;
        }

        $success = false;
        if (is_file($destination) && !is_link($destination)) {
            $success = self::write_record($record_handle, $data['file_id'], $data['extension']);
        } else {
            $source_handle = self::is_staged($data) ? @fopen($source, 'rb') : false;
            if ($source_handle && flock($source_handle, LOCK_EX)) {
                $staging = $root . DIRECTORY_SEPARATOR . '.' . $data['file_id'] . '.' . self::new_id() . '.tmp';
                if (@copy($source, $staging)) {
                    @chmod($staging, 0600);
                    if (@rename($staging, $destination) && self::write_record($record_handle, $data['file_id'], $data['extension'])) {
                        @chmod($destination, 0600);
                        $success = true;
                    } else {
                        @unlink($staging);
                        @unlink($destination);
                    }
                }
                flock($source_handle, LOCK_UN);
                fclose($source_handle);
            } elseif ($source_handle) {
                fclose($source_handle);
            }
        }

        flock($record_handle, LOCK_UN);
        fclose($record_handle);
        return $success;
    }

    public static function registered_uploads()
    {
        $root = self::root();
        if (!$root) {
            return array();
        }
        $directory = $root . '.registry';
        if (!is_dir($directory) && !@mkdir($directory, 0700, true) && !is_dir($directory)) {
            return array();
        }
        @chmod($directory, 0700);
        $records = array();
        foreach (glob($directory . DIRECTORY_SEPARATOR . '*.json') as $record_path) {
            if (is_link($record_path) || !is_file($record_path) || !is_readable($record_path)) {
                continue;
            }
            $record = json_decode(file_get_contents($record_path), true);
            if (is_array($record) && empty($record['deleted']) && isset($record['id'], $record['extension'], $record['created'])
                && preg_match('/^[a-f0-9]{32}$/', $record['id'])
                && in_array(strtolower($record['extension']), array('pdf', 'jpg', 'jpeg', 'png', 'gif'), true)
                && basename($record_path) === $record['id'] . '.json') {
                $records[] = array('record' => $record, 'path' => $record_path);
            }
        }
        return $records;
    }

    public static function delete_registered($record, $record_path, $older_than)
    {
        if (!is_array($record) || !isset($record['id'], $record['extension'])
            || !preg_match('/^[a-f0-9]{32}$/', $record['id'])
            || !in_array(strtolower($record['extension']), array('pdf', 'jpg', 'jpeg', 'png', 'gif'), true)) {
            return false;
        }
        $root = self::root();
        if (!$root) {
            return false;
        }
        $registry = $root . '.registry';
        if (!is_string($record_path) || dirname($record_path) !== rtrim($registry, '/\\')
            || basename($record_path) !== $record['id'] . '.json' || is_link($record_path)) {
            return false;
        }
        $handle = @fopen($record_path, 'c+');
        if (!$handle || !flock($handle, LOCK_EX)) {
            if ($handle) {
                fclose($handle);
            }
            return false;
        }
        rewind($handle);
        $current = json_decode(stream_get_contents($handle), true);
        if (!is_array($current) || empty($current['created']) || $current['created'] >= $older_than || !empty($current['deleted'])
            || !isset($current['id'], $current['extension']) || $current['id'] !== $record['id']
            || !in_array(strtolower($current['extension']), array('pdf', 'jpg', 'jpeg', 'png', 'gif'), true)) {
            flock($handle, LOCK_UN);
            fclose($handle);
            return false;
        }
        $path = self::file_path($current['id'], $current['extension']);
        $success = $path && !is_link($path) && (!is_file($path) || @unlink($path));
        if ($success) {
            self::write_record($handle, $current['id'], $current['extension'], true);
        }
        flock($handle, LOCK_UN);
        fclose($handle);
        return $success;
    }

    private static function write_record($handle, $id, $extension, $deleted = false)
    {
        $record = array('id' => $id, 'extension' => strtolower($extension), 'created' => time());
        if ($deleted) {
            $record['deleted'] = true;
        }
        $json = wp_json_encode($record);
        if ($json === false || !ftruncate($handle, 0) || !rewind($handle) || fwrite($handle, $json) !== strlen($json) || !fflush($handle)) {
            return false;
        }
        @chmod(stream_get_meta_data($handle)['uri'], 0600);
        return true;
    }

    private static function allowed_extension($extension)
    {
        return is_string($extension) && in_array(strtolower($extension), array('pdf', 'jpg', 'jpeg', 'png', 'gif'), true);
    }

    public static function download_url($order_id, $file_id, $order_key)
    {
        return add_query_arg(array(
            'action' => 'wck_private_upload',
            'order_id' => absint($order_id),
            'file_id' => $file_id,
            'order_key' => $order_key
        ), admin_url('admin-post.php'));
    }

    public static function download()
    {
        $order_id = isset($_GET['order_id']) ? absint($_GET['order_id']) : 0;
        $file_id = isset($_GET['file_id']) && is_string($_GET['file_id']) ? sanitize_text_field(wp_unslash($_GET['file_id'])) : '';
        $order_key = isset($_GET['order_key']) && is_string($_GET['order_key']) ? wc_clean(wp_unslash($_GET['order_key'])) : '';
        $order = function_exists('wc_get_order') ? wc_get_order($order_id) : false;
        if (!$order || !preg_match('/^[a-f0-9]{32}$/', $file_id)) {
            status_header(404);
            exit;
        }

        $authorized = current_user_can('manage_woocommerce');
        if (!$authorized && get_current_user_id() && (int) $order->get_customer_id() === get_current_user_id()) {
            $authorized = true;
        }
        if (!$authorized && $order_key !== '' && hash_equals((string) $order->get_order_key(), $order_key)) {
            $authorized = true;
        }
        $found = false;
        foreach ($order->get_items() as $item) {
            $files = $item->get_meta('_wck_private_files', true);
            if (is_array($files) && in_array($file_id, $files, true)) {
                $found = true;
                break;
            }
        }
        if (!$authorized || !$found) {
            status_header(404);
            exit;
        }

        $path = self::find_file($file_id);
        if (!$path || is_link($path) || !is_file($path) || !is_readable($path)) {
            status_header(404);
            exit;
        }
        $type = function_exists('wp_check_filetype') ? wp_check_filetype($path) : array();
        nocache_headers();
        header('Content-Type: ' . (!empty($type['type']) ? $type['type'] : 'application/octet-stream'));
        header('Content-Length: ' . (string) filesize($path));
        header('Content-Disposition: attachment; filename="' . basename($path) . '"');
        readfile($path);
        exit;
    }

    private static function find_file($id)
    {
        $root = self::root();
        if (!$root || !is_dir($root)) {
            return false;
        }
        $matches = glob($root . $id . '.*');
        return $matches ? $matches[0] : false;
    }
}
