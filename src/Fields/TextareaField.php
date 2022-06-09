<?php

namespace WCKalkulator\Fields;

use WCKalkulator\View;

/**
 * Class TextField
 * @package WCKalkulator
 */
class TextareaField extends TextField
{
    protected $type = "textarea";
    protected $group = "input";
    
    /**
     * @param string $value
     * @return string
     */
    public function admin_fields($value = '')
    {
        $html = parent::admin_fields($value);
        $this->admin_title = __("Textarea", "wc-kalkulator");
        return $html;
    }
    
    /**
     * Output HTML for product page
     * @param $value
     * @return string
     */
    public function render_for_product($value = "")
    {
        $args = $this->prepared_data();
        $args['placeholder'] = esc_html($this->data["default_value"]);
        $args['min_length'] = $this->data["min"];
        $args['max_length'] = $this->data["max"];
        $args['value'] = $value;
        
        return View::render('fields/front/textarea', $args);
    }
}