<?php

namespace WCKalkulator;

/**
 * Class GlobalParametersPostType
 *
 * This Class handles all backend actions for the new Post Type
 *
 * @package WCKalkulator
 * @author Krzysztof Piątkowski
 * @license GPLv2
 * @since 1.2.0
 */
class GlobalParametersPostType
{
    const POST_TYPE = "wck_global_parameter";

    public static $meta_keys = array(
        '_wck_param_name' => 'text',
        '_wck_param_value' => 'text'
    );

    /**
     * Initialize properties, add WP actions and filters.
     *
     * This method is called in Plugin::run()
     *
     * @since 1.2.0
     */
    public static function init()
    {
        add_action('init', array(__CLASS__, 'register_post_type'));
        add_filter('manage_' . self::POST_TYPE . '_posts_columns', array(__CLASS__, 'manage_posts_columns'));
        add_action('manage_' . self::POST_TYPE . '_posts_custom_column', array(__CLASS__, 'manage_posts_custom_column'), 10, 2);
        add_action('save_post_' . self::POST_TYPE, array(__CLASS__, 'save_post'));
        add_filter('get_user_option_meta-box-order_' . self::POST_TYPE, array(__CLASS__, 'metabox_order'));
        add_filter('bulk_actions-edit-' . self::POST_TYPE, array(__CLASS__, 'bulk_actions'));
        add_action('load-edit.php', function () {
            add_filter('views_edit-' . self::POST_TYPE, array(__CLASS__, 'help'));
        });
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
            'label' => __('Global Parameters', 'wc-kalkulator'),
            'url' => 'edit.php?post_type=wck_global_parameter',
            'slug' => 'wck_global_parameter'
        );
        return $items;
    }

    /**
     *  Registers the new Post Type
     *
     * @since 1.2.0
     */
    public static function register_post_type()
    {
        if (!current_user_can('manage_woocommerce') || post_type_exists(self::POST_TYPE)) {
            return;
        }

        register_post_type(self::POST_TYPE,
            array(
                'labels' => array(
                    'name' => __('Global Parameters', 'wc-kalkulator'),
                    'singular_name' => __('Parameter', 'wc-kalkulator'),
                    'menu_name' => __('Parameter', 'wc-kalkulator'),
                    'all_items' => __('Global Params', 'wc-kalkulator'),
                    'view_item' => __('View Parameter', 'wc-kalkulator'),
                    'add_new_item' => __('Add new Parameter', 'wc-kalkulator'),
                    'add_new' => __('Add new', 'wc-kalkulator'),
                    'edit_item' => __('Edit Parameter', 'wc-kalkulator'),
                    'update_item' => __('Save Parameter', 'wc-kalkulator'),
                    'search_items' => __('Search Parameter', 'wc-kalkulator'),
                    'not_found' => __('Parameter not found', 'wc-kalkulator'),
                    'not_found_in_trash' => __('There is no Parameter in the trash', 'wc-kalkulator')
                ),
                'description' => __('Global Parameters for Fieldsets', 'wc-kalkulator'),
                'public' => false,
                'hierarchical' => false,
                'show_ui' => true,
                'has_archive' => false,
                'map_meta_cap' => true,
                'capability_type' => 'post',
                'capabilities' => array(),
                'publicly_queryable' => false,
                'exclude_from_search' => true,
                'query_var' => true,
                'show_in_nav_menus' => false,
                'show_in_menu' => false,
                'delete_with_user' => false,
                'supports' => array('title'),
                'register_meta_box_cb' => array(__CLASS__, 'meta_boxes')
            )
        );
    }

    /**
     * Add columns in the Post List
     *
     * @param array $columns
     * @return array
     * @since 1.1.0
     */
    public static function manage_posts_columns($columns)
    {
        $columns['wck_parameters'] = __('Parameters', 'wc-kalkulator');
        unset($columns['date']);
        return $columns;
    }

    /**
     * Set column values in the Post List
     *
     * @param string $column
     * @param int $post_id
     * @since 1.1.0
     */
    public static function manage_posts_custom_column($column, $post_id)
    {
        if ($column === 'wck_parameters') {
            $name = get_post_meta($post_id, '_wck_param_name', true);
            $value = get_post_meta($post_id, '_wck_param_value', true);
            echo esc_html($name . ' = ' . $value);
        }
    }

    /**
     * Add metaboxes to the new Post Type
     * Each metabox has its own template file in the "/views/admin" directory
     *
     * @since 1.1.0
     */
    public static function meta_boxes()
    {
        $metaboxes = array(
            'documentation' => array(
                'title' => __('Documentation', 'wc-kalkulator'),
                'position' => 'side'
            ),
            'global_parameters' => array(
                'title' => __('Global parameters', 'wc-kalkulator'),
                'position' => 'advanced'
            )
        );

        foreach ($metaboxes as $id => $metabox) {
            add_meta_box(
                'wck_' . $id,
                $metabox['title'],
                function ($post, $data) {
                    echo View::render('admin/' . $data['args']);
                },
                self::POST_TYPE,
                $metabox['position'],
                'default',
                $id
            );
        }
    }


    /**
     * Reorder the metaboxes in the Post Type edit view
     *
     * @param array $order
     * @return array
     * @since 1.1.0
     */
    public static function metabox_order($order)
    {
        return array(
            'normal' => 'slugdiv,wck_global_parameters',
            'side' => 'submitdiv,wck_docs'
        );
    }

    /**
     * Save custom post data
     *
     * @param $post
     * @since 1.1.0
     */
    public static function save_post($post)
    {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            return;

        global $post;

        if (isset($_POST['_wck_nonce']) && wp_verify_nonce($_POST['_wck_nonce'], self::POST_TYPE)) {

            $post_id = $post->ID;

            foreach (self::$meta_keys as $key => $data_type) {
                if (isset($_POST[$key])) {
                    $value = Sanitizer::sanitize($_POST[$key], $data_type);
                    update_post_meta($post_id, $key, $value);
                }
            }
        }
    }

    /**
     * Remove "edit" option from the bulk actions dropdown
     *
     * @param $actions
     * @return mixed
     * @since 1.1.0
     */
    public static function bulk_actions($actions)
    {
        unset($actions['edit']);
        return $actions;
    }

    /**
     * Removes all custom posts
     *
     * @param $actions
     * @param $post
     * @return mixed
     * @since 1.1.0
     */
    public static function delete_all_posts()
    {
        $args = array(
            'numberposts' => -1,
            'post_type' => self::POST_TYPE,
            'post_status' => 'any'
        );
        $posts = get_posts($args);
        if ($posts) {
            foreach ($posts as $item) {
                wp_delete_post($item->ID, true);
            }
        }
    }

    /**
     * Display help for the user
     *
     * @return void
     * @since 1.3.0
     */
    public static function help()
    {
        echo '<p>';
        _e('Global Parameters are numeric variables, which can be used in formulas across all fieldsets. For example if you define MyParam = 100, then you can use {MyParam} in all fieldsets.', 'wc-kalkulator');
        echo '<br />';
        _e('Click "Add New" to create new global parameter.', 'wc-kalkulator');
        echo '</p>';
    }

}