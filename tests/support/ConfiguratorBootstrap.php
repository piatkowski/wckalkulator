<?php
/** Source-only test doubles. Never loads vendor or WordPress/WooCommerce dependencies. */
namespace WCKalkulator {
    class Plugin {
        const NAME = 'wc-kalkulator';
        public static $defined_fields = array('WCKalkulator\\Fields\\NumberField', 'WCKalkulator\\Fields\\MeasurementField', 'WCKalkulator\\Fields\\SectionField', 'WCKalkulator\\Fields\\RepeaterField', 'WCKalkulator\\Fields\\TextField', 'WCKalkulator\\Fields\\SelectField', 'WCKalkulator\\Fields\\CheckboxField');
        public static function path() { return dirname(__DIR__, 2); }
    }
    class GlobalParameter { public static function get_all($public = false) { return array('rate' => 100); } }
    class FieldsetAssignment { public static function match($id) { return 7; } }
    class Cache { public static function store($key, $value) {} }
    class ProductHelper {
        private $id;
        public function __construct($id, $variation = 0) { $this->id = $id; }
        public function is_valid() { return true; }
        public function price() { return $this->id === 2 ? 50 : 30; }
        public function regular_price() { return $this->price(); }
        public function get_weight() { return 0; }
        public function get_width() { return 0; }
        public function get_height() { return 0; }
        public function get_length() { return 0; }
        public function is_on_sale() { return false; }
    }
    class Ajax { public static function response($status, $message) { return array('is_error' => true, 'value' => null); } }
    /** Explicit formula fixtures verify data flow and money, not the excluded Symfony parser. */
    class ExpressionParser {
        private $expression;
        private $variables;
        public $error = '';
        public function __construct($expression, $variables, $type = 'price') { $this->expression = $expression['expr']; $this->variables = $variables; }
        public function is_ready() { return true; }
        public function execute() {
            $v = $this->variables;
            switch ($this->expression) {
                case '{width} * {height} * {global:rate}': $result = $v['width'] * $v['height'] * $v['global:rate']; break;
                case '{product_price} + {items}': $result = $v['product_price'] + $v['items']; break;
                case '{items}': $result = $v['items']; break;
                case '{width}': $result = $v['width']; break;
                default: throw new \RuntimeException('Unexpected test formula: ' . $this->expression);
            }
            return array('is_error' => false, 'value' => $result);
        }
    }
}
namespace WCKalkulator\Woocommerce { class Attribute { public static function from_request($attributes = null) { return array(); } } }
namespace {
    error_reporting(E_ALL & ~E_DEPRECATED);
    if (!defined('ABSPATH')) { define('ABSPATH', '/test/'); }
    function _e($v) { echo $v; }
    function __($v) { return $v; }
    function esc_html__($v) { return esc_html($v); }
    function esc_html_e($v) { echo esc_html($v); }
    function esc_attr_e($v) { echo esc_attr($v); }
    function esc_html($v) { return htmlspecialchars((string) $v, ENT_QUOTES, 'UTF-8'); }
    function esc_attr($v) { return esc_html($v); }
    function esc_url($v) { return esc_attr($v); }
    function selected($a, $b) { if ((string) $a === (string) $b) { echo ' selected'; } }
    function checked($v) { if ($v) { echo ' checked'; } }
    function wp_json_encode($v) { return json_encode($v); }
    function wp_hash($v) { return 'fixture-hash'; }
    function sanitize_text_field($v) { return trim(strip_tags((string) $v)); }
    function sanitize_textarea_field($v) { return trim(strip_tags((string) $v)); }
    function wp_unslash($v) { return $v; }
    function wp_kses($v, $allowed) { return $v; }
    function wp_kses_post($v) { return $v; }
    function wp_kses_allowed_html($context) { return array(); }
    function get_stylesheet_directory() { return '/nonexistent-test-theme'; }
    function is_user_logged_in() { return false; }
    function current_time($format) { return '1'; }
    function absint($v) { return abs((int) $v); }
    function apply_filters($name, $value) { return $value; }
    function get_post_meta($id, $key, $single = true) { return ''; }
    function wc_add_notice($message, $type = '') { $GLOBALS['configurator_notices'][] = $message; }
    spl_autoload_register(function ($class) {
        if (strpos($class, 'WCKalkulator\\') !== 0) { return; }
        $path = dirname(__DIR__, 2) . '/src/' . str_replace('\\', '/', substr($class, 13)) . '.php';
        if (is_file($path)) { require_once $path; }
    });
    function configurator_fields() {
        $measurement = array('type' => 'measurement', 'title' => 'Width', 'name' => 'width', 'required' => '1', 'base_unit' => 'm', 'units' => 'mm,cm,m', 'min' => 0, 'max' => 10, 'step' => 0.01, 'precision' => 4, 'repeat_group' => 'items');
        return array(
            'size' => array('type' => 'section', 'name' => 'size', 'title' => 'Dimensions', 'section_mode' => 'step'),
            'items' => array('type' => 'repeater', 'name' => 'items', 'title' => 'Blinds', 'min_rows' => 1, 'max_rows' => 5, 'repeat_mode' => 'button', 'row_formula' => '{width} * {height} * {global:rate}'),
            'width' => $measurement,
            'height' => array_merge($measurement, array('name' => 'height', 'title' => 'Height')),
            'finish' => array('type' => 'section', 'name' => 'finish', 'title' => 'Summary', 'section_mode' => 'step'),
            'note' => array('type' => 'text', 'name' => 'note', 'title' => 'Note', 'required' => '0', 'min' => 0, 'max' => 100)
        );
    }
    function configurator_snapshot($fields = null, $expression = '{product_price} + {items}') {
        return array('id' => 7, 'schema_version' => 2, 'version_hash' => 'fixture', 'fieldset' => $fields === null ? configurator_fields() : $fields, 'choose_expression_type' => 'oneline', 'expression' => array('mode' => 'oneline', 'expr' => $expression));
    }
    function configurator_input() {
        return array('items' => array(
            array('width' => array('value' => '100', 'unit' => 'cm'), 'height' => array('value' => '2000', 'unit' => 'mm')),
            array('width' => array('value' => '0.5', 'unit' => 'm'), 'height' => array('value' => '100', 'unit' => 'cm'))
        ));
    }
}
