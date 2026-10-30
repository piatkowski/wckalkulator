<?php

namespace WCKalkulator;

/** Relationships are additive to the flat historical fieldset format. */
class FieldStructure
{
    public static function children($fieldset, $parent)
    {
        $children = array();
        foreach ($fieldset as $name => $field) {
            if (isset($field['repeat_group']) && $field['repeat_group'] === $parent) {
                unset($field['repeat_group']);
                $children[$name] = $field;
            }
        }
        return $children;
    }

    public static function version($fieldset)
    {
        foreach ($fieldset as $field) {
            if (in_array($field['type'], array('measurement', 'section', 'repeater'), true) || !empty($field['repeat_group'])) {
                return 2;
            }
        }
        return 1;
    }

    public static function validate($fieldset)
    {
        foreach ($fieldset as $name => $field) {
            if (isset($field['repeat_group']) && $field['repeat_group'] !== '') {
                $parent = $field['repeat_group'];
                if (!is_string($parent) || !isset($fieldset[$parent]) || $fieldset[$parent]['type'] !== 'repeater'
                    || !in_array($field['type'], array('number', 'measurement', 'text', 'textarea', 'email', 'select', 'dropdown', 'radio', 'checkbox', 'checkboxgroup'), true)) {
                    return false;
                }
            }
            if (in_array($field['type'], array('measurement', 'repeater', 'section'), true) && isset($field['price']) && $field['price'] !== '') { return false; }
            if ($field['type'] === 'measurement' && !Fields\MeasurementField::valid_configuration($field)) {
                return false;
            }
            if ($field['type'] === 'section' && (!isset($field['section_mode']) || !in_array($field['section_mode'], array('section', 'step'), true)
                || !empty($field['visibility']))) {
                return false;
            }
            if ($field['type'] === 'repeater') {
                foreach (array('min_rows', 'max_rows') as $bound) {
                    if (!isset($field[$bound]) || !is_scalar($field[$bound]) || !preg_match('/\A[0-9]+\z/', (string) $field[$bound])) {
                        return false;
                    }
                }
                if ((int) $field['min_rows'] > (int) $field['max_rows'] || (int) $field['max_rows'] < 1 || (int) $field['max_rows'] > 50
                    || !isset($field['repeat_mode'], $field['row_formula']) || !in_array($field['repeat_mode'], array('button', 'quantity'), true)
                    || !is_string($field['row_formula']) || trim($field['row_formula']) === '') {
                    return false;
                }
                $children = self::children($fieldset, $name);
                if (!$children || count($children) > 20 || !FieldsetPostType::validate_expression_schema(array('mode' => 'oneline', 'expr' => $field['row_formula']), $children)) {
                    return false;
                }
                if (!self::validate_visibility($children)) { return false; }
            }
        }
        if (self::version($fieldset) > 1) {
            $root_fields = array_filter($fieldset, function ($field) { return empty($field['repeat_group']); });
            return self::validate_visibility($root_fields);
        }
        return true;
    }

    /** Each rule refers to its own scope; cycles and malformed conditions cannot be published. */
    private static function validate_visibility($fields)
    {
        $graph = array();
        foreach ($fields as $name => $field) {
            $source = isset($field['visibility']) ? $field['visibility'] : '';
            if (!is_string($source)) { return false; }
            $groups = $source === '' ? array() : json_decode($source, true);
            if (!is_array($groups) || count($groups) > 100) { return false; }
            $graph[$name] = array();
            foreach ($groups as $conditions) {
                if (!is_array($conditions) || !$conditions || count($conditions) > 100) { return false; }
                foreach ($conditions as $rule) {
                    if (!is_array($rule) || !isset($rule['field'], $rule['comp']) || !is_string($rule['field'])
                        || !isset($fields[$rule['field']]) || !is_scalar($rule['comp'])
                        || !in_array((string) $rule['comp'], array('1', '2', '3', '4', '5', '6', '7', '8', '9'), true)
                        || in_array($fields[$rule['field']]['type'], array('repeater', 'section'), true)) { return false; }
                    if (!in_array((string) $rule['comp'], array('1', '2'), true)
                        && (!array_key_exists('value', $rule) || !is_scalar($rule['value']))) { return false; }
                    $graph[$name][] = $rule['field'];
                }
            }
        }
        $done = array(); $visiting = array();
        foreach (array_keys($graph) as $name) {
            if (!self::visit_dependency($name, $graph, $done, $visiting)) { return false; }
        }
        return true;
    }

    private static function visit_dependency($name, $graph, &$done, &$visiting)
    {
        if (isset($done[$name])) { return true; }
        if (isset($visiting[$name])) { return false; }
        $visiting[$name] = true;
        foreach ($graph[$name] as $dependency) {
            if (!self::visit_dependency($dependency, $graph, $done, $visiting)) { return false; }
        }
        unset($visiting[$name]);
        $done[$name] = true;
        return true;
    }
}
