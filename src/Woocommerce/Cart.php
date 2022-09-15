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
        add_filter('woocommerce_widget_cart_item_quantity', array($this, 'widget_cart_item_quantity'), 10, 3);
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
                $fieldset_version_hash = isset($cart_item['wckalkulator_fieldset_version_hash']) ? $cart_item['wckalkulator_fieldset_version_hash'] : '';
                $fieldset_id = isset($cart_item['wckalkulator_fieldset_id']) ? $cart_item['wckalkulator_fieldset_id'] : 0;

                if ($this->is_fieldset_outdated($fieldset_id, $fieldset_version_hash)) {
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
            return '<span class="quantity">' . sprintf('%s &times; %s', $cart_item['quantity'], strip_tags(wc_price($cart_item['wckalkulator_price']))) . '</span>';
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
    private function is_fieldset_outdated($id, $hash)
    {
        if ((int)$id === 0)
            return false;
        /**
         * Added in 1.1.0 - cache used fieldsets.
         */
        if (!isset($this->fielsets[$id])) {
            $this->fieldsets[$id] = FieldsetProduct::getInstance()->get_data($id);
        }

        return $hash !== $this->fieldsets[$id]->version_hash;
    }

    private function __clone()
    {
    }
}