<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<tr>
    <td class="value">
        <a href="<?php echo wp_get_attachment_url(absint($view->content)); ?>" target="_blank"><?php echo esc_html($view->title); ?></a>
    </td>
</tr>