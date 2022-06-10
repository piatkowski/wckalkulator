<?php

namespace WCKalkulator\Fields;

use WCKalkulator\View;

/**
 * Class DropdownField
 * @package WCKalkulator
 */
class DropdownField extends AbstractField
{
    protected $parameters = array("type", "name", "title", "hint", "options_title", "css_class", "required", "default_value", "price");
    protected $default_data = array("css_class" => "", "required" => false, "default_value" => "", "hint" => "");
    protected $data;
    protected $type = "dropdown";
    protected $admin_title;
    protected $use_expression = false;
    protected $group = "select";
    
    /**
     * Output HTML for fields at backend.
     * @param $value
     * @return string
     */
    public function admin_fields($value = '')
    {
        $this->admin_title = __("Dropdown", "wc-kalkulator");
        return View::render('fields/admin/dropdown');
    }
    
    /**
     * Output HTML for product page
     * @param $selected_name
     * @return string
     */
    public function render_for_product($selected_name = "")
    {
        if ($selected_name === "") {
            $selected_name = $this->data["default_value"];
        }
        $args = $this->prepared_data();
        $args['value'] = $selected_name;
        $args['options_title'] = $this->data['options_title'];
    
        return View::render('fields/front/dropdown', $args);
        
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
     * Run validation tests
     * @param $value
     * @return bool
     */
    public function validate($value)
    {
        if (!$this->is_required() && empty($value)) {
            return true;
        }
        return in_array($value, $this->data["options_title"]);
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