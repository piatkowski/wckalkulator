<?php

namespace WCKalkulator;

/**
 * Class Settings
 * @package WCKalkulator
 * @since 1.2.0
 */
class Settings
{
    const PAGE = "wck_settings";

    const OPTIONS = "wck_options";

    private static $fields;

    /**
     * Add actions and filters
     *
     * @return void
     * @since 1.2.0
     */
    public static function init()
    {
        add_action('admin_init', array(__CLASS__, 'settings_init'));
        add_action('admin_menu', array(__CLASS__, 'add_menu_page'));
        self::$fields = array(
            'form_css_selector' => array(
                'label' => __('Form selector', 'wc-kalkulator'),
                'desc' => __('You can change default form tag selector. It is a form on product page with "Add to cart" button. Default value is: form.cart', 'wc-kalkulator'),
                'type' => 'text',
                'default' => 'form.cart'
            ),
            'display_errors' => array(
                'label' => __('Display calculation errors', 'wc-kalkulator'),
                'desc' => __('Enable this option to show additional informations about price calculation errors. This errors are shown only to admin user or shop manager. This messages are not public.', 'wc-kalkulator'),
                'type' => 'checkbox',
                'default' => 'yes',
            ),
            'upload_retain_time' => array(
                'label' => __('Keep customer files [days]', 'wc-kalkulator'),
                'desc' => __('Number of days for which files uploaded by clients will be kept. Applies to files attached to orders.', 'wc-kalkulator'),
                'type' => 'number',
                'default' => 360,
            ),
            'upload_temp_retain_time' => array(
                'label' => __('Keep temp files [days]', 'wc-kalkulator'),
                'desc' => __('Number of days for which files uploaded by guests will be kept. Applies to temporary files attached to the carts. This files must be deleted very frequently.'),
                'type' => 'number',
                'default' => 3,
            ),
            'upload_customer_data_dir' => array(
                'label' => __('Upload path for Customer data', 'wc-kalkulator'),
                'desc' => __('This path will be used to store files uploaded by customers on checkout. This setting is used by Image Upload field.'),
                'type' => 'text',
                'default' => wp_upload_dir()['basedir'] . '/wc-kalkulator/customer-data/'
            ),
            'dismiss_notices' => array(
                'label' => __('Do you supported this plugin?', 'wc-kalkulator'),
                'desc' => __('The plugin shows notices twice a week, but you can hide it forever by turning on this setting.'),
                'type' => 'checkbox',
                'default' => 'no'
            )
        );
        add_filter('wck_admin_navigation', array(__CLASS__, 'wck_admin_navigation'));
    }

    /**
     * Add admin navigation item
     *
     * @param $items
     * @return mixed
     * @since 1.5.0
     */
    public static function wck_admin_navigation($items)
    {
        $items[] = array(
            'label' => __('Settings', 'wc-kalkulator'),
            'url' => 'edit.php?post_type=product&page=wck_settings',
            'slug' => 'wck_settings'
        );
        return $items;
    }

    /**
     * Initialize Settings API, add sections and fields
     *
     * @return void
     * @since 1.2.0
     */
    public static function settings_init()
    {
        if(!current_user_can('manage_woocommerce')) return;

        register_setting(self::PAGE, self::OPTIONS, array(__CLASS__, 'validate'));

        /*
         * Section "General"
         */
        $section = 'wck_section_general';

        add_settings_section(
            $section,
            '',
            null,
            self::PAGE
        );

        foreach (self::$fields as $name => $field) {
            add_settings_field(
                'wck_' . $name,
                $field['label'],
                array(__CLASS__, 'field_render'),
                self::PAGE,
                $section,
                array(
                    'name' => $name,
                    'type' => $field['type'],
                    'default' => $field['default'],
                    'desc' => $field['desc']
                )
            );
        }
    }

    /**
     * Add page to the menu under WooCommerce Products
     *
     * @return void
     * @since 1.2.0
     */
    public static function add_menu_page()
    {
        add_submenu_page(
            '',//'edit.php?post_type=product',
            'Settings',
            'Settings',
            'manage_options',
            self::PAGE,
            array(__CLASS__, 'html_page')
        );
    }

    /**
     * Render HTML for the settings page
     *
     * @return void
     * @since 1.2.0
     */
    public static function html_page()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        if (isset($_GET['settings-updated'])) {
            add_settings_error('wck_messages', 'wck_message', __('Settings Saved', 'wc-kalkulator'), 'updated');
        }
        settings_errors('wck_messages');
        ?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields(self::PAGE);
                do_settings_sections(self::PAGE);
                submit_button(__('Save settings', 'wc-kalkulator'));
                ?>
            </form>
        </div>
        <?php
    }

    /**
     * Get option value
     *
     * @param $option_name
     * @return string|int
     * @since 1.3.0
     */
    public static function get($name)
    {
        $options = get_option(self::OPTIONS);
        if (isset($options[$name])) {
            return $options[$name];
        }
        return self::$fields[$name]['default'];
    }

    /*
     * ----------------------------------
     *             Callbacks
     * ----------------------------------
     */

    /**
     * Field callback
     *
     * @param $args
     * @return void
     * @since 1.2.0
     */
    public static function field_render($args)
    {
        $name = $args['name'];
        $type = $args['type'];
        $value = get_option(self::OPTIONS);

        $value = (isset($value[$name])) ? $value[$name] : $args['default'];
        $name = self::OPTIONS . '[' . $name . ']';

        switch ($type) {
            case 'text':
            case 'number':
                echo '<input type="' . esc_attr($type) . '" class="regular-text" name="' . esc_attr($name) . '" value="' . esc_html($value) . '" />';
                break;
            case 'checkbox':
                $checked = $value === 'yes';
                echo '<input type="hidden" name="' . esc_attr($name) . '" value="no" />';
                echo '<input type="checkbox" name="' . esc_attr($name) . '" value="yes" ' . checked($checked, true, false) . ' />';
                break;
        }

        echo '<p class="description"><small><strong>Default: ' . esc_html($args['default']) . '</strong></small><br />' . esc_html($args['desc']) . '</p>';
    }

    /**
     * Validate and sanitize user input - callback
     *
     * @param $input
     * @return array
     * @since 1.3.0
     */
    public static function validate($input)
    {
        $input['form_css_selector'] = sanitize_text_field($input['form_css_selector']);
        $input['display_errors'] = $input['display_errors'] === 'yes' ? 'yes' : 'no';
        $input['upload_customer_data_dir'] = rtrim($input['upload_customer_data_dir'], '/') . '/';
        $input['upload_retain_time'] = max(1, absint($input['upload_retain_time']));
        $input['upload_temp_retain_time'] = max(1, absint($input['upload_temp_retain_time']));
        return $input;
    }
}