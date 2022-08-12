<?php

namespace WCKalkulator;

/**
 * Class Sanitizer
 *
 * This Class handles sanitization on the user input data.
 *
 * @package WCKalkulator
 * @author Krzysztof Piątkowski
 * @license GPLv2
 * @since 1.1.0
 */
final class Sanitizer
{
    const MAX_DEPTH = 1000;
    
    /**
     * Allowed HTML tags for wp_kses function
     *
     * @return array
     */
    public static function allowed_html()
    {
        $allowed = array(
            'input' => array(
                'name' => true,
                'type' => true,
                'value' => true,
                'checked' => true,
                'id' => true,
                'class' => true,
                'placeholder' => true,
                'minlength' => true,
                'maxlength' => true,
                'min' => true,
                'max' => true,
                'step' => true,
                'required' => true,
                'readonly' => true,
                'data-type' => true,
                'data-group' => true,
                'data-required' => true,
                'data-limit' => true,
                'style' => true,
                'pattern' => true,
                'title' => true,
                'data-extension' => true,
                'accept' => true,
                'onchange' => true,
                'data-maxfilesize' => true,
            ),
            'textarea' => array(
                'name' => true,
                'type' => true,
                'value' => true,
                'checked' => true,
                'id' => true,
                'class' => true,
                'placeholder' => true,
                'minlength' => true,
                'maxlength' => true,
                'required' => true,
                'style' => true
            ),
            'select' => array(
                'name' => true,
                'type' => true,
                'value' => true,
                'checked' => true,
                'id' => true,
                'class' => true,
                'placeholder' => true,
                'minlength' => true,
                'maxlength' => true,
                'required' => true,
                'style' => true
            ),
            'option' => array(
                'name' => true,
                'value' => true,
                'checked' => true,
                'selected' => true,
                'class' => true,
                'id' => true,
                'style' => true
            ),
            'label' => array(
                'style' => true,
                'for' => true
            ),
            'span' => array(
                'data-expr' => true
            )
        );
        return array_merge($allowed, wp_kses_allowed_html('post'));
    }
    
    /**
     * This metod unifies the sanitization of different data types.
     *
     * Variable "$var" is sanitized as a "$data_type" type.
     * If $data_type is an array, this method check if a "$var" is in an "$data_type" array.
     * For example: Sanitizer::sanitize('A', array('A', 'B', 'C')); returns "true"
     * For example: Sanitizer::sanitize($_POST['text'], 'text'); returns sanitized $_POST text value.
     *
     * @param $var
     * @param string|array $data_type
     * @return bool|float|int|string|array
     * @since 1.1.0
     */
    public static function sanitize($var, $data_type)
    {
        if (is_array($data_type) && in_array($var, $data_type)) {
            return $var;
        }
        switch ($data_type) {
            case 'text':
                return sanitize_text_field($var);
            case 'textarea':
                return sanitize_textarea_field($var);
            case 'price':
                return self::sanitize_price($var);
            case 'json':
                return self::is_json_valid($var) ? self::sanitize_json($var) : "{}";
            case 'array':
                return self::sanitize_array($var);
            case 'text_array':
                return self::sanitize_single_array_text($var);
            case 'absint_array':
                return self::sanitize_single_array_absint($var);
            case 'email':
                return sanitize_email($var);
            case 'absint':
                return absint($var);
            case 'int':
                return intval($var);
            case 'float':
                return floatval($var);
            case 'bool':
                return ($var === true || $var === 1 || $var === '1');
        }
        return sanitize_text_field($var);
    }
    
    /**
     * Sanitize the price as float value.
     * @param $input
     * @return float
     * @since 1.1.0
     */
    private static function sanitize_price($input)
    {
        return floatval(str_replace(',', '.', trim($input)));
    }
    
    /**
     * Check if JSON string is valid
     *
     * @param string $json
     * @return bool
     * @since 1.1.0
     */
    private static function is_json_valid($json)
    {
        json_decode(stripslashes($json), true);
        return json_last_error() === JSON_ERROR_NONE;
    }
    
    /**
     * Sanitize JSON (keys and values) and return an array
     *
     * @param string $json
     * @return array
     * @since 1.1.0
     */
    private static function sanitize_json($json)
    {
        $array = json_decode(stripslashes($json), true);
        
        $return = array();
        foreach ($array as $key => $value) {
            if (!is_array($value)) {
                $return[sanitize_text_field($key)] = sanitize_text_field($value);
            } else {
                $return[sanitize_text_field($key)] = self::sanitize_array($value);
            }
        }
        return $return;
    }
    
    /**
     * Sanitize multidimensional array (keys and values)
     *
     * @param array $array
     * @return array
     * @since 1.1.0
     */
    private static function sanitize_array($array, $depth = 1)
    {
        $return = array();
        foreach ($array as $key => $value) {
            if (!is_array($value)) {
                if ($key === 'content') { //allowed HTML content
                    $return[sanitize_text_field($key)] = wp_kses_post($value);
                } else {
                    $return[sanitize_text_field($key)] = sanitize_text_field($value);
                }
            } else {
                if ($depth < self::MAX_DEPTH) {
                    $return[sanitize_text_field($key)] = self::sanitize_array($value, ++$depth);
                }
            }
        }
        return $return;
    }
    
    /**
     * Sanitize array/list where values are text.
     *
     * @param array $array
     * @return array
     * @since 1.1.0
     */
    private static function sanitize_single_array_text($array)
    {
        $return = array();
        foreach ($array as $value) {
            if (!is_array($value))
                $return[] = sanitize_text_field($value);
        }
        return $return;
    }
    
    /**
     * Sanitize array/list where values are absolute integer.
     *
     * @param array $array
     * @return array
     * @since 1.1.0
     */
    private static function sanitize_single_array_absint($array)
    {
        $return = array();
        foreach ($array as $key => $value) {
            if (!is_array($value))
                $return[] = absint($value);
        }
        return $return;
    }
}