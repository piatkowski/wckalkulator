<?php

namespace WCKalkulator\Woocommerce;

use WCKalkulator\Cache;
use WCKalkulator\FieldsetAssignment;
use WCKalkulator\FieldsetProduct;
use WCKalkulator\Helper;
use WCKalkulator\Plugin;
use WCKalkulator\PrivateUploads;
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
    /** Cart key pairs to replace after WooCommerce confirms add_to_cart success. */
    private static $pending_cart_replacements = array();
    private static $default_price = 0;
    private static $validated_field_inputs = array();

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
        add_action('woocommerce_add_to_cart', array(__CLASS__, 'finish_cart_item_replacement'), 100, 6);
        add_action('woocommerce_before_calculate_totals', array(__CLASS__, 'before_calculate_totals'), 1000, 1);
        add_action('woocommerce_check_cart_items', array(__CLASS__, 'check_cart_price_errors'));
        add_action('woocommerce_store_api_cart_errors', array(__CLASS__, 'store_api_cart_price_errors'), 10, 2);
        add_filter('woocommerce_cart_item_name', array(__CLASS__, 'cart_item_name'), 10, 3);
        add_action('woocommerce_checkout_create_order_line_item', array(__CLASS__, 'checkout_create_order_line_item'), 10, 4);
        add_action('woocommerce_after_checkout_validation', array(__CLASS__, 'validate_checkout_uploads'), 10, 2);
        /*
         * Removed from 1.5.4
         * add_action('woocommerce_before_add_to_cart_button', array(__CLASS__, 'price_block'));
         */
        add_filter('woocommerce_order_item_quantity', array(__CLASS__, 'reduce_inventory'), 10, 3);
        add_filter('woocommerce_cart_item_required_stock_is_not_enough', array(__CLASS__, 'validate_cart_required_stock'), 10, 3);
        add_action('woocommerce_after_cart_item_quantity_update', array(__CLASS__, 'refresh_stock_multiplier'), 10, 4);
        add_filter('woocommerce_can_restock_refunded_items', array(__CLASS__, 'restock_refunded_items'), 10, 3);
        add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_scripts'));
        add_action('woocommerce_before_order_itemmeta', array(__CLASS__, 'display_itemmeta_for_admin'), 10, 3);
        add_filter('woocommerce_hidden_order_itemmeta', array(__CLASS__, 'hide_itemmeta'));

        PriceFilter::getInstance();
        Cart::getInstance();

        //remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
        //add_filter('woocommerce_structured_data_product_offer', array(__CLASS__, 'override_structured_data'), 10, 2);
    }

    /** Return the stock units required for a customer quantity and multiplier. */
    public static function required_stock_quantity($quantity, $multiplier)
    {
        if ((!is_int($quantity) && !is_float($quantity) && !is_numeric($quantity))
            || (!is_int($multiplier) && !is_float($multiplier) && !is_numeric($multiplier))
            || !is_finite((float) $quantity) || !is_finite((float) $multiplier)
            || (float) $quantity <= 0 || (float) $multiplier <= 0
            || (float) $quantity > 1000000 || (float) $multiplier > 1000000) {
            return false;
        }

        $required = ceil((float) $quantity * (float) $multiplier);
        return is_finite($required) && $required > 0 && $required <= 1000000000000
            ? (int) $required
            : false;
    }

    /** Check a prospective cart line against all lines sharing the stock pool. */
    private static function has_enough_stock_for_add($product_id, $variation_id, $quantity, $multiplier)
    {
        $required = self::required_stock_quantity($quantity, $multiplier);
        if ($required === false) {
            return false;
        }
        $product = wc_get_product($variation_id ? $variation_id : $product_id);
        if (!$product || !$product->managing_stock() || $product->backorders_allowed()) {
            return true;
        }
        if (!$product->is_in_stock()) {
            return false;
        }

        $managed_id = $product->get_stock_managed_by_id();
        $already_required = 0;
        $cart = function_exists('WC') && WC() ? WC()->cart : null;
        if (is_object($cart) && method_exists($cart, 'get_cart')) {
            $editing_key = isset($_POST['_wck_edit']) && is_scalar($_POST['_wck_edit'])
                ? sanitize_text_field(wp_unslash((string) $_POST['_wck_edit']))
                : '';
            foreach ($cart->get_cart() as $cart_key => $cart_item) {
                if ($editing_key !== '' && $cart_key === $editing_key) {
                    continue;
                }
                if (empty($cart_item['data']) || !is_object($cart_item['data'])
                    || !method_exists($cart_item['data'], 'get_stock_managed_by_id')
                    || (int) $cart_item['data']->get_stock_managed_by_id() !== (int) $managed_id) {
                    continue;
                }
                if (!empty($cart_item['wckalkulator_stock_error'])) {
                    return false;
                }
                $line_multiplier = isset($cart_item['wckalkulator_stock_reduction_multiplier'])
                    ? $cart_item['wckalkulator_stock_reduction_multiplier']
                    : 1;
                if (!isset($cart_item['quantity'])) {
                    return false;
                }
                $line_required = self::required_stock_quantity($cart_item['quantity'], $line_multiplier);
                if ($line_required === false) {
                    return false;
                }
                $already_required += $line_required;
            }
        }

        $held = self::held_stock_quantity($product);
        return (float) $product->get_stock_quantity() >= $held + $already_required + $required;
    }

    /** Apply multiplier-adjusted quantities to WooCommerce cart stock checks. */
    public static function validate_cart_required_stock($not_enough, $product, $cart_item)
    {
        if (!$product || !is_object($product) || !method_exists($product, 'get_stock_managed_by_id')) {
            return $not_enough;
        }
        $cart = function_exists('WC') && WC() ? WC()->cart : null;
        if (!is_object($cart) || !method_exists($cart, 'get_cart')) {
            return $not_enough;
        }

        $required = 0;
        $managed_id = $product->get_stock_managed_by_id();
        foreach ($cart->get_cart() as $item) {
            if (empty($item['data']) || !is_object($item['data'])
                || !method_exists($item['data'], 'get_stock_managed_by_id')
                || (int) $item['data']->get_stock_managed_by_id() !== (int) $managed_id) {
                continue;
            }
            if (!empty($item['wckalkulator_stock_error'])) {
                return true;
            }
            $multiplier = isset($item['wckalkulator_stock_reduction_multiplier'])
                ? $item['wckalkulator_stock_reduction_multiplier']
                : 1;
            if (!isset($item['quantity'])) {
                return true;
            }
            $line_required = self::required_stock_quantity($item['quantity'], $multiplier);
            if ($line_required === false) {
                return true;
            }
            $required += $line_required;
        }

        if (!method_exists($product, 'managing_stock') || !$product->managing_stock()
            || (method_exists($product, 'backorders_allowed') && $product->backorders_allowed())) {
            return $not_enough;
        }
        $held = self::held_stock_quantity($product);
        return $not_enough || (float) $product->get_stock_quantity() < $held + $required;
    }

    /** Match WooCommerce's exclusion of the current classic/Store API checkout order. */
    private static function held_stock_quantity($product)
    {
        if (!function_exists('wc_get_held_stock_quantity')) {
            return 0;
        }
        $order_id = 0;
        $woocommerce = function_exists('WC') ? WC() : null;
        if (is_object($woocommerce) && isset($woocommerce->session) && is_object($woocommerce->session)
            && method_exists($woocommerce->session, 'get')) {
            $order_id = absint($woocommerce->session->get('order_awaiting_payment'));
            if (!$order_id) {
                $order_id = absint($woocommerce->session->get('store_api_draft_order', 0));
            }
        }
        return (float) wc_get_held_stock_quantity($product, $order_id);
    }

    /** Re-evaluate quantity-dependent multiplier formulas when cart quantity changes. */
    public static function refresh_stock_multiplier($cart_item_key, $quantity, $old_quantity, $cart)
    {
        if (!is_object($cart) || empty($cart->cart_contents[$cart_item_key])) {
            return;
        }
        $item = $cart->cart_contents[$cart_item_key];
        if (empty($item['wckalkulator_fields']) || !is_array($item['wckalkulator_fields'])
            || empty($item['product_id']) || empty($item['data']) || !is_object($item['data'])
            || !method_exists($item['data'], 'get_price')) {
            return;
        }
        $fieldset = FieldsetProduct::getInstance();
        $product_id = absint($item['product_id']);
        $variation_id = isset($item['variation_id']) ? absint($item['variation_id']) : 0;
        if (!$fieldset->has_fieldset($product_id)) {
            $cart->cart_contents[$cart_item_key]['wckalkulator_stock_error'] = true;
            return;
        }
        if (!self::init_fieldset_for_cart_item($fieldset, $item, $product_id, $variation_id)) {
            $cart->cart_contents[$cart_item_key]['wckalkulator_stock_error'] = true;
            return;
        }
        if (!$fieldset->set_cart_context($item['wckalkulator_fields'], array(
            'quantity' => $quantity,
            'product_price' => isset($item['wckalkulator_base_price']) ? $item['wckalkulator_base_price'] : $item['data']->get_price(),
            'attributes' => isset($item['variation']) && is_array($item['variation']) ? $item['variation'] : array(),
            'parameter_snapshot' => isset($item['wckalkulator_parameter_snapshot']) && is_array($item['wckalkulator_parameter_snapshot'])
                ? $item['wckalkulator_parameter_snapshot'] : array()
        ))) {
            $cart->cart_contents[$cart_item_key]['wckalkulator_stock_error'] = true;
            return;
        }
        $multiplier = $fieldset->stock_reduction_multiplier();
        if ($multiplier === false || self::required_stock_quantity($quantity, $multiplier) === false) {
            $cart->cart_contents[$cart_item_key]['wckalkulator_stock_error'] = true;
            return;
        }
        $cart->cart_contents[$cart_item_key]['wckalkulator_stock_reduction_multiplier'] = $multiplier;
        unset($cart->cart_contents[$cart_item_key]['wckalkulator_stock_error']);
    }

    /**
     * Change product price in structured data
     *
     * @param $markup_offer
     * @param $product
     * @return array
     * @since 1.6.0
     */
    /*public static function override_structured_data($markup_offer, $product)
    {
        if(self::$default_price > 0) {
            $formated_price = wc_format_decimal(self::$default_price, wc_get_price_decimals());
            $markup_offer['price'] = $formated_price;
            $markup_offer['priceSpecification']['price'] = $formated_price;
        }
        return $markup_offer;
    }*/

    /**
     * Add styles and scripts to the product page
     *
     * @since 1.0.0
     */
    public static function enqueue_scripts()
    {
        $fieldset = FieldsetProduct::getInstance();
        if ($fieldset->has_fieldset('current')) {

            $fieldset->init();

            /*
             * @since 1.5.4
             */
            if ($fieldset->has_expression('current')) {
                $fieldset->add_action_price_block();
            }

            /*$default_price = 0;
            $fieldset = FieldsetProduct::getInstance();
            $fieldset->init();
            $fieldset->set_default_input();
            if ($fieldset->validate(true)) {
                try {
                    $default_price = $fieldset->calculate_minimum();
                    $default_price = $default_price['value'];
                } catch (\Exception $e) {
                    $default_price = 0;
                }
            }
            self::$default_price = $default_price;*/

            /*
             * "Price.min.css" forces price block to be hidden.
             * Price blocks should be visible if the product is type of "variable" and user has selected 'variation_prices_visible' option.
             * If price calculation is disabled, the option is not used and price.min.css will not load.
             */
            $product = wc_get_product(Product::get_id());
            $is_variable_product = $product && $product->is_type('variable');
            if ($fieldset->has_expression('current') && !($is_variable_product && $fieldset->is_variation_prices_visible())) {
                wp_register_style('wckalkulator_price_css', Plugin::url() . '/assets/css/price.min.css');
                wp_enqueue_style('wckalkulator_price_css');
            }

            wp_register_style('wckalkulator_product_css', Plugin::url() . '/assets/css/product.min.css');
            wp_enqueue_style('wckalkulator_product_css');

            // WooCommerce registers these public handles. The prefixed one is
            // current; retain the legacy handle for older WooCommerce releases.
            $tiptip_handle = wp_script_is('wc-jquery-tiptip', 'registered')
                ? 'wc-jquery-tiptip'
                : 'jquery-tiptip';
            wp_enqueue_script($tiptip_handle);

            /**
             * Enqueue scripts and styles from fields used in fieldset
             */
            $enqueued = array();
            $localize = array();

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
                wp_add_inline_script(
                    $script,
                    'var ' . str_replace('-', '_', $script) . '_options = ' . wp_json_encode($options) . ';'
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
                $field_name = $field->data('name');
                $value = isset($cart_fields[$field_name]) ? $cart_fields[$field_name] : '';
                if (isset($cart_fields['_files']) && is_array($cart_fields['_files']) && isset($cart_fields['_files'][$field_name])) {
                    $value = $cart_fields['_files'][$field_name];
                }
                $html['fields'][] = array(
                    'html' => wp_kses($field->render_for_product($value), Sanitizer::allowed_html()) . "\n",
                    //colspan since 1.4.0
                    'colspan' => max((int)$field->data('colspan'), 1),
                    //type, group since 1.5.0
                    'type' => $field->type(),
                    'group' => $field->group()
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
        // Preserve a rejection made by an earlier WooCommerce validation filter.
        if (!$validation) {
            $variation_id = isset($_POST['variation_id']) ? absint($_POST['variation_id']) : 0;
            unset(self::$validated_field_inputs[(int) $product_id . ':' . (int) $variation_id]);
            return false;
        }

        /*if (Ajax::is_doing()) {
            return false;
        }*/
        $fieldset = FieldsetProduct::getInstance();
        if ($fieldset->has_fieldset($product_id)) {
            if (isset($_POST['variation_id']) && !is_scalar($_POST['variation_id'])) {
                wc_add_notice(__('Product options have an invalid format.', 'wc-kalkulator'), 'error');
                return false;
            }
            $variation_id = isset($_POST['variation_id']) ? absint(wp_unslash($_POST['variation_id'])) : 0;

            $fieldset->init($product_id, $variation_id);
            $user_input = $fieldset->get_user_input();
            $validation = $fieldset->validate(false, array('quantity' => $quantity));
            if (!$validation) {
                foreach ($fieldset->validation_notices() as $notice) {
                    wc_add_notice($notice, 'error');
                }
            }

            if ($fieldset->has_expression()) {
                try {
                    $calc = $fieldset->calculate();
                    if ($calc['is_error']) {
                        wc_add_notice(__('Cannot calculate the correct price. Please check your options or contact the store.', 'wc-kalkulator'), 'error');
                        $validation = false;
                    }
                } catch (\Exception $e) {
                    error_log($e);
                    return false;
                } catch (\Throwable $e) {
                    error_log($e);
                    return false;
                }
            }

            if ($validation) {
                $multiplier = $fieldset->stock_reduction_multiplier();
                if ($multiplier === false) {
                    wc_add_notice(__('The inventory multiplier is invalid. Please contact the store.', 'wc-kalkulator'), 'error');
                    $validation = false;
                } elseif (!self::has_enough_stock_for_add($product_id, $variation_id, $quantity, $multiplier)) {
                    wc_add_notice(__('There is not enough stock for the selected quantity.', 'wc-kalkulator'), 'error');
                    $validation = false;
                }
            }

            if ($validation && !$fieldset->handle_temp_upload()) {
                wc_add_notice(__('The uploaded file could not be stored. Please try again.', 'wc-kalkulator'), 'error');
                $validation = false;
            }
            if ($validation) {
                self::$validated_field_inputs[(int) $product_id . ':' . (int) $variation_id] = $user_input;
            }
        }

        return $validation;
    }

    /**s
     * Render price block to show Ajax result (Total price)
     *
     * @since 1.1.0
     */
    public static function price_block()
    {
        echo View::render('woocommerce/price_block', array(
            'default_price' => self::$default_price
        ));
    }

    /**
     * Add validated field data to the cart. The unit price is recalculated from cart context.
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

        $input_key = (int) $product_id . ':' . (int) $variation_id;
        $has_validated_input = isset(self::$validated_field_inputs[$input_key]);
        if ($has_validated_input || FieldsetProduct::getInstance()->has_fieldset($product_id)) {
            $fieldset = FieldsetProduct::getInstance();
            if ($has_validated_input) {
                $user_input = self::$validated_field_inputs[$input_key];
                unset(self::$validated_field_inputs[$input_key]);
            } else {
                $fieldset->init($product_id, $variation_id);
                $user_input = $fieldset->get_user_input();
            }
            $success = $has_validated_input;
            if (!$has_validated_input) {
                if (!$fieldset->validate(false, array('quantity' => $quantity))) {
                    throw new \RuntimeException(__('Product has incorrect parameters. Please review the selected options.', 'wc-kalkulator'));
                }

                if ($fieldset->has_expression()) {
                    try {
                        $calc = $fieldset->calculate();
                        if (!$calc['is_error']) {
                            $success = true;
                        } else {
                            throw new \RuntimeException(__('The product price could not be calculated. Please review the selected options.', 'wc-kalkulator'));
                        }
                    } catch (\Exception $e) {
                        error_log($e);
                        throw $e;

                    } catch (\Throwable $e) {
                        error_log($e);
                        throw new \RuntimeException(__('The product price could not be calculated. Please try again.', 'wc-kalkulator'));
                    }
                } else {
                    $success = true;
                }
            }
            /** If cart item key has been added, then save user input */
            if ($success) {
                $multiplier = $fieldset->stock_reduction_multiplier();
                if ($multiplier === false) {
                    throw new \Exception(__('The inventory multiplier is invalid. Please contact the store.', 'wc-kalkulator'));
                }
                if (!self::has_enough_stock_for_add($product_id, $variation_id, $quantity, $multiplier)) {
                    throw new \Exception(__('There is not enough stock for the selected quantity.', 'wc-kalkulator'));
                }
                if (!$has_validated_input && !$fieldset->handle_temp_upload()) {
                    wc_add_notice(__('The uploaded file could not be stored. Please try again.', 'wc-kalkulator'), 'error');
                    throw new \RuntimeException(__('The uploaded file could not be stored. Please try again.', 'wc-kalkulator'));
                }
                $cart_item_data['wckalkulator_fields'] = $user_input;
                $cart_item_data['wckalkulator_fieldset_version_hash'] = $fieldset->version_hash();
                $cart_item_data['wckalkulator_fieldset_id'] = $fieldset->id();
                $configuration_snapshot = $fieldset->configuration_snapshot();
                if (!is_array($configuration_snapshot)) {
                    throw new \RuntimeException(__('The product configuration could not be saved. Please try again.', 'wc-kalkulator'));
                }
                $cart_item_data['wckalkulator_fieldset_snapshot_version'] = 1;
                $cart_item_data['wckalkulator_fieldset_snapshot'] = $configuration_snapshot;
                $cart_item_data['wckalkulator_parameter_snapshot'] = $fieldset->calculation_parameter_snapshot();
                $cart_item_data['wckalkulator_stock_reduction_multiplier'] = $multiplier;

                // Replace an edited cart line only after the new input and uploads are ready.
                if (isset($_POST['_wck_edit']) && is_scalar($_POST['_wck_edit'])) {
                    $old_key = sanitize_text_field(wp_unslash((string) $_POST['_wck_edit']));
                    if ($old_key !== '' && !empty(WC()->cart->get_cart_item($old_key))) {
                        self::$pending_cart_replacements[$old_key] = true;
                    }
                }
            }

        }

        return $cart_item_data;
    }

    /** Remove an edited line only after the replacement line has been added. */
    public static function finish_cart_item_replacement($new_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data)
    {
        if (!is_array(self::$pending_cart_replacements) || !self::$pending_cart_replacements) {
            return;
        }
        foreach (array_keys(self::$pending_cart_replacements) as $old_item_key) {
            unset(self::$pending_cart_replacements[$old_item_key]);
            if ($old_item_key !== $new_item_key && function_exists('WC') && WC() && isset(WC()->cart)
                && !empty(WC()->cart->get_cart_item($old_item_key))) {
                WC()->cart->remove_cart_item($old_item_key);
            }
        }
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
        if (!is_object($cart) || !method_exists($cart, 'get_cart') || !isset($cart->cart_contents) || !is_array($cart->cart_contents)) {
            return;
        }

        foreach ($cart->get_cart() as $key => $value) {
            if (!isset($value['wckalkulator_fields'], $value['data']) || !is_array($value['wckalkulator_fields']) || !is_object($value['data']) || !method_exists($value['data'], 'get_price')) {
                continue;
            }

            $quantity = isset($value['quantity']) && is_numeric($value['quantity']) && is_finite((float) $value['quantity'])
                ? (float) $value['quantity']
                : 0;
            $product_id = isset($value['product_id']) ? absint($value['product_id']) : 0;
            $variation_id = isset($value['variation_id']) ? absint($value['variation_id']) : 0;
            $current_price = $value['data']->get_price();

            if ($quantity <= 0 || $product_id < 1) {
                self::mark_cart_price_error($cart, $key);
                continue;
            }

            $fieldset = FieldsetProduct::getInstance();
            if (!self::init_fieldset_for_cart_item($fieldset, $value, $product_id, $variation_id)) {
                self::mark_cart_price_error($cart, $key);
                continue;
            }

            $has_expression = $fieldset->has_expression();
            if ($has_expression && (!is_numeric($current_price) || !is_finite((float) $current_price))) {
                self::mark_cart_price_error($cart, $key);
                continue;
            }

            $base_price = is_numeric($current_price) && is_finite((float) $current_price) ? (float) $current_price : 0;
            if ($has_expression && isset($value['wckalkulator_last_applied_price'], $value['wckalkulator_base_price']) && is_numeric($value['wckalkulator_last_applied_price']) && is_numeric($value['wckalkulator_base_price']) && abs($base_price - (float) $value['wckalkulator_last_applied_price']) < 0.0000001) {
                $base_price = (float) $value['wckalkulator_base_price'];
            }

            $attributes = isset($value['variation']) && is_array($value['variation']) ? $value['variation'] : array();
            if (!$fieldset->set_cart_context($value['wckalkulator_fields'], array(
                'quantity' => $quantity,
                'product_price' => $base_price,
                'attributes' => $attributes,
                'parameter_snapshot' => isset($value['wckalkulator_parameter_snapshot']) && is_array($value['wckalkulator_parameter_snapshot'])
                    ? $value['wckalkulator_parameter_snapshot'] : array()
            ))) {
                self::mark_cart_price_error($cart, $key);
                continue;
            }

            if (!$has_expression) {
                $cart->cart_contents[$key]['wckalkulator_formula_fields'] = $fieldset->calculate_formula_fields();
                unset($cart->cart_contents[$key]['wckalkulator_price_error']);
                continue;
            }

            try {
                $calculation = $fieldset->calculate();
                if (!is_array($calculation) || !isset($calculation['is_error']) || $calculation['is_error'] || !isset($calculation['value']) || !is_numeric($calculation['value']) || !is_finite((float) $calculation['value']) || (float) $calculation['value'] < 0) {
                    self::mark_cart_price_error($cart, $key);
                    continue;
                }

                $price = (float) $calculation['value'];
                if (!method_exists($value['data'], 'set_price')) {
                    self::mark_cart_price_error($cart, $key);
                    continue;
                }
                $value['data']->set_price($price);
                $cart->cart_contents[$key]['wckalkulator_price'] = $price;
                $cart->cart_contents[$key]['wckalkulator_base_price'] = $base_price;
                $cart->cart_contents[$key]['wckalkulator_last_applied_price'] = $price;
                $cart->cart_contents[$key]['wckalkulator_formula_fields'] = $fieldset->calculate_formula_fields();
                unset($cart->cart_contents[$key]['wckalkulator_price_error']);
            } catch (\Exception $e) {
                self::mark_cart_price_error($cart, $key);
            } catch (\Throwable $e) {
                self::mark_cart_price_error($cart, $key);
            }
        }
    }

    /** Load the configuration captured by a cart line, or validate a legacy line. */
    private static function init_fieldset_for_cart_item($fieldset, $cart_item, $product_id, $variation_id)
    {
        $fieldset_id = isset($cart_item['wckalkulator_fieldset_id']) ? (int) $cart_item['wckalkulator_fieldset_id'] : 0;
        if (isset($cart_item['wckalkulator_fieldset_snapshot_version'], $cart_item['wckalkulator_fieldset_snapshot'])) {
            if ((int) $cart_item['wckalkulator_fieldset_snapshot_version'] !== 1
                || !is_array($cart_item['wckalkulator_fieldset_snapshot'])
                || !isset($cart_item['wckalkulator_fieldset_snapshot']['id'])
                || (int) $cart_item['wckalkulator_fieldset_snapshot']['id'] !== $fieldset_id
                || !$fieldset->init_from_snapshot($cart_item['wckalkulator_fieldset_snapshot'], $product_id, $variation_id)
                || !$fieldset->configuration_is_valid()) {
                return false;
            }
            return (int) $fieldset->id() === $fieldset_id;
        }

        if ($fieldset_id < 1 || FieldsetAssignment::match($product_id) !== $fieldset_id) {
            return false;
        }
        $fieldset->init($product_id, $variation_id);
        if ((int) $fieldset->id() !== $fieldset_id || !$fieldset->configuration_is_valid()) {
            return false;
        }
        return !isset($cart_item['wckalkulator_fieldset_version_hash'])
            || (string) $cart_item['wckalkulator_fieldset_version_hash'] === (string) $fieldset->version_hash();
    }

    /** Validate a persisted snapshot before retaining the cart line after session load. */
    public static function cart_item_snapshot_is_valid($cart_item)
    {
        if (!is_array($cart_item) || !isset($cart_item['product_id'], $cart_item['wckalkulator_fieldset_id'])) {
            return false;
        }
        $fieldset = FieldsetProduct::getInstance();
        return self::init_fieldset_for_cart_item(
            $fieldset,
            $cart_item,
            absint($cart_item['product_id']),
            isset($cart_item['variation_id']) ? absint($cart_item['variation_id']) : 0
        );
    }

    /**
     * Mark a cart line when its current price cannot be recalculated safely.
     *
     * @param object $cart
     * @param string $key
     * @return void
     */
    private static function mark_cart_price_error($cart, $key)
    {
        if (isset($cart->cart_contents) && is_array($cart->cart_contents) && isset($cart->cart_contents[$key])) {
            $cart->cart_contents[$key]['wckalkulator_price_error'] = true;
        }
    }

    /**
     * Prevent checkout when a cart line could not be repriced.
     *
     * @return void
     */
    public static function check_cart_price_errors()
    {
        if (!function_exists('WC')) {
            return;
        }

        $woocommerce = WC();
        if (!$woocommerce || !isset($woocommerce->cart) || !is_object($woocommerce->cart)) {
            return;
        }

        foreach ($woocommerce->cart->get_cart() as $value) {
            if (!empty($value['wckalkulator_price_error'])) {
                wc_add_notice(__('A product price could not be recalculated. Please remove it from the cart and add it again.', 'wc-kalkulator'), 'error');
                return;
            }
        }
    }

    /**
     * Prevent Store API checkout when a cart line could not be repriced.
     *
     * @param object $errors
     * @param object $cart
     * @return void
     */
    public static function store_api_cart_price_errors($errors, $cart)
    {
        if (!is_object($errors) || !method_exists($errors, 'add') || !is_object($cart) || !method_exists($cart, 'get_cart')) {
            return;
        }

        foreach ($cart->get_cart() as $value) {
            if (!empty($value['wckalkulator_price_error'])) {
                $errors->add('wckalkulator_price_recalculation_failed', __('A product price could not be recalculated. Please remove it from the cart and add it again.', 'wc-kalkulator'));
                return;
            }
        }
        $checked_stock = array();
        foreach ($cart->get_cart() as $value) {
            if (empty($value['data']) || !is_object($value['data']) || !method_exists($value['data'], 'get_stock_managed_by_id')) {
                continue;
            }
            $managed_id = (int) $value['data']->get_stock_managed_by_id();
            if (isset($checked_stock[$managed_id])) {
                continue;
            }
            $checked_stock[$managed_id] = true;
            if (self::validate_cart_required_stock(false, $value['data'], $value)) {
                $errors->add('wckalkulator_stock_quantity_unavailable', __('There is not enough stock for the selected quantity.', 'wc-kalkulator'));
                return;
            }
        }
        self::check_cart_uploads($cart, $errors);
    }

    /** Ensure every cart upload can be finalized before checkout creates an order. */
    public static function validate_checkout_uploads($data, $errors)
    {
        if (!function_exists('WC') || !WC() || !isset(WC()->cart) || !is_object(WC()->cart)) {
            return;
        }
        self::check_cart_uploads(WC()->cart, $errors);
    }

    private static function check_cart_uploads($cart, $errors)
    {
        if (!is_object($cart) || !method_exists($cart, 'get_cart') || !is_object($errors) || !method_exists($errors, 'add')) {
            return;
        }
        foreach ($cart->get_cart() as $cart_item) {
            $product_id = isset($cart_item['product_id']) ? absint($cart_item['product_id']) : 0;
            $variation_id = isset($cart_item['variation_id']) ? absint($cart_item['variation_id']) : 0;
            if (!$product_id || !isset($cart_item['wckalkulator_fields']) || !is_array($cart_item['wckalkulator_fields'])) {
                continue;
            }
            $fieldset = FieldsetProduct::getInstance();
            if (!self::init_fieldset_for_cart_item($fieldset, $cart_item, $product_id, $variation_id)) {
                $errors->add('wck_fieldset_snapshot_invalid', __('A product configuration in the cart is no longer valid. Please remove and add the item again.', 'wc-kalkulator'));
                continue;
            }
            $visibility_error = null;
            $visibility = $fieldset->evaluate_visibility($cart_item['wckalkulator_fields'], $visibility_error);
            if ($visibility === false) {
                $errors->add('wck_field_visibility_invalid', __('Product field visibility rules are invalid. Please contact the store.', 'wc-kalkulator'));
                continue;
            }
            $upload_data = isset($cart_item['wckalkulator_fields']['_files']) && is_array($cart_item['wckalkulator_fields']['_files'])
                ? $cart_item['wckalkulator_fields']['_files']
                : array();
            foreach ($fieldset->fields() as $name => $field) {
                if (isset($visibility[$name]) && !$visibility[$name]) {
                    continue;
                }
                if (!method_exists($field, 'upload')) {
                    continue;
                }
                if (!isset($upload_data[$name])) {
                    if ($field->is_required()) {
                        $errors->add('wck_required_upload_missing', sprintf(__('A required file for “%s” is missing. Please upload it again.', 'wc-kalkulator'), $field->data('title')));
                    }
                    continue;
                }
                if (!$field->upload($upload_data[$name])) {
                    $errors->add('wck_upload_finalize_failed', sprintf(__('The file for “%s” could not be stored. Please try again.', 'wc-kalkulator'), $field->data('title')));
                }
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

        if (isset($cart_item['wckalkulator_fields']) && is_array($cart_item['wckalkulator_fields'])) {
            $fieldset = FieldsetProduct::getInstance();
            if (!self::init_fieldset_for_cart_item($fieldset, $cart_item, $product_id, $variation_id)) {
                return $item_name;
            }

            $visibility_error = null;
            $visibility = isset($cart_item['wckalkulator_fields']) && is_array($cart_item['wckalkulator_fields'])
                ? $fieldset->evaluate_visibility($cart_item['wckalkulator_fields'], $visibility_error)
                : false;
            if ($visibility === false) {
                return $item_name;
            }

            $html = '';
            foreach ($fieldset->fields() as $name => $field) {
                if (isset($visibility[$name]) && !$visibility[$name]) {
                    continue;
                }

                if (isset($cart_item['wckalkulator_fields'][$name])) {
                    $value = isset($cart_item['wckalkulator_fields']['_files'][$name]) ? $cart_item['wckalkulator_fields']['_files'][$name] : $cart_item['wckalkulator_fields'][$name];
                    $html .= $field->render_for_cart($value);
                }
                if (isset($cart_item['wckalkulator_formula_fields'][$name])) {
                    $value = $cart_item['wckalkulator_formula_fields'][$name]['value'];
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
        $values = is_array($values) ? $values : array();
        if (!array_key_exists('wckalkulator_fields', $values)) {
            return;
        }
        $product_id = absint($values["product_id"]);
        $variation_id = isset($values['variation_id']) ? absint($values['variation_id']) : 0;

        if (array_key_exists("wckalkulator_fields", $values)) {
            $fieldset = FieldsetProduct::getInstance();
            if (!self::init_fieldset_for_cart_item($fieldset, $values, $product_id, $variation_id)) {
                throw new \RuntimeException(__('The saved product configuration is no longer valid. Please remove the item and add it again.', 'wc-kalkulator'));
            }

            $order_fields = $values["wckalkulator_fields"];
            $visibility_error = null;
            $visibility = $fieldset->evaluate_visibility($order_fields, $visibility_error);
            if ($visibility === false) {
                wc_add_notice(__('Product field visibility rules are invalid. Please contact the store.', 'wc-kalkulator'), 'error');
                throw new \RuntimeException('WC Kalkulator field visibility rules are invalid.');
            }

            $item->add_meta_data('_wck_stock_reduction_multiplier', $values['wckalkulator_stock_reduction_multiplier']);
            $item->add_meta_data('_wck_formula_fields', $values['wckalkulator_formula_fields']);
            if (isset($values['wckalkulator_fieldset_snapshot']) && is_array($values['wckalkulator_fieldset_snapshot'])) {
                $item->add_meta_data('_wck_fieldset_snapshot_version', (int) $values['wckalkulator_fieldset_snapshot_version']);
                $item->add_meta_data('_wck_fieldset_snapshot', $values['wckalkulator_fieldset_snapshot']);
                $item->add_meta_data('_wck_parameter_snapshot', isset($values['wckalkulator_parameter_snapshot'])
                    && is_array($values['wckalkulator_parameter_snapshot']) ? $values['wckalkulator_parameter_snapshot'] : array());
            }
            $private_file_ids = array();

            foreach ($fieldset->fields() as $name => $field) {
                    if (isset($visibility[$name]) && !$visibility[$name]) {
                        unset($order_fields[$name]);
                        if (isset($order_fields['_files'][$name])) {
                            unset($order_fields['_files'][$name]);
                        }
                        continue;
                    }

                    if (isset($order_fields[$name])) {
                        $field_value = $field->order_item_value($order_fields[$name]);
                        if (method_exists($field, 'upload') && isset($order_fields['_files'][$name])) {
                            if ($field->upload($order_fields['_files'][$name])) {
                                $item->add_meta_data($field->data('title') . __(' (original name)', 'wc-kalkulator'), esc_html($order_fields['_files'][$name]['original_name']), true);
                                $item->add_meta_data($field->data('title') . __(' (size)', 'wc-kalkulator'), round($field_value / 1000000, 2) . ' MB', true);
                                $file_id = $order_fields['_files'][$name]['file_id'];
                                $private_file_ids[] = $file_id;
                                $download_url = PrivateUploads::download_url($order->get_id(), $file_id, $order->get_order_key());
                                $item->add_meta_data($field->data('title') . __(' (download)', 'wc-kalkulator'), $download_url, true);
                            } else {
                                wc_add_notice(sprintf(__('The file for “%s” could not be stored. The order was not completed.', 'wc-kalkulator'), $field->data('title')), 'error');
                                throw new \RuntimeException('WC Kalkulator could not finalize a customer upload.');
                            }
                        } else {
                            $item->add_meta_data($field->data('title'), $field_value, true);
                        }
                    } else {
                        if ($field->is_required()) {
                            throw new \RuntimeException(sprintf(
                                __('A required product option is missing: %s.', 'wc-kalkulator'),
                                $field->data('title')
                            ));
                        }
                    }
                }
            foreach (isset($order_fields['_files']) ? $order_fields['_files'] : array() as $file_name => $file_data) {
                unset($order_fields['_files'][$file_name]['tmp_name'], $order_fields['_files'][$file_name]['upload_tmp'], $order_fields['_files'][$file_name]['legacy_upload_tmp']);
            }
            $item->add_meta_data('_wck_fields', $order_fields);
            if ($private_file_ids) {
                $item->add_meta_data('_wck_private_files', $private_file_ids);
            }
        } else {
            throw new \RuntimeException(__('Product options are missing or invalid. Please remove the item and add it again.', 'wc-kalkulator'));
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
        global $post, $product;

        if (function_exists('is_product') && is_product() && is_object($post) && isset($post->ID)) {
            return absint($post->ID);
        }

        // WooCommerce shortcodes set the active product while rendering each product.
        if (is_object($product) && is_a($product, '\WC_Product') && method_exists($product, 'get_id')) {
            return absint($product->get_id());
        }

        if (is_object($post) && !empty($post->post_content) && strpos($post->post_content, '[product_page') !== false) {
            return absint(Helper::get_id_from_shortcode_tag($post->post_content, 'product_page'));
        }

        return 0;
    }

    /**
     * Display certain item meta only for admin (for example: special fields)
     *
     * @return void
     * @since 1.5.0
     */
    public static function display_itemmeta_for_admin($item_id, $item, $product)
    {
        if (!(is_admin() && $item->is_type('line_item')))
            return;

        $formula_fields = $item->get_meta('_wck_formula_fields');

        if (!empty($formula_fields)) {
            echo '<table class="display_meta"><tbody>';
            foreach ($formula_fields as $field) {
                echo '<tr><th>' . esc_html($field['title']) . ':</th>';
                echo '<td>' . esc_html($field['value']) . '</td></tr>';
            }
            echo '</tbody></table>';
        }
    }

    /**
     * Set hidden item meta
     *
     * @return array
     * @since 1.5.0
     */
    public static function hide_itemmeta($order_items)
    {
        $order_items[] = '_wck_stock_reduction_multiplier';
        $order_items[] = '_wck_formula_fields';
        $order_items[] = '_wck_fields';
        $order_items[] = '_wck_fieldset_snapshot_version';
        $order_items[] = '_wck_fieldset_snapshot';
        $order_items[] = '_wck_parameter_snapshot';
        $order_items[] = '_wck_private_files';
        return $order_items;
    }

    /**
     * Reduce inventory quantity
     *
     * @param $quantity
     * @param $order
     * @param $item
     * @return int
     * @since 1.4.0
     */
    public static function reduce_inventory($quantity, $order, $item)
    {
        if ($item->meta_exists('_wck_stock_reduction_multiplier')) {
            $multiplier = $item->get_meta('_wck_stock_reduction_multiplier', true);
            $required = self::required_stock_quantity($quantity, $multiplier);
            if ($required === false) {
                error_log('WC Kalkulator: invalid stock multiplier for order item ' . (method_exists($item, 'get_id') ? $item->get_id() : 'unknown'));
                return 0;
            }
            return $required;
        }
        return $quantity;
    }

    /**
     * Apply the same effective stock-unit calculation to refunded quantities.
     * Returning false prevents WooCommerce from restocking the unscaled customer quantity.
     */
    public static function restock_refunded_items($can_restock, $order, $refunded_line_items)
    {
        if (!$can_restock || !is_object($order) || !method_exists($order, 'get_items') || !is_array($refunded_line_items)) {
            return $can_restock;
        }

        foreach ($order->get_items() as $item_id => $item) {
            if (!isset($refunded_line_items[$item_id]['qty'])) {
                continue;
            }
            $product = $item->get_product();
            $reduced = $item->get_meta('_reduced_stock', true);
            $refund_quantity = $refunded_line_items[$item_id]['qty'];
            if (!$product || !$product->managing_stock() || !is_numeric($reduced) || (float) $reduced <= 0
                || !is_numeric($refund_quantity) || (float) $refund_quantity <= 0) {
                continue;
            }

            if ($item->meta_exists('_wck_stock_reduction_multiplier')) {
                $multiplier = $item->get_meta('_wck_stock_reduction_multiplier', true);
                $restock_quantity = self::required_stock_quantity($refund_quantity, $multiplier);
                if ($restock_quantity === false) {
                    error_log('WC Kalkulator: invalid stock multiplier while restocking refund for order item ' . $item_id);
                    $order->add_order_note(__('Stock could not be restored for a refunded item because its saved stock multiplier is invalid.', 'wc-kalkulator'));
                    continue;
                }
            } else {
                $restock_quantity = (float) $refund_quantity;
            }
            $restock_quantity = min((float) $restock_quantity, (float) $reduced);
            if ($restock_quantity <= 0) {
                continue;
            }

            $old_stock = $product->get_stock_quantity();
            $new_stock = wc_update_product_stock($product, $restock_quantity, 'increase');
            if (is_wp_error($new_stock)) {
                $order->add_order_note(sprintf(__('Unable to restore stock for item %s.', 'woocommerce'), $product->get_name()));
                error_log('WC Kalkulator: stock restoration failed for refunded order item ' . $item_id);
                continue;
            }

            $item->update_meta_data('_reduced_stock', (float) $reduced - $restock_quantity);
            $item->update_meta_data(
                '_restock_refunded_items',
                (float) $item->get_meta('_restock_refunded_items', true) + $restock_quantity
            );
            $item->save();
            $order->add_order_note(sprintf(
                __('Item #%1$s stock increased from %2$s to %3$s.', 'woocommerce'),
                $product->get_id(),
                $old_stock,
                $new_stock
            ));
            do_action('woocommerce_restock_refunded_item', $product->get_id(), $old_stock, $new_stock, $order, $product);
        }

        return false;
    }

}
