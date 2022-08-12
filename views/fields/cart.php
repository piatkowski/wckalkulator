<?php
if (!defined('ABSPATH')) {
    exit;
}

$has_value = !empty($view->value);
$has_image = !empty($view->image);
$has_colorswatch = !empty($view->colorswatch);

if ($has_value || $has_image || $has_colorswatch): ?>
    <strong><?php echo esc_html($view->title); ?></strong>:<br/>
<?php endif; ?>

<?php if ($has_value) {
    echo esc_html($view->value) . '<br/>';
} ?>

<?php if ($has_image): ?>
    <img src="<?php echo esc_html($view->image); ?>" width="64" alt=""/>
<?php endif; ?>

<?php if ($has_colorswatch): ?>
    <span class="wck-colorswatch" style="background-color: <?php echo esc_html($view->colorswatch); ?>"></span><br/>
<?php endif; ?>
