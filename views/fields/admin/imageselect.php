<?php
if (!defined('ABSPATH')) {
    exit;
}
use WCKalkulator\Helper;
?>
<label>
    <input type="checkbox" class="param f-required" step="any" value="1">
    <?php _e('Select an option to make the field required ', 'wc-kalkulator'); ?>
</label>
<label>* <?php _e('Option Items', 'wc-kalkulator'); ?></label>
<div class="pairs fs-options">
    <div class="pair fs-option">
        <div class="column column-1-half">
            <label><?php _e("Option value", "wc-kalkulator"); ?></label>
            <input type="number" step="any" class="key fs-name" placeholder="0" required>
        </div>
        <div class="column column-2">
            <label><?php _e("Option Text", "wc-kalkulator"); ?></label>
            <input type="text" class="value fs-title" placeholder="Text value" required>
        </div>
        <div class="column column-2-half">
            <a href="#" class="button action-add-image">Add image</a>
            <img class="wp-media-image-preview action-add-image">
            <input type="hidden" class="value fs-image wp-media-image-id" placeholder="">
        </div>
        <div class="column column-3">
            <label><?php _e("Default", "wc-kalkulator"); ?></label>
            <input type="radio" name="default_value_{id}" class="f-default-value">
        </div>
        <div class="column column-4">
            <span class="action-delete right dashicons dashicons-no-alt"></span>
        </div>
        <div class="clearfix"></div>
    </div>
    <button type="button" class="button action-add">
        <?php _e("Add new option", "wc-kalkulator"); ?>
    </button>
    <button type="button" class="button action-removeall">
        <?php _e("Remove all", "wc-kalkulator"); ?>
    </button>
    <button type="button" class="button action-showimport">
        <?php _e("Import options", "wc-kalkulator"); ?>
    </button>
    <div class="importer">
        <?php echo Helper::html_help_tip( __('Import: each option in new line, format: value;title', 'wc-kalkulator') ); ?>
        <textarea placeholder="value;title"></textarea>
        <button type="button" class="button action-import">
            <?php _e("Add", "wc-kalkulator"); ?>
        </button>
    </div>
</div>