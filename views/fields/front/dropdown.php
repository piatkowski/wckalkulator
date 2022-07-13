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
    <?php echo wp_kses_post(apply_filters('wck_field_td', '<td class="value">', $view->field_type)); ?>
        <select id="<?php echo esc_html($view->id); ?>"
                name="<?php echo esc_html($view->name); ?>" <?php echo esc_html($view->required); ?>>
        <?php foreach ($view->options_title as $i => $opt_title) : ?>
            <option class="attached enabled"
                    value="<?php echo esc_html($opt_title); ?>" <?php selected(is_array($view->value) ? in_array($opt_title, $view->value) : ($view->value === $opt_title)); ?>>
                <?php echo esc_html($view->options_title[$i]); ?>
            </option>
        <?php endforeach; ?>
        </select>
    </td>
</tr>