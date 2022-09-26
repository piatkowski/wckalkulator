<?php

namespace WCKalkulator;


use WCKalkulator\Woocommerce\Product;

/**
 * Class Ajax
 *
 * @package WCKalkulator
 * @author Krzysztof Piątkowski
 * @license GPLv2
 * @since 1.0.0
 */
class Ajax
{
    public const NONCE = "wckalkulator-ajax-nonce";

    /**
     * Private actions
     *
     * @var array
     */
    private static $actions = array(
        'wckalkulator_calculate_price',
        'wckalkulator_json_search_tags',
        'wckalkulator_json_search_attributes',
        'wckalkulator_fieldset_post_type_toggle_publish'
    );

    /**
     * Public actions
     *
     * @var array
     */
    private static $actions_nopriv = array(
        'wckalkulator_calculate_price'
    );

    public static function init()
    {
        foreach (self::$actions as $action) {
            add_action('wp_ajax_' . $action, array(__CLASS__, $action));
        }
        foreach (self::$actions_nopriv as $action) {
            add_action('wp_ajax_nopriv_' . $action, array(__CLASS__, $action));
        }
        add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_scripts'));
    }

    /**
     * Add Javascript files to the Product Page
     *
     * @since 1.0.0
     */
    public static function enqueue_scripts()
    {
        $fieldset = FieldsetProduct::getInstance();
        if ($fieldset->has_fieldset('current')/* && $fieldset->has_expression('current')*/) {
            $fieldset->init();
            $formula_parameters = $fieldset->set_additional_input_variables(true);

            wp_enqueue_script(
                'wck-ajax-script',
                Plugin::url() . '/assets/js/wckalkulator.min.js',
                ['jquery'],
                Plugin::VERSION
            );

            wp_add_inline_script(
                'wck-ajax-script',
                'var wck_ajax_object = ' . wp_json_encode(
                    array(
                        'ajax_url' => admin_url('admin-ajax.php'),
                        '_wck_ajax_nonce' => wp_create_nonce(Ajax::NONCE),
                        '_wck_has_expression' => $fieldset->has_expression('current') ? '1' : '0',
                        '_wck_i18n_required' => __('You should check at least one option.', 'wc-kalkulator'),
                        '_wck_i18n_maxfilesize' => __('This file is too big!', 'wc-kalkulator'),
                        'form' => Settings::get('form_css_selector'),
                        '_wck_visibility_rules' => $fieldset->visibility_rules(),
                        '_wck_additional_parameters' => ($formula_parameters)
                    )
                ) . ';'
            );

            $fieldset->js_api();
        }
    }

    /**
     * Ajax action - calculate product price
     * POST request: product_id, hash, nonce, *fields values*, quantity
     *
     * @since 1.0.0
     */
    public static function wckalkulator_calculate_price()
    {
        if (!wp_verify_nonce($_POST['_wck_ajax_nonce'], Ajax::NONCE) || !isset($_POST["_wck_product_id"]) || !isset($_POST["_wck_hash"])) {
            wp_die('Bad request!');
        }
        if (wp_hash($_POST["_wck_product_id"]) !== $_POST["_wck_hash"]) {
            wp_die('Bad hash!');
        }

        /**
         * Get user input
         */
        $product_id = absint($_POST["_wck_product_id"]);
        $variation_id = isset($_POST["variation_id"]) ? absint($_POST["variation_id"]) : 0;
        $quantity = absint($_POST["quantity"]);


        if ($product_id === 0 || $quantity === 0) {
            Helper::message_for_manager("Unknown product or incorrect user input!");
            wp_die("");
        }

        $fieldset = FieldsetProduct::getInstance();
        $fieldset->init($product_id, $variation_id);
        $fieldset->get_user_input();

        if (!$fieldset->validate(true)) {
            Helper::message_for_manager("Data is invalid");
            wp_die("");
        }
        try {
            $calc = $fieldset->calculate();
            if (!$calc['is_error']) {
                $response = "";
                $price_current = $calc['value'] * $quantity;
                $price_regular = $price_current;
                if (isset($calc['regular_value']) && $calc['regular_value'] > 0) {
                    $price_regular = $calc['regular_value'] * $quantity;
                    $response .= '<del>' . str_replace('woocommerce-Price-amount', '', wc_price($price_regular)) . '</del>&nbsp;';
                }

                $response .= '<ins>' . str_replace('woocommerce-Price-amount', '', wc_price($price_current)) . '</ins>';
                echo apply_filters('wck_total_price_ajax', $response, $price_regular, $price_current);
            } else {
                Helper::message_for_manager($calc["value"]);
                wp_die("");
            }
        } catch (\Exception $e) {
            Helper::message_for_manager("Expression fatal error.");
            error_log($e);
            wp_die("");
        } catch (\Throwable $e) {
            Helper::message_for_manager("Expression fatal error.");
            error_log($e);
            wp_die("");
        }
        wp_die();
    }

    /**
     * Ajax action - search tags
     * POST request: term
     * Output: JSON
     *
     * @since 1.2.0
     */
    public static function wckalkulator_json_search_tags()
    {
        check_ajax_referer('search-products', 'security');

        $term = "";
        if (!empty($_GET['term'])) {
            $term = (string)wc_clean(wp_unslash($_GET['term']));
        }

        if (empty($term)) {
            wp_die();
        }

        $tags = get_terms('product_tag', array(
            'search' => $term,
            'hide_empty' => false
        ));

        $output = array();

        foreach ($tags as $tag) {
            $output[$tag->term_id] = $tag->name;
        }

        wp_send_json($output);
    }

    /**
     * Ajax action - search product attributes
     * POST request: term
     * Output: JSON
     *
     * @since 1.4.0
     */
    public static function wckalkulator_json_search_attributes()
    {
        check_ajax_referer('search-products', 'security');

        $term = "";
        if (!empty($_GET['term'])) {
            $term = (string)wc_clean(wp_unslash($_GET['term']));
        }

        if (empty($term)) {
            wp_die();
        }

        $output = array();
        $taxonomies = get_taxonomies(null, 'objects');

        foreach ($taxonomies as $taxonomy) {
            $attributes = get_terms($taxonomy->name, array(
                'search' => $term,
                'hide_empty' => false
            ));
            foreach ($attributes as $attr) {
                $output[$attr->term_id] = $taxonomy->label . ': ' . $attr->name;
            }
        }

        wp_send_json($output);
    }

    /**
     * Toggle custom post status
     * @return void
     * @since 1.4.0
     */
    public static function wckalkulator_fieldset_post_type_toggle_publish()
    {
        $post_id = isset($_POST['post_id']) ? absint($_POST['post_id']) : 0;
        if (!wp_verify_nonce($_POST['_wck_ajax_nonce'], Ajax::NONCE) || $post_id <= 0) {
            wp_send_json(array('status' => 'error'));
        }
        if (get_post_type($post_id) !== FieldsetPostType::POST_TYPE) {
            wp_send_json(array('status' => 'error'));
        }
        $status = get_post_status($post_id);
        $new_status = $status === 'publish' ? 'draft' : 'publish';
        $new_state = $new_status === 'publish' ? 'enabled' : 'disabled';
        $update_post = array(
            'ID' => $post_id,
            'post_status' => $new_status
        );
        if (wp_update_post($update_post) === $post_id) {
            wp_send_json(array('status' => 'success', 'state' => $new_state));
        }
        wp_send_json(array('status' => 'error'));
    }

    /**
     * Return true if it is an AJAX request
     *
     * @return bool
     * @since 1.0.0
     */
    public static function is_doing()
    {
        return defined('DOING_AJAX') && DOING_AJAX;
    }

    /**
     * Response as array
     *
     * @param $type ('error', 'success')
     * @param $value
     * @return array
     * @since 1.1.0
     */
    public static function response($type, $value)
    {
        return array(
            'is_error' => $type === 'error',
            'value' => $value
        );
    }


}