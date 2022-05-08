<?php

namespace WCKalkulator\Fields;

use WCKalkulator\View;

/**
 * Class EmailField
 * @package WCKalkulator
 */
class EmailField extends TextField
{
    protected $type = "email";
    protected $group = "input";
    
    /**
     * @param string $value
     * @return string
     */
    public function admin_fields($value = '')
    {
        $html = parent::admin_fields($value);
        $this->admin_title = __("E-mail Field", "wc-kalkulator");
        return $html;
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
        
        return View::render('fields/front/email', $args);
    }
    
    /**
     * Run all validation tests
     * @param $value
     * @return bool
     */
    public function validate($value)
    {
        $is_email_valid = empty($value) ? true : sanitize_email($value);
        
        return parent::validate($value) && $is_email_valid;
    }
    
}