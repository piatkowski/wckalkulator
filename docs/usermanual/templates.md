---
order: -600
label: "Templates"
icon: file-code
---
# Templates

WC Kalkulator v1.1.0 brings a new approach to modifying the appearance of fields. Every file in ``view`` directory can be overriden.
Just copy file from the ``view`` directory to the ``themes/your-theme/wc-kalkulator`` directory and make your modifications.

For example: ``view/fields/front/text.php`` should be copied to the ``/themes/your-theme/wc-kalkulator/fields/front/text.php``

## Structure of the view directory

|Files|Description|
|---|---|
|``views/fields/front/*.php``|Template files for every field rendered on the produt page|
|``views/woocommerce/catalog_price.php``|Template for the price filter - prefix, price, suffix.|
|``views/woocommerce/price_block.php``|Template for the "Total" in the product page|
|``views/woocommerce/product.php``|Container for the fields on the product page|