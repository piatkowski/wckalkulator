<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<tr class="<?php echo esc_html($view->css_class); ?>">
    <?php echo wp_kses_post(apply_filters('wck_field_td_label', '<td class="label">', $view->field_type)); ?>
        <?php echo wp_kses($view->hint, \WCKalkulator\Sanitizer::allowed_html()); ?>
        <label for="<?php echo esc_html($view->id); ?>_from">
            <?php include '_label.php'; ?>
        </label>
    </td>
    <?php echo wp_kses_post(apply_filters('wck_field_td', '<td class="value">', $view->field_type)); ?>
        <input type="text" id="<?php echo esc_html($view->id); ?>_from" class="wck-range-date-picker attached enabled date_from"
               name="<?php echo esc_html($view->name); ?>[from]"
               value="<?php echo esc_html($view->value_from); ?>"<?php echo esc_html($view->required); ?>>
        <input type="text" id="<?php echo esc_html($view->id); ?>_to" class="wck-range-date-picker attached enabled date_to"
               name="<?php echo esc_html($view->name); ?>[to]"
               value="<?php echo esc_html($view->value_to); ?>"<?php echo esc_html($view->required); ?>>
        <div class="clearfix"></div>
    </td>
</tr>