<?php
if (!defined('ABSPATH')) {
    exit;
}

use WCKalkulator\Helper;

global $post;

?>
<p class="post-attributes-label-wrapper">
    <?php
    $enabled = (int)get_post_meta($post->ID, '_wck_variation_prices_visible', true) === 1;
    ?>
    <input type="hidden" name="_wck_variation_prices_visible" value="0"/>
    <input type="checkbox" name="_wck_variation_prices_visible" id="wck_variation_prices_visible"
           value="1" <?php echo checked($enabled); ?> />
    <label for="wck_variation_prices_visible">
        <?php _e("Show price blocks for variable products", 'wc-kalkulator'); ?>
        <?php echo Helper::html_help_tip(__('Check this option to show variation price blocks on product page. Default: hidden.', 'wc-kalkulator')); ?>
    </label>
</p>
<p class="post-attributes-label-wrapper">
    <?php
    $price_block_action = (int)get_post_meta($post->ID, '_wck_price_block_action', true);
    ?>
    <label for="wck_price_block_action" class="post-attributes-label">
        <?php _e("Position of the price block"); ?>
    </label>
</p>

<select id="wck_price_block_action" name="_wck_price_block_action">
    <option value="0"<?php selected($price_block_action, 0); ?>><?php _e('After Add to Cart button', 'wc-kalkulator'); ?></option>
    <option value="1"<?php selected($price_block_action, 1); ?>><?php _e('Before Add to Cart button', 'wc-kalkulator'); ?></option>
</select>


