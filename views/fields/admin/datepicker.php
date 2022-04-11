<?php
if (!defined('ABSPATH')) {
    exit;
}
use WCKalkulator\Helper;
?>
<label>
    <input type="checkbox" class="param f-required" value="1">
    <?php _e('Select an option to make the field required ', 'wc-kalkulator'); ?>
</label>

<label>
    <input type="checkbox" class="param fdp-disallow-past-date" value="1">
    <?php _e('Disallow past date', 'wc-kalkulator'); ?>
    <?php echo Helper::html_help_tip( __('Select this option to disallow the user from selecting a past date ', 'wc-kalkulator')); ?>
</label>

<label><?php _e('Price', 'wc-kalkulator'); ?>
    <?php echo Helper::html_help_tip( __('The price value to use in a expression/formula.', 'wc-kalkulator')); ?></label>
<input type="number" step="any" min="0" class="param f-price">