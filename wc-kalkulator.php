<?php

/**
 * Plugin Name: WC Kalkulator
 * Description: Description: Store Manager can add fieldsets to Products and Orders. WC Kalkulator allows to order and calculate the price of the product based on the values of the fields selected by the Customer.
 * Version: 1.6.1
 * Author: Krzysztof Piątkowski
 * Author URI: https://wckalkulator.com
 * Text Domain: wc-kalkulator
 * Domain Path: /languages
 * License: GPLv2
 * Requires PHP: 5.6
 * Network: true
 */

namespace WCKalkulator;

use WCKalkulator\Woocommerce\Attribute;
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
        const VERSION = "1.6.1";

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
            Fields\CheckboxgroupField::class,
            Fields\TextField::class,
            Fields\TextareaField::class,
            Fields\ColorpickerField::class,
            Fields\DatepickerField::class,
            Fields\RangedatepickerField::class,
            Fields\EmailField::class,
            Fields\RadioField::class,
            Fields\ImageselectField::class,
            Fields\FileuploadField::class,
            Fields\ImageuploadField::class,
            Fields\HtmlField::class,
            Fields\HeadingField::class,
            Fields\ParagraphField::class,
            Fields\FormulaField::class,
            Fields\LinkField::class,
            Fields\AttachmentField::class,
            Fields\ImageswatchesField::class,
            Fields\ColorswatchesField::class,
            Fields\EmptyField::class,
            Fields\HiddenField::class
        );

        /**
         * Run the plugin
         *
         * @since 1.0.0
         */
        public static function run()
        {
            register_activation_hook(__FILE__, array(__CLASS__, 'activation'));
            register_deactivation_hook(__FILE__, array(__CLASS__, 'deactivation'));

            self::$url = plugins_url('', __FILE__);
            self::$path = WP_PLUGIN_DIR . '/' . Plugin::NAME;

            FieldsetPostType::init();
            GlobalParametersPostType::init();
            Ajax::init();
            Product::init();
            Settings::init();
            AdminNotice::init();
            Cron::init();
            Attribute::init();

            add_action('plugins_loaded', array(__CLASS__, 'load_text_domain'));
            add_action('current_screen', array(__CLASS__, 'current_screen'));
        }

        /**
         * Load text domain
         * @return void
         */
        public static function load_text_domain()
        {
            load_plugin_textdomain(
                'wc-kalkulator',
                false,
                dirname(plugin_basename(__FILE__)) . '/languages'
            );
        }

        /**
         * Adds actions and filters on admin screens
         *
         * @param $screen
         * @return void
         * @since 1.5.0
         */
        public static function current_screen($screen)
        {
            $pages = array(
                'product_page_wck_settings'
            );
            $post_types = array(
                FieldsetPostType::POST_TYPE,
                GlobalParametersPostType::POST_TYPE
            );
            if (isset($screen->post_type) && in_array($screen->post_type, $post_types) || in_array($screen->base, $pages)) {
                add_action('in_admin_header', array(__CLASS__, 'in_admin_header'));
                wp_register_style('wckalkulator_admin_nav_css', Plugin::url() . '/assets/css/admin.nav.min.css');
                wp_enqueue_style('wckalkulator_admin_nav_css');
            }
        }

        /**
         * Renders admin naviation toolbar
         *
         * @return void
         * @since 1.5.0
         */
        public static function in_admin_header()
        {
            echo View::render('admin/navigation', array(
                'items' => apply_filters('wck_admin_navigation', array())
            ));
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
                        GlobalParametersPostType::delete_all_posts();
                    }
                    restore_current_blog();
                }
            } else {
                FieldsetPostType::delete_all_posts();
                GlobalParametersPostType::delete_all_posts();
            }
        }

        /**
         * Store the current version number of this plugin
         *
         * @return void
         * @since 1.0.0
         */
        public static function activation()
        {
            update_option('wck_version', self::VERSION);
        }

        /**
         * Delete cron jobs
         *
         * @return void
         * @since 1.3.0
         */
        public static function deactivation()
        {
            Cron::deactivate();
        }

    }
    Plugin::run();
}


