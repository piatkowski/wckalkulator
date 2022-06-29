<?php

namespace WCKalkulator\Fields;

use WCKalkulator\Plugin;
use WCKalkulator\View;

/**
 * Class DatepickerField
 * @package WCKalkulator
 */
class DatepickerField extends TextField
{
    protected $parameters = array("type", "name", "title", "hint", "css_class", "required", "disallow_past_date", "price");
    protected $default_data = array("css_class" => "", "required" => false, "disallow_past_date" => false, "hint" => "");
    protected $type = "datepicker";
    protected $group = "picker";
    /**
     * Called in enqueue_scripts action
     */
    public function enqueue_scripts()
    {
        wp_enqueue_style(
            'wck-jquery-ui-css',
            Plugin::url() . '/assets/css/jquery-ui.min.css'
        );
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script(
            'wck-date-picker',
            Plugin::url() . '/assets/js/datepicker.min.js',
            array('jquery-ui-datepicker'),
            Plugin::VERSION,
            1
        );
    }
    
    /**
     * @return array
     */
    public function localize_script()
    {
        $options = array(
            'dateFormat' => 'yy-mm-dd',
            'minDate' => $this->data('disallow_past_date') ? '0' : null
        );
        
        return array(
            'script' => 'wck-date-picker',
            'field_name' => $this->data("name"),
            'options' => $options
        );
    }
    
    /**
     * Output HTML for fields at backend.
     * @param $value
     * @return string
     */
    public function admin_fields($value = '')
    {
        $this->admin_title = __("Date Picker", "wc-kalkulator");
        return View::render('fields/admin/datepicker');
    }
    
    /**
     * Output HTML for product page
     * @param $value
     * @return string
     */
    public function render_for_product($value = "")
    {
        $args = $this->prepared_data();
        $args['value'] = $value;
        $args['disallow_past_date'] = $this->data["disallow_past_date"];
        
        return View::render('fields/front/datepicker', $args);
    }
    
    /**
     * Run validation tests
     * @param $value
     * @return bool
     */
    public function validate($value)
    {
        if (!$this->is_required() && empty($value)) {
            return true;
        }
        //yyyy-mm-dd
        $is_valid = false;
        $date_arr = explode('-', $value);
        if (count($date_arr) === 3) {
            $year = $date_arr[0];
            $month = $date_arr[1];
            $day = $date_arr[2];
            $is_valid = checkdate($month, $day, $year);
        }
        if (!$is_valid) {
            error_log($this->data("name") . " - date is incorrect " . $value);
        }
    
        if ($is_valid && $this->data("disallow_past_date")) {
            try {
                $date = new \DateTime($value);
                $date->setTime(0, 0, 0);
                $now = new \DateTime();
                $now->setTime(0, 0, 0);
            } catch(\Exception $e) {
                error_log($this->data("name") . " - DateTime exception");
                return false;
            }
            $is_valid = $date >= $now;
            if (!$is_valid) {
                error_log($this->data("name") . " - past date is not allowed");
            }
        }
        return $is_valid;
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
        return $this->get_option_title($value);
    }
}