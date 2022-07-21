<?php
if (!defined('ABSPATH')) {
    exit;
}

use WCKalkulator\Helper;

/**
 * _wck_filter_price_enabled
 * _wck_filter_price_prefix
 * _wck_filter_price_value
 * _wck_filter_price_sufix
 */

global $post;

$enabled = (int) get_post_meta($post->ID, '_wck_filter_price_enabled', true) === 1;
$prefix = get_post_meta($post->ID, '_wck_filter_price_prefix', true);
$value = get_post_meta($post->ID, '_wck_filter_price_value', true);
$sufix = get_post_meta($post->ID, '_wck_filter_price_sufix', true);

?>
<p class="post-attributes-label-wrapper">
    <input type="hidden" name="_wck_filter_price_enabled" value="0">
    <input type="checkbox" name="_wck_filter_price_enabled" id="wck_filter_price_enabled" value="1" <?php checked($enabled); ?>>
    <label for="wck_filter_price_enabled" class="post-attributes-label">
        <?php _e('Enable price filtering', 'wc-kalkulator'); ?>
        <?php echo Helper::html_help_tip( __('Check to enable price filtering', 'wc-kalkulator') ); ?>
    </label>
</p>

<p class="post-attributes-label-wrapper">
    <label for="wck_filter_price_prefix" class="post-attributes-label">
        <?php _e('Price prefix', 'wc-kalkulator'); ?>
        <?php echo Helper::html_help_tip( __('For example: "from"', 'wc-kalkulator') ); ?>
    </label>
</p>
<input type="text" name="_wck_filter_price_prefix" id="wck_filter_price_prefix" value="<?php echo esc_html($prefix); ?>">

<p class="post-attributes-label-wrapper">
    <label for="wck_filter_price_value" class="post-attributes-label">
        <?php _e('Price value to show', 'wc-kalkulator'); ?>
        <?php echo Helper::html_help_tip( __('For example the lowest price', 'wc-kalkulator') ); ?>
    </label>
</p>
<input type="text" name="_wck_filter_price_value" id="wck_filter_price_value" value="<?php echo esc_html($value); ?>" autocomplete="off">

<p class="post-attributes-label-wrapper">
    <label for="wck_filter_price_sufix" class="post-attributes-label">
        <?php _e('Price sufix', 'wc-kalkulator'); ?>
        <?php echo Helper::html_help_tip( __('For example: "per m2"', 'wc-kalkulator') ); ?>
    </label>
</p>
<input type="text" name="_wck_filter_price_sufix" id="wck_filter_price_sufix" value="<?php echo esc_html($sufix); ?>" autocomplete="off">