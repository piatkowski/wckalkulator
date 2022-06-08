<?php

namespace WCKalkulator\Fields;

use WCKalkulator\View;

/**
 * Class CheckboxgroupField
 * @package WCKalkulator
 */
class CheckboxgroupField extends RadiogroupField
{
    protected $type = "checkboxgroup";
    protected $use_expression = false;
    
    /**
     * Output HTML for fields at backend.
     * @param $value
     * @return string
     */
    public function admin_fields($value = '')
    {
        $this->admin_title = __("Checkbox Group", "wc-kalkulator");
        return View::render('fields/admin/select');
    }
    
    /**
     * Output HTML for User's cart nad order meta
     * @param $value
     * @return string
     */
    public function render_for_cart($value = '')
    {
        if (is_array($value)) {
            foreach ($value as $key => $val) {
                $value[$key] = $this->get_option_title($val);
            }
            $value = join(", ", $value);
        } else {
            $value = $this->get_option_title($value);
        }
        
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
        if(is_array($value)) {
            foreach($value as $val) {
                if (!in_array($val, $this->data["options_name"])) {
                    return false;
                }
                return true;
            }
        }
        // @todo: max select checkboxs
        /**
         * if $value is not an array
         */
        return in_array($value, $this->data["options_name"]);
    }
}