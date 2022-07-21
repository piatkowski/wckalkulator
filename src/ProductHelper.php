<?php

namespace WCKalkulator;

/**
 * Class ProductHelper
 *
 * @package WCKalkulator
 * @author Krzysztof Piątkowski
 * @license GPLv2
 * @since 1.0.0
 */
final class ProductHelper
{
    /**
     * @var \WC_Product
     */
    protected $product;

    /**
     * @var int
     */
    protected $product_id;

    /**
     * @var int
     */
    protected $variation_id;

    /**
     * @deprecated from 1.1.0
     * @var bool
     * private $can_add_price;
     */

    /**
     * ProductHelper constructor.
     *
     * @param $product_id
     * @param int $variation_id
     * @since 1.0.0
     */
    function __construct($product_id, $variation_id = 0)
    {
        $this->product_id = absint($product_id);
        $this->variation_id = absint($variation_id);
        $this->product = wc_get_product($this->product_id);

        /**
         * Removed in 1.1.0
         *
         * $this->can_add_price = $this->product->get_meta(Plugin::ADD_PRODUCT_PRICE_FIELD) === 'yes';
         */

        if ($this->product && $this->product->is_type("variable")) {
            if ($this->variation_id > 0) {
                $this->product = wc_get_product($this->variation_id);
            } else {
                $this->product = null;
            }
        }
    }

    /**
     * If property "product" is valid
     *
     * @return bool
     * @since 1.0.0
     */
    public function is_valid()
    {
        if ($this->product) {
            return true;
        }
        return false;
    }

    /**
     * Get actual price, regular or sale
     *
     * @return mixed
     * @since 1.0.0
     */
    public function price()
    {
        if ($this->product->is_on_sale()) {
            return $this->sale_price();
        }
        return $this->regular_price();
    }

    /**
     * Get sale price of the product or variation
     *
     * @return mixed
     * @since 1.0.0
     */
    public function sale_price()
    {
        return floatval($this->product->get_sale_price());
    }

    /**
     * Get regular price of the product or variation
     *
     * @return mixed
     * @since 1.0.0
     */
    public function regular_price()
    {
        return floatval($this->product->get_regular_price());
    }

    /**
     * Check if the product is on sale
     *
     * @return mixed
     * @since 1.0.0
     */
    public function is_on_sale()
    {
        return $this->product->is_on_sale();
    }

    /**
     * Get discount price
     *
     * @return mixed
     * @since 1.0.0
     */
    public function discount_price()
    {
        return $this->regular_price() - $this->sale_price();
    }

    /**
     * Get product weight
     *
     * @return string
     * @since 1.3.1
     */
    public function get_weight()
    {
        return floatval($this->product->get_weight());
    }

    /**
     * Get product weight
     *
     * @return string
     * @since 1.3.1
     */
    public function get_length()
    {
        return floatval($this->product->get_length());
    }

    /**
     * Get product weight
     *
     * @return string
     * @since 1.3.1
     */
    public function get_width()
    {
        return floatval($this->product->get_width());
    }

    /**
     * Get product weight
     *
     * @return string
     * @since 1.3.1
     */
    public function get_height()
    {
        return floatval($this->product->get_height());
    }

    /**
     *
     * [DEPRECATED]
     *
     * Check if product has option "Add Product Price" checked
     *
     * @return bool
     * @deprecated from 1.1.0
     * @since 1.0.0
     */
    public function can_add_price()
    {
        return false; //$this->can_add_price;
    }

}