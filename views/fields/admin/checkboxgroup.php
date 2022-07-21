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
            <option value="on"><?php _e('Yes'); ?></option>
        </select>
    </div>
    <div class="half second">
        <label>* <?php _e('Limit selected checkboxes', 'wc-kalkulator'); ?>
            <?php echo Helper::html_help_tip(__('Set maximum number of checkboxes the user may select. Empty or "0" means no limit.', 'wc-kalkulator')); ?></label>
        <input type="number" class="param fcbg-limit" min="0" value="0"/>
    </div>
    <div class="clear"></div>
<?php include 'select.php';