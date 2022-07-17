<?php

namespace WCKalkulator\Fields;

use WCKalkulator\View;

/**
 * Class CheckboxField
 * @package WCKalkulator
 */
class CheckboxField extends AbstractField
{
    protected $parameters = array("type", "name", "title", "hint", "css_class", "required", "default_state", "price");
    protected $default_data = array("css_class" => "", "required" => false, "default_state" => "", "hint" => "");
    protected $data;
    protected $type = "checkbox";
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
        $this->admin_title = __("Checkbox", "wc-kalkulator");
        return View::render('fields/admin/checkbox');
    }
    
    /**
     * Output HTML for product page
     * @param $value
     * @return string
     */
    public function render_for_product($value = 0)
    {
        $value = (int)$value;
        $args = $this->prepared_data();
        $args['value'] = $value === 1;
        $args['default_state'] = $this->data["default_state"];
        $args['checked'] = ($value === 1) || ($value === 0 && (int)$this->data["default_state"] === 1);
        return View::render('fields/front/checkbox', $args);
    }
    
    /**
     * Output HTML for User's cart nad order meta
     * @param $value
     * @return string
     */
    public function render_for_cart($value = 0)
    {
        $checked = (int)$value === 1;
        return View::render('fields/cart', array(
            'title' => $this->data['title'],
            'value' => ($checked ? __('yes', 'wc-kalkulator') : __('no', 'wc-kalkulator'))
        ));
    }
    
    /**
     * Run all validation tests
     * @param $value
     * @return bool
     */
    public function validate($value)
    {
        return empty($value) || (int)$value === 1;
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
        return (int)$value === 1 ? __('yes', 'wc-kalkulator') : __('no', 'wc-kalkulator');
    }
    
}