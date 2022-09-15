<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<tr class="<?php echo esc_html($view->css_class); ?>">
    <?php echo wp_kses_post(apply_filters('wck_field_td_label', '<td class="label">', $view->field_type)); ?>
        <?php echo wp_kses($view->hint, \WCKalkulator\Sanitizer::allowed_html()); ?>
        <label for="<?php echo esc_html($view->id); ?>">
            <?php include '_label.php'; ?>
        </label>
    </td>
    <?php echo wp_kses_post(apply_filters('wck_field_td', '<td class="value">', $view->field_type)); ?>
        <select id="<?php echo esc_html($view->id); ?>"
                name="<?php echo esc_html($view->name); ?>" <?php echo esc_html($view->required); ?>>
            <option value=""><?php _e( 'Choose an option', 'woocommerce' ); ?></option>
        <?php foreach ($view->options_name as $i => $opt_name) : ?>
            <option class="attached enabled"
                    value="<?php echo esc_html($opt_name); ?>" <?php selected($view->value, $opt_name); ?>>
                <?php echo esc_html($view->options_title[$i]); ?>
            </option>
        <?php endforeach; ?>
        </select>
    </td>
</tr>