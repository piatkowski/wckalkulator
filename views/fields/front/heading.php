<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<tr>
    <td class="value">
        <h<?php echo absint($view->level); ?>>
        <?php echo wp_kses_post($view->content); ?>
        </h<?php echo absint($view->level); ?>>
    </td>
</tr>