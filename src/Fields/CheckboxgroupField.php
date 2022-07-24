<?php

namespace WCKalkulator\Fields;

use WCKalkulator\View;

/**
 * Class CheckboxgroupField
 * @package WCKalkulator
 */
class CheckboxgroupField extends SelectField
{
    protected $type = "checkboxgroup";
    protected $default_data = array("css_class" => "", "required" => false, "default_value" => "", "hint" => "", "select_limit" => 0);

    /**
     * Output HTML for fields at backend.
     * @param $value
     * @return string
     */
    public function admin_fields($value = '')
    {
        $this->admin_title = __("Multi Checkbox", "wc-kalkulator");
        return View::render('fields/admin/' . $this->type);
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
            'value' => $this->order_item_value($value)
        ));
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
        $args['select_limit'] = absint($this->data('select_limit'));

        return View::render('fields/front/' . $this->type, $args);
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

        if (is_array($value)) {

            if (((int)$this->data('select_limit') > 0 && count($value) > $this->data('select_limit')) || ($this->is_required() && count($value) === 0)) {
                return false;
            }

            foreach ($value as $val) {
                if (!in_array($val, $this->data["options_name"])) {
                    return false;
                }
                return true;
            }
        }

        /**
         * if $value is not an array
         */

        if ($this->is_required() && empty($value)) {
            return false;
        }

        return in_array($value, $this->data["options_name"]);
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
        if (is_array($value)) {
            foreach ($value as $key => $val) {
                $value[$key] = $this->get_option_title($val);
            }
            $value = join(", ", $value);
        } else {
            $value = $this->get_option_title($value);
        }
        return $value;
    }
}