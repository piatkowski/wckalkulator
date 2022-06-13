<?php

namespace WCKalkulator\Fields;

use WCKalkulator\View;

/**
 * Class LinkField
 * @package WCKalkulator
 */
class LinkField extends HtmlField
{
    protected $parameters = array("type", "name", "content", "title", "target");
    protected $default_data = array("required" => false, "content" => "", "target" => "_blank");
    protected $data;
    protected $type = "link";
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
        $this->admin_title = __("Link / URL", "wc-kalkulator");
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
        $args['target'] = $this->data('target');
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
        return true;
        
    }
    
}