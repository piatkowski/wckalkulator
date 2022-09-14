<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<p class="wckalkulator-price">
    <?php _e('Total', 'wc-kalkulator'); ?>
    <span id="wckalkulator-price"><?php
        if(isset($view->default_price) && floatval($view->default_price) > 0) {
            echo str_replace('woocommerce-Price-amount', '', wc_price($view->default_price));
        }
        ?></span>
</p>