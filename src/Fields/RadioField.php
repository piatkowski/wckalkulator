<?php

namespace WCKalkulator\Fields;

use WCKalkulator\View;

/**
 * Class RadioField
 * @package WCKalkulator
 */
class RadioField extends SelectField
{
    protected $type = "radio";
    protected $group = "select";
    
    /**
     * Output HTML for fields at backend.
     * @param $value
     * @return string
     */
    public function admin_fields($value = '')
    {
        $this->admin_title = __("Radio", "wc-kalkulator");
        return View::render('fields/admin/select');
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
        
        return View::render('fields/front/radio', $args);
    }
 
}