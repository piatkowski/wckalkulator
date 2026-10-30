<?php
/** Dependency-free checks for loading a persisted fieldset configuration snapshot. */
namespace WCKalkulator {
    class Plugin
    {
        public static $defined_fields = array();
    }

    class FieldsetPostType
    {
        public static function validate_fieldset_schema($fieldset) { return is_array($fieldset); }
        public static function validate_expression_schema($expression, $fields) { return true; }
    }
}

namespace {
    function __($value) { return $value; }
    require __DIR__ . '/../src/FieldsetProduct.php';

    $fieldset = \WCKalkulator\FieldsetProduct::getInstance();
    $snapshot = array(
        'id' => 234,
        'version_hash' => 'old-version',
        'title' => 'Original title',
        'fieldset' => array(),
        'choose_expression_type' => 'oneline',
        'expression' => array('mode' => 'oneline', 'expr' => '3')
    );

    $loaded = $fieldset->init_from_snapshot($snapshot, 900, 901);
    $saved = $fieldset->configuration_snapshot();
    $reflection = new \ReflectionClass('WCKalkulator\\FieldsetProduct');
    $input_property = $reflection->getProperty('user_input');
    if (PHP_VERSION_ID < 80100) {
        $input_property->setAccessible(true);
    }
    $input_property->setValue($fieldset, array(
        'global:discount' => 0.9,
        'pa:color' => 7,
        'pa:color_id' => 42,
        'width' => 120
    ));
    $parameters = $fieldset->calculation_parameter_snapshot();
    $checks = array(
        'valid snapshot loads without resolving current assignment' => $loaded === true,
        'snapshot retains its original fieldset id' => $fieldset->id() === 234,
        'snapshot mode drives expression behavior' => $fieldset->has_expression() === true,
        'snapshot preserves original title and version' => $saved['title'] === 'Original title' && $saved['version_hash'] === 'old-version',
        'pricing parameters capture globals and attributes only' => $parameters === array('global:discount' => 0.9, 'pa:color' => 7, 'pa:color_id' => 42),
        'malformed snapshot is rejected' => $fieldset->init_from_snapshot(array('id' => 234), 900, 901) === false
    );

    $failed = array();
    foreach ($checks as $name => $passed) {
        if (!$passed) { $failed[] = $name; }
    }
    echo 'Cart fieldset snapshot regression: ' . (count($checks) - count($failed)) . '/' . count($checks) . ' passed' . PHP_EOL;
    if ($failed) {
        echo implode(PHP_EOL, $failed) . PHP_EOL;
        exit(1);
    }
}
