<?php

namespace WCKalkulator\Fields;

use WCKalkulator\View;

/**
 * Class HtmlField
 * @package WCKalkulator
 */
class HtmlField extends AbstractField
{
    protected $parameters = array("type", "name", "content");
    protected $default_data = array("required" => false, "content");
    protected $data;
    protected $type = "html";
    protected $admin_title;
    protected $use_expression = false;
    protected $group = "static";

    /**
     * Output HTML for fields at backend.
     * @param $value
     * @return string
     */
    public function admin_fields($value = '')
    {
        $this->admin_title = __("HTML", "wc-kalkulator");
        return View::render('fields/admin/' . $this->type);
    }

    /**
     * Output HTML for product page
     * @param $value
     * @return string
     */
    public function render_for_product($value = "")
    {
        return View::render('fields/front/' . $this->type, array(
            'content' => $this->data('content')
        ));
    }

    /**
     * No need to show static field in the user's cart
     * @param $value
     * @return string
     */
    public function render_for_cart($value = '')
    {
        return;
    }

    /**
     * No need to validate static field
     * @param $value
     * @return bool
     */
    public function validate($value)
    {
        return true;
    }

}