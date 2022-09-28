<?php

namespace WCKalkulator\Fields;

use WCKalkulator\View;

/**
 * Class ProductBundleCheckboxField
 * @package WCKalkulator
 */
class ProductBundleCheckboxField extends CheckboxgroupField
{
    protected $type = "productbundlecheckbox";
    protected $default_data = array("css_class" => "", "required" => false, "default_value" => "", "hint" => "", "select_limit" => 0);
    protected $use_expression = false;

    /**
     * Output HTML for fields at backend.
     * @param $value
     * @return string
     */
    public function admin_fields($value = '')
    {
        $this->admin_title = __("Product Bundle Multi-Checkbox", "wc-kalkulator");
        return View::render('fields/admin/' . $this->type);
    }

    public function render_for_cart($value = '')
    {
        return "";
    }
}