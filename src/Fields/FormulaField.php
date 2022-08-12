<?php

namespace WCKalkulator\Fields;

use WCKalkulator\View;

/**
 * Class FormulaField
 * @package WCKalkulator
 */
class FormulaField extends HtmlField
{
    protected $parameters = array("type", "name", "content", "title");
    protected $default_data = array("required" => false, "content" => "");
    protected $data;
    protected $type = "formula";
    protected $admin_title;
    protected $use_expression = false;
    protected $group = "special";
    protected $show_title = true;

    /**
     * Output HTML for fields at backend.
     * @param $value
     * @return string
     */
    public function admin_fields($value = '')
    {
        $this->admin_title = __("Value of Formula", "wc-kalkulator");
        return View::render('fields/admin/' . $this->type);
    }

    /**
     * Output HTML for product page
     * @param $value
     * @return string
     */
    public function render_for_product($value = "")
    {
        return "";
    }

    /**
     * No need to show hidden field in the user's cart
     * @param $value
     * @return string
     */
    public function render_for_cart($value = '')
    {
        if($this->data('display_on_user_cart') === '1') {
            return View::render('fields/cart', array(
                'title' => $this->data['title'],
                'value' => $value
            ));
        }
        return "";
    }

    /**
     * No need to validate
     * @param $value
     * @return bool
     */
    public function validate($value)
    {
        return true;
    }

}