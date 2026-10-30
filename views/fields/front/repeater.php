<?php
if (!defined('ABSPATH')) { exit; }
$field = $view->field;
$rules = array();
foreach ($field->child_fields() as $name => $child) {
    $rules[$name] = array('type' => $child->type(), 'visibility' => $child->data('visibility'), 'required' => $child->is_required());
}
?>
<tr class="wck-repeater-row" data-wck-field="<?php echo esc_attr($field->data('name')); ?>"><td colspan="2">
<fieldset class="wck-repeater" data-wck-name="<?php echo esc_attr($field->data('name')); ?>" data-wck-min="<?php echo (int) $field->data('min_rows'); ?>" data-wck-max="<?php echo (int) $field->data('max_rows'); ?>" data-wck-mode="<?php echo esc_attr($field->data('repeat_mode')); ?>" data-wck-children="<?php echo esc_attr(wp_json_encode($rules)); ?>">
<legend><?php echo esc_html($field->data('title')); ?></legend>
<div class="wck-repeat-rows"><?php foreach ($view->rows as $index => $row) { echo wp_kses($field->render_row($row, $index), \WCKalkulator\Sanitizer::allowed_html()); } ?></div>
<template class="wck-repeat-template"><?php echo wp_kses($field->render_row(array(), '__row__'), \WCKalkulator\Sanitizer::allowed_html()); ?></template>
<button type="button" class="button wck-add-row"><?php esc_html_e('Add item', 'wc-kalkulator'); ?></button>
<p class="wck-repeat-status" aria-live="polite"></p>
</fieldset>
</td></tr>
