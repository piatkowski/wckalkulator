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

<label>* <?php _e('Min. length', 'wc-kalkulator'); ?>
    <?php echo Helper::html_help_tip( __('Minimum character length. Consider that minimum length > 0 makes this field always required.', 'wc-kalkulator')); ?></label>
<input type="number" class="param ft-min-length" step="1" min="0" value="0">

<label>* <?php _e('Max. length', 'wc-kalkulator'); ?>
    <?php echo Helper::html_help_tip( __('Maximum charaster length.', 'wc-kalkulator')); ?></label>
<input type="number" class="param ft-max-length" step="1" min="0" value="80">

<label><?php _e('Placeholder', 'wc-kalkulator'); ?>
    <?php echo Helper::html_help_tip( __('The placeholder specifies a short hint to user. It is displayed in the field before the user enters a text.', 'wc-kalkulator')); ?></label>
<input type="text" name="default_value_{id}" step="any" class="param f-default-value">

<label><?php _e('Price for the field filled in by the user ', 'wc-kalkulator'); ?>
    <?php echo Helper::html_help_tip( __('The price value to use in a expression/formula. If the user does not fill in the field, the price is 0.', 'wc-kalkulator')); ?></label>
    <span>Note: If the user does not fill in the field, the price of this field is 0,00.</span>
<input type="number" step="any" min="0" class="param f-price">