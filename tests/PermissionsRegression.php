<?php
/** Dependency-free checks for CPT registration and WooCommerce attribute term writes. */
namespace {
    $wck_test_caps = array();
    $wck_registered_types = array();
    $wck_term_meta_writes = array();
    $wck_post_meta_writes = array();
    $wck_mock_posts = array();
    $wck_mock_autosave_id = 0;
    $wck_submenu_capability = '';
    $wck_test_term = (object) array('term_id' => 12, 'taxonomy' => 'pa_color');
    function __($value) { return $value; }
    function post_type_exists($type) { return false; }
    function register_post_type($type, $args) { global $wck_registered_types; $wck_registered_types[$type] = $args; }
    function wc_get_attribute_taxonomies() { return array((object) array('attribute_name' => 'color')); }
    function add_action() {}
    function add_filter() {}
    function current_user_can($capability, $object_id = null) {
        global $wck_test_caps;
        return !empty($wck_test_caps['*']) || !empty($wck_test_caps[$capability]);
    }
    function get_term($term_id) { global $wck_test_term; return $wck_test_term; }
    function get_taxonomy($taxonomy) {
        return (object) array('cap' => (object) array(
            'manage_terms' => 'manage_product_terms',
            'edit_terms' => 'edit_product_terms'
        ));
    }
    function is_wp_error($value) { return false; }
    function wp_nonce_field() { return ''; }
    function wp_verify_nonce($nonce, $action) {
        return $nonce === 'valid-nonce' && in_array($action, array('wck_attribute_term_value', 'wck_fieldset', 'wck_global_parameter', 'wck_duplicate_nonce'), true);
    }
    function wp_unslash($value) { return $value; }
    function absint($value) { return abs((int) $value); }
    function get_post($post_id) { global $wck_mock_posts; return isset($wck_mock_posts[$post_id]) ? $wck_mock_posts[$post_id] : null; }
    function wp_is_post_autosave($post_id) { global $wck_mock_autosave_id; return (int) $post_id === (int) $wck_mock_autosave_id; }
    function wp_is_post_revision($post_id) { return false; }
    function get_post_type_object($post_type) { return (object) array('cap' => (object) array('create_posts' => 'edit_products')); }
    function add_submenu_page($parent, $page_title, $menu_title, $capability) { global $wck_submenu_capability; $wck_submenu_capability = $capability; }
    function update_post_meta($post_id, $key, $value) { global $wck_post_meta_writes; $wck_post_meta_writes[] = array($post_id, $key, $value); }
    function admin_url($path = '') { return '/wp-admin/' . $path; }
    function wp_die($message = '') { throw new \RuntimeException('wp_die'); }
    function update_term_meta($term_id, $key, $value) {
        global $wck_term_meta_writes;
        $wck_term_meta_writes[] = array($term_id, $key, $value);
    }

    require __DIR__ . '/../src/FieldsetPostType.php';
    require __DIR__ . '/../src/GlobalParametersPostType.php';
    require __DIR__ . '/../src/Woocommerce/Attribute.php';
    require __DIR__ . '/../src/Settings.php';

    \WCKalkulator\FieldsetPostType::register_post_type();
    \WCKalkulator\GlobalParametersPostType::register_post_type();
    $checks = array(
        'fieldset CPT registers without a logged-in manager' => isset($wck_registered_types['wck_fieldset']),
        'global parameter CPT registers without a logged-in manager' => isset($wck_registered_types['wck_global_parameter']),
        'fieldset maps object capabilities' => !empty($wck_registered_types['wck_fieldset']['map_meta_cap'])
            && $wck_registered_types['wck_fieldset']['capability_type'] === 'wck_fieldset'
            && $wck_registered_types['wck_fieldset']['capabilities']['edit_posts'] === 'manage_woocommerce',
        'global parameters map object capabilities' => !empty($wck_registered_types['wck_global_parameter']['map_meta_cap'])
            && $wck_registered_types['wck_global_parameter']['capability_type'] === 'wck_global_parameter'
            && $wck_registered_types['wck_global_parameter']['capabilities']['edit_posts'] === 'manage_woocommerce'
    );

    \WCKalkulator\Settings::add_menu_page();
    $checks['settings page uses WooCommerce manager capability'] = $wck_submenu_capability === 'manage_woocommerce';

    $wck_mock_posts[23] = (object) array('ID' => 23, 'post_type' => 'wck_fieldset');
    $wck_test_caps = array('edit_post' => true);
    $_POST = array();
    \WCKalkulator\FieldsetPostType::save_post(23, $wck_mock_posts[23]);
    $checks['programmatic fieldset save without form nonce is ignored'] = count($wck_post_meta_writes) === 0;

    $_POST = array('_wck_nonce' => 'valid-nonce');
    $wck_mock_autosave_id = 23;
    \WCKalkulator\FieldsetPostType::save_post(23, $wck_mock_posts[23]);
    $checks['autosave does not write fieldset meta'] = count($wck_post_meta_writes) === 0;
    $wck_mock_autosave_id = 0;

    $wck_test_caps = array();
    \WCKalkulator\GlobalParametersPostType::save_post(24, (object) array('ID' => 24, 'post_type' => 'wck_global_parameter'));
    $checks['customer cannot save a global parameter'] = count($wck_post_meta_writes) === 0;

    $wck_mock_posts[33] = (object) array('ID' => 33, 'post_type' => 'post');
    $wck_test_caps = array('manage_woocommerce' => true, 'edit_post' => true, 'edit_products' => true);
    $_GET = array('post' => '33', 'wck_duplicate_nonce' => 'valid-nonce');
    try {
        \WCKalkulator\FieldsetPostType::duplicate_post();
        $checks['duplicate action rejects a non-fieldset source'] = false;
    } catch (\RuntimeException $exception) {
        $checks['duplicate action rejects a non-fieldset source'] = true;
    }

    $_POST = array('_wck_attribute_term_nonce' => 'valid-nonce');
    \WCKalkulator\Woocommerce\Attribute::save_edited(12);
    $checks['missing term value preserves existing meta'] = count($wck_term_meta_writes) === 0;

    $_POST['wck_value'] = '0';
    $wck_test_caps = array('edit_product_terms' => true);
    \WCKalkulator\Woocommerce\Attribute::save_edited(12);
    $checks['manager can save explicit zero'] = count($wck_term_meta_writes) === 1 && $wck_term_meta_writes[0][2] === 0.0;

    $wck_test_caps = array();
    $_POST['wck_value'] = '5';
    \WCKalkulator\Woocommerce\Attribute::save_edited(12);
    $checks['customer cannot update term meta'] = count($wck_term_meta_writes) === 1;

    $wck_test_caps = array('edit_product_terms' => true);
    $_POST['wck_value'] = array('5');
    \WCKalkulator\Woocommerce\Attribute::save_edited(12);
    $checks['array input is rejected'] = count($wck_term_meta_writes) === 1;

    $failed = array();
    foreach ($checks as $name => $passed) {
        if (!$passed) { $failed[] = $name; }
    }
    echo 'Permissions regression: ' . (count($checks) - count($failed)) . '/' . count($checks) . ' passed' . PHP_EOL;
    if ($failed) {
        echo implode(PHP_EOL, $failed) . PHP_EOL;
        exit(1);
    }
}
