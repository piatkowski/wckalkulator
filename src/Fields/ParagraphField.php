<?php

namespace WCKalkulator\Fields;

use WCKalkulator\View;

/**
 * Class ParagraphField
 * @package WCKalkulator
 */
class ParagraphField extends HtmlField
{
    protected $parameters = array("type", "name", "content");
    protected $default_data = array("required" => false, "content");
    protected $data;
    protected $type = "paragraph";
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
        $this->admin_title = __("Paragraph", "wc-kalkulator");
        return View::render('fields/admin/' . $this->type);
    }
    
}