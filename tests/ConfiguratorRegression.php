<?php
require __DIR__ . '/support/ConfiguratorBootstrap.php';

use WCKalkulator\FieldsetProduct;
use WCKalkulator\FieldsetPostType;
use WCKalkulator\Fields\MeasurementField;
use WCKalkulator\Woocommerce\Product;

$passed = 0;
$failures = array();
function check_configuration($name, $result) {
    global $passed, $failures;
    if ($result) { $passed++; } else { $failures[] = $name; }
}
$schema = configurator_fields();
check_configuration('complete structured schema is valid', FieldsetPostType::validate_fieldset_schema($schema));
$bad = $schema; $bad['width']['repeat_group'] = 'missing';
check_configuration('orphan child rejected', !FieldsetPostType::validate_fieldset_schema($bad));
$bad = $schema; $bad['items']['repeat_group'] = 'items';
check_configuration('nested or circular repeater rejected', !FieldsetPostType::validate_fieldset_schema($bad));
$bad = $schema; $bad['items']['max_rows'] = 51;
check_configuration('unbounded repeater rejected', !FieldsetPostType::validate_fieldset_schema($bad));
$bad = $schema; $bad['items']['row_formula'] = '{note}';
check_configuration('row formula cannot reference outside fields', !FieldsetPostType::validate_fieldset_schema($bad));
$bad = $schema; $bad['width']['units'] = 'cm,kg';
check_configuration('incompatible units rejected', !FieldsetPostType::validate_fieldset_schema($bad));
$bad = $schema; $bad['width']['units'] = 'cm,m,m';
check_configuration('duplicate units rejected', !FieldsetPostType::validate_fieldset_schema($bad));
$bad = $schema; $bad['size']['section_mode'] = 'unknown';
check_configuration('unknown section mode rejected', !FieldsetPostType::validate_fieldset_schema($bad));
check_configuration('root formula cannot use a row child as global value', !FieldsetPostType::validate_expression_schema(array('mode' => 'oneline', 'expr' => '{width}'), $schema));
check_configuration('aggregate parameters accepted', FieldsetPostType::validate_expression_schema(array('mode' => 'oneline', 'expr' => '{items:sum}+{items:count}'), $schema));

$measurement = new MeasurementField();
$measurement->fromArray($schema['width']);
check_configuration('centimeters normalize to base meters', $measurement->normalize(array('value' => '100', 'unit' => 'cm')) === 1.0);
check_configuration('millimeters normalize to base meters', $measurement->normalize(array('value' => '1000', 'unit' => 'mm')) === 1.0);
check_configuration('zero is a valid measurement', $measurement->normalize(array('value' => '0', 'unit' => 'm')) === 0.0);
check_configuration('step is enforced after conversion', !$measurement->validate(array('value' => '1.5', 'unit' => 'cm')));
check_configuration('limit enforced in base unit', !$measurement->validate(array('value' => '10001', 'unit' => 'mm')));
foreach (array(array('value' => array('1'), 'unit' => 'm'), array('value' => 'INF', 'unit' => 'm'), array('value' => '-1', 'unit' => 'm'), array('value' => '1', 'unit' => 'kg'), array('value' => '1', 'unit' => 'm', 'price' => 0)) as $bad_input) {
    check_configuration('malformed measurement rejected', !$measurement->validate($bad_input));
}
foreach (array(array('kg', 'g', '1000'), array('m2', 'cm2', '10000'), array('m3', 'l', '1000')) as $case) {
    $measurement->fromArray(array_merge($schema['width'], array('base_unit' => $case[0], 'units' => $case[0] . ',' . $case[1])));
    check_configuration('mass, area and volume conversion', $measurement->normalize(array('value' => $case[2], 'unit' => $case[1])) === 1.0);
}

$measurement->fromArray(array_merge($schema['width'], array('precision' => 2)));
check_configuration('measurement half tie rounds consistently with frontend', $measurement->normalize(array('value' => '1.005', 'unit' => 'm')) === 1.01);

$context = FieldsetProduct::create_context();
check_configuration('snapshot initializes structured fields', $context->init_from_snapshot(configurator_snapshot(), 1));
check_configuration('row children are not top level fields', $context->fields_names() === array('size', 'items', 'finish', 'note'));
$input = configurator_input();
check_configuration('valid structured input accepted', $context->set_input_context($input, array('quantity' => 1)));
check_configuration('two rows yield base 30 plus 250', $context->calculate()['value'] === 280.0);
check_configuration('customer units remain unchanged for cart and edit', $context->validated_input()['items'][0]['width']['unit'] === 'cm');
check_configuration('retry has the same price', $context->set_cart_context($context->validated_input(), array('quantity' => 1)) && $context->calculate()['value'] === 280.0);
$second = FieldsetProduct::create_context(); $second->init_from_snapshot(configurator_snapshot(), 2);
check_configuration('second product has independent base and rows', $second->set_input_context($input, array('quantity' => 1)) && $second->calculate()['value'] === 300.0 && $context->calculate()['value'] === 280.0);
$bad_input = $input; $bad_input['items'][0]['height']['value'] = '999999';
check_configuration('bad child value blocks the entire group', !$second->set_input_context($bad_input, array('quantity' => 1)));
$bad_input = $input; $bad_input['items'][0]['price'] = 0;
check_configuration('client row price rejected', !$second->set_input_context($bad_input, array('quantity' => 1)));
$bad_input = $input; unset($bad_input['items'][0]['width']);
check_configuration('missing required child rejected', !$second->set_input_context($bad_input, array('quantity' => 1)));
$bad_input = $input; $bad_input['items'] = array(1 => $input['items'][0]);
check_configuration('sparse row keys rejected', !$second->set_input_context($bad_input, array('quantity' => 1)));
check_configuration('missing required group rejected', !$second->set_input_context(array(), array('quantity' => 1)));
$bad = $schema;
$bad['width']['visibility'] = '[[{"field":"height","comp":"2"}]]';
$bad['height']['visibility'] = '[[{"field":"width","comp":"2"}]]';
check_configuration('row visibility cycle rejected before publishing', !FieldsetPostType::validate_fieldset_schema($bad));
$bad = $schema; $bad['width']['visibility'] = '[[{"field":"height","comp":"99"}]]';
check_configuration('invalid visibility comparator rejected before publishing', !FieldsetPostType::validate_fieldset_schema($bad));

$quantity_schema = $schema; $quantity_schema['items']['repeat_mode'] = 'quantity';
$quantity = FieldsetProduct::create_context(); $quantity->init_from_snapshot(configurator_snapshot($quantity_schema, '{items}'), 1);
check_configuration('one row per purchased unit has averaged unit price', $quantity->set_input_context($input, array('quantity' => 2)) && $quantity->calculate()['value'] === 125.0);
check_configuration('quantity multiplier applies once, total is sum of rows', $quantity->calculate()['value'] * 2 === 250.0);
check_configuration('quantity change requires matching configuration', !$quantity->set_cart_context($input, array('quantity' => 3)));

$optional = new MeasurementField(); $optional->fromArray(array_merge($schema['width'], array('required' => '0')));
check_configuration('optional empty measurement is zero for price but has no displayed value', $optional->normalize(array('value' => '', 'unit' => 'm')) === 0.0 && $optional->order_item_value(array('value' => '', 'unit' => 'm')) === '');
$visibility_fields = array('width' => array_merge($schema['width'], array('repeat_group' => '', 'required' => '0')), 'note' => array_merge($schema['note'], array('visibility' => '[[{"field":"width","comp":"2"}]]')));
$visibility_context = FieldsetProduct::create_context(); $visibility_context->init_from_snapshot(configurator_snapshot($visibility_fields, '{width}'), 1);
check_configuration('empty dimension and zero remain distinct in visibility', !$visibility_context->evaluate_visibility(array('width' => array('value' => '', 'unit' => 'm')))['note'] && $visibility_context->evaluate_visibility(array('width' => array('value' => '0', 'unit' => 'm')))['note']);
$hidden_schema = $schema;
$hidden_schema['width']['visibility'] = '[[{"field":"height","comp":"7","value":"0"}]]';
$hidden = FieldsetProduct::create_context(); $hidden->init_from_snapshot(configurator_snapshot($hidden_schema), 1);
$hidden_input = array('items' => array(array('width' => array('value' => '999999', 'unit' => 'm'), 'height' => array('value' => '0', 'unit' => 'm'))));
check_configuration('hidden invalid row child cannot affect price', $hidden->set_input_context($hidden_input, array('quantity' => 1)) && $hidden->calculate()['value'] === 30.0);
check_configuration('hidden row value is neutralized before cart storage', $hidden->validated_input()['items'][0]['width'] === '');
$too_many = $input; $too_many['items'] = array_fill(0, 6, $input['items'][0]);
check_configuration('runtime row limit is enforced', !$second->set_input_context($too_many, array('quantity' => 1)));
$overridden = FieldsetProduct::create_context(); $overridden->init_from_snapshot(configurator_snapshot(), 1);
check_configuration('row globals use the saved pricing snapshot', $overridden->set_cart_context($input, array('quantity' => 1, 'parameter_snapshot' => array('global:rate' => 80))) && $overridden->calculate()['value'] === 230.0);

$empty_row_schema = $schema;
$empty_row_schema['width']['required'] = '0'; $empty_row_schema['height']['required'] = '0';
$empty_row = FieldsetProduct::create_context(); $empty_row->init_from_snapshot(configurator_snapshot($empty_row_schema), 1);
$empty_row_input = array('items' => array(array('_wck_row' => '1', 'width' => array('value' => '', 'unit' => 'm'), 'height' => array('value' => '', 'unit' => 'm'))));
check_configuration('empty optional row is retained by a form marker', $empty_row->set_input_context($empty_row_input, array('quantity' => 1)) && count($empty_row->validated_input()['items']) === 1);
check_configuration('internal form marker is absent from stored customer data', !isset($empty_row->validated_input()['items'][0]['_wck_row']));

// Real product adapters with fake cart/order objects, against the captured schema.
class ConfiguratorTestProduct {
    public $price;
    public function __construct($price) { $this->price = $price; }
    public function get_price() { return $this->price; }
    public function set_price($price) { $this->price = $price; }
}
class ConfiguratorTestCart { public $cart_contents; public function get_cart() { return $this->cart_contents; } }
class ConfiguratorTestItem { public $meta = array(); public function add_meta_data($key, $value, $unique = false) { $this->meta[$key] = $value; } }
$cart = new ConfiguratorTestCart();
$cart->cart_contents = array('line' => array('product_id' => 1, 'quantity' => 1, 'data' => new ConfiguratorTestProduct(30), 'wckalkulator_fields' => $input,
    'wckalkulator_stock_reduction_multiplier' => 1, 'wckalkulator_fieldset_id' => 7, 'wckalkulator_fieldset_snapshot_version' => 1, 'wckalkulator_fieldset_snapshot' => configurator_snapshot()));
Product::before_calculate_totals($cart);
check_configuration('real cart adapter calculates structured price', $cart->cart_contents['line']['data']->price === 280.0);
Product::before_calculate_totals($cart);
check_configuration('repeated totals do not compound extras', $cart->cart_contents['line']['data']->price === 280.0);
$cart->cart_contents['line']['quantity'] = 3;
Product::before_calculate_totals($cart);
check_configuration('button-mode quantity change keeps per-unit configuration price', $cart->cart_contents['line']['data']->price === 280.0);
$item = new ConfiguratorTestItem();
Product::checkout_create_order_line_item($item, 'line', $cart->cart_contents['line'], new stdClass());
check_configuration('order preserves original units and rows', $item->meta['_wck_fields']['items'] === $input['items']);
check_configuration('order stores a readable row summary', strpos($item->meta['Blinds'], 'Width: 100 cm') !== false);
check_configuration('order stores the original schema snapshot', $item->meta['_wck_fieldset_snapshot']['fieldset'] === $schema);

class ConfiguratorUploadDouble extends \WCKalkulator\Fields\NumberField {
    protected $type = 'fileupload';
    protected $group = 'upload';
    public function validate($value) { return is_array($value) && isset($value['file_id'], $value['size']); }
}
$upload_context = FieldsetProduct::create_context();
$upload_context->init_from_snapshot(array('id' => 7, 'schema_version' => 1, 'fieldset' => array(), 'choose_expression_type' => 'off'), 1);
$upload = new ConfiguratorUploadDouble(); $upload->fromArray(array('name' => 'upload', 'title' => 'Upload', 'required' => '0'));
$reflection = new ReflectionClass($upload_context);
foreach (array('fields' => array('upload' => $upload), 'user_input' => array('upload' => 1234, '_files' => array('upload' => array('file_id' => 'fixture', 'size' => 1234)))) as $key => $value) {
    $property = $reflection->getProperty($key); $property->setAccessible(true); $property->setValue($upload_context, $value);
}
check_configuration('raw upload size remains scalar in cart input', $upload_context->validate(false, array('quantity' => 1)) && $upload_context->validated_input()['upload'] === 1234);

// Request ingestion must preserve bounded nesting and reject unknown top-level fields.
$_POST = array('wck' => $input);
$request = FieldsetProduct::create_context(); $request->init_from_snapshot(configurator_snapshot(), 1);
check_configuration('POST ingestion preserves structured values', $request->get_user_input()['items'] === $input['items'] && $request->validate(false, array('quantity' => 1)));
$_POST['wck']['unknown'] = '1';
$request->get_user_input();
check_configuration('unknown request key rejected', !$request->validate(false, array('quantity' => 1)));

// Render production templates, including a row template, without database dependencies.
$html_fields = array();
foreach ($context->fields() as $name => $field) {
    $html_fields[] = array('html' => $field->render_for_product(isset($input[$name]) ? $input[$name] : ''), 'type' => $field->type(), 'title' => $field->data('title'), 'section_mode' => $field->data('section_mode'), 'colspan' => 1);
}
$html = $context->render(array('hidden' => '', 'fields' => $html_fields));
check_configuration('renders steps and a progressive enhancement fallback', strpos($html, 'wck-step-navigation') !== false && strpos($html, 'Configuration progress') !== false);
check_configuration('renders nested measurement names and clone template', strpos($html, 'wck[items][0][width][unit]') !== false && strpos($html, '__row__') !== false);
check_configuration('server row formula is not exposed in HTML', strpos($html, '{width} * {height}') === false);

echo 'Configurator regression: ' . $passed . '/' . ($passed + count($failures)) . ' passed' . PHP_EOL;
if ($failures) { echo implode(PHP_EOL, $failures) . PHP_EOL; exit(1); }
