<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<tr class="<?php echo esc_html($view->css_class); ?>">
    <td class="label">
        <?php echo wp_kses($view->hint, \WCKalkulator\Sanitizer::allowed_html()); ?>
        <label for="<?php echo esc_html($view->id); ?>_from">
            <?php echo esc_html($view->title); ?>
        </label>
    </td>
    <td class="value">
        <input type="text" id="<?php echo esc_html($view->id); ?>_from" class="wck-range-date-picker attached enabled"
               name="<?php echo esc_html($view->name); ?>[from]"
               value="<?php echo esc_html($view->value_from); ?>"<?php echo esc_html($view->required); ?>>
    </td>
    <td class="value">
        <input type="text" id="<?php echo esc_html($view->id); ?>_to" class="wck-range-date-picker attached enabled"
               name="<?php echo esc_html($view->name); ?>[to]"
               value="<?php echo esc_html($view->value_to); ?>"<?php echo esc_html($view->required); ?>>
    </td>
</tr>