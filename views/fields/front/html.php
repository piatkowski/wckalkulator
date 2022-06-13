<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<tr>
    <td class="value">
        <?php echo wp_kses_post($view->content); ?>
    </td>
</tr>