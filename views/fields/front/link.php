<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<tr>
    <?php echo wp_kses_post(apply_filters('wck_field_td', '<td class="value">', $view->field_type)); ?>
        <a href="<?php echo esc_attr($view->content); ?>" target="<?php echo esc_attr($view->target); ?>"><?php echo esc_html($view->title); ?></a>
    </td>
</tr>