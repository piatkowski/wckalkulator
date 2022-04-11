<?php
if (!defined('ABSPATH')) {
    exit;
}
use WCKalkulator\Helper;
?>
<label class="mb-20">
    <input type="checkbox" class="param f-required" step="any" value="1">
    <?php _e('Select an option to make the field required ', 'wc-kalkulator'); ?>
</label>


<label><?php _e('Price', 'wc-kalkulator'); ?>
    <?php echo Helper::html_help_tip( __('The price value to use in a expression/formula.', 'wc-kalkulator')); ?></label>
<input type="number" step="any" min="0" class="param f-price">