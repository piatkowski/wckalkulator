<?php

namespace WCKalkulator\Woocommerce;

use WCKalkulator\Cache;
use WCKalkulator\FieldsetProduct;
use WCKalkulator\Helper;
use WCKalkulator\Plugin;
use WCKalkulator\Sanitizer;
use WCKalkulator\View;

/**
 * Class Product
 *
 * @package WCKalkulator
 * @author Krzysztof Piątkowski
 * @license GPLv2
 * @since 1.0.0
 */
class Product
{
    /**
     * Add hooks and filters
     *
     * @since 1.0.0
     */
    public static function init()
    {
        add_action('woocommerce_before_add_to_cart_button', array(__CLASS__, 'render_fields_on_product_page'));
        add_filter('woocommerce_add_to_cart_validation', array(__CLASS__, 'validate_fields_on_product_page'), 10, 3);
        add_filter('woocommerce_add_cart_item_data', array(__CLASS__, 'add_cart_item_data'), 10, 4);
        add_action('woocommerce_before_calculate_totals', array(__CLASS__, 'before_calculate_totals'), 10, 1);
        add_filter('woocommerce_cart_item_name', array(__CLASS__, 'cart_item_name'), 10, 3);
        add_action('woocommerce_checkout_create_order_line_item', array(__CLASS__, 'checkout_create_order_line_item'), 10, 4);
        add_action('woocommerce_after_add_to_cart_button', array(__CLASS__, 'price_block'));
        add_filter('woocommerce_order_item_quantity', array(__CLASS__, 'reduce_inventory'), 10, 3);
        add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_scripts'));

        PriceFilter::getInstance();
        Cart::getInstance();
    }

    /**
     * Add styles and scripts to the product page
     *
     * @since 1.0.0
     */
    public static function enqueue_scripts()
    {
        $fieldset = FieldsetProduct::getInstance();
        if ($fieldset->has_fieldset('current')) {
            if ($fieldset->has_expression('current')) {
                wp_register_style('wckalkulator_price_css', Plugin::url() . '/assets/css/price.min.css');
                wp_enqueue_style('wckalkulator_price_css');
            }

            wp_register_style('wckalkulator_product_css', Plugin::url() . '/assets/css/product.min.css');
            wp_enqueue_style('wckalkulator_product_css');

            wp_enqueue_script('jquery-tiptip',
                WC()->plugin_url() . '/assets/js/jquery-tiptip/jquery.tipTip.min.js',
                array('jquery')
            );

            /**
             * Enqueue scripts and styles from fields used in fieldset
             */
            $enqueued = array();
            $localize = array();
            $fieldset->init();

            foreach ($fieldset->fields() as $field) {
                if (!in_array($field->type(), $enqueued)) {
                    $field->enqueue_scripts();
                    $enqueued[] = $field->type();
                }
                $localize[] = $field->localize_script();
            }
            unset($enqueued);

            $unique_scripts = array();
            foreach ($localize as $item) {
                if (!empty($item))
                    $unique_scripts[$item["script"]][$item["field_name"]] = $item["options"];
            }
            unset($localize);

            foreach ($unique_scripts as $script => $options) {
                wp_localize_script(
                    $script,
                    str_replace('-', '_', $script) . '_options',
                    $options
                );
            }
        } else if (is_page('cart') || is_cart() || is_checkout() || is_page('checkout')) {
            wp_register_style('wckalkulator_frontend_css', Plugin::url() . '/assets/css/cart.min.css');
            wp_enqueue_style('wckalkulator_frontend_css');
        }
    }

    /**
     * Show fieldset on product page
     *
     * @return void
     * @since 1.0.0
     */
    public static function render_fields_on_product_page()
    {
        /*if (Ajax::is_doing()) {
            return false;
        }*/
        $fieldset = FieldsetProduct::getInstance();

        if ($fieldset->has_fieldset('current')) {

            $fieldset->init();
            /*
             * Get field's values from cart item (if in edit mode)
             * $html changed from string to array since 1.4.0 (2-col layout support)
             */
            $html = array(
                'hidden' => '',
                'fields' => array()
            );
            $cart_fields = null;
            if (isset($_GET['wck_edit'])) {
                $cart_item = WC()->cart->get_cart_item($_GET['wck_edit']);
                if (!empty($cart_item)) {
                    $cart_fields = isset($cart_item['wckalkulator_fields']) ? $cart_item['wckalkulator_fields'] : null;
                    $html['hidden'] .= '<input type="hidden" name="_wck_edit" value="' . esc_html($cart_item['key']) . '" />' . "\n";
                }
            }

            /*
             * Get field's Html code
             */
            foreach ($fieldset->fields() as $field) {
                //Try to get value of the field from cart item
                $value = isset($cart_fields[$field->data('name')]) ? $cart_fields[$field->data('name')] : '';
                $html['fields'][] = array(
                    'html' => wp_kses($field->render_for_product($value), Sanitizer::allowed_html()) . "\n",
                    //colspan since 1.4.0
                    'colspan' => max((int)$field->data('colspan'), 1),
                );
                //$html['visibility'][$field->data('name')] = $field->data('visibility');
            }
            /*
             * Output rendered Html
             */
            echo $fieldset->render($html);
        }
    }

    /**
     * Validate user input, validates correct price from expression
     * @param $validation
     * @param $product_id
     * @param $quantity
     * @return bool
     * @since 1.0.0
     */
    public static function validate_fields_on_product_page($validation, $product_id, $quantity)
    {
        /*if (Ajax::is_doing()) {
            return false;
        }*/
        $fieldset = FieldsetProduct::getInstance();
        if ($fieldset->has_fieldset($product_id)) {
            $variation_id = isset($_POST['variation_id']) ? absint($_POST['variation_id']) : 0;

            $fieldset->init($product_id, $variation_id);
            $user_input = $fieldset->get_user_input();

            $validation = $fieldset->validate($user_input);
            if (!$validation) {
                foreach ($fieldset->validation_notices() as $notice) {
                    wc_add_notice($notice, 'error');
                }
            }

            if ($fieldset->has_expression()) {
                try {
                    $calc = $fieldset->calculate();
                    if ($calc['is_error']) {
                        wc_add_notice(__("Cannot calculate the correct price!", "wc-kalkulator") . " " . $calc['value'], 'error');
                        $validation = false;
                    }
                } catch (\Exception $e) {
                    return false;
                } catch (\Throwable $e) {
                    return false;
                }
            }
        }

        return $validation;
    }

    /**
     * Render price block to show Ajax result (Total price)
     *
     * @since 1.1.0
     */
    public static function price_block()
    {
        $fieldset = FieldsetProduct::getInstance();
        if ($fieldset->has_fieldset('current')) {
            if ($fieldset->has_expression('current')) {
                echo View::render('woocommerce/price_block');
            }
        }
    }

    /**
     * Add item data to Cart. We add field for storing calculated price
     * !Important - do not multiply by $quantity. Price for single product.
     *
     * @param $cart_item_data
     * @param $product_id
     * @param $variation_id
     * @param $quantity
     * @return bool|array
     * @throws \Exception
     * @since 1.0.0
     */
    public static function add_cart_item_data($cart_item_data, $product_id, $variation_id, $quantity)
    {
        /*if (Ajax::is_doing()) {
            return false;
        }*/

        if (FieldsetProduct::getInstance()->has_fieldset($product_id)) {

            $fieldset = FieldsetProduct::getInstance();
            $fieldset->init($product_id, $variation_id);

            $user_input = $fieldset->get_user_input();
            if (!$fieldset->validate($user_input)) {
                wp_die('Bad request (2)!');
            }

            $success = false;

            if ($fieldset->has_expression()) {
                try {
                    $calc = $fieldset->calculate();
                    if (!$calc['is_error']) {
                        $cart_item_data['wckalkulator_price'] = $calc['value'];
                        $cart_item_data['wckalkulator_stock_reduction_multiplier'] = $fieldset->stock_reduction_multiplier();
                        $success = true;
                    } else {
                        wp_die('Bad request (3)!');
                    }
                } catch (\Exception $e) {
                    error_log($e);
                    wp_die('Bad request (4)!');

                } catch (\Throwable $e) {
                    error_log($e);
                    wp_die('Bad request (4)!');
                }
            } else {
                /**
                 * If there is no expression, then add only fields
                 */
                $success = true;
            }
            /*
             * Remove old cart item
             */
            if (isset($_POST['_wck_edit'])) {
                $old_key = sanitize_text_field($_POST['_wck_edit']);
                if (!empty(WC()->cart->get_cart_item($old_key))) {
                    WC()->cart->remove_cart_item($old_key);
                }
            }

            /** If cart item key has been added, then save user input */
            if ($success) {
                $fieldset->handle_temp_upload();
                $cart_item_data['wckalkulator_fields'] = $user_input;
                $cart_item_data['wckalkulator_fieldset_version_hash'] = $fieldset->version_hash();
                $cart_item_data['wckalkulator_fieldset_id'] = $fieldset->id();
            }

        }
        return $cart_item_data;
    }

    /**
     * Update the total price based on the calculated custom price
     *
     * @param $cart
     * @return bool
     * @since 1.0.0
     */
    public static function before_calculate_totals($cart)
    {
        /*if (Ajax::is_doing()) {
            return false;
        }*/

        foreach ($cart->get_cart() as $key => $value) {
            if (isset($value['wckalkulator_price'])) {
                $value['data']->set_price(($value['wckalkulator_price']));
            }
        }
    }

    /**
     * Add the Fieldset to the User's cart page
     *
     * @param $item_name
     * @param $cart_item
     * @param $cart_item_key
     * @return bool|string
     * @since 1.0.0
     */
    public static function cart_item_name($item_name, $cart_item, $cart_item_key)
    {
        /*if (Ajax::is_doing()) {
            return false;
        }*/

        $product_id = absint($cart_item["product_id"]);
        $variation_id = isset($cart_item['variation_id']) ? absint($cart_item['variation_id']) : 0;

        if (FieldsetProduct::getInstance()->has_fieldset($product_id)) {

            $fieldset = FieldsetProduct::getInstance();
            $fieldset->init($product_id, $variation_id);

            $html = '';
            foreach ($fieldset->fields() as $name => $field) {

                if (isset($cart_item['wckalkulator_fields'][$name])) {
                    $value = isset($cart_item['wckalkulator_fields']['_files'][$name]) ? $cart_item['wckalkulator_fields']['_files'][$name] : $cart_item['wckalkulator_fields'][$name];
                    $html .= $field->render_for_cart($value);
                }
            }

            $item_name .= View::render('woocommerce/cart', array(
                'html' => $html,
                'item_name' => $item_name,
                'cart_item' => $cart_item,
                'cart_item_key' => $cart_item_key
            ));
            /**
             * Removed in 1.1.0
             * $item_name .= apply_filters('wck_field_html_wrapper_order_meta', $html_wrapped, $html);
             */

        }
        return $item_name;
    }

    /**
     * Create fieldset to Order
     * Fieldset is visible in e-mail notification and Order page in WP dashboard
     *
     * @param $item
     * @param $cart_item_key
     * @param $values
     * @param $order
     * @since 1.0.0
     */
    public static function checkout_create_order_line_item($item, $cart_item_key, $values, $order)
    {
        /*if (Ajax::is_doing()) {
            return;
        }*/
        $values = $item->legacy_values;
        $product_id = absint($values["product_id"]);
        $variation_id = isset($values['variation_id']) ? absint($values['variation_id']) : 0;

        if (FieldsetProduct::getInstance()->has_fieldset($product_id)) {
            if (array_key_exists("wckalkulator_fields", $values)) {

                $order_fields = $values["wckalkulator_fields"];
                $fieldset = FieldsetProduct::getInstance();

                $fieldset->init($product_id, $variation_id);

                // Add hidden field with all parameters. This is for cart editing.
                $item->add_meta_data('_wck_fields', $order_fields);
                $item->add_meta_data('_wck_stock_reduction_multiplier', $values['wckalkulator_stock_reduction_multiplier']);

                foreach ($fieldset->fields() as $name => $field) {

                    if (isset($order_fields[$name])) {
                        $field_value = $field->order_item_value($order_fields[$name]);
                        if (method_exists($field, 'upload') && isset($order_fields['_files'][$name])) {
                            if ($field->upload($order_fields['_files'][$name])) {
                                $item->add_meta_data($field->data('title') . __(' (original name)', 'wc-kalkulator'), esc_html($order_fields['_files'][$name]['original_name']), true);
                                $item->add_meta_data($field->data('title') . __(' (size)', 'wc-kalkulator'), round($field_value / 1000000, 2) . ' MB', true);
                                $item->add_meta_data($field->data('title') . __(' (url)', 'wc-kalkulator'), $order_fields['_files'][$name]['upload_url'], true);
                            }
                        } else {
                            $item->add_meta_data($field->data('title'), $field_value, true);
                        }
                    } else {
                        if ($field->is_required()) {
                            wp_send_json(array(
                                'result' => 'failure',
                                'messages' => '<div class="woocommerce-error">' . __('Product has incorrect parameters! Missing field ', 'wc-kalkulator') . '[' . $field->data("title") . '] in product #' . absint($product_id) . '</div>'
                            ));
                        }
                    }
                }
            } else {
                wp_send_json(array(
                    'result' => 'failure',
                    'messages' => '<div class="woocommerce-error">' . __('Corrupted data! You cannot checkout.', 'wc-kalkulator') . '</div>'
                ));
            }
        }

    }

    /**
     * Get the id from current Product
     *
     * This method return the post ID when this is a Product page.
     * Method also tries to get product ID from shortcode tag [product_page]
     *
     * @return int
     * @since 1.1.0
     */
    public static function get_id()
    {
        /**
         * Read cached value and return if exists
         */
        $cached_id = Cache::get('Product_get_id');
        if ($cached_id)
            return $cached_id;

        global $post;
        $product_id = 0;

        if (is_product()) {
            $product_id = $post->ID;
        } else if (!empty($post->post_content) && strstr($post->post_content, '[product_page')) {
            $product_id = absint(Helper::get_id_from_shortcode_tag($post->post_content, 'product_page'));
        }

        /**
         * Store value to class cache
         */
        Cache::store('Product_get_id', $product_id);

        return $product_id;
    }

    /**
     * Reduce inventory
     * @param $quantity
     * @param $order
     * @param $item
     * @return int
     * @since 1.4.0
     */
    public static function reduce_inventory($quantity, $order, $item)
    {
        if ($item->meta_exists('_wck_stock_reduction_multiplier')) {
            $multiplier = floatval( $item->get_meta('_wck_stock_reduction_multiplier', true) );
            return ceil($multiplier * $quantity);
        }
        return $quantity;
    }

}
