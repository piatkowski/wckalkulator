<?php

namespace WCKalkulator\Fields;

use WCKalkulator\View;

/**
 * Class RadiogroupField
 * @package WCKalkulator
 */
class RadiogroupField extends SelectField
{
    protected $parameters = array("type", "name", "title", "hint", "options_name", "options_title", "css_class", "required", "default_value");
    protected $default_data = array("css_class" => "", "required" => true, "default_value" => "", "hint" => "");
    protected $data;
    protected $type = "radiogroup";
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
        $this->admin_title = __("Radio Group", "wc-kalkulator");
        return View::render('fields/admin/select');
    }
}