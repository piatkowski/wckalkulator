<?php
if (!defined('ABSPATH')) {
    exit;
}

use WCKalkulator\Helper;

?>
<div class="field" data-type="<?php echo esc_html($view->type); ?>"
     data-use-expression="<?php echo esc_attr($view->use_expression); ?>"
     data-group="<?php echo esc_attr($view->group); ?>">
    <div class="header">
        <span class="action-drag left dashicons dashicons-editor-justify" title="move - drag & drop"></span>
        <span class="left text"><?php echo esc_html($view->title); ?></span>
        <span class="left name"></span>
        <span class="action-delete right dashicons dashicons-trash"></span>
        <span class="action-toggle right dashicons dashicons-arrow-up-alt2"></span>
        <span class="action-duplicate right dashicons dashicons-admin-page" title="duplicate"></span>
        <span class="right wck-toggle-colspan woocommerce-input-toggle woocommerce-input-toggle--disabled"> </span>
        <span class="right wck-toggle-colspan-label"><?php _e('Full row', 'wc-kalkulator'); ?></span>
        <div class="clear"></div>
    </div>
    <div class="body">
        <input type="hidden" class="f-colspan" value="1"/>
        <?php if ($view->type !== 'empty') : ?>
            <div class="half first">
                <?php if ($view->show_title) : ?>
                    <label>* <?php _e('Title', 'wc-kalkulator'); ?>
                        <?php echo Helper::html_help_tip(__('Title will be displayed on the product page.', 'wc-kalkulator')); ?>
                    </label>
                    <input type="text" class="param f-title" placeholder="Field Name" required>
                <?php endif; ?>
            </div>
            <div class="half second">
                <label>* <?php _e('Unique Field Name', 'wc-kalkulator'); ?>
                    <?php echo Helper::html_help_tip(__('Field name must be unique and consist of letters, numbers and underscores. Correct names are: field_name, product_width.', 'wc-kalkulator')); ?>
                </label>
                <input type="text" class="param f-name" pattern="[a-zA-Z0-9_]+" title="A-Z a-z 0-9 _"
                       placeholder="field_name"
                       required>
            </div>
            <div class="clear"></div>
            <?php if ($view->show_title && $view->group !== 'static' && $view->group !== 'special') : ?>
                <div class="half first">
                    <label><?php _e('Text before Title (on Product Page)', 'wc-kalkulator'); ?>
                        <?php echo Helper::html_help_tip(__('Text displated before field`s Title (displayed only on product page)', 'wc-kalkulator')); ?>
                    </label>
                    <input type="text" class="param f-before-title" placeholder="">
                </div>
                <div class="half second">
                    <label><?php _e('Text after Title (on Product Page)', 'wc-kalkulator'); ?>
                        <?php echo Helper::html_help_tip(__('Text displayed after field`s Title (displayed only on product page)', 'wc-kalkulator')); ?>
                    </label>
                    <input type="text" class="param f-after-title" placeholder="">
                </div>
                <div class="clear"></div>
            <?php endif; ?>
        <?php endif; ?>
        <?php if ($view->group !== 'static' && $view->group !== 'special') : ?>
            <div class="half first">
                <label>
                    <?php _e('Hint for Customer (tooltip)', 'wc-kalkulator'); ?>
                    <?php echo Helper::html_help_tip(__('Text to be displayed as a tooltip just like this one.', 'wc-kalkulator')); ?>
                </label>
                <input type="text" class="param f-hint" placeholder="Hint for Customer">
            </div>
            <div class="half second">
                <label>
                    <?php _e('CSS Class', 'wc-kalkulator'); ?>
                    <?php echo Helper::html_help_tip(__('CSS class or multiple classes separated by a space.', 'wc-kalkulator')); ?>
                </label>
                <input type="text" class="param f-css-class" pattern="[a-z A-Z0-9_-]+" title="A-Z a-z 0-9 _ - (space)"
                       placeholder="css_class">
            </div>
            <div class="clear"></div>
        <?php endif; ?>
        <?php echo wp_kses($view->admin_fields, \WCKalkulator\Sanitizer::allowed_html()); ?>

        <?php if ($view->type !== 'hidden' && $view->group !== 'special') : ?>
            <label>
                <?php _e('Conditional Visibility', 'wc-kalkulator'); ?>
                <?php echo Helper::html_help_tip(__('Set the rules for which this field will be visible.', 'wc-kalkulator')); ?>
            </label>
            <div class="cv-container">
                <button type="button"
                        class="button action-field-visibility"><?php _e('Edit Rules', 'wc-kalkulator'); ?></button>
                <input type="text" class="param visibility-readable" value=""
                       placeholder="<?php _e('Set the rules for which this field will be visible. Click on the button --->', 'wc-kalkulator'); ?>"
                       readonly>
                <input type="hidden" class="param f-visibility">
                <input type="hidden" class="param f-visibility-readable">
            </div>
        <?php endif; ?>
    </div>
</div>