<?php

namespace WCKalkulator;

/**
 * Class View
 *
 * This Class handles HTML rendering from view files in /view/ directory.
 * $view array can be passed to the view file. The $view array is converted to an object.
 *
 * @package WCKalkulator
 * @author Krzysztof Piątkowski
 * @license GPLv2
 * @since 1.1.0
 */
class View
{
    /**
     * Render template HTML code. Return output buffer.
     *
     * Pass $view variable as object.
     * Files in /views folder can be overriden in themes/your-theme/wc-kalkulator/
     *
     * @param string $template format: dir1/dir2/file
     * @param array $view
     * @return string|null
     * @since 1.1.0
     */
    public static function render($template, $view = array())
    {
        if (preg_match('/^([a-z0-9_]+\/?)+$/', $template)) {
            $view = (object)$view;
            ob_start();
            
            $override = self::override_file($template);
            if (file_exists($override)) {
                include $override;
            } else {
                include Plugin::path() . '/views/' . $template . '.php';
            }
            
            return ob_get_clean();
        }
        return;
    }
    
    private static function override_file($template)
    {
        /*
         * Using get_stylesheet_directory() to support child themes
         */
        return get_stylesheet_directory() . '/' . Plugin::NAME . '/' . $template . '.php';
    }
    
}