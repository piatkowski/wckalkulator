=== Custom Fields and Price Calculator for WooCommerce (Product Addons) ===
Tags: woocommerce custom fields,  woocommerce product price, woocommerce product fields, woocommerce custom price field, woocommerce personalized product, woocommerce custom product fields, product fields, custom product price, price calculation, price formula
Requires at least: 5.0
Tested up to: 5.9.3
Stable tag: 1.1.0
Requires PHP: 5.6
License: GNU GPLv2
Donate link: https://www.paypal.com/donate/?hosted_button_id=5DNZK72H5YCBY

WooCommerce custom fields and custom price calculation. Add fields to product page of your WooCommerce store. Use formula based price calculation.

== Description ==
**WC Kalkulator** is a Wordpress plugin which extends the WooCommerce to use custom fields with products and orders.
Custom fields can be used to calculate a custom product price.

Full documentation at: [wckalkulator.com](https://wckalkulator.com)

DEMO at: [demo.wckalkulator.com](https://demo.wckalkulator.com)

You can sell products by anything. You are not limited to sell only by length, area, volume, etc. **You decide how the prices will be calculated.**

## What makes WC Kalkulator unique ?

When designing the functionality of the plugin, we wanted to obtain software that would be easy to use and **highly configurable** and **suitable for any** type of store. 

The plugin allows **full freedom** to define fields and formulas for calculating the price. 
Some of fields are not used in formulas, but you can use it to get informations from Customer (for example: text, date, date range, color, dropdown, etc.)

The plugin is designed to work with multisite mode. We encourage you to contact us and request new features.

# About the Plugin

The plugin extends the WooCommerce store with the ability to add your own fields to the product page. 
Almost all fields are used in formulas to calculate a custom price for a product. 
The plugin allows full freedom to define fields and formulas for calculating the price.
Some of fields can be used to get informations from Customer (for example: text, date, date range, color, dropdown, etc.)

The plugin is designed to work with multisite mode. We encourage you to contact us and request new features.

## Definitions

- **Field** - is used to get user\'s input on the product page. Field can be used to calculate the price (is used in formula)
- **Fieldset** - store manager can create the fieldset which consists of different **Fields**. Fieldset must have at least one **Field** and the expression (formula) to calculate the product price
- **Expression/Formula** - mathematical and/or logical expression using to calculate the product price. The expression can be single-line (one-line) or conditional. **Expression** is protected and calculated only server-side.
- **Validation** - each **Field** has specific requirements to be met. Incorrect values make it impossible to calculate the price and add the product to the cart.

## Features

- define unlimited fieldsets with unlimited fields
- fields are displayed in product page, cart and order details,
- **expression builder**
- define single-line expression to calculate the price
- define unlimited conditional expressions to calculate the price
- attach fieldset to: all products/catgories, selected products/categories
- **supported variable products**
- **supported multisite**
- the plugin is translatable
- every field has built-in validation tests
- the product will be removed from user\'s cart when shop manager updates the fieldset settings
- **product shortcodes are supported**
- **the formula is protected and will not be shown to the user** - the price is calculated only server-side
- regular and sale price are supported
- supported price filters
- customizable HTML code of every field
- HMTL template of every field can be overridden in a theme directory

Full documentation at: [wckalkulator.com](https://wckalkulator.com)

== Changelog ==
2022-02-18 v.1.1.0

- fieldset post type
- assign the fieldset to products/categories on the fieldset\'s edit page
- added a price option to all fields
- new edit page
- added the priority option to fieldsets
- field's template can be overrided in your theme folder
- performance fixes
- bug fixes

2022-01-20
v.1.0.0

- Initial release