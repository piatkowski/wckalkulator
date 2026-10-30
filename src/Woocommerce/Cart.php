<?php

namespace WCKalkulator\Woocommerce;

use WCKalkulator\FieldsetProduct;

/**
 * Class Cart
 *
 * @package WCKalkulator\Woocommerce
 * @author Krzysztof Piątkowski
 * @license GPLv2
 * @since 1.1.0
 */
class Cart
{
    /**
     * @var Cart
     */
    protected static $instance;

    /**
     * @var array
     */
    protected $fieldsets;

    private function __construct()
    {
        add_action('woocommerce_cart_loaded_from_session', array($this, 'check_cart'));
        add_filter('woocommerce_get_cart_item_from_session', array($this, 'restore_legacy_uploads'), 10, 3);
        add_filter('woocommerce_widget_cart_item_quantity', array($this, 'widget_cart_item_quantity'), 10, 3);
    }

    /** Restore staged files from 1.6.1 when a cart session is read. */
    public function restore_legacy_uploads($cart_item, $values, $key)
    {
        if (!isset($cart_item['wckalkulator_fields']['_files']) || !is_array($cart_item['wckalkulator_fields']['_files'])) {
            return $cart_item;
        }
        foreach ($cart_item['wckalkulator_fields']['_files'] as $name => $file) {
            $converted = \WCKalkulator\PrivateUploads::restore_legacy_staged($file);
            if ($converted !== false) {
                $cart_item['wckalkulator_fields']['_files'][$name] = $converted;
            }
        }
        return $cart_item;
    }

    /**
     * Get instance of a singleton
     *
     * @return Cart
     * @since 1.1.0
     */
    public static function getInstance(): Cart
    {
        if (self::$instance === null) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    /**
     * Check cart items for outdated fieldsets. Removes products with outdated fieldsets
     */
    public function check_cart()
    {
        $cart_items = WC()->cart->get_cart();
        if ($cart_items && count($cart_items) > 0) {
            foreach ($cart_items as $cart_item_key => $cart_item) {
                if ($this->is_fieldset_outdated($cart_item)) {
                    wc_add_notice(
                        sprintf(__('The product "%s" has been changed by shop manager and removed from the cart. ', 'woocommerce_kalkulator'),
                            $cart_item['data']->get_title()
                        ), 'error');
                    WC()->cart->remove_cart_item($cart_item_key);
                }
            }
        }
    }

    /**
     * Show the calculated product price in cart widget (cart popup)
     *
     * @param $html
     * @param $cart_item
     * @param $cart_item_key
     * @return mixed|string
     * @since 1.6.0
     */
    public function widget_cart_item_quantity($html, $cart_item, $cart_item_key)
    {
        if (isset($cart_item['wckalkulator_price'])) {
            $price = $cart_item['wckalkulator_price'];
            if (isset($cart_item['data']) && is_object($cart_item['data']) && is_numeric($price)) {
                $price = wc_get_price_to_display($cart_item['data'], array('price' => (float) $price));
            }
            return '<span class="quantity">' . sprintf('%s &times; %s', $cart_item['quantity'], strip_tags(wc_price($price))) . '</span>';
        } else {
            return $html;
        }
    }

    /**
     * Check if fieldset has been updated by admin/manager.
     *
     * @param $id
     * @param $hash
     * @return bool
     * @since 1.0.0
     */
    private function is_fieldset_outdated($cart_item)
    {
        $id = isset($cart_item['wckalkulator_fieldset_id']) ? (int) $cart_item['wckalkulator_fieldset_id'] : 0;
        if ((int)$id === 0)
            return false;

        // New cart lines keep the configuration they were priced with.
        if (isset($cart_item['wckalkulator_fieldset_snapshot_version'], $cart_item['wckalkulator_fieldset_snapshot'])) {
            return !Product::cart_item_snapshot_is_valid($cart_item);
        }

        $product_id = isset($cart_item['product_id']) ? absint($cart_item['product_id']) : 0;
        if (!$product_id || \WCKalkulator\FieldsetAssignment::match($product_id) !== $id) {
            return true;
        }
        $hash = isset($cart_item['wckalkulator_fieldset_version_hash']) ? $cart_item['wckalkulator_fieldset_version_hash'] : '';
        /**
         * Added in 1.1.0 - cache used fieldsets.
         */
        if (!isset($this->fieldsets[$id])) {
            $this->fieldsets[$id] = FieldsetProduct::getInstance()->get_data($id);
        }

        return $hash !== $this->fieldsets[$id]->version_hash;
    }

    private function __clone()
    {
    }
}
