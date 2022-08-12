<?php
if (!defined('ABSPATH')) {
    exit;
}

use WCKalkulator\Helper;

?>
<div class="half first">
    <label>
        <?php _e('Is this field required?', 'wc-kalkulator'); ?>
    </label>
    <select class="param f-required">
        <option value="off"><?php _e('No'); ?></option>
        <option value="on"><?php _e('Always require'); ?></option>
        <option value="if-visible"><?php _e('Only if visible'); ?></option>
    </select>
</div>
<div class="half second">
    <label><?php _e('Price', 'wc-kalkulator'); ?>
        <?php echo Helper::html_help_tip(__('The price value to use in a expression/formula. If the user does not fill in the field, the price is 0.', 'wc-kalkulator')); ?></label>
    <input type="number" step="any" min="0" class="param f-price">
</div>
<div class="clear"></div>

<div class="half first">
    <label>* <?php _e('Min. length', 'wc-kalkulator'); ?>
        <?php echo Helper::html_help_tip(__('Minimum character length. Consider that minimum length > 0 makes this field always required.', 'wc-kalkulator')); ?></label>
    <input type="number" class="param ft-min-length" step="1" min="0" value="0">
</div>
<div class="half second">
    <label>* <?php _e('Max. length', 'wc-kalkulator'); ?>
        <?php echo Helper::html_help_tip(__('Maximum charaster length.', 'wc-kalkulator')); ?></label>
    <input type="number" class="param ft-max-length" step="1" min="0" value="80">
</div>
<div class="clear"></div>
<div class="half first">
    <label><?php _e('Pattern (RegExp)', 'wc-kalkulator'); ?>
        <?php echo Helper::html_help_tip(__("Regular expression that the field's value is checked against.", 'wc-kalkulator')); ?>
    </label>
    <input type="text" class="param ft-pattern" value="" placeholder="[A-Za-z0-9]+">
</div>
<div class="half second">
    <label><?php _e('Placeholder', 'wc-kalkulator'); ?>
        <?php echo Helper::html_help_tip(__('The placeholder specifies a short hint to user. It is displayed in the field before the user enters a text.', 'wc-kalkulator')); ?></label>
    <input type="text" name="default_value_{id}" step="any" class="param f-default-value">
</div>
<div class="clear"></div>