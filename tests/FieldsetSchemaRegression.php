<?php
/** Dependency-free schema validation regressions. */
namespace WCKalkulator {
    class Plugin
    {
        public static $defined_fields = array(
            '\\WCKalkulator\\Fields\\NumberField',
            '\\WCKalkulator\\Fields\\SelectField',
            '\\WCKalkulator\\Fields\\DropdownField'
        );
    }
}
namespace {
    error_reporting(E_ALL & ~E_DEPRECATED);
    function sanitize_text_field($value) { return trim(strip_tags((string) $value)); }
    function wp_kses_post($value) { return $value; }
    require __DIR__ . '/../src/Sanitizer.php';
    require __DIR__ . '/../src/FieldStructure.php';
    require __DIR__ . '/../src/FieldsetPostType.php';
    $reflection = new \ReflectionClass('WCKalkulator\\FieldsetPostType');
    $fieldset_method = $reflection->getMethod('validate_fieldset_schema');
    $fieldset_method->setAccessible(true);
    $expression_method = $reflection->getMethod('validate_expression_schema');
    $expression_method->setAccessible(true);
    $field = array('type' => 'number', 'name' => 'width', 'min' => 10, 'max' => 100, 'required' => '1');
    $valid = $fieldset_method->invoke(null, array('width' => $field));
    $reserved = $field; $reserved['name'] = 'quantity';
    $unknown = $field; $unknown['type'] = 'unknown';
    $options = array('type' => 'select', 'name' => 'finish', 'options_name' => array('a', 'b'), 'options_title' => array('A', 'B'));
    $mismatched_options = $options; $mismatched_options['options_title'] = array('A');
    $passes = array(
        'valid numeric fieldset accepted' => $valid === true,
        'reserved runtime variable rejected' => $fieldset_method->invoke(null, array('quantity' => $reserved)) === false,
        'unregistered field type rejected' => $fieldset_method->invoke(null, array('width' => $unknown)) === false,
        'matching select option arrays accepted' => $fieldset_method->invoke(null, array('finish' => $options)) === true,
        'legacy value and label default option accepted' => $fieldset_method->invoke(null, array('finish' => array_merge($options, array('default_value' => 'a:A')))) === true,
        'dropdown default title accepted' => $fieldset_method->invoke(null, array('finish' => array_merge($options, array('type' => 'dropdown', 'default_value' => 'A')))) === true,
        'mismatched select option arrays rejected' => $fieldset_method->invoke(null, array('finish' => $mismatched_options)) === false,
        'known field reference accepted' => $expression_method->invoke(null, array('mode' => 'oneline', 'expr' => '{width}*2'), array('width' => 'number')) === true,
        'unknown field reference rejected' => $expression_method->invoke(null, array('mode' => 'oneline', 'expr' => '{missing}*2'), array('width' => 'number')) === false,
        'unsupported derived parameter rejected' => $expression_method->invoke(null, array('mode' => 'oneline', 'expr' => '{width:text}'), array('width' => 'number')) === false,
        'placeholder text inside a literal is ignored' => $expression_method->invoke(null, array('mode' => 'oneline', 'expr' => "'{missing}' == '{missing}' and {width}>1"), array('width' => 'number')) === true,
        'global parameter reference is accepted' => $expression_method->invoke(null, array('mode' => 'oneline', 'expr' => '{global:margin}+{width}'), array('width' => 'number')) === true,
        'product attribute reference is accepted' => $expression_method->invoke(null, array('mode' => 'oneline', 'expr' => '{pa:color}+{width}'), array('width' => 'number')) === true,
        'expression without a mode rejected' => $expression_method->invoke(null, array('expr' => '{width}*2'), array('width' => 'number')) === false,
        'conditional row missing operands rejected' => $expression_method->invoke(null, array('mode' => 'conditional', 'expr' => array(array('type' => 'condition', 'then' => '{width}'))), array('width' => 'number')) === false,
        'JSON scalar rejected instead of normalized' => \WCKalkulator\Sanitizer::sanitize('null', 'json') === false,
        'malformed JSON rejected' => \WCKalkulator\Sanitizer::sanitize('{broken', 'json') === false,
        'invalid enum rejected instead of text-sanitized' => \WCKalkulator\Sanitizer::sanitize('unexpected', array('oneline', 'off')) === false
    );
    $failed = array();
    foreach ($passes as $name => $passed) { if (!$passed) { $failed[] = $name; } }
    echo 'Fieldset schema regression: ' . (count($passes) - count($failed)) . '/' . count($passes) . ' passed' . PHP_EOL;
    if ($failed) { echo implode(PHP_EOL, $failed) . PHP_EOL; exit(1); }
}
