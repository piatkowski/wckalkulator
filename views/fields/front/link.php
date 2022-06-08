<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<tr>
    <td class="value">
        <a href="<?php echo esc_attr($view->content); ?>" target="<?php echo esc_attr($view->target); ?>"><?php echo esc_html($view->title); ?></a>
    </td>
</tr>