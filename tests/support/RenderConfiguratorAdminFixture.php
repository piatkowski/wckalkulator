<?php
require __DIR__ . '/ConfiguratorBootstrap.php';
$html = array();
foreach (\WCKalkulator\Plugin::$defined_fields as $class) {
    $field = new $class();
    $html[$field->type()] = $field->render_for_admin();
}
echo json_encode(array('html' => $html, 'fields' => configurator_fields()));
