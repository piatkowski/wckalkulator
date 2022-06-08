<?php
if (!defined('ABSPATH')) {
    exit;
}

use WCKalkulator\Helper;

?>
<label>* <?php _e('Heading level', 'wc-kalkulator'); ?></label>
<select class="param fst-level">
    <?php for ($i = 1; $i <= 6; $i++): ?>
        <option value="<?php echo $i; ?>">h<?php echo $i; ?></option>
    <?php endfor; ?>
</select>
<label>* <?php _e('Heading text', 'wc-kalkulator'); ?>
    <?php echo Helper::html_help_tip(__('Write HTML code which will be displayed on product page.', 'wc-kalkulator')); ?></label>
<input type="text" class="param fst-content">
