<?php
if (!defined('ABSPATH')) {
    exit;
}

use WCKalkulator\Sanitizer;

$edit_link = $view->cart_item['data']->get_permalink() . '?wck_edit=' . $view->cart_item_key;
?>
<p class="wck-cart">
    <?php echo wp_kses($view->html, Sanitizer::allowed_html()); ?>
    <a href="<?php echo esc_attr($edit_link); ?>"><?php _e('Edit product', 'wc-kalkulator'); ?></a>
</p>