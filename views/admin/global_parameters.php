<?php
if (!defined('ABSPATH')) {
    exit;
}

wp_nonce_field(\WCKalkulator\GlobalParametersPostType::POST_TYPE, '_wck_nonce');

global $post;
/*
 * _wck_param_name
 * _wck_param_value
 */
$name = get_post_meta($post->ID, '_wck_param_name', true);
$value = floatval(get_post_meta($post->ID, '_wck_param_value', true));

?>
<p class="post-attributes-label-wrapper">
    <label for="param_name" class="post-attributes-label">
        <?php _e('Name', 'wc-kalkulator'); ?>
    </label>
</p>
<input type="text" name="_wck_param_name" pattern="[a-zA-Z0-9_]+" id="param_name"
       value="<?php echo esc_html($name); ?>" required>
<p class="post-attributes-label-wrapper">
    <label for="param_value" class="post-attributes-label">
        <?php _e('Value (numeric)', 'wc-kalkulator'); ?>
    </label>
</p>
<input type="number" step="any" name="_wck_param_value" id="param_value"
       value="<?php echo esc_html($value); ?>" required>