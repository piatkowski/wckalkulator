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
$value = get_post_meta($post->ID, '_wck_param_value', true);

?>
<p class="post-attributes-label-wrapper">
    <label for="param_name" class="post-attributes-label">
        <?php _e('Unique Name', 'wc-kalkulator'); ?>
    </label>
</p>
<input type="text" name="_wck_param_name" pattern="[a-zA-Z0-9_]+" title="a-z A-Z 0-9 _" id="param_name"
       value="<?php echo esc_html($name); ?>" autocomplete="off" required>
<p class="post-attributes-label-wrapper">
    <label for="param_value" class="post-attributes-label">
        <?php _e('Value (numeric)', 'wc-kalkulator'); ?>
    </label>
</p>
<input type="text" name="_wck_param_value" id="param_value"
       value="<?php echo esc_html($value); ?>" style="width:100%" autocomplete="off" required>

<p>
    <?php _e('Example values:', 'wc-kalkulator'); ?><br/>
<ul>
    <li>10, 10.50, 99.99 - numeric</li>
    <li>[10, 20, 30, 40, 50, 60.99] - array, use {global:param_name}[0] to get value at index 0, which is "10"</li>
    <li>{first: 100, second: 200} - json object, use {global:param_name}["first"] to access object's value</li>
    <li><a href="https://youtu.be/qFA-4TJ6gvs" target="_blank">Go to video tutorial</a></li>
</ul>
</p>