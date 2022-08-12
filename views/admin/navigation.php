<?php
if (!defined('ABSPATH')) {
    exit;
}
use WCKalkulator\Plugin;
global $submenu_file, $plugin_page, $pagenow;
?>
<div class="wck-admin-navigation">
    <h2>WC Kalkulator <?php echo esc_html(Plugin::VERSION); ?></h2>
    <?php
    foreach ($view->items as $item) {
        $is_active = $item['url'] === $submenu_file || $item['slug'] === $plugin_page;
        printf(
            '<a class="wck-item%s" href="%s">%s</a>',
            $is_active ? ' is-active' : '',
            esc_url($item['url']),
            esc_html($item['label'])
        );
    }
    ?>
</div>