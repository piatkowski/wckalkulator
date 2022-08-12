<?php
if (!defined('ABSPATH')) {
    exit;
}

use WCKalkulator\Helper;

?>
<div class="half first">
    <label><?php _e('Default state', 'wc-kalkulator'); ?></label>
    <select class="param fcb-default-state">
        <option value="off"><?php _e('Unchecked', 'wc-kalkulator'); ?></option>
        <option value="on"><?php _e('Checked', 'wc-kalkulator'); ?></option>
    </select>
</div>
<div class="half second">
    <label>
        <?php _e('Is this field required?', 'wc-kalkulator'); ?>
    </label>
    <select class="param f-required">
        <option value="off"><?php _e('No'); ?></option>
        <option value="on"><?php _e('Always require'); ?></option>
        <option value="if-visible"><?php _e('Only if visible'); ?></option>
    </select>
</div>
<div class="clear"></div>
<label><?php _e('Price', 'wc-kalkulator'); ?>
    <?php echo Helper::html_help_tip(__('The price value to use in a expression/formula.', 'wc-kalkulator')); ?></label>
<input type="number" step="any" min="0" class="param f-price">