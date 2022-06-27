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

<label><?php _e('Price for the field filled in by the user ', 'wc-kalkulator'); ?>
    <?php echo Helper::html_help_tip( __('The price value to use in a expression/formula. If the user does not fill in the field, the price is 0.', 'wc-kalkulator')); ?></label>
<span>Note: If the user does not fill in the field, the price of this field is 0,00.</span>
<input type="number" step="any" min="0" class="param f-price">

<label>* <?php _e('Max. file size [MB]', 'wc-kalkulator'); ?>
    <?php echo Helper::html_help_tip( __('Maximum size of one file in [MB] megabytes', 'wc-kalkulator')); ?></label>
<input type="number" class="param fu-max-file-size" step="1" min="1" value="1">

<label><?php _e('Allowed extensions', 'wc-kalkulator'); ?>
    <?php echo Helper::html_help_tip( __('Select allowed file extensions.', 'wc-kalkulator')); ?></label>
<label><input type="checkbox" data-file-extension="*.jpg,*.jpeg">*.jpg/jpeg</label>
<label><input type="checkbox" data-file-extension="*.gif">*.gif</label>
<label><input type="checkbox" data-file-extension="*.png">*.png</label>
<input type="text" class="param fu-allowed-extensions">