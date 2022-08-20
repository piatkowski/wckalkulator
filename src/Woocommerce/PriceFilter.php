<?php

namespace WCKalkulator\Woocommerce;

use WCKalkulator\FieldsetProduct;
use WCKalkulator\View;

/**
 * Class PriceFilter
 *
 * Add actions and filters to change the Woocommerce behaviour and appearance
 *
 * @package WCKalkulator\Woocommerce
 * @author Krzysztof Piątkowski
 * @license GPLv2
 * @since 1.0.0
 */
class PriceFilter
{
    /**
     * @var
     */
    protected static $instance;
    
    private function __construct()
    {
        add_filter('woocommerce_get_price_html', array($this, 'filter_price_html'), 10, 2);
    }
    
    /**
     * Get instance of a singleton
     *
     * @return PriceFilter
     * @since 1.1.0
     */
    public static function getInstance(): PriceFilter
    {
        if (self::$instance === null) {
            self::$instance = new static();
        }
        
        return self::$instance;
    }
    
    /**
     * Changes HTML of product price
     *
     * @param $price
     * @param $product
     * @return string
     * @since 1.0.0
     */
    public function filter_price_html($price, $product)
    {
        $product_id = $product->get_id();
        $fieldset = FieldsetProduct::getTempInstance(); //fix 1.5.4

        if ($fieldset->has_fieldset($product_id)) {
            
            $fieldset = $fieldset->init($product_id);
            
            if (intval($fieldset->filter_price_enabled) === 1) {
               
                return View::render('woocommerce/catalog_price', array(
                    'prefix' => $fieldset->filter_price_prefix,
                    'sufix' => $fieldset->filter_price_sufix,
                    'value' => $fieldset->filter_price_value,
                    'price' => $price
                ));
                
                /*
                if ($product->get_meta(Plugin::ADD_PRODUCT_PRICE_FIELD) === 'yes') {
                    if ($product->is_on_sale()) {
                        if ($product->is_type('variable')) {
                            $regular_price = $product->get_variation_regular_price('min');
                            $sale_price = $product->get_variation_sale_price('min');
                        } else {
                            $regular_price = $product->get_regular_price();
                            $sale_price = $product->get_sale_price();
                        }
                        $price = wc_format_sale_price($value + $regular_price, $value + $sale_price);
                    } else {
                        if ($product->is_type('variable')) {
                            $actual_price = $product->get_variation_regular_price('min');
                        } else {
                            $actual_price = $product->get_price();
                        }
                        $price = wc_price($value + $actual_price);
                    }
                } else {
                    $price = wc_price($value);
                }
                */
            }
        }
        return $price;
    }
    
    private function __clone()
    {
    }
}