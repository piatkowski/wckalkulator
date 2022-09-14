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
        <input type="file"
               class="wck_imageupload <?php echo esc_html($view->id); ?>"
               name="<?php echo esc_html($view->name); ?>"
               accept="<?php echo esc_attr($view->accept); ?>"
               data-maxfilesize="<?php echo floatval($view->max_file_size); ?>"<?php echo esc_html($view->required); ?>>
    </td>
</tr>