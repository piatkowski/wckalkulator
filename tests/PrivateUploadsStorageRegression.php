<?php
/** Storage path configuration checks; does not create or read customer files. */
namespace {
    define('ABSPATH', sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'wck-wordpress-root' . DIRECTORY_SEPARATOR);
    $wck_test_storage = '';
    $wck_settings_options = array();
    function __($value) { return $value; }
    function apply_filters($name, $value) {
        global $wck_test_storage;
        return $name === 'wck_private_upload_directory' && $wck_test_storage !== '' ? $wck_test_storage : $value;
    }
    function add_action() {}
    function add_filter() {}
    function get_option($name) { global $wck_settings_options; return $wck_settings_options; }
    function sanitize_text_field($value) { return trim((string) $value); }
    function trailingslashit($value) { return rtrim($value, '/\\') . '/'; }
    function absint($value) { return abs((int) $value); }
    require __DIR__ . '/../src/Settings.php';
    require __DIR__ . '/../src/PrivateUploads.php';

    \WCKalkulator\Settings::init();
    $configured = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'wck-external-storage';
    $validated = \WCKalkulator\Settings::validate(array(
        'form_css_selector' => 'form.cart',
        'display_errors' => 'no',
        'upload_customer_data_dir' => $configured,
        'upload_retain_time' => '10',
        'upload_temp_retain_time' => '2'
    ));
    $wck_settings_options = array('upload_customer_data_dir' => $validated['upload_customer_data_dir']);
    $root = \WCKalkulator\PrivateUploads::root();
    $expected = realpath(dirname($configured)) . DIRECTORY_SEPARATOR . basename($configured) . DIRECTORY_SEPARATOR;
    if ($root !== $expected) {
        fwrite(STDERR, "Saved private storage path was not used.\n");
        exit(1);
    }

    $wck_test_storage = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'wck-filtered-storage' . DIRECTORY_SEPARATOR;
    $root = \WCKalkulator\PrivateUploads::root();
    $expected = realpath(dirname(rtrim($wck_test_storage, '/\\'))) . DIRECTORY_SEPARATOR
        . basename(rtrim($wck_test_storage, '/\\')) . DIRECTORY_SEPARATOR;
    if ($root !== $expected) {
        fwrite(STDERR, "Private storage filter did not override the saved path.\n");
        exit(1);
    }

    $wck_settings_options = array('upload_customer_data_dir' => ABSPATH . 'public-uploads/');
    $wck_test_storage = '';
    $root = \WCKalkulator\PrivateUploads::root();
    if (strpos(str_replace('\\', '/', $root), str_replace('\\', '/', ABSPATH)) === 0) {
        fwrite(STDERR, "Public WordPress path was accepted for private uploads.\n");
        exit(1);
    }
    echo "Private upload storage regression: 3/3 passed\n";
}
