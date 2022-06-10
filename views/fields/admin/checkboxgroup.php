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

    <label>* <?php _e('Limit selected checkboxes', 'wc-kalkulator'); ?>
        <?php echo Helper::html_help_tip(__('Set maximum number of checkboxes the user may select. Empty or "0" means no limit.', 'wc-kalkulator')); ?></label>
    <input type="number" class="param fcbg-limit" min="0" value="0" />
<?php

include 'select.php';