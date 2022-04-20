<?php

/**
 * Plugin Name: WC Kalkulator
 * Description: Description: Store Manager can add fieldsets to Products and Orders. WC Kalkulator allows to order and calculate the price of the product based on the values of the fields selected by the Customer.
 * Version: 1.1.1
 * Author: Krzysztof Piątkowski
 * Author URI: https://wckalkulator.com
 * Text Domain: wc-kalkulator
 * Domain Path: /languages
 * License: GPLv2
 * Requires PHP: 5.6
 * Network: true
 */

namespace WCKalkulator;

use WCKalkulator\Woocommerce\Product;

if (!defined('ABSPATH')) {
    wp_die("No direct access!");
}

include __DIR__ . '/vendor/autoload.php';

if (!class_exists('WCKalkulator\Plugin')) {
    
    /**
     * The Plugin
     * @package WCKalkulator
     */
    class Plugin
    {
        const VERSION = "1.1.0";
        
        const NAME = "wc-kalkulator";
        
        /**
         * @var string
         */
        private static $path = "";
        
        /**
         * @var string
         */
        private static $url = "";
        
        /**
         * @var Product
         */
        public static $product;
        
        /**
         * @var array
         */
        public static $defined_fields = array(
            Fields\NumberField::class,
            Fields\SelectField::class,
            Fields\DropdownField::class,
            Fields\CheckboxField::class,
            Fields\TextField::class,
            Fields\TextareaField::class,
            Fields\ColorpickerField::class,
            Fields\DatepickerField::class,
            Fields\RangedatepickerField::class
        );
        
        /**
         * Run the plugin
         *
         * @since 1.0.0
         */
        public static function run()
        {
            register_activation_hook(__FILE__, array(__CLASS__, 'activation'));
            
            self::$url = plugins_url('', __FILE__);
            self::$path = WP_PLUGIN_DIR . '/' . Plugin::NAME;
            
            FieldsetPostType::init();
            Ajax::init();
            Product::init();
        }
        
        /**
         * Get the plugin's path
         *
         * @return string
         * @since 1.1.0
         */
        public static function path()
        {
            return self::$path;
        }
        
        /**
         * Get the plugin's url
         *
         * @return string
         * @since 1.1.0
         */
        public static function url()
        {
            return self::$url;
        }
        
        /**
         * Deletes ths Plugin's data
         *
         * @since 1.1.0
         */
        public static function uninstall()
        {
            global $wpdb;
            delete_option('wck_version');
            if (function_exists('is_multisite') && is_multisite()) {
                $blog_sql = "SELECT blog_id FROM $wpdb->blogs WHERE archived = '0' AND spam = '0' AND deleted = '0'";
                $blog_ids = $wpdb->get_col($blog_sql);
                
                if ($blog_ids) {
                    foreach ($blog_ids as $blog_id) {
                        switch_to_blog($blog_id);
                        FieldsetPostType::delete_all_posts();
                    }
                    restore_current_blog();
                }
            } else {
                FieldsetPostType::delete_all_posts();
            }
        }
        
        /**
         * Store the current version number of this plugin
         *
         * @since 1.0.0
         */
        public static function activation()
        {
            update_option('wck_version', self::VERSION);
        }
        
    }
    
    Plugin::run();
    
}
