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
<label><?php _e('Price', 'wc-kalkulator'); ?>
    <?php echo Helper::html_help_tip(__('The price value to use in a expression/formula. If the user does not fill in the field, the price is 0.', 'wc-kalkulator')); ?></label>
<input type="number" step="any" min="0" class="param f-price">

<label>* <?php _e('Max. file size [MB]', 'wc-kalkulator'); ?>
    <?php echo Helper::html_help_tip(__('Maximum size of one file in [MB] megabytes', 'wc-kalkulator')); ?></label>
<input type="number" class="param fu-max-file-size" step="any" min="0,001" value="1">

<label><?php _e('Allowed extensions', 'wc-kalkulator'); ?>
    <?php echo Helper::html_help_tip(__('Select allowed file extensions.', 'wc-kalkulator')); ?></label>

<label class="inline"><input type="checkbox" data-extension="pdf" class="allowed-extensions ext-pdf">PDF</label>
<label class="inline"><input type="checkbox" data-extension="jpg|jpeg" class="allowed-extensions ext-jpg">JPG/JPEG</label>
<label class="inline"><input type="checkbox" data-extension="gif" class="allowed-extensions ext-gif">GIF</label>
<label class="inline"><input type="checkbox" data-extension="png" class="allowed-extensions ext-png">PNG</label>
<input type="hidden" class="param fu-allowed-extensions">