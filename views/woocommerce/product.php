<?php
if (!defined('ABSPATH')) {
    exit;
}

use WCKalkulator\FieldsetProduct, WCKalkulator\Sanitizer;

$has_sections = false;
foreach ($view->fields as $field) { if ($field['type'] === 'section') { $has_sections = true; break; } }
if ($has_sections) { echo \WCKalkulator\View::render('woocommerce/configurator', get_object_vars($view)); return; }
?>
<table class="variations<?php echo $view->layout === 2 ? ' wck-layout-two-col' : '' ?>">
    <input type="hidden" name="_wck_product_id" value="<?php echo absint($view->product_id); ?>">
    <input type="hidden" name="_wck_hash" value="<?php echo wp_hash($view->product_id); ?>">
    <?php echo isset($view->hidden) ? wp_kses($view->hidden, Sanitizer::allowed_html()) : ''; ?>
    <?php
    echo \WCKalkulator\View::render('woocommerce/field_rows', array('fields' => $view->fields, 'layout' => $view->layout));
    ?>
</table>
