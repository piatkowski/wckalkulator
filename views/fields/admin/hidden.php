<?php
if (!defined('ABSPATH')) {
    exit;
}

use WCKalkulator\Helper;

?>
<label>* <?php _e('Field value', 'wc-kalkulator'); ?>
    <?php echo Helper::html_help_tip(__('You can write any text. This field is not used in the formula.', 'wc-kalkulator')); ?></label>
<input type="text" class="param fst-content">
