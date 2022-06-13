<?php

namespace WCKalkulator\Fields;

use WCKalkulator\View;

/**
 * Class HiddenField
 * @package WCKalkulator
 */
class HiddenField extends HtmlField
{
    protected $parameters = array("type", "name", "content", "title");
    protected $default_data = array("required" => false, "content" => "");
    protected $data;
    protected $type = "hidden";
    protected $admin_title;
    protected $use_expression = false;
    protected $group = "static";
    protected $show_title = true;
    
    /**
     * Output HTML for fields at backend.
     * @param $value
     * @return string
     */
    public function admin_fields($value = '')
    {
        $this->admin_title = __("Hidden", "wc-kalkulator");
        return View::render('fields/admin/' . $this->type);
    }
    
    /**
     * Output HTML for product page
     * @param $value
     * @return string
     */
    public function render_for_product($value = "")
    {
        $args = $this->prepared_data();
        $args['content'] = $this->data('content');
        return View::render('fields/front/' . $this->type, $args);
    }
    
    /**
     * No need to show hidden field in the user's cart
     * @param $value
     * @return string
     */
    public function render_for_cart($value = '')
    {
        return;
    }
    
    /**
     * No need to validate hidden static field
     * @param $value
     * @return bool
     */
    public function validate($value)
    {
        return stripslashes($value) === ($this->data('title') . ': '. $this->data('content'));

    }
    
}