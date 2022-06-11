<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<strong><?php echo esc_html($view->title); ?></strong>:<br />

<?php if (isset($view->value)): ?>
    <?php echo esc_html($view->value); ?><br/>
<?php endif; ?>

<?php if (isset($view->image)): ?>
    <img src="<?php echo esc_html($view->image); ?>" width="64" alt=""/>
<?php endif; ?>

<?php if (isset($view->colorswatch)): ?>
    <span class="wck-colorswatch" style="background-color: <?php echo esc_html($view->colorswatch); ?>"></span><br/>
<?php endif; ?>
