<?php
if (!defined('ABSPATH')) {
    exit;
}
use WCKalkulator\Helper;
?>
<label>* <?php _e('Limit selected products', 'wc-kalkulator'); ?>
    <?php echo Helper::html_help_tip(__('Set maximum number of checkboxes the user may select. Empty or "0" means no limit.', 'wc-kalkulator')); ?></label>
<input type="number" class="param fcbg-limit" min="0" value="0"/>
<label>
    <?php _e('Is this field required?', 'wc-kalkulator'); ?>
</label>
<select class="param f-required">
    <option value="off"><?php _e('No'); ?></option>
    <option value="on"><?php _e('Always require'); ?></option>
    <option value="if-visible"><?php _e('Only if visible'); ?></option>
</select>

<label>* <?php _e('Products', 'wc-kalkulator'); ?></label>
<div class="pairs fs-options">
    <div class="pair fs-option">
        <div class="column column-1">
            <label><?php _e("Product ID", "wc-kalkulator"); ?></label>
            <input type="number" step="any" class="key fs-name" placeholder="0" required>
        </div>
        <div class="column column-2">
            <label><?php _e("Option Label", "wc-kalkulator"); ?></label>
            <input type="text" class="value fs-title" placeholder="Text value" required>
        </div>
        <div class="column column-3">
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