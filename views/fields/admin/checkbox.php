<?php
if (!defined('ABSPATH')) {
    exit;
}
use WCKalkulator\Helper;
?>
<label class="mb-20">
    <input type="checkbox" class="param fcb-default-state" value="1">
    <?php _e('Default state checked/unchecked', 'wc-kalkulator'); ?>
</label>

<label><?php _e('Price', 'wc-kalkulator'); ?>
    <?php echo Helper::html_help_tip( __('The price value to use in a expression/formula.', 'wc-kalkulator')); ?></label>
<input type="number" step="any" min="0" class="param f-price">