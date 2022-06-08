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
        <?php for($i = 0; $i < $view->max_file_count; $i++): ?>
        <input type="file"
               class="<?php echo esc_html($view->id); ?>"
               name="<?php echo esc_html($view->name); ?>[]">
        <?php endfor; ?>
    </td>
</tr>