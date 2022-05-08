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
        <select id="<?php echo esc_html($view->id); ?>"
                name="<?php echo esc_html($view->name); ?>" <?php echo esc_html($view->required); ?>>
        <?php foreach ($view->options_name as $i => $opt_name) : ?>
            <option class="attached enabled"
                    value="<?php echo esc_html($opt_name); ?>" <?php selected($view->value, $opt_name); ?>>
                <?php echo esc_html($view->options_title[$i]); ?>
            </option>
        <?php endforeach; ?>
        </select>
    </td>
</tr>