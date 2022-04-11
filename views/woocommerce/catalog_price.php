<?php
if (!defined('ABSPATH')) {
    exit;
}

use WCKalkulator\Sanitizer;

?>

<?php if ($view->prefix !== ''): ?>
    <span class="wck-price-prefix"><?php echo esc_html($view->prefix); ?></span>
<?php endif; ?>

<?php if ($view->value !== '') {
    echo wc_price( Sanitizer::sanitize($view->value, 'price') );
} else {
    echo esc_html($view->price);
} ?>

<?php if ($view->sufix !== ''): ?>
    <span class="wck-price-sufix"><?php echo esc_html($view->sufix); ?></span>
<?php endif; ?>
