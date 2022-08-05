=== Product Fields and Price Calculator for WooCommerce ===
Tags: woocommerce custom fields,  woocommerce product price, woocommerce product fields, woocommerce custom price field, woocommerce personalized product, woocommerce custom product fields, product fields, custom product price, price calculation, price formula
Requires at least: 5.0
Tested up to: 6.0.1
Stable tag: 1.4.7
Requires PHP: 5.6
License: GNU GPLv2
Donate link: https://www.paypal.com/donate/?hosted_button_id=5DNZK72H5YCBY

WooCommerce extra product fields, addons and price calculator (formula). Absolutely FREE - 23 different field types on your product and order page.

== Description ==

**WC Kalkulator (WCK)** is a Wordpress plugin which extends the WooCommerce to use custom extra fields with products and orders.
Extra product fields can be used to calculate product price and save information in order details. 

*	Absolutely **FREE plugin** with **PRO features**
*	**23 field types** to get customer input
*	Price calculation based on **formula**, **conditional expression** or **price add-ons** (product addons).

If you appreciate my work - [Buy me a Coffee](https://www.buymeacoffee.com/piatkowski) or [Donate via PayPal](https://www.paypal.com/donate/?hosted_button_id=5DNZK72H5YCBY)

Sell products by anything. You are not limited to sell only by length, area, volume, etc. **You decide how the prices will be calculated.** WooCommerce Product Fields and Price Calculator plugin allows **full freedom** to define fields and formulas for calculating custom price. You can create a personalized product in your store.

## Documentation

*	Documentation @ [wckalkulator.com](https://wckalkulator.com)
*	GitHub @ [github.com/piatkowski](https://github.com/piatkowski/wckalkulator)

## 22 Field Types (ALL FREE)

    1.  Attachment (URL/File)
    2.  Checkbox
    3.  Multi checkbox (group)
    4.  Color Picker
    5.  Color Swatches
    6.  Date Picker
    7.  Date Range Picker
    8.  Dropdown
    9.  E-mail input
    10. Heading
    11. Hidden
    12. HTML
    13. Image Select
    14. Image Swatches
    15. Link   
    16. Number
    17. Paragraph
    18. Radio
    19. Select
    20. Textarea
    21. Text input
    22. Image upload
    23. Formula Value
= =

[youtube https://www.youtube.com/watch?v=Jrc1dXof_pw]

## Definitions

- **Field** - is used to get user's input on the woocommerce product page. Custom Field can be used to calculate the price (is used in formula)

- **Fieldset** - store manager can create the fieldset (set of custom product fields) which consists of different **Fields**. Fieldset must have at least one **Field** and the expression (formula) to calculate the product price

- **Expression/Formula** - mathematical and/or logical expression using to calculate the woocommerce custom price. The expression can be single-line, conditional price addons (product addons). **Expression** is protected and calculated only server-side.

- **Validation** - each **Field** has specific requirements to be met. Incorrect values make it impossible to calculate the woocommerce custom price and add the product to the cart.

- **Global Parameters** - are numeric variables which can be used in formulas across all fieldsets.

### Formula/Expression Builder

Use field's values as variables to calculate product price. Drag&drop conditional statements.
You can use product addons to add extra price to the product regular price.

### Price Add-Ons

Use custom fields to make Product Addons.

### Display Fields

Assign fields to products, categories or product tags. Include/exclude options.
Mass assignment.

### Validation

Each field has built-in validation tests on user input. You can define additional conditions.

### Protected Formula

The price is calculated server-side only, so the Client can't see exact expression.

### Advanced Customization

Field HTML templates can be overloaded in your theme file. Each field has CSS class to set custom styles.

### Functions

You can use basic math functions in the formula. Fields such as Multi-checkbox, Date Picker, Date Range Picker has additional functions to get sum, min, max value or get date, days between dates values as number.

### Global Parameters

You can define numeric variable across all fieldsets. You can modify all prices by global parameters.

### Cart

The customer can edit product options after adding to cart.

## Compatibility

* multisite mode is supported
* product shortcodes
* translation
* virtual and variable products are supported
* product regular and sale prices are supported
* product tags and attributes

## More Features

*    define unlimited fieldsets with unlimited fields
*    fields are displayed in product page, cart and order details,
*    **expression builder**
*    define single-line expression to calculate the price
*    define unlimited conditional expressions to calculate the price
*    attach fieldset to: all products/catgories/tags, selected products/categories/tags
*    **supported variable products**
*    **supported multisite**
*    the plugin is translatable
*    every field has built-in validation tests
*    the product will be removed from user's cart when shop manager updates the fieldset settings
*    **product shortcodes are supported**
*    **the formula is protected and will not be shown to the user** - the price is calculated only server-side
*    regular and sale price are supported
*    supported price filters
*    customizable HTML code of every field
*    HMTL template of every field can be overridden in a theme directory
*    static fields such as HTML, Heading, Paragraph, Hidden, Link, Attachment
*    math functions to use in the expression
*    additional functions for radio group, checkbox group (sum, max, min), range date picker (days between dated)
*    global parameters can be defined and used in formula
*    ability to edit product fields after from a cart

Full documentation at: [www.wckalkulator.com](https://wckalkulator.com)

== Changelog ==
2022-08-05 v.1.4.7
- fieldset's options (toggle default price blocks)
- new field: formula value
- bug fixes

2022-07-23 v.1.4.6
- added is_selected() function
- bug fixes in multi checkbox

2022-07-23 v.1.4.5
- conditional visibility support for multi checkbox

2022-07-23 v.1.4.4
- bug fixes

2022-07-23 v.1.4.3
- bug fixes

2022-07-22 v.1.4.1, 1.4.2
- bug fixes

2022-07-21 v.1.4.0
- new formula builder
- apply filters on td elements in field's templates
- new assignment type: product attribute
- more columns on fieldset table
- toggle button to publish/unpublish fieldsets quickly
- support for stock management and stock reduction multiplier
- layouts feature - you can choose one or two column layout
- conditional visibility (set rules to show/hide fields)
- bug fixes

2022-07-11 v.1.3.3
- Bug fixed: str_replace on array
- Bug fixed: missing numberposts argument on get_posts()

2022-07-07 v.1.3.2
- added support for array and json objects in global parameters

2022-07-06 v.1.3.1
- new variables to get product's weight, width, height, length
- new variable to determine if current visitor is logged in
- upload path settings

2022-07-05 v 1.3.0
- new calculation mode - Price Add-ons
- you can use formulas in HTML/Paragraph field, for example: {={field_a}*{field_b}/100}
- Image upload field added – you can use file size parameter in expressions
- cron jobs to keep uploaded files clean
- strlen() function added to expressions – it returns text length
- Settings page added – you can define custom product form selector, you can toggle error messages for admin/manager

2022-06-15 v.1.2.3

- issue with ajax form serialization has been fixed

2022-06-14 v.1.2.2

- add notices

2022-06-13 v.1.2.1

- fixed issue with price calculation

2022-06-13 v.1.2.0

- New fields: image select, image swatches, color swatches, checkbox group (multicheckbox), HTML, Heading, Paragraph, Hidden, Link, Attachment
- Math functions to use in the expression
- Additional functions for radio group, checkbox group (sum, max, min), range date picker (days between dated)
- Global parameters can be defined and used in formula
- Assign fieldset to product's tags
- Customer can edit cart item
- text field has new option: pattern (regexp)
- bug fixes

2022-06-03 v.1.1.3

- bug fixes on ajax calls

2022-05-08 v.1.1.2

- Added new fields: email, radio
- fixed field builder (js script issue)
- fixed typo in HTML code for dropdown and select fields

2022-04-20 v.1.1.1

- Bug fix

2022-02-18 v.1.1.0

- custom fields (fieldset) post type
- assign the fieldset to products/categories on the fieldset\'s edit page
- added a price option to all fields
- new edit page
- added the priority option to fieldsets
- field's template can be overrided in your theme folder
- performance fixes
- bug fixes

2022-01-20 v.1.0.0

- Initial release