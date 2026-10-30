<?php

/**
 * Plugin Name: WC Kalkulator
 * Description: Description: Store Manager can add fieldsets to Products and Orders. WC Kalkulator allows to order and calculate the price of the product based on the values of the fields selected by the Customer.
 * Version: 2.0.0
 * Author: Krzysztof Piątkowski
 * Author URI: https://wckalkulator.com
 * Text Domain: wc-kalkulator
 * Domain Path: /languages
 * License: GPLv2
 * Requires PHP: 7.4
 * Requires Plugins: woocommerce
 * Network: true
 */

namespace WCKalkulator;

use WCKalkulator\Woocommerce\Attribute;
use WCKalkulator\Woocommerce\Product;

if (!defined('ABSPATH')) {
    wp_die("No direct access!");
}

/**
 * Report a missing runtime dependency without booting any integrations.
 *
 * @param string $message Notice text.
 * @return void
 */
function dependency_admin_notice($message)
{
    add_action('admin_notices', function () use ($message) {
        if (current_user_can('activate_plugins')) {
            echo '<div class="notice notice-error"><p>' . esc_html($message) . '</p></div>';
        }
    });
}

$wck_autoloader = __DIR__ . '/vendor/autoload.php';
if (!is_readable($wck_autoloader)) {
    dependency_admin_notice(__('WC Kalkulator nie został uruchomiony: brakuje pliku vendor/autoload.php. Zainstaluj kompletną paczkę wtyczki.', 'wc-kalkulator'));
    return;
}

include $wck_autoloader;

if (!class_exists('WCKalkulator\Plugin')) {

    /**
     * The Plugin
     * @package WCKalkulator
     */
    class Plugin
    {
        const VERSION = "2.0.0";

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
            Fields\MeasurementField::class,
            Fields\SectionField::class,
            Fields\RepeaterField::class,
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

            add_action('plugins_loaded', array(__CLASS__, 'load_text_domain'), 1);
            add_action('plugins_loaded', array(__CLASS__, 'boot'), 20);
        }

        /**
         * Initialize integrations after all plugins have loaded.
         *
         * @return void
         */
        public static function boot()
        {
            if (!class_exists('WooCommerce')) {
                dependency_admin_notice(__('WC Kalkulator wymaga aktywnej wtyczki WooCommerce. Włącz WooCommerce, aby uruchomić integrację.', 'wc-kalkulator'));
                return;
            }

            self::$url = plugins_url('', __FILE__);
            self::$path = dirname(__FILE__);

            FieldsetPostType::init();
            GlobalParametersPostType::init();
            Ajax::init();
            Product::init();
            Settings::init();
            PrivateUploads::init();
            AdminNotice::init();
            Cron::init();
            Attribute::init();

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
            require_once __DIR__ . '/src/Uninstall.php';
            Uninstall::run();
        }

        /**
         * Store the current version number of this plugin
         *
         * @return void
         * @since 1.0.0
         */
        public static function activation($network_wide = false)
        {
            self::for_each_site($network_wide, function () {
                update_option('wck_version', self::VERSION);
            });
        }

        /**
         * Delete cron jobs
         *
         * @return void
         * @since 1.3.0
         */
        public static function deactivation($network_wide = false)
        {
            self::for_each_site($network_wide, function () {
                Cron::deactivate();
                AdminNotice::deactivate();
            });
        }

        /** Run a lifecycle callback in each affected site context. */
        private static function for_each_site($network_wide, $callback)
        {
            if (!$network_wide || !is_multisite()) {
                call_user_func($callback);
                return;
            }

            $offset = 0;
            do {
                $site_ids = get_sites(array(
                    'fields' => 'ids',
                    'number' => 100,
                    'offset' => $offset
                ));
                foreach ($site_ids as $site_id) {
                    switch_to_blog($site_id);
                    try {
                        call_user_func($callback);
                    } finally {
                        restore_current_blog();
                    }
                }
                $offset += 100;
            } while (count($site_ids) === 100);
        }

    }
    Plugin::run();
}


