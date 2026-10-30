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

    /** @var array Server-evaluated visibility state for the current input. */
    private $field_visibility = array();

    /** @var bool Whether the current request payload had a valid shape. */
    private $request_input_valid = true;

    /** @var bool Whether the loaded fieldset passed its runtime schema checks. */
    private $configuration_valid = true;

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
        $this->data = null;
        $this->fields = array();
        $this->user_input = array();
        $this->is_valid = false;
        $this->validation_notices = array();
        $this->field_visibility = array();
        $this->request_input_valid = true;
        $this->configuration_valid = true;

        $this->product_id = ($product_id === null) ? Product::get_id() : $product_id;
        $this->variation_id = ($variation_id === null) ? null : $variation_id;

        //if ($this->has_fieldset()) {
        $this->data = $this->get_data();
        $this->init_fields();
        //}
        return $this->data;
    }

    /** Initialize this context from a versioned cart/order configuration snapshot. */
    public function init_from_snapshot($snapshot, $product_id, $variation_id = null)
    {
        $this->data = null;
        $this->fields = array();
        $this->user_input = array();
        $this->is_valid = false;
        $this->validation_notices = array();
        $this->field_visibility = array();
        $this->request_input_valid = true;
        $this->configuration_valid = true;
        $this->product_id = (int) $product_id;
        $this->variation_id = $variation_id === null ? null : (int) $variation_id;

        if (!is_array($snapshot) || !isset($snapshot['id']) || (int) $snapshot['id'] < 1
            || !isset($snapshot['fieldset']) || !is_array($snapshot['fieldset'])) {
            $this->configuration_valid = false;
            return false;
        }

        $this->data = (object) $snapshot;
        $this->init_fields();
        return $this->configuration_valid;
    }

    /** Return a plain, serializable copy of the active fieldset configuration. */
    public function configuration_snapshot()
    {
        if (!$this->configuration_valid || !is_object($this->data)) {
            return false;
        }
        $snapshot = get_object_vars($this->data);
        return is_array($snapshot) ? $snapshot : false;
    }

    public function configuration_is_valid()
    {
        return $this->configuration_valid;
    }

    /** Capture global and product-attribute values used by the configured formulas. */
    public function calculation_parameter_snapshot()
    {
        $snapshot = array();
        foreach (is_array($this->user_input) ? $this->user_input : array() as $name => $value) {
            if (is_string($name) && (strpos($name, 'global:') === 0 || strpos($name, 'pa:') === 0)) {
                $snapshot[$name] = $value;
            }
        }
        return $snapshot;
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
        $versioned = isset($this->data->schema_version) && (int) $this->data->schema_version > 0;
        if (!is_array($this->data->fieldset) || ($versioned && count($this->data->fieldset) > 200)) {
            $this->configuration_valid = false;
            return;
        }
        // Existing 1.6.1 configurations have no schema marker. Keep reading
        // them without applying rules introduced for newly saved documents.
        if ($versioned && !FieldsetPostType::validate_fieldset_schema($this->data->fieldset)) {
            $this->configuration_valid = false;
            return;
        }
        $mode = isset($this->data->choose_expression_type) ? $this->data->choose_expression_type : 'off';
        if ($versioned && $mode !== 'off' && isset($this->data->expression) && $this->data->expression !== ''
            && !FieldsetPostType::validate_expression_schema($this->data->expression, $this->data->fieldset)) {
            $this->configuration_valid = false;
            return;
        }
        $class_by_type = array();
        foreach (Plugin::$defined_fields as $field_class) {
            $class_parts = explode('\\', $field_class);
            $class_name = end($class_parts);
            $class_by_type[strtolower(substr($class_name, 0, -5))] = $field_class;
        }
        foreach ($this->data->fieldset as $name => $field_data) {
            if (!is_array($field_data) || !isset($field_data['type'], $field_data['name'])
                || !is_string($field_data['type']) || !isset($class_by_type[$field_data['type']])
                || !is_string($field_data['name']) || (string) $name !== $field_data['name']) {
                $this->configuration_valid = false;
                continue;
            }
            try {
                $field_class = $class_by_type[$field_data['type']];
                $field = new $field_class();
                $field->fromArray($field_data);
                $this->fields[$name] = $field;
            } catch (\Throwable $e) {
                $this->configuration_valid = false;
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
        if ($current === 'current' && ((function_exists('is_product') && is_product()) || Product::get_id() > 0)) {
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
        if ($current === '' && is_object($this->data)) {
            return isset($this->data->choose_expression_type) && $this->data->choose_expression_type !== "off";
        }

        if ($current === 'current') {
            $product_id = $this->product_id > 0 ? $this->product_id : Product::get_id();
        } elseif (is_scalar($current) && preg_match('/\A[0-9]+\z/', (string) $current) === 1 && (int) $current > 0) {
            $product_id = (int) $current;
        } elseif ($current === '') {
            $product_id = $this->product_id > 0 ? $this->product_id : Product::get_id();
        } else {
            return false;
        }

        if ($product_id < 1) {
            return false;
        }

        $fieldset_id = FieldsetAssignment::match($product_id);
        if (!$fieldset_id) {
            return false;
        }

        $fieldset = $this->get_data($fieldset_id);
        return isset($fieldset->choose_expression_type) && $fieldset->choose_expression_type !== "off";
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
        $filtered_post = array();
        $raw_post = isset($_POST['wck']) ? wp_unslash($_POST['wck']) : array();
        if (!is_array($raw_post)) {
            $this->request_input_valid = false;
            $raw_post = array();
        }
        foreach ($raw_post as $name => $unused) {
            if (!in_array($name, $allowed_names, true)) {
                $this->request_input_valid = false;
            }
        }
        foreach ($allowed_names as $name) {
            $field = isset($this->fields[$name]) ? $this->fields[$name] : null;
            if (!$field) {
                continue;
            }
            if (array_key_exists($name, $raw_post)) {
                $value = $raw_post[$name];
                if ($field->type() === 'textarea') {
                    $filtered_post[$name] = is_scalar($value) ? sanitize_textarea_field((string) $value) : $value;
                } elseif (is_array($value)) {
                    $filtered_post[$name] = array();
                    foreach ($value as $sub_key => $sub_value) {
                        if (!is_scalar($sub_value)) {
                            $this->request_input_valid = false;
                            continue;
                        }
                        $filtered_post[$name][$sub_key] = sanitize_text_field((string) $sub_value);
                    }
                } else {
                    $filtered_post[$name] = is_scalar($value) ? sanitize_text_field((string) $value) : $value;
                }
            } elseif ($field->type() === 'checkboxgroup') {
                $filtered_post[$name] = array();
            }
        }

        $user_input = $filtered_post;

        $user_input['_files'] = array();

        if (isset($_FILES['wck'])) {
            $upload = $_FILES['wck'];
            if (!is_array($upload) || !isset($upload['name'], $upload['tmp_name'], $upload['error'], $upload['size'])
                || !is_array($upload['name']) || !is_array($upload['tmp_name'])
                || !is_array($upload['error']) || !is_array($upload['size'])
                || (isset($upload['type']) && !is_array($upload['type']))) {
                $this->request_input_valid = false;
            } else {
                foreach ($upload['name'] as $name => $file_name) {
                    if (!in_array($name, $allowed_names, true) || !isset($this->fields[$name])
                        || $this->fields[$name]->group() !== 'upload'
                        || !is_string($file_name) || !isset($upload['tmp_name'][$name], $upload['error'][$name], $upload['size'][$name])
                        || !is_string($upload['tmp_name'][$name]) || !is_numeric($upload['error'][$name])
                        || !is_numeric($upload['size'][$name])) {
                        $this->request_input_valid = false;
                        continue;
                    }
                    $error_code = (int) $upload['error'][$name];
                    if ($error_code === UPLOAD_ERR_NO_FILE) {
                        continue;
                    }
                    if ($error_code !== UPLOAD_ERR_OK || !is_uploaded_file($upload['tmp_name'][$name])) {
                        $this->request_input_valid = false;
                        continue;
                    }
                    $validate = wp_check_filetype_and_ext($upload['tmp_name'][$name], $file_name);
                    if (isset($validate['ext']) && $validate['ext'] !== false) {
                        $file_id = PrivateUploads::new_id();
                        if (!$file_id) {
                            $this->request_input_valid = false;
                            continue;
                        }
                        $extension = strtolower($validate['ext']);
                        $user_input[$name] = (int) $upload['size'][$name];
                        $user_input['_files'][$name] = array(
                            'name' => $name,
                            'file_id' => $file_id,
                            'extension' => $extension,
                            'original_name' => $file_name,
                            'type' => isset($upload['type'][$name]) && is_string($upload['type'][$name]) ? $upload['type'][$name] : '',
                            'tmp_name' => $upload['tmp_name'][$name],
                            'size' => (int) $upload['size'][$name],
                            'error' => 0,
                        );
                    } else {
                        $this->request_input_valid = false;
                    }
                }
            }
        }

        // A cart edit may retain an existing staged upload when the form has no replacement file.
        $edit_key = isset($_POST['_wck_edit']) && is_scalar($_POST['_wck_edit'])
            ? sanitize_text_field(wp_unslash((string) $_POST['_wck_edit'])) : '';
        if ($edit_key !== '' && function_exists('WC') && WC() && isset(WC()->cart)) {
            $old_item = WC()->cart->get_cart_item($edit_key);
            if (is_array($old_item) && isset($old_item['product_id'], $old_item['wckalkulator_fields'])
                && (int) $old_item['product_id'] === (int) $this->product_id
                && (!isset($old_item['wckalkulator_fieldset_id']) || (int) $old_item['wckalkulator_fieldset_id'] === (int) $this->id())) {
                $old_files = isset($old_item['wckalkulator_fields']['_files']) && is_array($old_item['wckalkulator_fields']['_files'])
                    ? $old_item['wckalkulator_fields']['_files'] : array();
                foreach ($old_files as $name => $file_data) {
                    if (!isset($this->fields[$name]) || $this->fields[$name]->group() !== 'upload'
                        || isset($user_input['_files'][$name]) || !PrivateUploads::is_staged($file_data)) {
                        continue;
                    }
                    $file_data['tmp_name'] = PrivateUploads::staged_path($file_data);
                    $user_input['_files'][$name] = $file_data;
                    $user_input[$name] = (int) $file_data['size'];
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
                if (!isset($this->fields[$name]) || !$this->fields[$name]->validate($data) || !$this->fields[$name]->upload_temp($data)) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Validate user input
     *
     * @param bool $is_ajax_cart
     * @return bool
     * @since 1.0.0
     */
    public function validate($is_ajax_cart = false, $context = array())
    {
        if (!$this->configuration_valid) {
            $this->validation_notices[] = __('Product field configuration is invalid.', 'wc-kalkulator');
            $this->is_valid = false;
            return false;
        }
        if (!$this->request_input_valid || !is_array($this->user_input)) {
            $this->validation_notices[] = __('Product options have an invalid format.', 'wc-kalkulator');
            $this->is_valid = false;
            return false;
        }
        //$this->user_input = $input;

        if (!$this->apply_visibility_to_user_input()) {
            $this->is_valid = false;
            $this->user_input = array();
            return false;
        }

        if ($is_ajax_cart) {
            $this->is_valid = $this->validate_for_expression();
        } else {
            $this->is_valid = $this->validate_names() && $this->validate_values();
        }

        if (!$this->is_valid) {
            $this->user_input = array();
            return false;
        }

        $this->set_additional_input_variables(false, $context);

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
        $references = array();
        $this->collect_expression_field_references($this->expression(), $references);
        foreach ($this->fields() as $field) {
            $field_name = $field->data('name');
            if (!isset($references[$field_name])) {
                continue;
            }
            if (array_key_exists($field_name, $this->user_input)) {
                $value = isset($this->user_input['_files'][$field_name])
                    ? $this->user_input['_files'][$field_name]
                    : $this->user_input[$field_name];
                $valid_upload_preview = $field->group() === 'upload'
                    && (is_int($value) || is_float($value) || is_string($value))
                    && is_numeric($value) && is_finite((float) $value)
                    && (float) $value >= 0
                    && (float) $value <= (float) $field->data('max_file_size') * 1000000
                    && ((float) $value > 0 || !$field->is_required());
                if (!$valid_upload_preview && !$field->validate($value)) {
                    $this->validation_notices[] = sprintf(__('Field %s has incorrect value.', 'wc-kalkulator'), $field->data('title'));
                    return false;
                }
            }
        }
        return true;
    }

    /** Collect field references, including derived parameters such as :sum. */
    private function collect_expression_field_references($value, &$references)
    {
        if (is_array($value)) {
            foreach ($value as $item) {
                $this->collect_expression_field_references($item, $references);
            }
            return;
        }
        if (!is_string($value)) {
            return;
        }
        if (preg_match_all('/\\{([^{}]+)\\}/u', $value, $matches)) {
            foreach ($matches[1] as $reference) {
                $reference = explode(':', $reference, 2);
                $field_name = $reference[0];
                if (isset($this->fields[$field_name])) {
                    $references[$field_name] = true;
                }
            }
        }
    }

    /**
     * Evaluate visibility rules as OR groups containing AND conditions.
     * Missing references, malformed rules and dependency cycles are invalid configuration.
     *
     * @param array $input
     * @param string|null $error
     * @return array|false
     */
    public function evaluate_visibility($input, &$error = null)
    {
        $error = null;
        if (!is_array($input)) {
            $error = 'invalid_input';
            return false;
        }

        $states = array();
        $visiting = array();
        foreach ($this->fields() as $field) {
            $name = $field->data('name');
            if (!is_string($name) || $name === '') {
                return false;
            }
            $this->resolve_field_visibility($name, $input, $states, $visiting, $error);
            if ($error !== null) {
                return false;
            }
        }
        return $states;
    }

    /** Resolve a field's visibility, detecting cycles while walking dependencies. */
    private function resolve_field_visibility($name, $input, &$states, &$visiting, &$error)
    {
        if (array_key_exists($name, $states)) {
            return $states[$name];
        }
        if (isset($visiting[$name])) {
            $error = 'visibility_cycle';
            return false;
        }
        if (!isset($this->fields[$name])) {
            $error = 'visibility_reference_missing';
            return false;
        }

        $visiting[$name] = true;
        $raw_rules = $this->fields[$name]->data('visibility');
        if ($raw_rules === null || $raw_rules === false || (is_string($raw_rules) && trim($raw_rules) === '')) {
            unset($visiting[$name]);
            $states[$name] = true;
            return true;
        }

        $rules = is_array($raw_rules) ? $raw_rules : json_decode(stripslashes((string) $raw_rules), true);
        if (!is_array($rules) || !$rules) {
            $error = 'visibility_rules_invalid';
            unset($visiting[$name]);
            return false;
        }

        foreach ($rules as $or_group) {
            if (!is_array($or_group) || !$or_group) {
                $error = 'visibility_rules_invalid';
                unset($visiting[$name]);
                return false;
            }
            $group_matches = true;
            foreach ($or_group as $condition) {
                if (!is_array($condition) || !isset($condition['field'], $condition['comp'])
                    || !is_scalar($condition['field']) || !is_scalar($condition['comp'])) {
                    $error = 'visibility_rules_invalid';
                    unset($visiting[$name]);
                    return false;
                }
                $reference = (string) $condition['field'];
                $comparison = (string) $condition['comp'];
                if (!isset($this->fields[$reference])
                    || $this->fields[$reference]->group() === 'static') {
                    $error = 'visibility_reference_missing';
                    unset($visiting[$name]);
                    return false;
                }
                if (!in_array($comparison, array('1', '2', '3', '4', '5', '6', '7', '8', '9'), true)
                    || (!in_array($comparison, array('1', '2'), true)
                        && (!array_key_exists('value', $condition) || !is_scalar($condition['value'])))) {
                    $error = 'visibility_rules_invalid';
                    unset($visiting[$name]);
                    return false;
                }
                $reference_visible = $this->resolve_field_visibility($reference, $input, $states, $visiting, $error);
                if ($reference_visible === false && $error !== null) {
                    unset($visiting[$name]);
                    return false;
                }
                $reference_values = $reference_visible
                    ? (array_key_exists($reference, $input) ? $input[$reference] : '')
                    : '';
                if (!$this->compare_visibility_values($reference_values, $comparison, isset($condition['value']) ? $condition['value'] : '')) {
                    $group_matches = false;
                    break;
                }
            }
            if ($group_matches) {
                unset($visiting[$name]);
                $states[$name] = true;
                return true;
            }
        }

        unset($visiting[$name]);
        $states[$name] = false;
        return false;
    }

    /** Match one condition using the same comparison codes as the field rules editor. */
    private function compare_visibility_values($value, $comparison, $target)
    {
        $values = is_array($value) ? $value : array($value);
        $values = array_values(array_filter($values, 'is_scalar'));
        $nonempty = array_values(array_filter($values, function ($candidate) {
            return $candidate !== '';
        }));

        if ($comparison === '1') {
            return count($nonempty) === 0;
        }
        if ($comparison === '2') {
            return count($nonempty) > 0;
        }

        $target = (string) $target;
        if ($comparison === '3') {
            foreach ($nonempty as $candidate) {
                if ((string) $candidate === $target) {
                    return true;
                }
            }
            return false;
        }
        if ($comparison === '4') {
            if (!$nonempty) {
                return false;
            }
            foreach ($nonempty as $candidate) {
                if ((string) $candidate === $target) {
                    return false;
                }
            }
            return true;
        }
        if ($comparison === '9') {
            foreach ($nonempty as $candidate) {
                if (strpos((string) $candidate, $target) !== false) {
                    return true;
                }
            }
            return false;
        }

        $target = str_replace(',', '.', $target);
        if (!is_numeric($target) || !is_finite((float) $target) || abs((float) $target) > 1000000000000) {
            return false;
        }
        $target_number = floor((float) $target * 1000 + 0.5) / 1000;
        foreach ($nonempty as $candidate) {
            $candidate = str_replace(',', '.', (string) $candidate);
            if (!is_numeric($candidate) || !is_finite((float) $candidate) || abs((float) $candidate) > 1000000000000) {
                continue;
            }
            $candidate_number = floor((float) $candidate * 1000 + 0.5) / 1000;
            if (($comparison === '5' && $candidate_number < $target_number)
                || ($comparison === '6' && $candidate_number <= $target_number)
                || ($comparison === '7' && $candidate_number > $target_number)
                || ($comparison === '8' && $candidate_number >= $target_number)) {
                return true;
            }
        }
        return false;
    }

    /** Remove hidden values and replace them with a neutral value before validation/calculation. */
    private function apply_visibility_to_user_input()
    {
        if (!is_array($this->user_input)) {
            $this->validation_notices[] = __('Field visibility configuration is invalid.', 'wc-kalkulator');
            return false;
        }
        $error = null;
        $visibility = $this->evaluate_visibility($this->user_input, $error);
        if ($visibility === false) {
            $this->validation_notices[] = __('Field visibility configuration is invalid.', 'wc-kalkulator');
            return false;
        }

        $this->field_visibility = $visibility;
        foreach ($this->fields() as $field) {
            $name = $field->data('name');
            if (!empty($visibility[$name])) {
                continue;
            }
            unset($this->user_input[$name]);
            if (isset($this->user_input['_files']) && is_array($this->user_input['_files'])) {
                unset($this->user_input['_files'][$name]);
            }
            $this->user_input[$name] = $this->neutral_field_value($field);
        }
        if (!isset($this->user_input['_files']) || !is_array($this->user_input['_files'])) {
            $this->user_input['_files'] = array();
        }
        return true;
    }

    private function field_value_is_empty($value)
    {
        return $value === null || $value === '' || $value === array();
    }

    private function neutral_field_value($field)
    {
        return $field->type() === 'checkboxgroup' ? array() : '';
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
        $is_valid = true;
        foreach ($this->fields() as $field) {
            $field_name = $field->data("name");
            if (isset($this->field_visibility[$field_name]) && !$this->field_visibility[$field_name]) {
                continue;
            }
            $is_required = $field->is_required() || $field->is_required_when_visible();
            if ($is_required && (!array_key_exists($field_name, $this->user_input)
                || $this->field_value_is_empty($this->user_input[$field_name]))) {
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

            if (isset($this->field_visibility[$field_name]) && !$this->field_visibility[$field_name]) {
                $filtered_input[$field_name] = $this->neutral_field_value($field);
                continue;
            }

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
    public function set_additional_input_variables($return = false, $context = array())
    {
        $context = is_array($context) ? $context : array();
        if (!$return && !is_array($this->user_input)) {
            return;
        }
        if(!$return) {
            $field_names = array_keys($this->user_input);
            foreach ($this->fields() as $field) {
                $name = $field->data("name");

                // Check if field has price paramter and its name is in user input
                if ($field->data("price") !== null && in_array($name, $field_names, true)) {
                    $raw_value = $this->user_input[$name];
                    if ($field->group() !== 'static' && array_key_exists($name, $this->user_input)) {
                        $this->set_additional_field_parameters($field, $raw_value);
                    }
                    $static_price = Sanitizer::sanitize($field->data("price"), "price");

                    if ($field->type() === "checkbox") {
                        $static_price = intval((string) $raw_value === '1') * $static_price;
                    }

                    if ($raw_value === '' || $raw_value === null || $raw_value === false || $raw_value === array()) {
                        $static_price = 0;
                    }
                    $this->user_input[$name] = $static_price;
                } elseif ($field->group() !== 'static' && array_key_exists($name, $this->user_input)) {
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
                $this->user_input["product_price"] = array_key_exists('product_price', $context)
                    ? $context['product_price']
                    : $product_helper->price();
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
        if (array_key_exists('quantity', $context)) {
            $this->user_input["quantity"] = is_numeric($context['quantity']) && is_finite((float) $context['quantity'])
                ? (float) $context['quantity']
                : 0;
        }

        /*
         * Get values of global parameters
         */
        foreach (GlobalParameter::get_all() as $name => $value) {
            $this->user_input['global:' . $name] = $value;
        }/*
         * @since 1.5.0 - get custom numerical value (wck_value) of product attribute term
         */;
        $attributes = array_key_exists('attributes', $context) ? $context['attributes'] : null;
        $this->user_input = array_merge($this->user_input, Attribute::from_request($attributes));

        /*
         * @since 1.5.0 - support for ACF integration
         */
        Cache::store("ACF_Post_IDs", array(
            "product_id" => $this->product_id, //highest priority
            "fieldset_id" => $this->get_id(),
            "variation_id" => $this->variation_id //lowest priority
        ));

        if ($return) {
            $public_parameters = array();
            foreach (GlobalParameter::get_all(true) as $name => $value) {
                $public_parameters['global:' . $name] = $value;
            }
            return $public_parameters;
        }

    }

    /**
     * Load a previously validated cart input and refresh request-dependent values.
     *
     * @param array $input
     * @param array $context
     * @return bool
     */
    public function set_cart_context($input, $context)
    {
        if (!$this->configuration_valid || !is_array($input) || !is_array($context)) {
            return false;
        }

        $this->user_input = array();
        foreach ($this->fields_names() as $name) {
            if (array_key_exists($name, $input)) {
                $this->user_input[$name] = $input[$name];
            }
        }
        if (isset($input['_files']) && is_array($input['_files'])) {
            $this->user_input['_files'] = $input['_files'];
        } else {
            $this->user_input['_files'] = array();
        }

        $this->validation_notices = array();
        $this->field_visibility = array();
        if (!$this->apply_visibility_to_user_input() || !$this->validate_names()) {
            $this->is_valid = false;
            return false;
        }
        $this->is_valid = true;
        $this->set_additional_input_variables(false, $context);
        if (isset($context['parameter_snapshot']) && is_array($context['parameter_snapshot'])) {
            foreach ($context['parameter_snapshot'] as $name => $value) {
                if (is_string($name) && (strpos($name, 'global:') === 0 || strpos($name, 'pa:') === 0)) {
                    $this->user_input[$name] = $value;
                }
            }
        }

        return true;
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
                $this->user_input[$name . ':text'] = sanitize_text_field((string) $input);
                break;
            case 'textarea':
                $this->user_input[$name . ':text'] = sanitize_textarea_field((string) $input);
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
                    $parser = new ExpressionParser($expression, $this->user_input, 'formula');
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
     * @return float|int|false
     * @since 1.4.0
     */
    public function stock_reduction_multiplier()
    {
        if ($this->product_id > 0) {
            $expression = isset($this->data->stock_reduction_multiplier)
                ? trim((string) $this->data->stock_reduction_multiplier)
                : '';
            if ($expression === '') {
                return 1;
            }
            try {
                $parser = new ExpressionParser(array(
                    'mode' => 'oneline',
                    'expr' => $expression
                ), $this->user_input, 'stock_multiplier');
                if ($parser->is_ready()) {
                    $calc = $parser->execute();
                    if (!$calc['is_error']) {
                        return $calc['value'];
                    }
                }
            } catch (\Exception $e) {
                return false;
            } catch (\Throwable $e) {
                return false;
            }
        }
        return false;
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
        // An absent flag denotes a script saved before the publication flag
        // existed. An explicit disabled flag is always respected.
        $enabled = get_post_meta($this->data->id, '_wck_javascript_enabled', true);
        if ($enabled !== '' && $enabled !== '1') {
            return;
        }
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
