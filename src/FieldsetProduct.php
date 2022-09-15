<?php

namespace WCKalkulator;

use WCKalkulator\Woocommerce\Attribute;
use WCKalkulator\Woocommerce\Product;

/**
 * Class FieldsetProduct
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
     * Main instance
     * @var array
     */
    private static $instance = null;

    /**
     * Temporary instance for Price Filter feature
     * @var array
     */
    private static $instance_temp = null;

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
     * Get second instance (for temporary use)
     *
     * @return FieldsetProduct
     * @since 1.5.4
     */
    public static function getTempInstance(): FieldsetProduct
    {
        if (self::$instance_temp === null) {
            self::$instance_temp = new static();
        }

        return self::$instance_temp;
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
        $this->variation_id = ($variation_id === null) ? null : $variation_id;

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
        if ($current === 'current' && (is_product() || Product::get_id() > 0)) {
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
        if ($current === 'current' && (is_product() || Product::get_id() > 0)) {
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
     * Get state of variation prices visibility
     *
     * @return bool
     * @since 1.5.0
     */
    public function is_variation_prices_visible()
    {
        return isset($this->data->variation_prices_visible) && (int)$this->data->variation_prices_visible === 1;
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
     * Get fieldset layout (1- or 2- cols)
     *
     * @return int  - number of columns (1 or 2 cols layout)
     * @since 1.4.0
     */
    public function layout()
    {
        if (!empty($this->fields) && reset($this->fields)->data('layout') === 'two-col') {
            return 2;
        }
        return 1;
    }

    /**
     * Return HTML string for product page
     *
     * @param array $html - array with keys 'hidden', 'fields'
     * @return string|null
     * @since 1.0.0
     */
    public function render($html)
    {
        $_html = $html['hidden'];
        foreach ($html['fields'] as $field) {
            $_html .= $field['html'];
        }
        return View::render('woocommerce/product', array(
            'product_id' => $this->product_id,
            'variation_id' => $this->variation_id,
            'hidden' => $html['hidden'],
            'fields' => $html['fields'],
            'html' => $_html, //for backward compatibility
            'layout' => $this->layout()
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
            return $this->fields[$name]->data();
        }
        return false;
    }

    public function set_default_input()
    {
        $data = array();
        foreach ($this->fields() as $name => $field) {
            $field = (object) $field->data();
            if ((int)$field->use_expression === 1) {
                if($field->type === 'checkboxgroup') {
                    $data[$name] = array($field->default_value);
                } else {
                    $data[$name] = $field->default_value;
                }
            }
        }
        $this->user_input = $data;
    }

    /**
     * Gets user input from $_POST. Sanitize input
     *
     * @return array
     * @since 1.2.0
     */
    public function get_user_input()
    {
        $allowed_names = $this->fields_names();

        //if (isset($_POST['wck']) && is_array($_POST['wck'])) {
        $filtered_post = array();
        foreach ($allowed_names as $name) {
            if (isset($_POST['wck'][$name])) {
                $filtered_post[$name] = $_POST['wck'][$name];
            } else {
                /* Set Default values if the field is not in POST data */
                if ($this->field($name)['type'] === 'checkboxgroup') {
                    $filtered_post[$name] = array();
                }
            }
        }

        $user_input = Sanitizer::sanitize($filtered_post, 'array');
        //}

        $user_input['_files'] = array();

        /* Since v.1.3.1 upload path is defined in WCK Settings
        $customer_dir = '/wc-kalkulator/customer-data/' . date("Y/m/");
        $upload_path = wp_upload_dir()['basedir'] . $customer_dir;
        $upload_url = wp_upload_dir()['baseurl'] . $customer_dir;
        */

        $upload_path = Settings::get('upload_customer_data_dir') . date("Y/m/");
        $upload_url = str_replace(ABSPATH, get_site_url() . '/', $upload_path);

        if (isset($_FILES['wck'])) {
            foreach ($_FILES['wck']['name'] as $name => $file_name) {
                if (in_array($name, $allowed_names) && $_FILES['wck']['error'][$name] == 0) {
                    $validate = wp_check_filetype_and_ext($_FILES['wck']['tmp_name'][$name], $file_name);
                    if ($validate['ext'] !== false) {
                        $file = uniqid() . '.' . $validate['ext'];
                        $upload_file = wp_unique_filename($upload_path, $file);
                        $temp_file = 'wckalkulator_tmp_' . wp_unique_filename(get_temp_dir(), $file);
                        $user_input[$name] = $_FILES['wck']['size'][$name]; //fixed
                        $user_input['_files'][$name] = array(
                            'name' => $name,
                            'original_name' => $file_name,
                            'type' => $_FILES['wck']['type'][$name],
                            'tmp_name' => $_FILES['wck']['tmp_name'][$name],
                            'upload_path' => $upload_path . $upload_file,
                            'upload_url' => $upload_url . $upload_file,
                            'upload_tmp' => get_temp_dir() . $temp_file,
                            'size' => $_FILES['wck']['size'][$name],
                            'error' => 0,
                        );
                    }
                }
            }
        }

        $this->user_input = $user_input;

        return $this->user_input;
    }

    /**
     * Handles file upload to temp directory when adding to the cart
     *
     * @return void
     * @since 1.3.0
     */
    public function handle_temp_upload()
    {
        $files = isset($this->user_input['_files']) ? $this->user_input['_files'] : null;
        if (!empty($files) && !empty($this->user_input)) {
            foreach ($files as $name => $data) {
                if ($this->fields[$name]->validate($data)) {
                    $this->fields[$name]->upload_temp($data);
                }
            }
        }
    }

    /**
     * Validate user input
     *
     * @param bool $is_ajax_cart
     * @return bool
     * @since 1.0.0
     */
    public function validate($is_ajax_cart = false)
    {
        //$this->user_input = $input;

        if ($is_ajax_cart) {
            $this->is_valid = $this->validate_for_expression();
        } else {
            $this->is_valid = $this->validate_names() && $this->validate_values();
        }

        if (!$this->is_valid) {
            $this->user_input = array();
            return false;
        }

        $this->set_additional_input_variables();

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
        //Implode array as string
        if (is_array($this->expression('expr'))) {
            $expr = implode("", array_map(function ($a) {
                return implode("", $a);
            }, $this->expression('expr')));
        } else {
            $expr = $this->expression('expr');
        }

        foreach ($this->fields() as $field) {
            if ($field->use_expression()) {
                $field_name = $field->data("name");
                if (strpos('{' . $field_name . '}', $expr) !== false) {
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
     * @return array|string|null
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

        return null;
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
                    $field->data("title") . ' ' . $field->data("name")
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

            if (isset($this->user_input['_files'][$field_name])) {
                $value = $this->user_input['_files'][$field_name];
            } else {
                $value = $this->user_input[$field_name];
            }

            if (!$field->validate($value)) {
                $this->validation_notices[] = sprintf(
                    __('Field %s has incorrect value.', 'wc-kalkulator'),
                    $field->data("title")
                );
                return false;
            }

            $filtered_input[$field_name] = $value;
        }
        $files = $this->user_input['_files'];
        $this->user_input = $filtered_input;
        $this->user_input['_files'] = $files;
        return true;
    }

    /**
     * Add field's static price to $user_input
     *
     * @param $return
     * @return void|array
     * @since 1.2.0
     */
    public function set_additional_input_variables($return = false)
    {
        if (!$return && !is_array($this->user_input)) {
            return;
        }
        if(!$return) {
            $field_names = array_keys($this->user_input);
            foreach ($this->fields() as $field) {
                $name = $field->data("name");

                // Check if field has price paramter and its name is in user input
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

                if ($field->group() !== 'static' && isset($this->user_input[$name])) {
                    $this->set_additional_field_parameters($field, $this->user_input[$name]);
                }
            }
        }

        /*
         * Get extra values (products, dates, user, qty)
         */

        if ($this->product_id > 0) {
            $product_helper = new ProductHelper($this->product_id, $this->variation_id);
            if ($product_helper->is_valid()) {
                $this->user_input["product_price"] = $product_helper->price();
                $this->user_input["product_weight"] = $product_helper->get_weight();
                $this->user_input["product_width"] = $product_helper->get_width();
                $this->user_input["product_height"] = $product_helper->get_height();
                $this->user_input["product_length"] = $product_helper->get_length();
                $this->user_input["product_regular_price"] = $product_helper->regular_price();
                $this->user_input["product_is_on_sale"] = (bool)$product_helper->is_on_sale();
            }
        }

        $this->user_input["is_user_logged"] = (int)is_user_logged_in();
        $this->user_input["current_month"] = absint(current_time("n"));
        $this->user_input["day_of_month"] = absint(current_time("j"));
        $this->user_input["day_of_week"] = absint(current_time("w"));
        $this->user_input["current_hour"] = absint(current_time("G"));
        if (isset($_POST["quantity"])) {
            $this->user_input["quantity"] = absint($_POST["quantity"]);
        }

        /*
         * Get values of global parameters
         */
        foreach (GlobalParameter::get_all() as $name => $value) {
            $this->user_input['global:' . $name] = $value;
        }/*
         * @since 1.5.0 - get custom numerical value (wck_value) of product attribute term
         */;
        $this->user_input = array_merge($this->user_input, Attribute::from_request());

        /*
         * @since 1.5.0 - support for ACF integration
         */
        Cache::store("ACF_Post_IDs", array(
            "product_id" => $this->product_id, //highest priority
            "fieldset_id" => $this->get_id(),
            "variation_id" => $this->variation_id //lowest priority
        ));

        if($return) {
            return $this->user_input;
        }

    }

    /**
     * Add extra input parameters field:param to the $user_input
     *
     * @param $field
     * @param $input
     * @since 1.2.0
     */
    public function set_additional_field_parameters($field, $input)
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
                $this->user_input[$name . ':max'] = count($input) > 0 ? max($input) : 0;
                $this->user_input[$name . ':min'] = count($input) > 0 ? min($input) : 0;
                $this->user_input[$name . ':sum'] = count($input) > 0 ? array_sum($input) : 0;
                break;
            case 'text':
            case 'textarea':
                $this->user_input[$name . ':text'] = sanitize_text_field($input);
                break;
            case 'imageupload':
            case 'fileupload':
                if (is_array($input)) {
                    $size = $input['size'];
                } else {
                    $size = $input;
                }
                $this->user_input[$name . ':size'] = round(absint($size) / 1000000, 2);
                break;
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
            if ($this->product_id > 0) {
                $product_helper = new ProductHelper($this->product_id, $this->variation_id);
                if (!$product_helper->is_valid()) {
                    return Ajax::response('error', __("Select variation options first!", "wc-kalkulator"));
                }
            }
            $parser = new ExpressionParser($this->expression(), $this->user_input);
            if ($parser->is_ready()) {
                $result = $parser->execute();
                if (isset($result['value']) && $result['is_error'] === false) {
                    $this->user_input['total_price'] = $result['value'] * $this->user_input["quantity"];
                }
                return $result;
            } else {
                return Ajax::response('error', $parser->error);
            }
        } else {
            return Ajax::response('error', __("Fields are not valid!", "wc-kalkulator"));
        }
    }

    /**
     * Calculate minimum price amount to display in price block
     *
     * @return void
     * @since 1.6.0
     */
    public function calculate_minimum()
    {
        $parser = new ExpressionParser($this->expression(), $this->user_input);
        if ($parser->is_ready()) {
            return $parser->execute();
        }
        return -1;
    }

    /**
     * Calculates the value of formula fields
     *
     * @return array
     * @since 1.5.0
     */
    public function calculate_formula_fields()
    {
        $result = array();
        try {
            foreach ($this->fields() as $field) {
                if ($field->is_type("formula")) {
                    $expression = array(
                        'mode' => 'oneline',
                        'expr' => $field->data("content")
                    );
                    $parser = new ExpressionParser($expression, $this->user_input);
                    if ($parser->is_ready()) {
                        $calc = $parser->execute();
                        $result[$field->data("name")] = array(
                            'title' => $field->data("title"),
                            'name' => $field->data("name"),
                            'value' => $calc['is_error'] === false ? $calc['value'] : ' - error - '
                        );
                    }
                }
            }
        } catch (\Exception $e) {
            error_log($e);
        } catch (\Throwable $e) {
            error_log($e);
        }
        return $result;
    }

    /**
     * Get stock reduction multiplier.
     *
     * @return array|int
     * @since 1.4.0
     */
    public function stock_reduction_multiplier()
    {
        if ($this->product_id > 0) {
            try {
                $parser = new ExpressionParser(array(
                    'mode' => 'oneline',
                    'expr' => $this->data->stock_reduction_multiplier
                ), $this->user_input);
                if ($parser->is_ready()) {
                    $calc = $parser->execute();
                    if (!$calc['is_error']) {
                        return $calc['value'];
                    }
                }
            } catch (\Exception $e) {
                return 1;
            } catch (\Throwable $e) {
                return 1;
            }
        }
        return 1;
    }

    /**
     * Get field's visibility rules
     * @return string
     * @since 1.4.0
     */
    public function visibility_rules()
    {
        $rules = array();

        if (empty($this->fields())) {
            $this->init();
        }
        foreach ($this->fields() as $field) {
            if (!empty($field->data('visibility')))
                $rules[$field->data('name')] = json_decode(stripslashes($field->data('visibility')), true);
        }
        return $rules;
    }

    public function js_api()
    {
        global $post;
        $this->product_id = $post->ID;
        $js = $this->get_meta('javascript');

        /*
            Remove comments in JS code
            author Alexander Yancharuk @ https://stackoverflow.com/a/19510664
        */
        $pattern = '/(?:(?:\/\*(?:[^*]|(?:\*+[^*\/]))*\*+\/)|(?:(?<!\:|\\\|\')\/\/.*))/';
        $js = preg_replace($pattern, '', $js);

        $js = trim(str_replace(array("\r\n", "  ", "\t"), array("", " ", ""), $js));
        if (!empty($js)) {
            wp_add_inline_script('wck-ajax-script', '(function ($) { $(document).ready(function ($) { ' . html_entity_decode($js, ENT_QUOTES) . ' }) })(jQuery);');
        }
    }

    /**
     * Add action to render Price Block (before or after add to cart button)
     *
     * @return void
     * @since 1.5.4
     */
    public function add_action_price_block()
    {
        if ((int)$this->data->price_block_action === 1) {
            add_action('woocommerce_before_add_to_cart_button', array(Product::class, 'price_block'));
        } else {
            add_action('woocommerce_after_add_to_cart_button', array(Product::class, 'price_block'));
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