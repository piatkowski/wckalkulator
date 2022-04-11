<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<tr class="<?php echo esc_html($view->css_class); ?>">
    <td class="label">
        <?php echo wp_kses($view->hint, \WCKalkulator\Sanitizer::allowed_html()); ?>
        <label for="<?php echo esc_html($view->id); ?>">
            <?php echo esc_html($view->title); ?>
        </label>
    </td>
    <td class="value">
        <input type="number" id="<?php echo esc_html($view->id); ?>" class="attached enabled"
               name="<?php echo esc_html($view->name); ?>"
               min="<?php echo esc_html($view->min); ?>" max="<?php echo esc_html($view->max); ?>" step="any"
               value="<?php echo esc_html($view->value); ?>"<?php echo esc_html($view->required); ?>>
    </td>
</tr>