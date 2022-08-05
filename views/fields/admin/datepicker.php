<?php
if (!defined('ABSPATH')) {
    exit;
}
use WCKalkulator\Helper;
?>
<label>
    <?php _e('Is this field required?', 'wc-kalkulator'); ?>
</label>
<select class="param f-required">
    <option value="off"><?php _e('No'); ?></option>
    <option value="on"><?php _e('Always require'); ?></option>
    <option value="if-visible"><?php _e('Only if visible'); ?></option>
</select>


<label>
    <input type="checkbox" class="param fdp-disallow-past-date" value="1">
    <?php _e('Disallow past date', 'wc-kalkulator'); ?>
    <?php echo Helper::html_help_tip( __('Select this option to disallow the user from selecting a past date ', 'wc-kalkulator')); ?>
</label>

<label><?php _e('Price', 'wc-kalkulator'); ?>
    <?php echo Helper::html_help_tip( __('The price value to use in a expression/formula.', 'wc-kalkulator')); ?></label>
<input type="number" step="any" min="0" class="param f-price">