<?php
if (!defined('ABSPATH')) { exit; }
?>
<label><?php esc_html_e('Repeat mode', 'wc-kalkulator'); ?></label>
<select class="param" data-wck-param="repeat_mode"><option value="button"><?php esc_html_e('Add items with a button', 'wc-kalkulator'); ?></option><option value="quantity"><?php esc_html_e('One row per product quantity', 'wc-kalkulator'); ?></option></select>
<label><?php esc_html_e('Minimum rows', 'wc-kalkulator'); ?></label><input class="param" data-wck-param="min_rows" type="number" min="0" max="50" value="1" required>
<label><?php esc_html_e('Maximum rows', 'wc-kalkulator'); ?></label><input class="param" data-wck-param="max_rows" type="number" min="1" max="50" value="10" required>
<label><?php esc_html_e('Price formula for one row', 'wc-kalkulator'); ?></label><input class="param expression-editor-enabled" data-wck-param="row_formula" type="text" value="0" required>
<p><?php esc_html_e('Assign ordinary fields to this group using their Repeatable group setting. Use their names in the row formula. The group value is the sum, or the average per product in quantity mode; :sum is always the total and :count the row count. Maximum 20 fields per group.', 'wc-kalkulator'); ?></p>
