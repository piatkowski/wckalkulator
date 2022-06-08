<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<input type="hidden" name="<?php echo esc_html($view->name); ?>" value="<?php echo esc_html($view->title . ': ' . $view->content); ?>" />