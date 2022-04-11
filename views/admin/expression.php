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
    <label for="choose_expression_type_conditional">
        <?php _e('Conditional Expression', 'wc-kalkulator'); ?>
        <?php echo Helper::html_help_tip(__('If the condition [if] is met, calculate the price according to the assigned formula [=]. You can use multiple conditions. The [else] formula is used when none of the conditions are met.', 'wc-kalkulator')); ?>
    </label>
</p>
<p class="off-hide">
    <label><strong>Fields:</strong> </label>
    <span class="formula-field">{product_price}</span>
    <span class="formula-field">{product_regular_price}</span>
    <span class="formula-field">{quantity}</span>
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
    <button type="button" class="button add-condition"><?php _e('Add contition', 'wc-kalkulator'); ?></button>
</div>

<div class="expression_off">
    <p>
        <?php _e('Price calculation has been disabled.', 'wc-kalkulator'); ?>
    </p>
</div>
