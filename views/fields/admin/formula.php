<?php
if (!defined('ABSPATH')) {
    exit;
}

use WCKalkulator\Helper;

?>
<label>* <?php _e('Formula', 'wc-kalkulator'); ?>
    <?php echo Helper::html_help_tip(__('This formula is calculated separately and saved in order details. Its result is not available as an input to the product price formula.', 'wc-kalkulator')); ?></label>
<input type="text" class="param fst-content expression-editor-enabled show-total-price">
<label><?php _e("Display field on user's cart", 'wc-kalkulator'); ?></label>
<select class="param fst-display-on-user-cart">
    <option value="off"><?php _e('No', 'wc-kalkulator'); ?></option>
    <option value="on"><?php _e('Yes', 'wc-kalkulator'); ?></option>
</select>
