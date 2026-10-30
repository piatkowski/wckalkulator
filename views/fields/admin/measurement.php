<?php
if (!defined('ABSPATH')) { exit; }
?>
<label><?php esc_html_e('Base unit used in formulas', 'wc-kalkulator'); ?></label>
<select class="param" data-wck-param="base_unit">
<?php foreach (\WCKalkulator\Fields\MeasurementField::unit_catalog() as $unit => $definition) : ?>
<option value="<?php echo esc_attr($unit); ?>"<?php selected($unit, 'm'); ?>><?php echo esc_html($unit); ?></option>
<?php endforeach; ?>
</select>
<label><?php esc_html_e('Allowed units (comma separated, same measurement type)', 'wc-kalkulator'); ?></label>
<input class="param" data-wck-param="units" type="text" value="mm,cm,m" required>
<?php foreach (array('min' => 0, 'max' => 1000000, 'step' => 0.01, 'precision' => 4) as $key => $default) : ?>
<label><?php echo esc_html(array('min' => __('Minimum in base unit', 'wc-kalkulator'), 'max' => __('Maximum in base unit', 'wc-kalkulator'), 'step' => __('Step in base unit (from minimum)', 'wc-kalkulator'), 'precision' => __('Decimal places in base unit', 'wc-kalkulator'))[$key]); ?></label>
<input class="param" data-wck-param="<?php echo esc_attr($key); ?>" type="number" min="0" step="<?php echo $key === 'precision' ? '1' : 'any'; ?>" value="<?php echo esc_attr($default); ?>" required>
<?php endforeach; ?>
<label><?php esc_html_e('Default value in base unit', 'wc-kalkulator'); ?></label><input class="param f-default-value" type="number" min="0" step="any">
<label><?php esc_html_e('Is this field required?', 'wc-kalkulator'); ?></label>
<select class="param f-required"><option value="off"><?php esc_html_e('No', 'wc-kalkulator'); ?></option><option value="on"><?php esc_html_e('Always require', 'wc-kalkulator'); ?></option><option value="if-visible"><?php esc_html_e('Only if visible', 'wc-kalkulator'); ?></option></select>
