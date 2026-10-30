<?php
if (!defined('ABSPATH')) { exit; }
$catalog = \WCKalkulator\Fields\MeasurementField::unit_catalog();
?>
<tr class="<?php echo esc_attr($view->css_class); ?> wck-measurement-row" data-wck-field="<?php echo esc_attr(str_replace(array('wck[', ']'), '', $view->name)); ?>">
<td class="label"><label for="<?php echo esc_attr($view->id); ?>"><?php include '_label.php'; ?></label></td>
<td class="value"><div class="wck-measurement" data-wck-config="<?php echo esc_attr(wp_json_encode(array_intersect_key($view->config, array_flip(array('base_unit', 'min', 'max', 'step', 'precision'))))); ?>">
<input type="number" class="attached enabled" id="<?php echo esc_attr($view->id); ?>" name="<?php echo esc_attr($view->name); ?>[value]" min="0" step="any" value="<?php echo esc_attr($view->value['value']); ?>"<?php echo $view->required ? ' required' : ''; ?>>
<label class="screen-reader-text" for="<?php echo esc_attr($view->id); ?>_unit"><?php esc_html_e('Unit', 'wc-kalkulator'); ?></label>
<select id="<?php echo esc_attr($view->id); ?>_unit" name="<?php echo esc_attr($view->name); ?>[unit]">
<?php foreach ($view->units as $unit) : ?>
<option value="<?php echo esc_attr($unit); ?>" data-wck-factor="<?php echo esc_attr($catalog[$unit][1] / $catalog[$view->config['base_unit']][1]); ?>"<?php selected($view->value['unit'], $unit); ?>><?php echo esc_html($unit); ?></option>
<?php endforeach; ?>
</select>
</div><small><?php echo esc_html(sprintf(__('Range: %1$s–%2$s %3$s; step: %4$s %3$s.', 'wc-kalkulator'), $view->config['min'], $view->config['max'], $view->config['base_unit'], $view->config['step'])); ?></small></td>
</tr>
