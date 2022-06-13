<?php
if (!defined('ABSPATH')) {
    exit;
}

use WCKalkulator\FieldsetProduct, WCKalkulator\Sanitizer;
?>
<table class="variations">
    <input type="hidden" name="_wck_product_id" value="<?php echo absint($view->product_id); ?>">
    <input type="hidden" name="_wck_hash" value="<?php echo wp_hash($view->product_id); ?>">
    <?php echo isset($view->html) ? wp_kses($view->html, Sanitizer::allowed_html()) : ''; ?>
</table>