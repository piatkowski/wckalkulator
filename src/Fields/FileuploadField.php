<?php

namespace WCKalkulator\Fields;

use WCKalkulator\View;

/**
 * Class FileuploadField
 * @package WCKalkulator
 */
class FileuploadField extends AbstractField
{
    protected $parameters = array("type", "name", "title", "hint", "css_class", "required", "max_file_size", "allowed_extensions");
    protected $default_data = array("css_class" => "", "required" => false, "default_value" => "", "hint" => "");
    protected $data;
    protected $type = "fileupload";
    protected $admin_title;
    protected $use_expression = true;
    protected $group = "upload";
    protected $mimes = array("pdf" => "application/pdf", "jpg" => "image/jpeg", "jpeg" => "image/jpeg", "png" => "image/png", "gif" => "image/gif");
    protected $ext = array("pdf", "jpg", "jpeg", "png", "gif");

    /**
     * Output HTML for fields at backend.
     * @param $value
     * @return string
     */
    public function admin_fields($value = '')
    {
        $this->admin_title = __("File Upload", "wc-kalkulator");
        return View::render('fields/admin/' . $this->type);
    }

    /**
     * Output HTML for product page
     * @param $value
     * @return string
     */
    public function render_for_product($value = "")
    {
        $args = $this->prepared_data();
        $args['max_file_size'] = $this->data('max_file_size');
        if (!empty($this->data('allowed_extensions'))) {
            $args['allowed_extensions'] = $this->data('allowed_extensions');
            $args['accept'] = '.' . str_replace('|', ', .', $this->data('allowed_extensions'));
        } else {
            $args['allowed_extensions'] = 'pdf|jpg|jpeg|png|gif';
            $args['accept'] = '.pdf, .jpg, .jpeg, .png, .gif';
        }
        return View::render('fields/front/' . $this->type, $args);
    }

    /**
     * Output HTML for User's cart nad order meta
     * @param $file
     * @return string
     */
    public function render_for_cart($file = '')
    {
        if (is_array($file)) {
            return View::render('fields/cart', array(
                'title' => $this->data['title'],
                'value' => esc_html($file['original_name']) . ' (' . round($file['size'] / 1000000, 2) . ' MB)'
            ));
        }
    }

    /**
     * Run all validation tests
     * @param $value
     * @return bool
     */
    public function validate($value)
    {
        if (!$this->is_required() && empty($value))
            return true;

        if ($this->data['required'] && empty($value))
            return false;

        if (is_array($value)) {
            if (is_uploaded_file($value['tmp_name'])) {
                $allowed_size = $this->data('max_file_size') * 1000000; //Bytes
                $allowed_ext = array();

                foreach ($this->ext as $ext) {
                    if (strpos($this->data('allowed_extensions'), $ext) !== false) {
                        $allowed_ext[] = $ext;
                    }
                }

                if (empty($allowed_ext))
                    $allowed_ext = $this->ext;

                $mimes = array();

                foreach ($allowed_ext as $ext)
                    $mimes[$ext] = $this->mimes[$ext];

                if ($value['size'] > 0 && $value['size'] <= $allowed_size && filesize($value['tmp_name']) <= $allowed_size) {
                    $fileinfo = wp_check_filetype_and_ext($value['tmp_name'], $value['original_name'], $mimes);
                    if ($fileinfo['type'] !== false && in_array($fileinfo['type'], $mimes) && in_array($fileinfo['ext'], $allowed_ext)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Display value of the field in order line item at backend
     *
     * @param $value
     * @return string
     * @since 1.2.0
     */
    public function order_item_value($value)
    {
        return $value;
    }

    /**
     * Handles file upload. Copy file from temp location to the customer directory
     *
     * @param $data
     * @return bool
     * @since 1.3.0
     */
    public function upload($data)
    {
        if (!file_exists($data['upload_tmp'])) {
            return false;
        }

        $dir = dirname($data['upload_path']);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
            file_put_contents($dir . '/index.html', '');
        }

        return rename($data['upload_tmp'], $data['upload_path']);
    }

    /**
     * Handles file upload from POST to the temp directory
     *
     * @param $data
     * @return bool
     * @since 1.3.0
     */
    public function upload_temp($data)
    {
        if (move_uploaded_file($data['tmp_name'], $data['upload_tmp'])) {
            chmod($data['upload_tmp'], 0644);
            return true;
        }
        return false;
    }

}