<?php

namespace WCKalkulator\Fields;

use WCKalkulator\View;

/** A measurement keeps the customer's unit; formulas receive the configured base unit. */
class MeasurementField extends NumberField
{
    protected $type = 'measurement';
    protected $default_data = array('css_class' => '', 'required' => false, 'hint' => '', 'default_value' => '',
        'base_unit' => 'm', 'units' => 'mm,cm,m', 'min' => 0, 'max' => 1000000, 'step' => 0.01, 'precision' => 4);

    public static function unit_catalog()
    {
        return array(
            'mm' => array('length', 0.001), 'cm' => array('length', 0.01), 'm' => array('length', 1),
            'in' => array('length', 0.0254), 'ft' => array('length', 0.3048), 'yd' => array('length', 0.9144),
            'mm2' => array('area', 0.000001), 'cm2' => array('area', 0.0001), 'm2' => array('area', 1), 'ft2' => array('area', 0.09290304),
            'ml' => array('volume', 0.000001), 'cm3' => array('volume', 0.000001), 'l' => array('volume', 0.001),
            'm3' => array('volume', 1), 'ft3' => array('volume', 0.028316846592),
            'g' => array('mass', 0.001), 'kg' => array('mass', 1), 'lb' => array('mass', 0.45359237)
        );
    }

    public static function valid_configuration($data)
    {
        $catalog = self::unit_catalog();
        if (!isset($data['base_unit'], $data['units']) || !is_string($data['base_unit']) || !isset($catalog[$data['base_unit']])
            || !is_string($data['units']) || strlen($data['units']) > 100) {
            return false;
        }
        $units = array_map('trim', explode(',', $data['units']));
        if (count($units) !== count(array_unique($units)) || !in_array($data['base_unit'], $units, true)) {
            return false;
        }
        foreach ($units as $unit) {
            if (!isset($catalog[$unit]) || $catalog[$unit][0] !== $catalog[$data['base_unit']][0]) {
                return false;
            }
        }
        foreach (array('min', 'max', 'step', 'precision') as $key) {
            if (!isset($data[$key]) || !is_scalar($data[$key]) || !is_numeric($data[$key]) || !is_finite((float) $data[$key])) {
                return false;
            }
        }
        return (float) $data['min'] >= 0 && (float) $data['max'] >= (float) $data['min']
            && (float) $data['step'] > 0 && (float) $data['precision'] === (float) (int) $data['precision']
            && (int) $data['precision'] >= 0 && (int) $data['precision'] <= 8
            && (float) $data['step'] >= pow(10, -(int) $data['precision']);
    }

    public function normalize($value)
    {
        if (!self::valid_configuration($this->data) || !is_array($value) || count($value) !== 2
            || !array_key_exists('value', $value) || !isset($value['unit']) || !is_string($value['unit'])
            || !in_array($value['unit'], array_map('trim', explode(',', $this->data['units'])), true)) {
            return false;
        }
        if ($value['value'] === '' && !$this->is_required()) {
            return 0.0;
        }
        if (!is_scalar($value['value']) || is_bool($value['value']) || !is_numeric($value['value'])
            || !is_finite((float) $value['value']) || (float) $value['value'] < 0) {
            return false;
        }
        $catalog = self::unit_catalog();
        $number = round((float) $value['value'] * $catalog[$value['unit']][1] / $catalog[$this->data['base_unit']][1], (int) $this->data['precision']);
        if (!is_finite($number) || $number < (float) $this->data['min'] || $number > (float) $this->data['max']) {
            return false;
        }
        $steps = ($number - (float) $this->data['min']) / (float) $this->data['step'];
        return abs($steps - round($steps)) <= 0.000001 ? $number : false;
    }

    public function validate($value)
    {
        return $this->normalize($value) !== false;
    }

    public function admin_fields($value = '')
    {
        $this->admin_title = __('Measurement with unit', 'wc-kalkulator');
        return View::render('fields/admin/measurement');
    }

    public function render_for_product($value = '')
    {
        $args = $this->prepared_data();
        $args['value'] = is_array($value) ? $value : array('value' => $this->data('default_value'), 'unit' => $this->data('base_unit'));
        $args['config'] = $this->data;
        $args['units'] = array_map('trim', explode(',', $this->data('units')));
        return View::render('fields/front/measurement', $args);
    }

    public function order_item_value($value)
    {
        return $this->validate($value) && $value['value'] !== '' ? (string) $value['value'] . ' ' . $value['unit'] : '';
    }

    public function render_for_cart($value = '')
    {
        return View::render('fields/cart', array('title' => $this->data('title'), 'value' => $this->order_item_value($value)));
    }
}
