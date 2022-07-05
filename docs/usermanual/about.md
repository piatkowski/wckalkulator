---
order: -100
label: "About the Plugin"
icon: info
---

# About the Plugin

The plugin extends the Woocommerce store with the ability to add your own fields to the product page. 
Almost all fields are used in formulas to calculate a custom price for a product. 
The plugin allows full freedom to define fields and formulas for calculating the price.
Some of fields can be used to get informations from Customer (for example: text, date, date range, color, dropdown, etc.)

The plugin is designed to work with multisite mode. We encourage you to contact us and request new features.

## Definitions

- **Field** - is used to get user's input on the product page. Field can be used to calculate the price (is used in formula)
- **Fieldset** - store manager can create the fieldset which consists of different **Fields**. Fieldset must have at least one **Field** and the expression (formula) to calculate the product price
- **Expression/Formula** - mathematical and/or logical expression using to calculate the product price. The expression can be single-line (one-line) or conditional. **Expression** is protected and calculated only server-side.
- **Validation** - each **Field** has specific requirements to be met. Incorrect values make it impossible to calculate the price and add the product to the cart.
- **Global Parameters** - are numeric variables which can be used in formulas across all fieldsets.

## Requirements

**Minimum tested version, but not recommended**  
- Wordpress v.5.0
- Woocommerce v.3.5.0

**Maximum tested version**  
- Wordpress v.6.0.0
- Woocommerce v.6.6.1

**Recommended version**  

We strongly recommend to use **most recent** versions of Wordpress and Woocommerce.
There is no restriction to PHP version, but please note that PHP below 7.4 is marked as end-of-life. 
We recommend using PHP7.4 and above. PHP7.4 is supported in Wordpress v5.3 and above.

<a href="https://www.php.net/supported-versions.php" target="_blank" rel="nofollow">Supported PHP versions</a>  
<a href="https://make.wordpress.org/core/handbook/references/php-compatibility-and-wordpress-versions/" target="_blank" rel="nofollow">PHP compatibility and Wordpress versions</a>

## Dependencies

- <a href="https://symfony.com/doc/5.4/components/expression_language.html" target="_blank" rel="nofollow">Symfony ExpressionLanguage Component</a>
- jQuery, jQuery UI (built in Wordpress Core)
- <a href="https://github.com/bugwheels94/math-expression-evaluator" target="_blank" rel="nofollow">Math Expression Evaluator</a> by bugwheels94

## Features

- define unlimited fieldsets with unlimited fields
- fields are displayed in product page, cart and order details,
- **expression builder**
- define single-line expression to calculate the price
- define unlimited conditional expressions to calculate the price
- attach fieldset to: all products/catgories/tags, selected products/categories/tags
- **supported variable products**
- **supported multisite**
- the plugin is translatable
- every field has built-in validation tests
- the product will be removed from user's cart when shop manager updates the fieldset settings
- **product shortcodes are supported**
- **the formula is protected and will not be shown to the user** - the price is calculated only server-side
- regular and sale price are supported
- supported price filters
- customizable HTML code of every field
- HTML template of every field can be overridden in a theme directory
- static fields such as HTML, Heading, Paragraph, Hidden, Link, Attachment
- math functions to use in the expression
- additional functions for radio group, checkbox group (sum, max, min), range date picker (days between dated)
- global parameters can be defined and used in formula
- ability to edit product fields after from a cart
- image upload field (use file size in formula/expression)
- dynamic formula in a static field's contents

## Conflict with other plugins 

This plugin has been tested only with Wordpress and Woocommerce without additional plugins.
Note that there may be a conflict with plugins that modifies the product price and user's cart, or has similar functionality. 
This section will be updated and conflicts will be resolved. 

- We know about issue with displaying prices in a cart page. In some themes there's an issue with cart page. Theme should display `cart item price` instead of `product price`. The plugin modifies cart items, but not products itself.