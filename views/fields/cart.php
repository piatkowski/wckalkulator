<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<?php echo esc_html($view->title); ?>:

<?php if (isset($view->value)): ?>
    <strong><?php echo esc_html($view->value); ?></strong><br/>
<?php endif; ?>

<?php if (isset($view->image)): ?>
    <img src="<?php echo esc_html($view->image); ?>" width="64" alt=""/><br/>
<?php endif; ?>
