<?php

namespace WCKalkulator;

use WCKalkulator\Woocommerce\Product;

/**
 * Class FieldsetProduct [Singleton]
 *
 * This class handles all core operations between the Fieldset (Field group) and the Product.
 *
 * @package WCKalkulator
 * @author Krzysztof Piątkowski
 * @license GPLv2
 * @since 1.1.0
 */
class FieldsetProduct
{
    /**
     * Singleton instances
     * @var array
     */
    private static $instance = null;
    
    /**
     * @var array
     */
    private $validation_notices = array();
    
    /**
     * @var object
     */
    private $data;
    
    /**
     * @var array of Field classes
     */
    private $fields;
    
    /**
     * @var array
     */
    private $user_input;
    
    /**
     * @var bool
     */
    private $is_valid = false;
    
    /**
     * @var int
     */
    private $product_id;
    
    /**
     * @var int
     */
    private $variation_id;
    
    /**
     * Private constructor for a singleton
     *
     * @since 1.1.0
     */
    protected function __construct()
    {
    }
    
    /**
     * Get instance of a singleton
     *
     * @return FieldsetProduct
     * @since 1.1.0
     */
    public static function getInstance(): FieldsetProduct
    {
        if (self::$instance === null) {
            self::$instance = new static();
        }
        
        return self::$instance;
    }
    
    /**
     * Initialize product_id, variation_id and load fieldset data from the Post meta.
     * + Initialize Fields classes
     *
     * @param null|int $product_id
     * @param null|int $variation_id
     * @return object
     * @since 1.1.0
     */
    public function init($product_id = null, $variation_id = null)
    {
        $this->product_id = ($product_id === null) ? Product::get_id() : $product_id;
        $this->variation_id = ($product_id === null) ? null : $variation_id;
        
        //if ($this->has_fieldset()) {
        $this->data = $this->get_data();
        $this->init_fields();
        //}
        return $this->data;
    }
    
    /**
     * Get all meta data for Fieldset
     * Pass $id = null to check current product
     *
     * @return object
     * @since 1.1.0
     */
    public function get_data($id = null)
    {
        $id = ($id === null) ? $this->get_id() : $id;
        $result = array(
            'id' => $id
        );
        if ($id > 0) {
            foreach (FieldsetPostType::$meta_keys as $key => $value) {
                $result[str_replace('_wck_', '', $key)] = $this->get_meta($key, $id);
            }
        }
        return (object)$result;
    }
    
    /**
     * Get ID of the Fieldset (Post ID)
     * Pass $product_id = null to check current product
     *
     * @return int
     * @since 1.1.0
     */
    public function get_id()
    {
        if ($this->product_id > 0) {
            return FieldsetAssignment::match($this->product_id);
        }
        return null;
    }
    
    /**
     * Get meta data by $key and post $id
     * Pass $id = null to check current product
     *
     * @param null|int $id Fieldset ID (Post ID)
     * @return mixed
     * @since 1.1.0
     */
    public function get_meta($key, $id = null)
    {
        $id = ($id === null) ? $this->get_id() : $id;
        if ($id > 0) {
            $key = (substr($key, 0, 5) === '_wck_') ? $key : '_wck_' . $key;
            return get_post_meta($id, $key, true);
        }
        return null;
    }
    
    /**
     * Initializes Fields classes. Load defined settings.
     *
     * $fieldset has all post meta data
     * $fields has instances of Fields classes
     *
     * @since 1.1.0
     */
    private function init_fields()
    {
        if (!isset($this->data->fieldset)) {
            return;
        }
        if (is_array($this->data->fieldset)) {
            foreach ($this->data->fieldset as $name => $data) {
                $FieldClass = "\\WCKalkulator\\Fields\\" . ucfirst($data["type"]) . "Field";
                $this->fields[$name] = new $FieldClass();
                $this->fields[$name]->fromArray($data);
            }
        }
    }
    
    /**
     * Check if Product has fieldset
     * Pass $current = 'current' to check current product
     *
     * @param string $current 'current' to check only current Product
     * @return bool
     * @since 1.1.0
     */
    public function has_fieldset($current = '')
    {
        if (is_product() && $current === 'current') {
            return FieldsetAssignment::match(Product::get_id()) !== null;
        } elseif ((int)$current > 0) {
            return FieldsetAssignment::match((int)$current) !== null;
        } elseif ($current === '') {
            return $this->get_id() !== null;
        }
        return false;
    }
    
    /**
     * Check if Product has expression (mode !== "off")
     * Pass $current = 'current' to check current product
     *
     * @param string $current 'current' to check only current Product
     * @return bool
     * @since 1.1.0
     */
    public function has_expression($current = '')
    {
        $id = 0;
        if (is_product() && $current === 'current') {
            $id = FieldsetAssignment::match(Product::get_id());
        } elseif ((int)$current > 0) {
            $id = FieldsetAssignment::match((int)$current) !== null;
        } elseif ($current === '') {
            $id = $this->get_id();
        }
        
        return $id > 0 && $this->get_data($id)->choose_expression_type !== "off";
    }
    
    /**
     * Cannot unserialize a singleton
     * @throws \Exception
     * @since 1.1.0
     */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize a singleton.");
    }
    
    /**
     * Get the product_id
     *
     * @return int
     * @since 1.0.0
     */
    public function product_id()
    {
        return $this->product_id;
    }
    
    /**
     * Get the fieldset data
     *
     * @param $key
     * @return mixed|null
     * @since 1.1.0
     */
    public function fieldset()
    {
        return $this->data;
    }
    
    /**
     * Get the version hash
     *
     * @return mixed|null
     * @since 1.0.0
     */
    public function version_hash()
    {
        return $this->data->version_hash;
    }
    
    /**
     * Get fieldset id
     *
     * @return mixed|null
     * @since 1.0.0
     */
    public function id()
    {
        return $this->data->id;
    }
    
    /**
     * Return HTML string for product page
     *
     * @return string|null
     * @since 1.0.0
     */
    public function render()
    {
        
        return View::render('woocommerce/product', array(
            'product_id' => $this->product_id,
            'variation_id' => $this->variation_id
        ));
        
    }
    
    /**
     * Return one field by its name. If field does not exists, return false
     *
     * @param $name
     * @return bool|array
     * @since 1.0.0
     */
    public function field($name)
    {
        if (array_key_exists($name, $this->fields)) {
            return $this->fields[$name]->data;
        }
        return false;
    }
    
    /**
     * Validate user input
     *
     * @param array $input
     * @param bool $is_ajax_cart
     * @return bool
     * @since 1.0.0
     */
    public function validate($input, $is_ajax_cart = false)
    {
        $this->user_input = $input;
        
        if ($is_ajax_cart) {
            $this->is_valid = $this->validate_for_expression();
        } else {
            $this->is_valid = $this->validate_names() && $this->validate_values();
        }
        
        if (!$this->is_valid) {
            $this->user_input = array();
            return false;
        }
        
        $this->add_static_prices($input);
        
        return $this->is_valid;
    }
    
    /**
     * Validates only fields, which will be used to calculate the price. Use by ajax calls: src/Ajax.php
     *
     * @return bool
     * @since 1.0.0
     */
    public function validate_for_expression()
    {
        if (!is_array($this->expression())) {
            return true;
        }
        $field_names = array_keys($this->user_input);
        foreach ($this->fields() as $field) {
            if ($field->use_expression()) {
                $field_name = $field->data("name");
                if (strpos('{' . $field_name . '}', $this->expression('expr')) !== false) {
                    if (!$field->validate($this->user_input[$field_name])) {
                        return false;
                    }
                }
            }
        }
        return true;
    }
    
    /**
     * Get the expression
     *
     * @return mixed|null
     * @since 1.0.0
     */
    public function expression($key = '')
    {
        if ($key === '') {
            return $this->data->expression;
        }
        
        if (is_array($this->data->expression) && isset($this->data->expression[$key])) {
            return $this->data->expression[$key];
        }
        
        return;
    }
    
    /**
     * Get fields
     *
     * @return array
     * @since 1.0.0
     */
    public function fields()
    {
        return $this->fields;
    }
    
    /**
     * Check if $input has all required fields.
     *
     * @return bool
     * @since 1.0.0
     */
    public function validate_names()
    {
        $field_names = array_keys($this->user_input);
        $is_valid = true;
        foreach ($this->fields() as $field) {
            $field_name = $field->data("name");
            if ((!in_array($field_name, $field_names) || $this->user_input[$field_name] === '') && $field->is_required()) {
                $this->validation_notices[] = sprintf(
                    __('Field %s is required.', 'wc-kalkulator'),
                    $field->data("title")
                );
                $is_valid = false;
            }
        }
        return $is_valid;
    }
    
    /**
     * Validate's user input based on fields
     *
     * @return bool
     * @since 1.0.0
     */
    public function validate_values()
    {
        $filtered_input = array();
        foreach ($this->fields() as $field) {
            
            $field_name = $field->data("name");
            
            if (!array_key_exists($field_name, $this->user_input)) {
                continue;
            }
            $value = $this->user_input[$field_name];
            
            if (!$field->validate($value)) {
                $this->validation_notices[] = sprintf(
                    __('Field %s has incorrect value.', 'wc-kalkulator'),
                    $field->data("title")
                );
                return false;
            }
            
            $filtered_input[$field_name] = $value;
        }
        $this->user_input = $filtered_input;
        return true;
    }
    
    /**
     * Add field's static price to $user_input
     *
     * @param $input
     * @return void
     * @since 1.2.0
     */
    public function add_static_prices($input)
    {
        if (!is_array($this->user_input)) {
            return;
        }
        $field_names = array_keys($this->user_input);
        foreach ($this->fields() as $field) {
            $name = $field->data("name");
            if ($field->data("price") !== null && in_array($name, $field_names)) {
                $static_price = Sanitizer::sanitize($field->data("price"), "price");
                if ($field->type() === "checkbox") {
                    $static_price = intval((int)$this->user_input[$name] === 1) * $static_price;
                }
                if (empty($this->user_input[$field->data("name")])) {
                    $static_price = 0;
                }
                $this->user_input[$name] = $static_price;
            }
            $this->register_extra_input_parameters($field, $input[$name]);
        }
    }
    
    /**
     * Add extra input parameters field:param to the $user_input
     *
     * @param $field
     * @param $input
     * @since 1.2.0
     */
    public function register_extra_input_parameters($field, $input)
    {
        $name = $field->data("name");
        
        switch ($field->type()) {
            case 'rangedatepicker':
                $date_from = strtotime($input['from']);
                $date_to = strtotime($input['to']);
                $this->user_input[$name . ':date_from'] = $date_from;
                $this->user_input[$name . ':date_to'] = $date_to;
                $this->user_input[$name . ':days'] = abs(round(($date_to - $date_from) / 86400));
                break;
            case 'datepicker':
                $this->user_input[$name . ':date'] = strtotime($input);
                break;
            case 'checkboxgroup':
                $input = array_map('floatval', $input);
                $this->user_input[$name . ':max'] = is_null($input) ? 0 : max($input);
                $this->user_input[$name . ':min'] = is_null($input) ? 0 : min($input);
                $this->user_input[$name . ':sum'] = is_null($input) ? 0 : array_sum($input);
                break;
        }
        
        foreach(GlobalParameter::get_all() as $name => $value) {
            $this->user_input['global:' . $name] = $value;
        }
    }
    
    /**
     * Returns field names
     *
     * @return array
     * @since 1.0.0
     */
    public function fields_names()
    {
        return array_keys($this->fields);
    }
    
    /**
     * Returns array of validation notices
     *
     * @return array
     * @since 1.0.0
     */
    public function validation_notices()
    {
        return $this->validation_notices;
    }
    
    /**
     * Returns expression value based on user input
     *
     * @return array
     * @since 1.0.0
     */
    public function calculate()
    {
        if ($this->is_valid) {
            if ((int)$this->product_id > 0) {
                $product_helper = new ProductHelper($this->product_id, $this->variation_id);
                if ($product_helper->is_valid() && isset($_POST["quantity"])) {
                    $this->user_input["product_price"] = $product_helper->price();
                    $this->user_input["product_regular_price"] = $product_helper->regular_price();
                    $this->user_input["quantity"] = absint($_POST["quantity"]);
                } else {
                    return Ajax::response('error', __("Cannot access product data!", "wc-kalkulator"));
                }
            }
            
            $parser = new ExpressionParser($this->expression(), $this->user_input);
            if ($parser->is_ready()) {
                $price = $parser->execute();
                return $price;
            } else {
                return Ajax::response('error', $parser->error);
            }
        } else {
            return Ajax::response('error', __("Fields are not valid!", "wc-kalkulator"));
        }
    }
    
    /**
     * Cannot clone singleton
     *
     * @since 1.1.0
     */
    protected function __clone()
    {
    }
}