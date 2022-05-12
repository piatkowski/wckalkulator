<?php

namespace WCKalkulator\Fields;

use WCKalkulator\View;

/**
 * Class FileuploadFiled
 * @package WCKalkulator
 */
class FileuploadField extends AbstractField
{
    protected $parameters = array("type", "name", "title", "hint", "css_class", "required", "max_file_count", "max_file_size", "allowed_extensions");
    protected $default_data = array("css_class" => "", "required" => false, "default_value" => "", "hint" => "");
    protected $data;
    protected $type = "fileupload";
    protected $admin_title;
    protected $use_expression = false;
    protected $group = "upload";
    
    /**
     * Output HTML for fields at backend.
     * @param $value
     * @return string
     */
    public function admin_fields($value = '')
    {
        $this->admin_title = __("File Upload Field", "wc-kalkulator");
        return View::render('fields/admin/fileupload');
    }
    
    /**
     * Output HTML for product page
     * @param $value
     * @return string
     */
    public function render_for_product($value = "")
    {
        $args = $this->prepared_data();
        return View::render('fields/front/fileupload', $args);
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
            'value' => $value
        ));
    }
    
    /**
     * Run all validation tests
     * @param $value
     * @return bool
     */
    public function validate($value)
    {
        if(!$this->is_required() && empty($value)) {
            return true;
        }
        
        $is_required_and_nonempty = true;
        if ($this->data['required']) {
            if (empty($value) || $value === "") {
                $is_required_and_nonempty = false;
            }
        }
        
        return $is_required_and_nonempty;
    }
    
}