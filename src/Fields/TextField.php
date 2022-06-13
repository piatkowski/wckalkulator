<?php

namespace WCKalkulator\Fields;

use WCKalkulator\View;

/**
 * Class TextField
 * @package WCKalkulator
 */
class TextField extends AbstractField
{
    protected $parameters = array("type", "name", "title", "hint", "css_class", "required", "default_value", "min", "max", "price", "pattern");
    protected $default_data = array("css_class" => "", "required" => false, "default_value" => "", "hint" => "", "pattern" => "");
    protected $data;
    protected $type = "text";
    protected $admin_title;
    protected $use_expression = true;
    protected $group = "input";
    
    /**
     * Output HTML for fields at backend.
     * @param $value
     * @return string
     */
    public function admin_fields($value = '')
    {
        $this->admin_title = __("Text", "wc-kalkulator");
        return View::render('fields/admin/text');
    }
    
    /**
     * Output HTML for product page
     * @param $value
     * @return string
     */
    public function render_for_product($value = "")
    {
        $args = $this->prepared_data();
        $args['placeholder'] = esc_html($this->data["default_value"]);
        $args['min_length'] = $this->data["min"];
        $args['max_length'] = $this->data["max"];
        $args['value'] = $value;
        $args['pattern'] = $this->data('pattern');
        
        return View::render('fields/front/text', $args);
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
        if(!$this->is_required() && empty($value)) {
            return true;
        }
        
        $length = strlen(esc_html($value));
        
        if (intval($this->data["min"]) > 0)
            $is_longer_than_min = $length >= $this->data["min"];
        else
            $is_longer_than_min = true;
        
        if (intval($this->data["max"]) > 0)
            $is_shorter_than_max = $length <= $this->data["max"];
        else
            $is_shorter_than_max = true;
        
        
        $is_required_and_nonempty = true;
        if ($this->data['required']) {
            if (empty($value) || $value === "") {
                $is_required_and_nonempty = false;
            }
        }
        
        return $is_longer_than_min && $is_shorter_than_max && $is_required_and_nonempty;
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