<?php

namespace WCKalkulator\Fields;

use WCKalkulator\View;

/**
 * Class EmptyField
 * @package WCKalkulator
 */
class EmptyField extends AbstractField
{
    protected $parameters = array("type", "name", "content");
    protected $default_data = array("required" => false);
    protected $data;
    protected $type = "empty";
    protected $admin_title;
    protected $use_expression = false;
    protected $group = "static";

    /**
     * Output HTML for fields at backend.
     *
     * @param $value
     * @return string
     */
    public function admin_fields($value = '')
    {
        $this->admin_title = __("Empty", "wc-kalkulator");
        return View::render('fields/admin/' . $this->type);
    }

    /**
     * Output HTML for product page
     *
     * @param $value
     * @return string
     */
    public function render_for_product($value = "")
    {
        return '';
    }

    /**
     * No need to show static field in the user's cart
     *
     * @param $value
     * @return string
     */
    public function render_for_cart($value = '')
    {
        return '';
    }

    /**
     * No need to validate static field
     *
     * @param $value
     * @return bool
     */
    public function validate($value)
    {
        return true;
    }

    /**
     * No need to display the field in order line item
     *
     * @param $value
     */
    public function order_item_value($value)
    {
        return;
    }

}