<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<tr data-wck-static-name="<?php echo esc_attr(isset($view->name) ? $view->name : ""); ?>">
    <?php echo wp_kses_post(apply_filters('wck_field_td', '<td class="value">', $view->field_type)); ?>
        <?php echo wp_kses_post($view->content); ?>
    </td>
</tr>