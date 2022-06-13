<?php
if (!defined('ABSPATH')) {
    exit;
}
use WCKalkulator\Helper;
?>

<label>* <?php _e('Link URL', 'wc-kalkulator'); ?>
    <?php echo Helper::html_help_tip( __('Write only URL starting with https://...', 'wc-kalkulator')); ?></label>
<input type="text" class="param fst-content" />
<label>* <?php _e('Link opening', 'wc-kalkulator'); ?></label>
<select class="param fst-target">
    <option value="_blank"><?php _e('Open in new window', 'wc-kalkulator'); ?></option>
    <option value="_self"><?php _e('Open in the same window', 'wc-kalkulator'); ?></option>
</select>