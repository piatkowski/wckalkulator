<?php
if (!defined('ABSPATH')) {
    exit;
}

use WCKalkulator\FieldsetAssignment;
use WCKalkulator\Helper;

wp_nonce_field(\WCKalkulator\FieldsetPostType::POST_TYPE, '_wck_nonce');

/**
 * _wck_assign_type
 * _wck_assign_products
 * _wck_assign_categories
 * _wck_assign_tags
 */

global $post;

$type = (int)get_post_meta($post->ID, '_wck_assign_type', true);
$priority = (int)get_post_meta($post->ID, '_wck_assign_priority', true);
$products = get_post_meta($post->ID, '_wck_assign_products', true);
$categories = get_post_meta($post->ID, '_wck_assign_categories', true);
$tags = get_post_meta($post->ID, '_wck_assign_tags', true);
$attributes = get_post_meta($post->ID, '_wck_assign_attributes', true);

?>
<p class="post-attributes-label-wrapper">
    <select name="_wck_assign_type" id="assign_type" class="wc-enhanced-select">
        <?php foreach (FieldsetAssignment::all() as $id => $title): ?>
            <option value="<?php echo esc_html($id); ?>" <?php selected($type, $id); ?>><?php echo esc_html($title); ?></option>
        <?php endforeach; ?>
    </select>
</p>
<div class="hide-if-disabled">
    <p class="post-attributes-label-wrapper">
        <label for="assign_products" class="post-attributes-label">
            <?php _e('Choose Products', 'wc-kalkulator'); ?>
            <?php echo Helper::html_help_tip(__('Search a product by title. You can choose multiple products.', 'wc-kalkulator')); ?>
        </label>
    </p>
    <select class="wc-product-search" multiple="multiple" id="assign_products" name="_wck_assign_products[]"
            data-action="woocommerce_json_search_products">
        <?php foreach (FieldsetAssignment::products_readable($products) as $id => $title): ?>
            <option value="<?php echo esc_html($id); ?>" selected><?php echo esc_html($title); ?></option>
        <?php endforeach; ?>
    </select>

    <p class="post-attributes-label-wrapper">
        <label for="assign_categories" class="post-attributes-label">
            <?php _e('Choose Categories', 'wc-kalkulator'); ?>
            <?php echo Helper::html_help_tip(__('Search a category by title. You can choose multiple categories.', 'wc-kalkulator')); ?>
        </label>
    </p>
    <select class="wc-category-search" multiple="multiple" id="assign_categories" name="_wck_assign_categories[]">
        <?php foreach (FieldsetAssignment::categories_readable($categories) as $id => $title): ?>
            <option value="<?php echo esc_html($id); ?>" selected><?php echo esc_html($title); ?></option>
        <?php endforeach; ?>
    </select>

    <p class="post-attributes-label-wrapper">
        <label for="assign_tags" class="post-attributes-label">
            <?php _e('Choose Tags', 'wc-kalkulator'); ?>
            <?php echo Helper::html_help_tip(__('Search a tag by name. You can choose multiple tags.', 'wc-kalkulator')); ?>
        </label>
    </p>

    <select class="wc-product-search" multiple="multiple" id="assign_tags" name="_wck_assign_tags[]"
            data-action="wckalkulator_json_search_tags">
        <?php foreach (FieldsetAssignment::tags_readable($tags) as $id => $title): ?>
            <option value="<?php echo esc_html($id); ?>" selected><?php echo esc_html($title); ?></option>
        <?php endforeach; ?>
    </select>

    <p class="post-attributes-label-wrapper">
        <label for="assign_tags" class="post-attributes-label">
            <?php _e('Choose Product Attributes', 'wc-kalkulator'); ?>
            <?php echo Helper::html_help_tip(__('Search an attribute by name. You can choose multiple attributes.', 'wc-kalkulator')); ?>
        </label>
    </p>

    <select class="wc-product-search" multiple="multiple" id="assign_attributes" name="_wck_assign_attributes[]"
            data-action="wckalkulator_json_search_attributes">
        <?php foreach (FieldsetAssignment::attributes_readable($attributes) as $id => $title): ?>
            <option value="<?php echo esc_html($id); ?>" selected><?php echo esc_html($title); ?></option>
        <?php endforeach; ?>
    </select>
</div>

<p class="post-attributes-label-wrapper">
    <label for="assign_priority" class="post-attributes-label">
        <?php _e('Priority (number)', 'wc-kalkulator'); ?>
        <?php echo Helper::html_help_tip(__('If the product has many groups assigned to it, the one with the highest priority (the highest number) will be used. ', 'wc-kalkulator')); ?>
    </label>
</p>
<input type="number" step="1" name="_wck_assign_priority" id="assign_priority"
       value="<?php echo esc_html($priority); ?>">