<?php

namespace WCKalkulator\Fields;

use WCKalkulator\View;

/**
 * Class ImageselectField
 * @package WCKalkulator
 */
class ImageselectField extends SelectField
{
    protected $parameters = array("type", "name", "title", "hint", "options_name", "options_title", "options_image", "css_class", "required", "default_value");
    protected $default_data = array("css_class" => "", "required" => false, "default_value" => "", "hint" => "", "image_size" => 60);
    protected $type = "imageselect";
    
    /**
     * Output HTML for fields at backend.
     * @param $value
     * @return string
     */
    public function admin_fields($value = '')
    {
        $this->admin_title = __("Select Image", "wc-kalkulator");
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
            'value' => $this->get_option_title($value),
            'image' => $this->get_option_image($value),
        ));
    }
    
    /**
     * Return option title based on option value from select field
     * @param $value
     * @return string
     */
    public function get_option_image($value)
    {
        $id = array_search($value, $this->data['options_name']);
        return wp_get_attachment_image_url( $this->data['options_image'][ $id ] );
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
        $args['options_image'] = $this->data['options_image'];
        $args['size'] = $this->data("image_size");
        
        return View::render('fields/front/' . $this->type, $args);
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
        $img = $this->get_option_image($value);
        $title = $this->get_option_title($value);
        return $title . ' <a href="'.$img.'" target="_blank"><img src="'.$img.'" style="max-width:60px" /></a>';
    }
}