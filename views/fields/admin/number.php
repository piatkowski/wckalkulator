<?php
if (!defined('ABSPATH')) {
    exit;
}

use WCKalkulator\Helper;

?>
<div class="half first">
    <label>* <?php _e('Min. value', 'wc-kalkulator'); ?>
        <?php echo Helper::html_help_tip(__('The minimum value of the field.', 'wc-kalkulator')); ?>
    </label>
    <input type="number" class="param fn-min-value" step="any" required>
</div>
<div class="half second">
    <label>* <?php _e('Max. value', 'wc-kalkulator'); ?>
        <?php echo Helper::html_help_tip(__('The maximum value of the field.', 'wc-kalkulator')); ?>
    </label>
    <input type="number" class="param fn-max-value" step="any" required>
</div>
<div class="clear"></div>
<div class="half first">
    <label><?php _e('Default value', 'wc-kalkulator'); ?>
        <?php echo Helper::html_help_tip(__('The default value of the field.', 'wc-kalkulator')); ?>
    </label>
    <input type="number" name="default_value_{id}" step="any" class="param f-default-value">
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