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
<hr />
<p class="off-hide">
    <a href="http://wckalkulator.com/usermanual/expression-syntax/"
       target="_blank"><?php _e('Read about syntax, functions and operators', 'wc-kalkulator'); ?></a>
</p>
<p>
    <?php _e('Insert field', 'wc-kalkulator'); ?>:
    <select id="wck-parameters">
        <optgroup label="Defined Fields" class="defined-fields"></optgroup>
        <optgroup label="Product">
            <option value="{product_price}">Price</option>
            <option value="{product_regular_price}">Regular price</option>
            <option value="{product_weight}">Weight</option>
            <option value="{product_width}">Width</option>
            <option value="{product_height}">Height</option>
            <option value="{product_length}">Length</option>
            <option value="{quantity}">Quantity</option>
        </optgroup>
        <optgroup label="Current Visitor">
            <option value="{is_user_logged}">Is logged?</option>
        </optgroup>
        <optgroup label="Current Date/Time">
            <option value="{current_month}">Month (actual value: <?php echo (int)current_time("n"); ?>)</option>
            <option value="{day_of_month}">Day of the month (actual value: <?php echo (int)current_time("j"); ?>)
            </option>
            <option value="{day_of_week}">Day of the week (actual value: <?php echo (int)current_time("w"); ?>)</option>
            <option value="{current_hour}">Current hour (actual value: <?php echo (int)current_time("G"); ?>)</option>
        </optgroup>
        <optgroup label="Global Parameters" class="global-parameters"></optgroup>
    </select>
    <button type="button" class="button add-field-to-formula"><?php _e('Insert', 'wc-kalkulator'); ?></button>
    <!---   <label><strong>Fields:</strong> </label>
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
       <a href="#" class="savefields">Update list</a> -->
</p>
<p>
    <?php
    $operators = array(
        '+' => __('Add', 'wc-kalkulator'),
        '-' => __('Subtract', 'wc-kalkulator'),
        '*' => __('Multiply', 'wc-kalkulator'),
        '/' => __('Divide', 'wc-kalkulator'),
        '%' => __('Modulus', 'wc-kalkulator'),
        '**' => __('Power', 'wc-kalkulator'),
        '==' => __('Equal', 'wc-kalkulator'),
        '!=' => __('Not equal', 'wc-kalkulator'),
        '<' => __('Less than', 'wc-kalkulator'),
        '<=' => __('Less or equal', 'wc-kalkulator'),
        '>' => __('Grater than', 'wc-kalkulator'),
        '>=' => __('Greater or equal', 'wc-kalkulator'),
        'and' => __('Logical and', 'wc-kalkulator'),
        'or' => __('Logical or', 'wc-kalkulator'),
        'not' => __('Logical not', 'wc-kalkulator')
    );
    foreach ($operators as $op => $title) {
        echo '<button type="button" class="add-operator button" value=" ' . esc_attr($op) . ' " title="' . esc_attr($title) . '">' . esc_html($op) . '</button>';
    }
    echo ' | Functions: ';
    $operators = array(
        'round' => __('round(x, p) - round x with the precision of p', 'wc-kalkulator'),
        'ceil' => __('ceil(x) - round up to the integer number', 'wc-kalkulator'),
        'floor' => __('floor(x) - round down to the integer number', 'wc-kalkulator'),
        'abs' => __('abs(x) - absolute number', 'wc-kalkulator'),
        'max' => __('max(a,b,...) - maximal value', 'wc-kalkulator'),
        'min' => __('min(a,b,...) - minimal value', 'wc-kalkulator'),
        'sqrt' => __('sqrt(x) - square root of x', 'wc-kalkulator'),
        'strlen' => __('strlen(x) - Text length of x', 'wc-kalkulator')
    );
    foreach ($operators as $op => $title) {
        echo '<button type="button" class="add-operator button" value=" ' . esc_attr($op) . '( " title="' . esc_attr($title) . '">' . esc_html($op) . '</button>';
    }
    ?>
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

<div class="inventory">
    <hr />
    <p>
        <label>
            <strong><?php _e('Inventory - Stock quantity reduction multiplier', 'wc-kalkulator'); ?></strong>  <sup style="color:red">Beta</sup>
        </label>
    </p>
    <p><?php _e('In this section you can define inventory reduction multiplier (formula or number). Reduction will be rouded up to the integer number. For example: stock quantity = 1000. Customer buys 3 pieces and the reduction is set to 10, the stock quantity will be reduced by 30 (3*10)', 'wc-kalkulator'); ?></p>
    <?php
        global $post;
        $reduction_m = get_post_meta($post->ID, '_wck_stock_reduction_multiplier', true);
    ?>
    <div class="input-icon input-stock">
        <input type="text" name="_wck_stock_reduction_multiplier" placeholder="<?php _e('Number or formula (value will be multiplied by quantity)...', 'wc-kalkulator'); ?>"
               value="<?php echo esc_html($reduction_m); ?>"><i></i>
    </div>
    <p class="description">You can use fields, operators and functions as above. You can modify stock quantity (i.e. add unit) using filter `woocommerce_format_stock_quantity`.</p>
</div>
