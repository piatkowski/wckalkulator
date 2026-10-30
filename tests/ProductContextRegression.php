<?php
/** Dependency-free regressions for explicit and changing product contexts. */
namespace WCKalkulator {
    class FieldsetAssignment
    {
        public static $assignments = array();
        public static function match($product_id) { return isset(self::$assignments[$product_id]) ? self::$assignments[$product_id] : null; }
    }
    class FieldsetPostType
    {
        public static $meta_keys = array('_wck_choose_expression_type' => '');
    }
    class Helper
    {
        public static function get_id_from_shortcode_tag($content, $tag)
        {
            return preg_match('/\\[product_page[^]]*id=["\\\']?(\\d+)/', $content, $match) ? (int) $match[1] : 0;
        }
    }
}
namespace {
    class WC_Product {}
}
namespace WCKalkulator\Woocommerce {
    class ProductContextTestProduct extends \WC_Product
    {
        private $id;
        public function __construct($id) { $this->id = $id; }
        public function get_id() { return $this->id; }
    }
}
namespace {
    function absint($value) { return abs((int) $value); }
    function is_product() { return false; }
    function get_post_meta($id, $key, $single = true)
    {
        $types = array(77 => 'oneline', 88 => 'off');
        return $key === '_wck_choose_expression_type' && isset($types[$id]) ? $types[$id] : '';
    }
    require __DIR__ . '/../src/FieldsetProduct.php';
    require __DIR__ . '/../src/Woocommerce/Product.php';

    $reflection = new \ReflectionClass('WCKalkulator\\FieldsetProduct');
    $fieldset = $reflection->newInstanceWithoutConstructor();
    $field = $reflection->getProperty('data');
    $field->setValue($fieldset, (object) array('id' => 77));
    \WCKalkulator\FieldsetAssignment::$assignments = array(123 => 77, 456 => 88);

    $context_reflection = new \ReflectionClass('WCKalkulator\\Woocommerce\\ProductContextTestProduct');
    $GLOBALS['product'] = new \WCKalkulator\Woocommerce\ProductContextTestProduct(123);
    $first_product_id = \WCKalkulator\Woocommerce\Product::get_id();
    $GLOBALS['product'] = new \WCKalkulator\Woocommerce\ProductContextTestProduct(456);
    $second_product_id = \WCKalkulator\Woocommerce\Product::get_id();
    unset($GLOBALS['product'], $GLOBALS['post']);
    $empty_product_id = \WCKalkulator\Woocommerce\Product::get_id();
    $GLOBALS['post'] = (object) array('post_content' => '[product_page id=789]');
    $shortcode_product_id = \WCKalkulator\Woocommerce\Product::get_id();
    unset($GLOBALS['post']);

    $product_id_property = $reflection->getProperty('product_id');
    $product_id_property->setValue($fieldset, 123);
    $GLOBALS['post'] = (object) array('ID' => 999);
    $fieldset->js_api();
    $js_api_product_id = $fieldset->product_id();
    unset($GLOBALS['post']);

    $passes = array(
        'explicit product ID resolves its fieldset expression' => $fieldset->has_expression(123) === true,
        'explicit second product ID uses its own expression setting' => $fieldset->has_expression(456) === false,
        'active product context is read afresh' => $first_product_id === 123 && $second_product_id === 456,
        'missing global post and product return zero' => $empty_product_id === 0,
        'ordinary page product shortcode resolves its product' => $shortcode_product_id === 789,
        'inline API setup preserves initialized product context' => $js_api_product_id === 123,
    );
    $failed = array();
    foreach ($passes as $name => $passed) {
        if (!$passed) { $failed[] = $name; }
    }
    echo 'Product context regression: ' . (count($passes) - count($failed)) . '/' . count($passes) . ' passed' . PHP_EOL;
    if ($failed) { echo implode(PHP_EOL, $failed) . PHP_EOL; exit(1); }
}
