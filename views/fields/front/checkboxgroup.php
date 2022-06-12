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
            <?php if (absint($view->select_limit) > 0): ?>
                <small class="multicheckbox-limit-info"><?php echo esc_html(sprintf(__('(max. %s)', 'wc-kalkulator'), $view->select_limit)); ?></small>
            <?php endif; ?>
            <?php if (isset($view->is_required) && $view->is_required) : ?>
                <span class="required-asterisk">*</span>
            <?php endif; ?>
        </label>
    </td>
    <td class="value">
        <?php foreach ($view->options_name as $i => $opt_name) : ?>
            <label class="multicheckbox-label">
                <input type="checkbox"
                       data-type="<?php echo esc_html($view->type); ?>"
                       data-group="<?php echo esc_html($view->id); ?>"
                       data-required="<?php echo absint($view->is_required); ?>"
                       data-limit="<?php echo absint($view->select_limit); ?>"
                       name="<?php echo esc_html($view->name); ?>[]"
                       class="<?php echo esc_html($view->id); ?>"
                       value="<?php echo esc_html($opt_name); ?>"
                    <?php checked(is_array($view->value) ? in_array($opt_name, $view->value) : ($view->value === $opt_name)); ?>>
                <?php echo esc_html($view->options_title[$i]); ?>
            </label>
        <?php endforeach; ?>
    </td>
</tr>