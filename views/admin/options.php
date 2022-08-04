<?php
if (!defined('ABSPATH')) {
    exit;
}
use WCKalkulator\Helper;

global $post;

?>
<p>
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

