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
    <?php echo Helper::html_help_tip(__('The price value to use in a expression/formula. If the user does not fill in the field, the price is 0.', 'wc-kalkulator')); ?></label>
<span>Note: If the user does not fill in the field, the price of this field is 0,00.</span>
<input type="number" step="any" min="0" class="param f-price">

<label>* <?php _e('Max. file size [MB]', 'wc-kalkulator'); ?>
    <?php echo Helper::html_help_tip(__('Maximum size of one file in [MB] megabytes', 'wc-kalkulator')); ?></label>
<input type="number" class="param fu-max-file-size" step="any" min="0,001" value="1">

<label><?php _e('Allowed extensions', 'wc-kalkulator'); ?>
    <?php echo Helper::html_help_tip(__('Select allowed file extensions.', 'wc-kalkulator')); ?></label>

<label class="inline"><input type="checkbox" data-extension="jpg|jpeg" class="allowed-extensions">JPG/JPEG</label>
<label class="inline"><input type="checkbox" data-extension="gif" class="allowed-extensions">GIF</label>
<label class="inline"><input type="checkbox" data-extension="png" class="allowed-extensions">PNG</label>
<input type="hidden" class="param fu-allowed-extensions">