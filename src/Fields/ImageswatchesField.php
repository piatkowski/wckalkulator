<?php

namespace WCKalkulator\Fields;

use WCKalkulator\View;

/**
 * Class ImageswatchesField
 * @package WCKalkulator
 */
class ImageswatchesField extends ImageselectField
{
    protected $type = "imageswatches";
    
    /**
     * Output HTML for fields at backend.
     * @param $value
     * @return string
     */
    public function admin_fields($value = '')
    {
        $this->admin_title = __("Image Swatches", "wc-kalkulator");
        return View::render('fields/admin/imageselect');
    }
 
}