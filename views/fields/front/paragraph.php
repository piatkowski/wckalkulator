<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<tr>
    <td class="value">
        <p><?php echo wp_kses_post($view->content); ?></p>
    </td>
</tr>