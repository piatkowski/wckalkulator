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

    private static $changing_invalid_status = false;

    public static $meta_keys = array(
        '_wck_param_name' => 'text',
        '_wck_param_value' => 'text',
        '_wck_param_public' => 'bool'
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
        add_action('save_post_' . self::POST_TYPE, array(__CLASS__, 'save_post'), 10, 2);
        add_action('admin_notices', array(__CLASS__, 'show_save_error'));
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
        if (post_type_exists(self::POST_TYPE)) {
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
                'capability_type' => 'wck_global_parameter',
                'capabilities' => array(
                    'edit_posts' => 'manage_woocommerce',
                    'edit_others_posts' => 'manage_woocommerce',
                    'delete_posts' => 'manage_woocommerce',
                    'publish_posts' => 'manage_woocommerce',
                    'read_private_posts' => 'manage_woocommerce',
                    'delete_private_posts' => 'manage_woocommerce',
                    'delete_published_posts' => 'manage_woocommerce',
                    'delete_others_posts' => 'manage_woocommerce',
                    'edit_private_posts' => 'manage_woocommerce',
                    'edit_published_posts' => 'manage_woocommerce',
                    'read' => 'manage_woocommerce'
                ),
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
    public static function save_post($post_id, $post = null)
    {
        if (!is_scalar($post_id)) {
            return;
        }
        $post_id = absint($post_id);
        if (!is_object($post) && function_exists('get_post')) {
            $post = get_post($post_id);
        }
        if (self::$changing_invalid_status || !is_object($post) || $post->post_type !== self::POST_TYPE
            || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            || wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
            return;
        }
        if (!isset($_POST['_wck_nonce']) || !is_scalar($_POST['_wck_nonce'])
            || !wp_verify_nonce(wp_unslash((string) $_POST['_wck_nonce']), self::POST_TYPE)
            || !current_user_can('edit_post', $post->ID)
            || !isset($_POST['_wck_param_name'], $_POST['_wck_param_value'])
            || !is_scalar($_POST['_wck_param_name']) || !is_scalar($_POST['_wck_param_value'])) {
            return;
        }

        $name = sanitize_text_field(wp_unslash((string) $_POST['_wck_param_name']));
        $expression = sanitize_text_field(wp_unslash((string) $_POST['_wck_param_value']));
        $error = null;

        if (!GlobalParameter::is_valid_name($name)) {
            $error = 'invalid_name';
        } elseif (self::has_duplicate_parameter_name($name, $post_id)) {
            $error = 'duplicate_name';
        } else {
            $result = null;
            if (!GlobalParameter::evaluate_expression($expression, $result, $error)) {
                // The evaluator returns only generic error codes, never exception details.
            }
        }

        if ($error !== null) {
            set_transient('wck_global_parameter_save_error_' . get_current_user_id(), array(
                'post_id' => $post_id,
                'reason' => $error
            ), 10 * MINUTE_IN_SECONDS);
            // Preserve the last working published value until a valid edit.
            return;
        }

        update_post_meta($post_id, '_wck_param_name', $name);
        update_post_meta($post_id, '_wck_param_value', $expression);
        update_post_meta($post_id, '_wck_param_public', isset($_POST['_wck_param_public'])
            && wp_unslash($_POST['_wck_param_public']) === '1' ? '1' : '0');
        update_post_meta($post_id, '_wck_schema_version', 1);
        GlobalParameter::clear_cache();
    }

    private static function has_duplicate_parameter_name($name, $exclude_id)
    {
        $posts = get_posts(array(
            'post_type' => self::POST_TYPE,
            'post_status' => array('publish', 'draft', 'pending', 'private', 'future'),
            'numberposts' => -1,
            'fields' => 'ids'
        ));
        foreach ($posts as $post_id) {
            if ((int) $post_id !== (int) $exclude_id
                && get_post_meta($post_id, '_wck_param_name', true) === $name) {
                return true;
            }
        }
        return false;
    }

    public static function show_save_error()
    {
        if (!current_user_can('manage_woocommerce')) {
            return;
        }
        $key = 'wck_global_parameter_save_error_' . get_current_user_id();
        $error = get_transient($key);
        if (!is_array($error) || empty($error['post_id'])) {
            return;
        }
        delete_transient($key);
        $messages = array(
            'invalid_name' => __('Nazwa parametru może zawierać tylko litery, cyfry i podkreślenia.', 'wc-kalkulator'),
            'duplicate_name' => __('Nazwa parametru musi być unikalna.', 'wc-kalkulator'),
            'invalid_type' => __('Wartość parametru musi być liczbą lub tablicą liczb.', 'wc-kalkulator'),
            'invalid_expression' => __('Formuła parametru jest niepoprawna.', 'wc-kalkulator')
        );
        $message = isset($messages[$error['reason']]) ? $messages[$error['reason']] : __('Parametr globalny jest niepoprawny.', 'wc-kalkulator');
        $link = get_edit_post_link((int) $error['post_id']);
        echo '<div class="notice notice-error"><p>' . esc_html($message);
        if ($link) {
            echo ' <a href="' . esc_url($link) . '">' . esc_html__('Popraw parametr', 'wc-kalkulator') . '</a>';
        }
        echo '</p></div>';
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
        do {
            $post_ids = get_posts(array(
                'numberposts' => 100,
                'post_type' => self::POST_TYPE,
                'post_status' => array_keys(get_post_stati()),
                'fields' => 'ids'
            ));
            foreach ($post_ids as $post_id) {
                wp_delete_post($post_id, true);
            }
        } while (count($post_ids) === 100);
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
