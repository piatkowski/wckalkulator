<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<div class="inventory">
    <p><?php _e('In this section you can define inventory reduction multiplier (formula or number). Reduction will be rouded up to the integer number. For example: stock quantity = 1000. Customer buys 3 pieces and the reduction is set to 10, the stock quantity will be reduced by 30 (3*10)', 'wc-kalkulator'); ?></p>
    <?php
    global $post;
    $reduction_m = get_post_meta($post->ID, '_wck_stock_reduction_multiplier', true);
    ?>
    <div class="input-icon input-stock">
        <input type="text" name="_wck_stock_reduction_multiplier"
               placeholder="<?php _e('Number or formula (value will be multiplied by quantity)...', 'wc-kalkulator'); ?>"
               value="<?php echo esc_html($reduction_m); ?>"
               autocomplete="off"
               role="presentation"><i></i>
    </div>
    <p class="description"><?php _e('You can use fields, operators and functions as above. You can modify stock quantity (i.e. add
        unit) using filter `woocommerce_format_stock_quantity`.', 'wc-kalkulator'); ?></p>
</div>
