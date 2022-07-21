<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<tr>
    <?php echo wp_kses_post(apply_filters('wck_field_td_label', '<td class="label">', $view->field_type)); ?>
        <h<?php echo absint($view->level); ?>>
        <?php echo wp_kses_post($view->content); ?>
        </h<?php echo absint($view->level); ?>>
    </td>
</tr>