<?php
if (!defined('ABSPATH')) {
    exit;
}
use WCKalkulator\Helper;
?>
<label>* <?php _e('HTML code', 'wc-kalkulator'); ?>
    <?php echo Helper::html_help_tip( __('Write HTML code which will be displayed on product page.', 'wc-kalkulator')); ?></label>
<textarea rows="8" class="param fst-content"></textarea>