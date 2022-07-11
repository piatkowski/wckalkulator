<?php
if (!defined('ABSPATH')) {
    exit;
}

use WCKalkulator\Helper;

?>
<input type="hidden" name="_wck_expression" value="">
<p>
    <label>
        <strong><?php _e('Choose the type of calculation', 'wc-kalkulator'); ?>:</strong>
    </label>
</p>
<p>
    <input type="radio" name="_wck_choose_expression_type" id="choose_expression_type_off"
           class="expression_type expression_off"
           value="off">
    <label class="pr-20" for="choose_expression_type_off">
        <?php _e('Turn off', 'wc-kalkulator'); ?>
    </label>

    <input type="radio" name="_wck_choose_expression_type" id="choose_expression_type_oneline"
           class="expression_type expression_oneline"
           value="oneline" checked>
    <label class="pr-20" for="choose_expression_type_oneline">
        <?php _e('Single-line Formula', 'wc-kalkulator'); ?>
        <?php echo Helper::html_help_tip(__('The result of the calculation is a number.', 'wc-kalkulator')); ?>
    </label>

    <input type="radio" name="_wck_choose_expression_type" id="choose_expression_type_conditional"
           class="expression_type expression_conditional"
           value="conditional">
    <label class="pr-20" for="choose_expression_type_conditional">
        <?php _e('Conditional Expression', 'wc-kalkulator'); ?>
        <?php echo Helper::html_help_tip(__('If the condition [if] is met, calculate the price according to the assigned formula [=]. You can use multiple conditions. The [else] formula is used when none of the conditions are met.', 'wc-kalkulator')); ?>
    </label>

    <input type="radio" name="_wck_choose_expression_type" id="choose_expression_type_addon"
           class="expression_type expression_addon"
           value="addon">
    <label for="choose_expression_type_addon">
        <?php _e('Price Add-ons', 'wc-kalkulator'); ?> <sup style="color:red">Experimental</sup>
        <?php echo Helper::html_help_tip(__('The basic price of the product is increased by the price of the add-ons', 'wc-kalkulator')); ?>
    </label>
</p>
<p class="off-hide">
    <label><strong>Fields:</strong> </label>
    <span class="formula-field">{product_price}</span>
    <span class="formula-field">{product_regular_price}</span>
    <span class="formula-field">{product_weight}</span>
    <span class="formula-field">{product_width}</span>
    <span class="formula-field">{product_height}</span>
    <span class="formula-field">{product_length}</span>
    <span class="formula-field">{is_user_logged}</span>
    <span class="formula-field">{quantity}</span>
    <span class="formula-field">{current_month}</span> (1-12)
    <span class="formula-field">{day_of_month}</span> (1-31)
    <span class="formula-field">{day_of_week}</span> (0-6)
    <span class="formula-field">{current_hour}</span> (0-23)

    <span id="formula_fields"> &dash; </span>
    <a href="#" class="savefields">Update list</a>
</p>
<p class="off-hide">
    <label>
        <strong><?php _e('Build the formula', 'wc-kalkulator'); ?>:</strong>
    </label>
</p>
<div class="expression_oneline">
    <div class="input-icon input-equation">
        <input type="text" placeholder="<?php _e('equation...', 'wc-kalkulator'); ?>" value=""><i></i>
    </div>
</div>

<div class="expression_conditional">
    <div id="extra-inputs"></div>
    <div class="input-icon input-else">
        <input type="text" placeholder="<?php _e('equation...', 'wc-kalkulator'); ?>" value=""><i></i>
    </div>
    <button type="button" class="button add-condition"><?php _e('Add condition', 'wc-kalkulator'); ?></button>
</div>

<div class="expression_addon">
    <p><?php _e('If the "if" condition is met, the price from the "add" field will be added to the price of the product.
    Unlike the conditional expression, Price Add-ons allows you to add multiple amounts to the price of a product.', 'wc-kalkulator'); ?></p>
    <div id="addon-inputs"></div>
    <button type="button" class="button add-addon"><?php _e('New addon', 'wc-kalkulator'); ?></button>
</div>

<div class="expression_off">
    <p>
        <?php _e('Price calculation has been disabled.', 'wc-kalkulator'); ?>
    </p>
</div>
