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
    }
    
    /**
     * Initialize Settings API, add sections and fields
     *
     * @return void
     * @since 1.2.0
     */
    public static function settings_init()
    {
        register_setting(self::PAGE, self::OPTIONS);
        
        /*
         * Secion "General"
         */
        
        $section = 'wck_section_general';
        
        add_settings_section(
            $section,
            __('General Settings', 'wc-kalkulator'),
            null,
            self::PAGE
        );
        
        add_settings_field(
            'wck_field_test',
            __('Pill', self::PAGE),
            array(__CLASS__, 'field_test'),
            self::PAGE,
            $section
        );
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
            'edit.php?post_type=product',
            'WCK Settings',
            'WCK Settings',
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
    public static function field_test($args)
    {
        $options = get_option(self::OPTIONS);
        //@todo render field
    }
}