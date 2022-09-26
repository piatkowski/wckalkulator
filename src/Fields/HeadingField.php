<?php

namespace WCKalkulator\Fields;

use WCKalkulator\View;

/**
 * Class StaticHtmlField
 * @package WCKalkulator
 */
class HeadingField extends HtmlField
{
    protected $parameters = array("type", "name", "content", "level");
    protected $default_data = array("required" => false, "content" => "", "level" => "1");
    protected $data;
    protected $type = "heading";
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
        $this->admin_title = __("Heading", "wc-kalkulator");
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
            'name' => $this->data('name'),
            'content' => $this->data('content'),
            'level' => $this->data('level')
        ));
    }
}