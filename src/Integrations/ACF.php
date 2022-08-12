<?php

use WCKalkulator\Cache;

/**
 * Function to use in ExpressionParser. Integrates ACF get_field function
 *
 * @param string $name
 * @param int $post_id
 * @return float
 * @since 1.5.0
 */
if (!function_exists('wck_integration_acf_get_field')) {
    function wck_integration_acf_get_field($name, $post_id = false)
    {
        if (class_exists('ACF') && function_exists('get_field')) {
            if ($post_id !== false) {
                return floatval(get_field($name, $post_id));
            } else {
                $cache = Cache::get("ACF_Post_IDs");
                foreach ($cache as $id) {
                    $value = get_field($name, $id);
                    if ($value) {
                        return floatval($value);
                    }
                }
            }
        }
        return 0;
    }
}