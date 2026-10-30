<?php

namespace WCKalkulator\Fields;

use WCKalkulator\FieldsetProduct;
use WCKalkulator\Plugin;
use WCKalkulator\View;

/** Bounded rows of ordinary fields, priced independently on the server. */
class RepeaterField extends AbstractField
{
    protected $parameters = array();
    protected $data;
    protected $default_data = array('required' => false, 'min_rows' => 1, 'max_rows' => 10, 'repeat_mode' => 'button', 'row_formula' => '0', 'children' => array());
    protected $type = 'repeater';
    protected $admin_title;
    protected $use_expression = true;
    protected $group = 'special';
    protected $show_title = true;

    public function is_required() { return (int) $this->data('min_rows') > 0; }
    public function is_required_when_visible() { return false; }

    public function admin_fields($value = '')
    {
        $this->admin_title = __('Repeatable field group', 'wc-kalkulator');
        return View::render('fields/admin/repeater');
    }

    public function child_fields()
    {
        $fields = array();
        foreach ($this->data('children') as $name => $data) {
            foreach (Plugin::$defined_fields as $class) {
                $parts = explode('\\', $class);
                if (strtolower(substr(end($parts), 0, -5)) === $data['type']) {
                    $field = new $class();
                    $field->fromArray($data);
                    $fields[$name] = $field;
                    break;
                }
            }
        }
        return $fields;
    }

    public function validate($rows)
    {
        if (!is_array($rows) || count($rows) < (int) $this->data('min_rows') || count($rows) > (int) $this->data('max_rows')
            || ($rows && array_keys($rows) !== range(0, count($rows) - 1))) {
            return false;
        }
        $children = $this->data('children');
        foreach ($rows as $row) {
            if (is_array($row) && array_key_exists('_wck_row', $row)) {
                if ($row['_wck_row'] !== '1') { return false; }
                unset($row['_wck_row']);
            }
            if (!is_array($row) || count($row) > count($children) || array_diff(array_keys($row), array_keys($children))) {
                return false;
            }
        }
        return true;
    }

    public function evaluate_rows($rows, $context)
    {
        if (!$this->validate($rows)) { return false; }
        if ($this->data('repeat_mode') === 'quantity') {
            $quantity = isset($context['quantity']) ? $context['quantity'] : 1;
            if (!is_numeric($quantity) || (float) $quantity !== (float) count($rows)) { return false; }
        }
        $prices = array();
        $clean_rows = array();
        foreach ($rows as $row) {
            unset($row['_wck_row']);
            $fieldset = $this->row_context($context);
            if (!$fieldset->set_input_context($row, $context)) { return false; }
            $clean_row = $fieldset->validated_input();
            unset($clean_row['_files']);
            $clean_rows[] = $clean_row;
            $price = $fieldset->calculate();
            if (!is_array($price) || !isset($price['is_error'], $price['value']) || $price['is_error']
                || !is_numeric($price['value']) || !is_finite((float) $price['value']) || $price['value'] < 0) { return false; }
            $prices[] = (float) $price['value'];
        }
        $sum = array_sum($prices);
        if (!is_finite($sum)) { return false; }
        return array('rows' => $clean_rows, 'sum' => $sum, 'count' => count($rows), 'min' => $prices ? min($prices) : 0, 'max' => $prices ? max($prices) : 0);
    }

    private function row_context($context = array())
    {
        $fieldset = FieldsetProduct::create_context();
        $fieldset->init_from_snapshot(array('id' => 1, 'schema_version' => 2, 'fieldset' => $this->data('children'),
            'choose_expression_type' => 'oneline', 'expression' => array('mode' => 'oneline', 'expr' => $this->data('row_formula'))),
            isset($context['product_id']) ? $context['product_id'] : 0, isset($context['variation_id']) ? $context['variation_id'] : null);
        return $fieldset;
    }

    public function render_row($row, $index)
    {
        $html = '';
        foreach ($this->child_fields() as $name => $field) {
            $child_html = $field->render_for_product(isset($row[$name]) ? $row[$name] : '');
            // Rewrite only generated identifiers; values and labels remain untouched.
            $child_html = str_replace('name="wck[' . $name . ']', 'name="wck[' . esc_attr($this->data('name')) . '][' . esc_attr($index) . '][' . $name . ']', $child_html);
            $child_html = str_replace(array('id="wck_' . $name, 'for="wck_' . $name),
                array('id="wck_' . esc_attr($this->data('name') . '_' . $index . '_') . $name, 'for="wck_' . esc_attr($this->data('name') . '_' . $index . '_') . $name), $child_html);
            $html .= $child_html;
        }
        return '<div class="wck-repeat-row"><input type="hidden" name="wck[' . esc_attr($this->data('name')) . '][' . esc_attr($index) . '][_wck_row]" value="1"><table class="variations wck-repeat-fields">' . $html . '</table><button type="button" class="button wck-remove-row">'
            . esc_html__('Remove item', 'wc-kalkulator') . '</button></div>';
    }

    public function render_for_product($value = '')
    {
        $rows = is_array($value) ? $value : array_fill(0, (int) $this->data('min_rows'), array());
        return View::render('fields/front/repeater', array('field' => $this, 'rows' => $rows));
    }

    public function order_item_value($value)
    {
        if (!$this->validate($value)) { return ''; }
        $lines = array();
        $children = $this->child_fields();
        foreach ($value as $index => $row) {
            $error = null;
            $visibility = $this->row_context()->evaluate_visibility($row, $error);
            if ($visibility === false) { continue; }
            $parts = array();
            foreach ($children as $name => $field) {
                if (!empty($visibility[$name]) && isset($row[$name])) {
                    $parts[] = $field->data('title') . ': ' . $field->order_item_value($row[$name]);
                }
            }
            $lines[] = ($index + 1) . '. ' . implode('; ', $parts);
        }
        return implode("\n", $lines);
    }

    public function render_for_cart($value = '')
    {
        return View::render('fields/cart', array('title' => $this->data('title'), 'value' => $this->order_item_value($value)));
    }
}
