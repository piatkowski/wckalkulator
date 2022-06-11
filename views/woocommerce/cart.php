<?php
if (!defined('ABSPATH')) {
    exit;
}

use WCKalkulator\Sanitizer;

?>
<p class="wck-cart">
    <?php echo wp_kses($view->html, Sanitizer::allowed_html()); ?>
    <a href="#"><?php _e('Edit product', 'wc-kalkulator'); ?></a>
</p>

