<?php

namespace WCKalkulator\Fields;

use WCKalkulator\View;

/**
 * Class NumberField
 * @package WCKalkulator
 */
class NumberField extends AbstractField
{
    protected $parameters = array("type", "name", "title", "hint", "min", "max", "css_class", "required", "default_value");
    protected $default_data = array("css_class" => "", "required" => true, "default_value" => "", "hint" => "");
    protected $data;
    protected $type = "number";
    protected $admin_title;
    protected $use_expression = true;
    protected $group = "input";
    
    /**
     * Output HTML for fields at backend.
     * @param $value
     * @return string
     */
    public function admin_fields($value = "")
    {
        $this->admin_title = __("Number Field", "wc-kalkulator");
        return View::render('fields/admin/number');
    }
    
    /**
     * Output HTML for product page
     * @param $value
     * @return string
     */
    public function render_for_product($value = "")
    {
        if ($value === "") {
            $value = $this->data["default_value"];
            if ($value === "") {
                $value = $this->data["min"];
            }
        }
        
        $args = $this->prepared_data();
        $args['min'] = $this->data["min"];
        $args['max'] = $this->data["max"];
        $args['value'] = $value;
        
        return View::render('fields/front/number', $args);
    }
    
    /**
     * Output HTML for User's cart nad order meta
     * @param $value
     * @return string
     */
    public function render_for_cart($value = '')
    {
        return View::render('fields/cart', array(
            'title' => $this->data['title'],
            'value' => $value
        ));
    }
    
    /**
     * Run all validation tests
     * @param $value
     * @return bool
     */
    public function validate($value)
    {
        if (!$this->is_required() && empty($value)) {
            return true;
        }
        $is_greater_than_min = $value >= $this->data["min"];
        $is_less_than_max = $value <= $this->data["max"];
        $is_numeric = is_numeric($value);
        return $is_greater_than_min && $is_less_than_max && $is_numeric;
    }
    
    /**
     * Display value of the field in order line item at backend
     *
     * @param $value
     * @return string
     * @since 1.2.0
     */
    public function order_item_value($value)
    {
        return $value;
    }
    
}