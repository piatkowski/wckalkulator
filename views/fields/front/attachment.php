<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<tr>
    <?php echo wp_kses_post(apply_filters('wck_field_td', '<td class="value">', $view->field_type)); ?>
        <a href="<?php echo wp_get_attachment_url(absint($view->content)); ?>" target="_blank"><?php echo esc_html($view->title); ?></a>
    </td>
</tr>