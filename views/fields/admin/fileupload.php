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
<label>* <?php _e('Max. number of files', 'wc-kalkulator'); ?>
    <?php echo Helper::html_help_tip( __('Maximum numer of files that Customer can upload.', 'wc-kalkulator')); ?></label>
<input type="number" class="param fu-max-file-count" step="1" min="1" value="1">

<label>* <?php _e('Max. file size [MB]', 'wc-kalkulator'); ?>
    <?php echo Helper::html_help_tip( __('Maximum size of one file in [MB] MegaBytes', 'wc-kalkulator')); ?></label>
<input type="number" class="param fu-max-file-size" step="1" min="1" value="1">

<label><?php _e('Allowed extensions', 'wc-kalkulator'); ?>
    <?php echo Helper::html_help_tip( __('Select allowed file extensions.', 'wc-kalkulator')); ?></label>
<label><input type="checkbox" data-file-extension=""></label>
<input type="text" class="param fu-allowed-extensions">