<?php
if (!defined('ABSPATH')) {
    exit;
}

use WCKalkulator\Sanitizer;

$product_link = $view->cart_item['data']->get_permalink();
$query = parse_url($product_link, PHP_URL_QUERY);
$edit_link =  $product_link . ($query ? '&' : '?') . 'wck_edit=' . $view->cart_item_key;
?>
<p class="wck-cart">
    <?php echo wp_kses($view->html, Sanitizer::allowed_html()); ?>
    <?php if (is_page('cart') || is_cart()): ?>
        <a href="<?php echo esc_attr($edit_link); ?>"><?php _e('Edit product', 'wc-kalkulator'); ?></a>
    <?php endif; ?>
</p>