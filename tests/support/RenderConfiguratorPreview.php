<?php
/** Render a browser fixture with production templates. php -n tests/support/RenderConfiguratorPreview.php > /tmp/.../index.html */
require __DIR__ . '/ConfiguratorBootstrap.php';
function preview_form($id, $quantity_mode) {
    $schema = configurator_fields();
    $schema['size']['title'] = 'Wymiary'; $schema['finish']['title'] = 'Dodatkowe informacje';
    $schema['note']['title'] = 'Uwagi do zamówienia';
    $schema['items']['title'] = 'Rolety';
    if ($quantity_mode) { $schema['items']['repeat_mode'] = 'quantity'; }
    $context = \WCKalkulator\FieldsetProduct::create_context();
    $context->init_from_snapshot(configurator_snapshot($schema), $id);
    $input = configurator_input(); $html_fields = array();
    foreach ($context->fields() as $name => $field) {
        $html_fields[] = array('html' => $field->render_for_product(isset($input[$name]) ? $input[$name] : ''), 'type' => $field->type(), 'title' => $field->data('title'), 'section_mode' => $field->data('section_mode'), 'colspan' => 1);
    }
    echo '<form id="product-' . $id . '"><label>Liczba sztuk <input type="number" name="quantity" min="1" max="5" value="2"></label>';
    echo $context->render(array('hidden' => '', 'fields' => $html_fields));
    echo '<button type="submit">Dodaj do koszyka</button><p class="submission" role="status"></p></form>';
}
?>
<!doctype html><html lang="pl"><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>WC Kalkulator — podgląd testowy</title><link rel="stylesheet" href="product.min.css">
<style>body{font-family:system-ui,sans-serif;margin:24px auto;padding:0 16px;max-width:900px;color:#222;background:#fff}form{padding-block:16px 40px;border-bottom:1px solid #ccc}table{width:100%;border-collapse:collapse}td{padding:6px;text-align:left;vertical-align:top}td.label{width:30%}input,select,textarea,button{font:inherit;padding:8px;border:1px solid #777;border-radius:3px}button{cursor:pointer;background:#f5f5f5;color:#111}button:disabled{opacity:.5}h1{font-size:24px}h2{font-size:20px}.screen-reader-text{position:absolute;width:1px;height:1px;overflow:hidden;clip-path:inset(50%)}@media(max-width:600px){td{display:block;width:auto!important}.wck-step-navigation{gap:.5em}}</style>
<h1>WC Kalkulator — podgląd testowy</h1><h2>Dodawanie elementów przyciskiem</h2><?php preview_form(1, false); ?>
<h2>Wiersz na każdą sztukę produktu</h2><?php preview_form(2, true); ?>
<script src="jquery.min.js"></script><script>var wck_configurator_i18n={step:'Krok %1$s z %2$s',rows:'Elementy: %s',limit:'Liczba sztuk musi mieścić się w zakresie grupy.',measurement:'Wpisz wymiar zgodny z zakresem i krokiem.',required:'Wybierz co najmniej jedną opcję.'};</script>
<script src="configurator.min.js"></script><script>jQuery('form').on('submit',function(e){e.preventDefault();jQuery(this).find('.submission').text('Formularz poprawny');});</script>
</html>
