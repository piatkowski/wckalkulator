<?php

namespace WCKalkulator;

/**
 * Class FieldsetPostType
 *
 * This Class handles all backend actions for the new Post Type
 *
 * @package WCKalkulator
 * @author Krzysztof Piątkowski
 * @license GPLv2
 * @since 1.1.0
 */
class FieldsetPostType
{
    const POST_TYPE = "wck_fieldset";


    /**
     * Allowed meta keys and sanitization mode used for WCKalkulator\Sanitizer class
     *
     * @var array
     * @since 1.1.0
     */
    public static $meta_keys = array(
        '_wck_assign_type' => array('1', '2', '3'),
        '_wck_assign_products' => 'absint_array',
        '_wck_assign_categories' => 'absint_array',
        '_wck_assign_tags' => 'absint_array',
        '_wck_assign_attributes' => 'absint_array',
        '_wck_assign_priority' => 'absint',
        '_wck_filter_price_enabled' => 'bool',
        '_wck_filter_price_prefix' => 'text',
        '_wck_filter_price_value' => 'text',
        '_wck_filter_price_sufix' => 'text',
        '_wck_fieldset' => 'json', //will be stored as array
        '_wck_expression' => 'json', //will be stored as array
        '_wck_choose_expression_type' => array('oneline', 'conditional', 'off', 'addon'),
        '_wck_version_hash' => 'text',
        '_wck_priority' => 'int',
        '_wck_stock_reduction_multiplier' => 'text',
        '_wck_variation_prices_visible' => 'bool',
        '_wck_price_block_action' => 'absint',
        '_wck_javascript' => 'textarea'
    );

    /**
     * Initialize properties, add WP actions and filters.
     *
     * This method is called in Plugin::run()
     *
     * @since 1.1.0
     */
    public static function init()
    {
        FieldsetAssignment::init();

        add_action('init', array(__CLASS__, 'register_post_type'));
        add_filter('manage_' . self::POST_TYPE . '_posts_columns', array(__CLASS__, 'manage_posts_columns'));
        add_action('manage_' . self::POST_TYPE . '_posts_custom_column', array(__CLASS__, 'manage_posts_custom_column'), 10, 2);
        add_action('save_post_' . self::POST_TYPE, array(__CLASS__, 'save_post'));
        add_filter('woocommerce_screen_ids', array(__CLASS__, 'add_screen_to_woocommerce'));
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueue_scripts'), 11);
        add_filter('get_user_option_meta-box-order_' . self::POST_TYPE, array(__CLASS__, 'metabox_order'));
        add_filter('woocommerce_json_search_found_categories', array(__CLASS__, 'woocommerce_json_search_found_categories'));
        add_filter('bulk_actions-edit-' . self::POST_TYPE, array(__CLASS__, 'bulk_actions'));
        add_filter('post_row_actions', array(__CLASS__, 'duplicate_post_link'), 10, 2);
        add_action('admin_action_wck_duplicate_post', array(__CLASS__, 'duplicate_post'));
        add_filter('admin_body_class', array(__CLASS__, 'add_css_class_to_body'));
        add_action('manage_edit-' . self::POST_TYPE . '_sortable_columns', array(__CLASS__, 'add_sortable_columns'));
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
            'label' => __('Fieldsets', 'wc-kalkulator'),
            'url' => 'edit.php?post_type=wck_fieldset',
            'slug' => 'wck_fieldset'
        );
        return $items;
    }

    /**
     * Sort by custom column - priority
     * @param $cols
     * @return array
     * @since 1.4.0
     */
    public static function add_sortable_columns($cols)
    {
        $cols['wck_assign_priority'] = 'wck_assign_priority';
        return $cols;
    }

    /**
     *  Registers the new Post Type
     *
     * @since 1.1.0
     */
    public static function register_post_type()
    {
        if (!current_user_can('manage_woocommerce') || post_type_exists(self::POST_TYPE)) {
            return;
        }

        register_post_type(self::POST_TYPE,
            array(
                'labels' => array(
                    'name' => __('Fieldsets', 'wc-kalkulator'),
                    'singular_name' => __('Fieldset', 'wc-kalkulator'),
                    'menu_name' => __('Fieldset', 'wc-kalkulator'),
                    'all_items' => __('WCK Fieldsets', 'wc-kalkulator'),
                    'view_item' => __('View Fieldset', 'wc-kalkulator'),
                    'add_new_item' => __('Add new Fieldset', 'wc-kalkulator'),
                    'add_new' => __('Add new', 'wc-kalkulator'),
                    'edit_item' => __('Edit Fieldset', 'wc-kalkulator'),
                    'update_item' => __('Save Fieldset', 'wc-kalkulator'),
                    'search_items' => __('Search Fieldset', 'wc-kalkulator'),
                    'not_found' => __('Fieldset not found', 'wc-kalkulator'),
                    'not_found_in_trash' => __('There is no Fieldset in the trash', 'wc-kalkulator')
                ),
                'description' => __('Fieldsets for Product Fields', 'wc-kalkulator'),
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
                'show_in_menu' => 'edit.php?post_type=product',
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
        $columns['wck_assign_type_text'] = __('Assigned to', 'wc-kalkulator');
        $columns['wck_assign_priority'] = __('Priority', 'wc-kalkulator');
        $columns['wck_calculation_mode'] = __('Calculation Mode', 'wc-kalkulator');
        $columns['wck_fields'] = __('Field names', 'wc-kalkulator');
        $columns['wck_toggle_publish'] = __('Published', 'wc-kalkulator');
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
        switch ($column) {
            case 'wck_calculation_mode':
                $mode = get_post_meta($post_id, '_wck_choose_expression_type', true);
                $name = array(
                    'oneline' => __('Single-Line', 'wc-kalkulator'),
                    'conditional' => __('Conditional', 'wc-kalkulator'),
                    'off' => __('- off -', 'wc-kalkulator'),
                    'addon' => __('Price Add-Ons', 'wc-kalkulator')
                );
                echo esc_html(isset($name[$mode]) ? $name[$mode] : '');
                break;
            case 'wck_fields':
                $fieldset = get_post_meta($post_id, '_wck_fieldset', true);
                $names = array();
                if (!is_array($fieldset)) break;
                foreach ($fieldset as $name => $field) {
                    if (isset($field['title']))
                        $names[] = $field['title'];
                    else
                        $names[] = isset($field['name']) ? $field['name'] : '';
                }
                echo substr(esc_html(join(", ", $names)), 0, 100);
                break;
            case 'wck_assign_type_text':
                $type = (int)get_post_meta($post_id, '_wck_assign_type', true);
                $products = get_post_meta($post_id, '_wck_assign_products', true);
                $categories = get_post_meta($post_id, '_wck_assign_categories', true);
                $tags = get_post_meta($post_id, '_wck_assign_tags', true);
                $attributes = get_post_meta($post_id, '_wck_assign_attributes', true);
                echo FieldsetAssignment::get($type) . "<br />";
                if (is_array($products) && count($products) > 0) {
                    echo __('Products: ', 'wc-kalkulator');
                    echo join(", ", FieldsetAssignment::products_readable($products)) . " ";
                }
                if (is_array($categories) && count($categories) > 0) {
                    echo __('Categories: ', 'wc-kalkulator');
                    echo join(", ", FieldsetAssignment::categories_readable($categories)) . " ";
                }
                if (is_array($tags) && count($tags) > 0) {
                    echo __('Tags: ', 'wc-kalkulator');
                    echo join(", ", FieldsetAssignment::tags_readable($tags));
                }
                if (is_array($attributes) && count($attributes) > 0) {
                    echo __('Attributes: ', 'wc-kalkulator');
                    echo join(", ", FieldsetAssignment::attributes_readable($attributes));
                }
                break;
            case 'wck_assign_priority':
                $value = get_post_meta($post_id, '_wck_assign_priority', true);
                echo esc_html($value);
                break;
            case 'wck_toggle_publish':
                $status = get_post_status($post_id);
                $state = $status === 'publish' ? 'enabled' : 'disabled';
                ?>
                <a href="#" class="wck-toggle-publish" data-post-id="<?php echo (int)$post_id; ?>">
                    <span class="woocommerce-input-toggle woocommerce-input-toggle--<?php echo esc_html($state); ?>"><?php _e('Published', 'wc-kalkulator'); ?></span>
                </a>
                <?php
                break;
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
            'assignment' => array(
                'title' => __('Assign this fieldset to:', 'wc-kalkulator'),
                'position' => 'advanced'
            ),
            'options' => array(
                'title' => __('Options', 'wc-kalkulator'),
                'position' => 'side'
            ),
            'fields_editor' => array(
                'title' => __('Product Fields Settings', 'wc-kalkulator'),
                'position' => 'advanced'
            ),
            /*'fields' => array(
                'title' => __('Add Field', 'wc-kalkulator'),
                'position' => 'advanced'
            ),*/
            'expression' => array(
                'title' => __('Price Calculation', 'wc-kalkulator'),
                'position' => 'advanced'
            ),
            'inventory' => array(
                'title' => __('Inventory & Stock Management', 'wc-kalkulator'),
                'position' => 'advanced'
            ),
            'javascript' => array(
                'title' => __('Custom JavaScript', 'wc-kalkulator'),
                'position' => 'advanced'
            ),
            'pricefilter' => array(
                'title' => __('Price Filtering', 'wc-kalkulator'),
                'position' => 'side'
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
            'normal' => 'slugdiv,wck_assignment,wck_fields_editor,wck_expression,wck_inventory',
            'side' => 'submitdiv,wck_pricefilter,wck_options,wck_docs'
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

            $can_empty = array(
                '_wck_assign_products',
                '_wck_assign_categories',
                '_wck_assign_tags',
                '_wck_assign_attributes',
                '_wck_javascript'
            );

            /**
             * Update post meta if $_POST[key] parameter exists.
             * If $_POST[key] does not exist then clear only specified meta data (defined in $can_empty)
             */
            foreach (self::$meta_keys as $key => $data_type) {
                if (isset($_POST[$key])) {
                    $value = Sanitizer::sanitize($_POST[$key], $data_type);
                    update_post_meta($post_id, $key, $value);
                } elseif (in_array($key, $can_empty)) {
                    update_post_meta($post_id, $key, "");
                }
            }

            update_post_meta($post_id, '_wck_version_hash', wp_generate_password(32, false, false));

        }
    }

    /**
     * Add Post Type to Woocommerce Screen.
     *
     * This is needed to load all required scripts and styles from Woocommerce backend.
     *
     * @param array $screen_ids
     * @return array
     * @since 1.1.0
     */
    public static function add_screen_to_woocommerce($screen_ids)
    {
        $screen_ids[] = self::POST_TYPE;
        $screen_ids[] = 'edit-' . self::POST_TYPE;
        return $screen_ids;
    }

    /**
     * Filter the result of the search categories action.
     *
     * Change category 'slug' to category 'id' in results.
     *
     * @param array $found_categories
     * @return array
     * @since 1.1.0
     */
    public static function woocommerce_json_search_found_categories($found_categories)
    {
        $referer = parse_url(wp_get_referer());
        if (isset($referer['query'])) {
            parse_str($referer['query'], $query);
            if (isset($query['post']) && get_post_type(absint($query['post'])) === self::POST_TYPE) {
                foreach ($found_categories as $id => $category) {
                    $found_categories[$id]->slug = $id;
                }
            }
        }
        return $found_categories;
    }

    /**
     * Load scripts and styles only on the Post add/edit form.
     *
     * @param string $hook
     * @since 1.1.0
     */
    public static function enqueue_scripts($hook)
    {
        global $post;

        if (!$post) return;

        if (($hook === 'post.php' || $hook === 'post-new.php') && $post->post_type === self::POST_TYPE) {
            self::add_scripts();
            self::add_styles();
        }

        //Scripts to handle edit- screen (i.e. toggle button)
        if ($hook === 'edit.php' && $post->post_type === self::POST_TYPE) {
            wp_enqueue_script(
                'wck-fieldset-post-type-script',
                Plugin::url() . '/assets/js/admin-fieldset-post-type.min.js',
                array('jquery'),
                Plugin::VERSION
            );

            wp_add_inline_script(
                'wck-fieldset-post-type-script',
                'var wck_ajax_fieldset = ' . wp_json_encode(
                    array(
                        'ajax_url' => admin_url('admin-ajax.php'),
                        '_wck_ajax_nonce' => wp_create_nonce(Ajax::NONCE)
                    )
                ) . ';'
            );
        }
    }

    /**
     * Enqueue JS files
     *
     * Required: jQuery UI Core, jQuery UI Sortable, jQuery UI Autocomplete, assets/js/admin.js
     * Add global vars: wck_fields_html, wck_load_fieldset, wck_load_expression
     * wck_load_fieldset and wck_load_expression is used to load data to WCK editor
     *
     *
     * @since 1.1.0
     */
    private static function add_scripts()
    {
        global $post;

        wp_enqueue_script('jquery-ui-core');
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script(
            'wck-fieldset-script',
            Plugin::url() . '/assets/js/admin.min.js',
            array('jquery', 'jquery-ui-core', 'jquery-ui-sortable', 'jquery-ui-autocomplete'),
            Plugin::VERSION
        );

        // @since 1.2.0 - adds wp.media image selector
        wp_enqueue_media();

        wp_enqueue_script(
            'iris',
            admin_url('js/iris.min.js'),
            array('jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch'),
            false,
            1
        );
        wp_enqueue_script(
            'wp-color-picker',
            admin_url('js/color-picker.min.js'),
            array('iris', 'wp-i18n'),
            false,
            1
        );
        /**
         * This method sets values of $fields_html and $fields_dropdown properties
         */
        self::cache_fields_data();

        /**
         * This wp_add_inline_script must be used after self::cache_fields_data()
         */
        wp_add_inline_script(
            'wck-fieldset-script',
            'var wck_fields_html = ' . wp_json_encode(
                Cache::get_once('FieldsetPostType_fields_html')
            ) . ';'
        );

        /**
         * Add global parameters for auto-suggestion feature
         */
        wp_add_inline_script(
            'wck-fieldset-script',
            'var wck_global_parameters = ' . wp_json_encode(GlobalParameter::get_all()) . ';'
        );

        $constants = array(
            'wck_load_fieldset' => '_wck_fieldset',
            'wck_load_expression' => '_wck_expression'
        );
        foreach ($constants as $const => $key) {
            $meta = get_post_meta($post->ID, $key, true);

            if (is_array($meta)) {
                wp_add_inline_script(
                    'wck-fieldset-script',
                    'var ' . $const . ' = ' . wp_json_encode(self::decode_array($meta)) . ';'
                );
            }
        }

        wp_enqueue_code_editor(
            array(
                'type' => 'text/javascript',
                'lint' => true
            )
        );
        wp_add_inline_script(
            'code-editor',
            'jQuery( function() { wp.CodeMirror.fromTextArea( document.getElementById("wck_js_editor"), wp.codeEditor.defaultSettings.codemirror ); jsInit(); } );'
        );
        wp_enqueue_script(
            'code-editor-js',
            Plugin::url() . '/assets/js/javascript.cm.min.js',
            array('jquery', 'code-editor'),
            Plugin::VERSION
        );
    }

    /**
     * Store Field's data in Cache Class
     *
     * For each class defined in Plugin::$defined_fields do:
     *  1. crate an instance of field class
     *  2. store HTML output from render_admin() method => $fields_html
     *  3. build $fields_dropdown array to use in the metabox => views/admin/fields_editor.php
     *
     * @since 1.1.0
     */
    private static function cache_fields_data()
    {
        $fields_html = array();
        $fields_dropdown = array();
        foreach (Plugin::$defined_fields as $field_class) {
            $field_instance = new $field_class();
            $fields_html[$field_instance->type()] = str_replace(array('  ', "\r", "\n"), array('', '', ''), $field_instance->render_for_admin());
            $fields_dropdown[$field_instance->group_title()][$field_instance->type()] = array(
                'title' => $field_instance->admin_title(),
                'use_expression' => $field_instance->use_expression()
            );
        }
        Cache::store('FieldsetPostType_fields_html', $fields_html);
        Cache::store('FieldsetPostType_fields_dropdown', $fields_dropdown);
    }

    /**
     * Enqueue CSS files
     *
     * @since 1.1.0
     */
    private static function add_styles()
    {
        wp_register_style('wckalkulator_admin_css', Plugin::url() . '/assets/css/admin.min.css');
        wp_enqueue_style('wckalkulator_admin_css');
        wp_enqueue_style('wp-color-picker');
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
     * Duplicate fieldset (duplicate CPT)
     *
     * @since 1.1.0
     */
    public static function duplicate_post()
    {
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('This action is restriced!', 'wc-kalkulator'));
        }

        $post_id = isset($_GET['post']) ? absint($_GET['post']) : 0;
        $is_valid_nonce = isset($_GET['wck_duplicate_nonce']) ? wp_verify_nonce($_GET['wck_duplicate_nonce'], 'wck_duplicate_nonce') : false;

        if ($post_id === 0 || !$is_valid_nonce) {
            $link = ' <a href="' . admin_url() . 'edit.php?post_type=' . self::POST_TYPE . '">' . __('Go back to the Fieldset list', 'wc-kalkulator') . '</a>';
            wp_die(__('You cannot duplicate this Fieldset!', 'wc-kalkulator') . $link);
        }

        $post = get_post($post_id);
        $user = wp_get_current_user();

        if (isset($post) && $post !== null) {

            $new_id = wp_insert_post(array(
                'post_author' => $user->ID,
                'post_name' => $post->post_name,
                'post_status' => 'draft',
                'post_title' => $post->post_title,
                'post_type' => $post->post_type
            ));

            foreach (array_keys(self::$meta_keys) as $key) {
                $meta_value = get_post_meta($post_id, $key, true);
                if (!empty($meta_value)) {
                    update_post_meta($new_id, $key, $meta_value);
                }
            }
        }
        wp_redirect(admin_url() . 'edit.php?post_type=' . self::POST_TYPE);
        exit;
    }

    /**
     * Adds the duplicate post link in the Post List view
     *
     * @param $actions
     * @param $post
     * @return mixed
     * @since 1.1.0
     */
    public static function duplicate_post_link($actions, $post)
    {
        if (current_user_can('manage_woocommerce') && $post->post_type === self::POST_TYPE) {
            $link = '<a href="';
            $link .= wp_nonce_url('admin.php?action=wck_duplicate_post&post=' . $post->ID, 'wck_duplicate_nonce', 'wck_duplicate_nonce');
            $link .= '" rel="permalink">';
            $link .= __('Duplicate Fieldset', 'wc-kalkulator');
            $link .= '</a>';
            $actions['duplicate'] = $link;
        }
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
     * Need to wrap CSS styles in .wc-kalkulator-wrapper class
     * @param $classes
     * @return string
     * @since 1.2.0
     */
    public static function add_css_class_to_body($classes)
    {
        $classes .= ' wc-kalkulator-wrapper ';
        return $classes;
    }

    /**
     * Decode Html entities in multidimesional array
     *
     * @param $data
     * @return array|string
     */
    private static function decode_array($data)
    {
        if (is_array($data)) {
            return array_map(array(__CLASS__, 'decode_array'), $data);
        }
        return html_entity_decode($data);
    }

}