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
        <?php foreach ($view->options_name as $i => $opt_name) : ?>
            <input type="checkbox"
                   name="<?php echo esc_html($view->name); ?>[]"
                   class="<?php echo esc_html($view->id); ?>"
                   value="<?php echo esc_html($opt_name); ?>"
                <?php selected($view->value, $opt_name); ?>
                <?php echo esc_html($view->required); ?>>
            <?php echo esc_html($view->options_title[$i]); ?>
        <?php endforeach; ?>
    </td>
</tr>