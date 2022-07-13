<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<tr>
    <?php echo wp_kses_post(apply_filters('wck_field_td', '<td class="value">', $view->field_type)); ?>
        <?php echo wp_kses_post($view->content); ?>
    </td>
</tr>