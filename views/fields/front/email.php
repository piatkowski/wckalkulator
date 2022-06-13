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
            <?php if (isset($view->is_required) && $view->is_required) : ?>
                <span class="required-asterisk">*</span>
            <?php endif; ?>
        </label>
    </td>
    <td class="value">
        <input type="email" id="<?php echo esc_html($view->id); ?>" class="attached enabled"
               name="<?php echo esc_html($view->name); ?>"
               placeholder="<?php echo esc_html($view->placeholder); ?>"
               minlength="<?php echo absint($view->min_length); ?>" maxlength="<?php echo absint($view->max_length); ?>"
               value="<?php echo esc_html($view->value); ?>"<?php echo esc_html($view->required); ?>>
    </td>
</tr>