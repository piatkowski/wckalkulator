<?php
/** Dependency-free regressions for expression input, zero values and literal-safe rewriting. */
namespace WCKalkulator {
    class GlobalParameter { public static function get_all($public = false) { return array(); } }
    class Cache { public static function store($key, $value) {} }
}
namespace WCKalkulator\Woocommerce {
    class Attribute { public static function from_request($attributes = null) { return array(); } }
}
namespace {
    error_reporting(E_ALL & ~E_DEPRECATED);
    function __($value) { return $value; }
    function sanitize_text_field($value) { return trim(strip_tags((string) $value)); }
    function sanitize_textarea_field($value) { return trim(strip_tags((string) $value)); }
    function sanitize_email($value) { return (string) $value; }
    function is_email($value) { return filter_var($value, FILTER_VALIDATE_EMAIL) !== false; }
    function is_user_logged_in() { return false; }
    function current_time($format) { return '2026-10-30 12:00:00'; }
    function absint($value) { return abs((int) $value); }

    require __DIR__ . '/../src/FieldsetProduct.php';
    require __DIR__ . '/../src/Sanitizer.php';
    require __DIR__ . '/../src/Fields/AbstractField.php';
    require __DIR__ . '/../src/Fields/TextField.php';
    require __DIR__ . '/../src/Fields/NumberField.php';
    require __DIR__ . '/../src/Fields/DatepickerField.php';
    require __DIR__ . '/../src/Fields/RangedatepickerField.php';
    require __DIR__ . '/../src/Fields/ColorpickerField.php';
    require __DIR__ . '/../src/Fields/EmailField.php';
    require __DIR__ . '/../src/ExpressionParser.php';

    class RegressionField
    {
        private $name;
        private $valid;
        private $expression;
        public function __construct($name, $valid, $expression = true) { $this->name = $name; $this->valid = $valid; $this->expression = $expression; }
        public function data($key = '') { return $key === 'name' ? $this->name : ($key === 'title' ? $this->name : null); }
        public function use_expression() { return $this->expression; }
        public function validate($value) { $validator = $this->valid; return $validator($value); }
    }

    $passed = 0;
    $failed = array();
    $check = function ($name, $ok) use (&$passed, &$failed) { if ($ok) { $passed++; } else { $failed[] = $name; } };

    $class = new \ReflectionClass('WCKalkulator\\FieldsetProduct');
    $set = function ($object, $property, $value) use ($class) { $p = $class->getProperty($property); $p->setAccessible(true); $p->setValue($object, $value); };
    $fieldset = $class->newInstanceWithoutConstructor();
    $set($fieldset, 'fields', array(
        'width' => new RegressionField('width', function ($v) { return is_numeric($v) && $v >= 10 && $v <= 100; }),
        'color' => new RegressionField('color', function ($v) { return is_array($v) && count($v) > 0 && count(array_diff($v, array('red', 'blue'))) === 0; }),
        'notes' => new RegressionField('notes', function () { return false; }, false),
    ));
    $data = new \stdClass();
    $data->expression = array('mode' => 'oneline', 'expr' => '{width}*2+{color:sum}');
    $set($fieldset, 'data', $data);
    $set($fieldset, 'user_input', array('width' => '9999', 'color' => array('red'), 'notes' => 'anything', '_files' => array()));
    $check('preview validates a referenced number outside its range', $fieldset->validate_for_expression() === false);
    $set($fieldset, 'user_input', array('width' => '25', 'color' => array('red'), '_files' => array()));
    $check('preview accepts in-range fields and validates derived :sum field', $fieldset->validate_for_expression() === true);
    $set($fieldset, 'user_input', array('color' => array('red'), '_files' => array()));
    $check('preview leaves a not-yet-entered field pending', $fieldset->validate_for_expression() === true);
    $set($fieldset, 'fields', array('attachment' => new RegressionField('attachment', function ($v) { return is_array($v) && isset($v['file_id']); })));
    $data->expression = array('mode' => 'oneline', 'expr' => '{attachment:size}');
    $set($fieldset, 'user_input', array('attachment' => 10, '_files' => array('attachment' => array('file_id' => 'safe-id'))));
    $check('preview validates a referenced upload field', $fieldset->validate_for_expression() === true);

    $text = new \WCKalkulator\Fields\TextField();
    $text->fromArray(array('name' => 'code', 'title' => 'Code', 'required' => false, 'min' => 1, 'max' => 4, 'pattern' => '[0-9]+'));
    $check('text pattern rejects a value that only passed browser validation', $text->validate('letters') === false);
    $check('text zero string is retained and checked', $text->validate('0') === true);
    $unicode_text = new \WCKalkulator\Fields\TextField();
    $unicode_text->fromArray(array('name' => 'label', 'title' => 'Label', 'required' => false, 'min' => 1, 'max' => 1));
    $check('unicode character length is not counted as HTML bytes', $unicode_text->validate('ż') === true);
    $check('ampersand counts as one input character', $unicode_text->validate('&') === true);
    $email = new \WCKalkulator\Fields\EmailField();
    $email->fromArray(array('name' => 'email', 'title' => 'Email', 'required' => false, 'min' => 0, 'max' => 100));
    $check('email field rejects malformed address', $email->validate('not-an-email') === false);

    $number = new \WCKalkulator\Fields\NumberField();
    $number->fromArray(array('name' => 'amount', 'title' => 'Amount', 'required' => false, 'min' => 10, 'max' => 100));
    $check('optional numeric zero still obeys min', $number->validate('0') === false);

    $date = new \WCKalkulator\Fields\DatepickerField();
    $date->fromArray(array('name' => 'date', 'title' => 'Date', 'required' => false, 'disallow_past_date' => false));
    $check('datepicker rejects array request shape', $date->validate(array('2026-10-30')) === false);
    $check('datepicker rejects nonnumeric calendar parts', $date->validate('x-y-z') === false);
    $range = new \WCKalkulator\Fields\RangedatepickerField();
    $range->fromArray(array('name' => 'range', 'title' => 'Range', 'required' => false, 'disallow_past_date' => false));
    $check('range date rejects nested scalar shape', $range->validate(array('from' => array('2026-10-30'), 'to' => '2026-10-31')) === false);
    $color = new \WCKalkulator\Fields\ColorpickerField();
    $color->fromArray(array('name' => 'color', 'title' => 'Color', 'required' => false));
    $check('color field rejects array request shape', $color->validate(array('#ffffff')) === false);

    class PriceBearingField
    {
        private $data;
        public function __construct($name, $type, $price) { $this->data = array('name' => $name, 'title' => $name, 'price' => $price); $this->field_type = $type; }
        public $field_type;
        public function data($key = '') { return $key === '' ? $this->data : (array_key_exists($key, $this->data) ? $this->data[$key] : null); }
        public function group() { return 'input'; }
        public function type() { return $this->field_type; }
    }
    $fieldset = $class->newInstanceWithoutConstructor();
    $set($fieldset, 'fields', array(
        'label' => new PriceBearingField('label', 'text', '5'),
        'date' => new PriceBearingField('date', 'datepicker', '2'),
        'notes' => new PriceBearingField('notes', 'textarea', null)
    ));
    $set($fieldset, 'user_input', array('label' => '0', 'date' => '2026-10-30', 'notes' => "first line\nsecond line"));
    $fieldset->set_additional_input_variables(false);
    $derived = $class->getProperty('user_input'); $derived->setAccessible(true); $derived_values = $derived->getValue($fieldset);
    $check('static price preserves raw text in :text parameter', $derived_values['label'] === 5.0 && $derived_values['label:text'] === '0');
    $check('static price preserves original date in :date parameter', $derived_values['date'] === 2.0 && $derived_values['date:date'] === strtotime('2026-10-30'));
    $check('textarea derived value preserves line breaks', $derived_values['notes:text'] === "first line\nsecond line");

    $parser = (new \ReflectionClass('WCKalkulator\\ExpressionParser'))->newInstanceWithoutConstructor();
    $parser_class = new \ReflectionClass($parser);
    $clean = $parser_class->getMethod('str_clean'); $clean->setAccessible(true);
    $extract = $parser_class->getMethod('extract_placeholders'); $extract->setAccessible(true);
    $rewritten = $clean->invoke($parser, "{width} > 10 and {label:text} == 'A:B, C; {literal}'");
    $check('formula operators are rewritten while quoted literal punctuation and braces survive', $rewritten === "width > 10 and label__p__text == 'A:B, C; {literal}'");
    $references = $extract->invoke($parser, "{width} > 10 and label == '{not_a_reference}' and {label:text} == 'A:B'");
    $check('conditional placeholder extraction ignores string literals', $references === array('width', 'label:text'));

    echo 'Fieldset input regression: ' . $passed . '/' . ($passed + count($failed)) . ' passed' . PHP_EOL;
    if ($failed) { echo implode(PHP_EOL, $failed) . PHP_EOL; exit(1); }
}
