<?php

namespace WCKalkulator\Fields;

use WCKalkulator\Plugin;
use WCKalkulator\View;

/**
 * Class DatepickerField
 * @package WCKalkulator
 */
class RangedatepickerField extends DatepickerField
{
    protected $type = "rangedatepicker";
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
            'wck-range-date-picker',
            Plugin::url() . '/assets/js/rangedatepicker.min.js',
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
            'script' => 'wck-range-date-picker',
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
        $html = parent::admin_fields();
        $this->admin_title = __("Range Date Picker", "wc-kalkulator");
        return $html;
    }
    
    /**
     * Output HTML for product page
     * @param $value
     * @return string
     */
    public function render_for_product($value = array())
    {
        $args = $this->prepared_data();
        $args['value_from'] = isset($value['from']) ? $value['from'] : '';
        $args['value_to'] = isset($value['to']) ? $value['to'] : '';
        $args['disallow_past_date'] = $this->data["disallow_past_date"];
    
        return View::render('fields/front/rangedatepicker', $args);
    }
    
    /**
     * Output HTML for User's cart nad order meta
     * @param $value
     * @return string
     */
    public function render_for_cart($value = array())
    {
        return View::render('fields/cart', array(
            'title' => $this->data['title'],
            'value' => $value["from"] . ' - ' . $value["to"]
        ));
    }
    
    /**
     * @param $value
     * @return bool
     * @throws \Exception
     */
    public function validate($value)
    {
        if (!isset($value["from"]) || !isset($value["to"]) || count($value) != 2) {
            error_log($this->data("name") . " does not exists or is not an array of 2 items");
            return false;
        }
        
        if ($this->is_required()) {
            if (empty($value["from"]) || empty($value["to"])) {
                error_log($this->data("name") . " is required and has empty values");
                return false;
            }
        } else {
            if (empty($value["from"]) xor empty($value["to"])) {
                error_log($this->data("name") . " is not required, but has incorrect value");
                return false;
            }
            if (empty($value["from"]) && empty($value["to"])) {
                return true;
            }
        }
        
        //1. Check correct date
        $count_valid = 0;
        foreach ($value as $date) {
            $date_arr = explode('-', $date);
            if (count($date_arr) === 3) {
                $year = $date_arr[0];
                $month = $date_arr[1];
                $day = $date_arr[2];
                $count_valid += checkdate($month, $day, $year) ? 1 : 0;
            }
        }
        $is_valid = $count_valid === 2;
        if (!$is_valid) {
            error_log($this->data("name") . " - date is incorrect " . print_r($value, true) . " = " . $count_valid);
        }
        //2. Check past date option
        if ($is_valid) {
            foreach ($value as $date) {
                if ($this->data("disallow_past_date")) {
                    try {
                        $datetime = new \DateTime($date);
                        $datetime->setTime(0, 0, 0);
                        $now = new \DateTime();
                        $now->setTime(0, 0, 0);
                    } catch (\Exception $e) {
                        error_log($this->data("name") . " - DateTime exception");
                        return false;
                    }
                    if ($datetime < $now) {
                        error_log($this->data("name") . " - past date is not allowed");
                        return false;
                    }
                }
            }
            //3. Check date "from" is earlier than date "to"
            $date_from = new \DateTime($value["from"]);
            $date_from->setTime(0, 0, 0);
            $date_to = new \DateTime($value["to"]);
            $date_to->setTime(0, 0, 0);
            
            if ($date_from > $date_to) {
                error_log($this->data("name") . " date A is greater than date B");
                return false;
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
        return $value["from"] . ' - ' . $value["to"];
    }
}