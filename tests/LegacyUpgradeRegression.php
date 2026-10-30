<?php
/** Regression checks for 1.6.1 data, without WordPress or real customer uploads. */
namespace WCKalkulator {
    class Plugin { public static $defined_fields = array('WCKalkulator\\Fields\\TextField'); }
    class FieldsetAssignment { public static function match($id) { return 42; } }
    class FieldsetPostType {
        public static function validate_fieldset_schema($value) { return false; }
        public static function validate_expression_schema($value, $fields) { return false; }
    }
}
namespace {
    function __($value) { return $value; }
    function get_temp_dir() { return sys_get_temp_dir(); }
    function wp_salt($scheme) { return 'legacy-test-salt'; }
    function absint($value) { return abs((int) $value); }
    function get_post_meta($id, $key, $single) { return $GLOBALS['legacy_meta'][$key] ?? ''; }
    function wp_add_inline_script($handle, $script) { $GLOBALS['inline_script'] = $script; }
    require __DIR__ . '/../src/Fields/AbstractField.php';
    require __DIR__ . '/../src/Fields/TextField.php';
    require __DIR__ . '/../src/FieldsetProduct.php';
    require __DIR__ . '/../src/PrivateUploads.php';
    require __DIR__ . '/../src/Woocommerce/Product.php';

    $fieldset = \WCKalkulator\FieldsetProduct::getInstance();
    $legacy = array('id' => 42, 'fieldset' => array('width' => array('name' => 'width', 'type' => 'text')),
        'choose_expression_type' => 'oneline', 'expression' => array('mode' => 'oneline', 'expr' => '1'));
    $old_loaded = $fieldset->init_from_snapshot($legacy, 10);
    $new_rejected = !$fieldset->init_from_snapshot(array_merge($legacy, array('schema_version' => 1)), 10);
    $fieldset->init_from_snapshot($legacy, 10);
    $GLOBALS['legacy_meta'] = array('_wck_javascript' => 'window.legacy = true;');
    $GLOBALS['inline_script'] = '';
    $fieldset->js_api();
    $legacy_js = strpos($GLOBALS['inline_script'], 'window.legacy = true;') !== false;
    $GLOBALS['legacy_meta']['_wck_javascript_enabled'] = '0';
    $GLOBALS['inline_script'] = '';
    $fieldset->js_api();
    $disabled_js = $GLOBALS['inline_script'] === '';

    $name = 'wckalkulator_tmp_' . substr(bin2hex(random_bytes(8)), 0, 13) . '.pdf';
    $path = rtrim(get_temp_dir(), '/\\') . DIRECTORY_SEPARATOR . $name;
    $contents = '%PDF-legacy-fixture';
    file_put_contents($path, $contents);
    $file = array('upload_tmp' => $path, 'upload_path' => '/old/location/file.pdf', 'upload_url' => 'https://example.test/file.pdf',
        'original_name' => 'test.pdf', 'size' => strlen($contents));
    $converted = \WCKalkulator\PrivateUploads::restore_legacy_staged($file);
    $repeated = \WCKalkulator\PrivateUploads::restore_legacy_staged($file);
    $staged = is_array($converted) && \WCKalkulator\PrivateUploads::is_staged($converted);
    $preserved = file_get_contents($path) === $contents;
    $repeat = is_array($repeated) && $converted['file_id'] === $repeated['file_id'];
    $foreign = $file; $foreign['upload_tmp'] = '/other/' . $name;
    $reject = \WCKalkulator\PrivateUploads::restore_legacy_staged($foreign) === false;
    $plain_checkout = true;
    try { \WCKalkulator\Woocommerce\Product::checkout_create_order_line_item(null, 'key', array('product_id' => 10), null); }
    catch (\Throwable $e) { $plain_checkout = false; }
    @unlink($path);
    $checks = array('legacy configuration remains readable' => $old_loaded, 'new schema remains validated' => $new_rejected,
        'legacy script runs' => $legacy_js, 'explicitly disabled script stays disabled' => $disabled_js,
        'legacy upload staged' => $staged, 'original file retained' => $preserved, 'retry reuses identity' => $repeat,
        'foreign path rejected' => $reject, 'ordinary product checkout unaffected' => $plain_checkout);
    $failed = array_keys(array_filter($checks, function ($passed) { return !$passed; }));
    echo 'Legacy upgrade regression: ' . (count($checks) - count($failed)) . '/' . count($checks) . " passed\n";
    if ($failed) { echo implode("\n", $failed) . "\n"; exit(1); }
}
