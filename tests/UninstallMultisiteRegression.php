<?php
/** Multisite uninstall iteration checks using isolated WordPress function stubs. */
namespace {
    $wck_current_blog = 7;
    $wck_blog_stack = array();
    $wck_deleted_options = array();
    $wck_cleared_hooks = array();
    $wck_deleted_meta = array();
    function is_multisite() { return true; }
    function get_sites($args) { return array(2, 3, 7); }
    function switch_to_blog($blog_id) {
        global $wck_current_blog, $wck_blog_stack;
        $wck_blog_stack[] = $wck_current_blog;
        $wck_current_blog = (int) $blog_id;
        return true;
    }
    function restore_current_blog() {
        global $wck_current_blog, $wck_blog_stack;
        $wck_current_blog = array_pop($wck_blog_stack);
        return true;
    }
    function wp_clear_scheduled_hook($hook) { global $wck_current_blog, $wck_cleared_hooks; $wck_cleared_hooks[$wck_current_blog][] = $hook; return 0; }
    function delete_option($option) {
        global $wck_current_blog, $wck_deleted_options;
        $wck_deleted_options[$wck_current_blog][] = $option;
    }
    function delete_metadata($type, $id, $key, $value, $delete_all) { global $wck_current_blog, $wck_deleted_meta; $wck_deleted_meta[$wck_current_blog][] = array($type, $id, $key, $delete_all); return true; }
    function wp_delete_post($id, $force) { return null; }

    class WckUninstallFakeWpdb {
        public $posts = 'wp_posts';
        public function prepare($query) { return $query; }
        public function get_col($query) { return array(); }
    }
    $wpdb = new WckUninstallFakeWpdb();
    require __DIR__ . '/../src/Uninstall.php';

    \WCKalkulator\Uninstall::run();
    $valid = $wck_current_blog === 7 && $wck_blog_stack === array()
        && count($wck_deleted_options) === 3
        && count($wck_deleted_options[2]) === 3
        && count($wck_deleted_options[3]) === 3
        && count($wck_deleted_options[7]) === 3
        && count($wck_cleared_hooks[2]) === 3
        && count($wck_cleared_hooks[3]) === 3
        && count($wck_cleared_hooks[7]) === 3
        && count($wck_deleted_meta[2]) === 1
        && count($wck_deleted_meta[3]) === 1
        && count($wck_deleted_meta[7]) === 1;
    if (!$valid) {
        fwrite(STDERR, "Uninstall did not clean each site or restore the original site context.\n");
        exit(1);
    }
    echo "Multisite uninstall regression: 1/1 passed\n";
}
