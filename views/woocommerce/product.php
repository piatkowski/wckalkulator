<?php
if (!defined('ABSPATH')) {
    exit;
}

use WCKalkulator\FieldsetProduct;

$fieldset = FieldsetProduct::getInstance();

?>
<table class="variations">
    <input type="hidden" name="_wck_product_id" value="<?php echo absint($fieldset->product_id()); ?>">
    <input type="hidden" name="_wck_hash" value="<?php echo wp_hash($fieldset->product_id()); ?>">
<?php

    foreach ($fieldset->fields() as $field) {
        echo wp_kses($field->render_for_product(), \WCKalkulator\Sanitizer::allowed_html()) . "\n";
    }
?>
</table>