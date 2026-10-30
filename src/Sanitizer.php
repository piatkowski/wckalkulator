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
        $post_allowed = wp_kses_allowed_html('post');
        foreach ($allowed as $tag => $attributes) {
            if (!isset($post_allowed[$tag]) || !is_array($post_allowed[$tag])) {
                $post_allowed[$tag] = array();
            }
            $post_allowed[$tag] = array_merge($post_allowed[$tag], $attributes);
        }

        return $post_allowed;
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
        if (is_array($data_type)) {
            return is_scalar($var) && in_array((string) $var, array_map('strval', $data_type), true) ? $var : false;
        }
        switch ($data_type) {
            case 'text':
                return is_scalar($var) ? sanitize_text_field((string) $var) : false;
            case 'textarea':
                return is_scalar($var) ? sanitize_textarea_field((string) $var) : false;
            case 'price':
                return is_scalar($var) ? self::sanitize_price($var) : false;
            case 'json':
                return self::sanitize_json($var);
            case 'array':
                return is_array($var) ? self::sanitize_array($var) : false;
            case 'text_array':
                return is_array($var) ? self::sanitize_single_array_text($var) : false;
            case 'absint_array':
                return is_array($var) ? self::sanitize_single_array_absint($var) : false;
            case 'email':
                return is_scalar($var) ? sanitize_email((string) $var) : false;
            case 'absint':
                return is_scalar($var) ? absint($var) : false;
            case 'int':
                return is_scalar($var) ? intval($var) : false;
            case 'float':
                return is_scalar($var) ? floatval($var) : false;
            case 'bool':
                return ($var === true || $var === 1 || $var === '1');
        }
        return is_scalar($var) ? sanitize_text_field((string) $var) : '';
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
        if (!is_string($json) || strlen($json) > 1048576) {
            return false;
        }
        json_decode($json, true, 64);
        return json_last_error() === JSON_ERROR_NONE && is_array(json_decode($json, true, 64));
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
        if (!self::is_json_valid($json)) {
            return false;
        }
        $array = json_decode($json, true, 64);
        return self::sanitize_array($array);
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
        if (!is_array($array) || $depth > 64) {
            return array();
        }
        $return = array();
        foreach ($array as $key => $value) {
            $clean_key = is_int($key) ? $key : sanitize_text_field((string) $key);
            if (!is_array($value)) {
                if ($key === 'content' && is_string($value)) { //allowed HTML content
                    $return[$clean_key] = wp_kses_post($value);
                } else {
                    if (is_string($value)) {
                        $return[$clean_key] = sanitize_text_field($value);
                    } elseif (is_scalar($value) || $value === null) {
                        $return[$clean_key] = $value;
                    } else {
                        $return[$clean_key] = '';
                    }
                }
            } else {
                $return[$clean_key] = self::sanitize_array($value, $depth + 1);
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
