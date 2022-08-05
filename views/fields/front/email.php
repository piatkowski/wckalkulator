<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<tr class="<?php echo esc_html($view->css_class); ?>">
    <?php echo wp_kses_post(apply_filters('wck_field_td_label', '<td class="label">', $view->field_type)); ?>
        <?php echo wp_kses($view->hint, \WCKalkulator\Sanitizer::allowed_html()); ?>
        <label for="<?php echo esc_html($view->id); ?>">
            <?php echo esc_html($view->title); ?>
            <?php if (isset($view->show_required_asterisk) && $view->show_required_asterisk) : ?>
                <span class="required-asterisk">*</span>
            <?php endif; ?>
        </label>
    </td>
    <?php echo wp_kses_post(apply_filters('wck_field_td', '<td class="value">', $view->field_type)); ?>
        <input type="email" id="<?php echo esc_html($view->id); ?>" class="attached enabled"
               name="<?php echo esc_html($view->name); ?>"
               placeholder="<?php echo esc_html($view->placeholder); ?>"
               minlength="<?php echo absint($view->min_length); ?>" maxlength="<?php echo absint($view->max_length); ?>"
               value="<?php echo esc_html($view->value); ?>"<?php echo esc_html($view->required); ?>>
    </td>
</tr>