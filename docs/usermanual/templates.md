---
order: -600
label: "Templates & Layouts"
icon: file-code
---

# Templates

WC Kalkulator v1.1.0 brings a new approach to modifying the appearance of fields. Every file in ``view`` directory can
be overriden.
Just copy file from the ``view`` directory to the ``themes/your-theme/wc-kalkulator`` directory and make your
modifications.

For example: ``view/fields/front/text.php`` should be copied to
the ``/themes/your-theme/wc-kalkulator/fields/front/text.php``

## Structure of the view directory

|Files|Description|
|---|---|
|``views/fields/front/*.php``|Template files for every field rendered on the produt page|
|``views/woocommerce/catalog_price.php``|Template for the price filter - prefix, price, suffix.|
|``views/woocommerce/price_block.php``|Template for the "Total" in the product page|
|``views/woocommerce/product.php``|Container for the fields on the product page|

## Filters and Hooks

Example filter to change ``<td class="label">`` tag in field template

```php
add_filter('wck_field_td_label', function($html, $field_type){
   //... modify $html, add attributes, classes, etc. ...
   // use var_dump to explore $html, $field_type paramters
   return $html;
}, 10, 2);
```

Example filter to change ``<td class="value">`` tag in field template

```php
add_filter('wck_field_td', function($html, $field_type){
   //... modify $html, add attributes, classes, etc. ...
   // use var_dump to explore $html, $field_type paramters
   return $html;
}, 10, 2);
```

# Layouts

Layouts has beed added in v.1.3.4. You can choose default one column layout or switch to two column layout.
In this video I show how to use layouts feature.

[!embed](https://youtu.be/b2iNp1lHxK0)

!!! :zap: [Donate](https://www.paypal.com/donate/?hosted_button_id=5DNZK72H5YCBY) :zap:
This plugin is absolutely FREE with PRO features. It will always be free, so please donate if you like it!

[!button variant="light" icon=":heart:" text="I like it!"](https://www.paypal.com/donate/?hosted_button_id=5DNZK72H5YCBY)
&nbsp;
[!button variant="light" icon=":coffee:" text="Just coffee"](https://www.buymeacoffee.com/piatkowski)
!!!