<?php

namespace WCKalkulator\Fields;

use WCKalkulator\View;

/**
 * Class SelectField
 * @package WCKalkulator
 */
class SelectField extends AbstractField
{
    protected $parameters = array("type", "name", "title", "hint", "options_name", "options_title", "css_class", "required", "default_value");
    protected $default_data = array("css_class" => "", "required" => true, "default_value" => "", "hint" => "");
    protected $data;
    protected $type = "select";
    protected $admin_title;
    protected $use_expression = true;
    protected $group = "select";
    
    /**
     * Output HTML for fields at backend.
     * @param $value
     * @return string
     */
    public function admin_fields($value = '')
    {
        $this->admin_title = __("Select", "wc-kalkulator");
        return View::render('fields/admin/' . $this->type);
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
        $args['options_name'] = $this->data['options_name'];
        $args['options_title'] = $this->data['options_title'];
        
        return View::render('fields/front/' . $this->type, $args);
    }
    
    /**
     * Output HTML for User's cart nad order meta
     * @param $value
     * @return string
     */
    public function render_for_cart($value = '')
    {
        $value = $this->get_option_title($value);
        
        return View::render('fields/cart', array(
            'title' => $this->data['title'],
            'value' => $value
        ));
    }
    
    /**
     * Return option title based on option value from select field
     * @param $value
     * @return string
     */
    public function get_option_title($value)
    {
        $id = array_search($value, $this->data['options_name']);
        return $this->data['options_title'][$id];
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

        return in_array($value, $this->data["options_name"]);
    }
    
    /**
     * Return option value based on option title from select field
     * @param $title
     * @return string
     */
    public function get_option_value($title)
    {
        $id = array_search($title, $this->data['options_title']);
        return $this->data['options_name'][$id];
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
        return $this->get_option_title($value);
    }
}