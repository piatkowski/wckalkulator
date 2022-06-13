---
order: -300
label: "Field Types"
icon: rows
---
# Field Types

The plugin comes with several built-in fields to be used on the product page. 
Please contact us if you need to add new field types. 
We are working on the ability to build custom fields as extensions for the plugin. 
We would like to meet your expectations as much as possible.

Each field has its own parameters, but some parameters are the same for every field. See the list below:

|Parameter|Description|
|-|-|
|**Name**|Unique field name used in plugin code. It must consist of A-Z, a-z or underscore. For example: *product_width, table_height*, etc.|
|**Title**|Field name showed to the User. For example: *Product width, Table height*|
|**Hint for Customer**|Help message showed in tooltip. For example: *Please choose product color from the list.* |
|**CSS Class**|CSS class applied to the HTML code of the field. You can define additional styles in custom .css files in theme.|
|**Price**|If the field is filled in by the customer, the price will be used in the formula. Otherwise the price is 0. This parameter is not present for the: number field and select field.

!!!success Field's HTML code can be modified
See **Templates** section to know how to modify HTML code of any field.
!!!

## Number

**Number Field** is the basic field to use in the plugin. It is standard single-line input field, which takes a numeric value and can be used in a formula/expression.
!!!
This field is required by default.
!!!

|Parameter|Description|
|-|-|
|**Min. value**|The minimum value of the field|
|**Max. value**|The maximum value of the field|
|**Default value**|Initial value of the field. This value is loaded into field when user opens the product page.|


```html
<input type="number"...>
```

## Select

**Select Field** is a standard dropdown field. You must define numeric value of options, because this field is used in the formula/expression.
!!!
This field is required by default.
!!!

```html
<select...>
    <option value="1.50">Option A</option>
    <option value="3.00">Option B</option>
    <option value="5.50">Option C</option>
</select>
```

## Checkbox

|Parameter|Description|
|-|-|
|**Default state**|Default state of checkbox. Select this option if you want the field to be checked by default|

```html
<input type="checkbox"...>
```

## Color Picker

**Color Picker** is a standard input text field with **jQuery wpColorPicker** and **IRIS**. 
JS files are bundled in the **Wordpress Core**.

|Parameter|Description|
|-|-|
|**Required**|Select this option to make the field required|


## Date Picker

**Date Picker** is a standard input text field with **jQuery UI Datepicker**. 
JS files are bundled in the **Wordpress Core**.

|Parameter|Description|
|-|-|
|**Required**|Select this option to make the field required.|
|**Disallow past date**|Select this option to prohibit the user from selecting a date earlier than today.|

## Range Date Picker

**Range Date Picker** includes two **Date Picker** fields connected with each other.

|Parameter|Description|
|-|-|
|**Required**|Select this option to make the field required.|
|**Disallow past date**|Select this option to prohibit the user from selecting a date earlier than today.|


## Dropdown

**Dropdown** field is almost the same as **Select** field. This field accepts text values.

|Parameter|Description|
|-|-|
|**Required**|Select this option to make the field required.|

```html
<select...>
    <option value="Option A">Option A</option>
    <option value="Option B">Option B</option>
</select>
```

## Text

!!!warning
Note that defining **Min. length** greater than zero makes the field required.
Even if you have not checked **Required** option.
!!!

```html
<input type="text"...>
```

|Parameter|Description|
|-|-|
|**Required**|Select this option to make the field required.|
|**Min. length**|The minimum number of characters that the user can enter.|
|**Max. length**|The maximum number of characters that the user can enter.|
|**Placeholder**|Support text displayed inside the field. This is not the default value for the field. May be used as a guideline for the user.|

## Textarea

!!!warning
Note that defining **Min. length** greater than zero makes the field required.
Even if you have not checked **Required** option.
!!!

```html
<textarea...></textarea>
```

|Parameter|Description|
|-|-|
|**Required**|Select this option to make the field required.|
|**Min. length**|The minimum number of characters that the user can enter.|
|**Max. length**|The maximum number of characters that the user can enter.|
|**Placeholder**|Support text displayed inside the field. This is not the default value for the field. May be used as a guideline for the user.|

## E-mail

**E-mail field** is used to get e-mail address from the Customer input. This field has an e-mail validation test.

```html
<input type="email"...>
```

|Parameter|Description|
|-|-|
|**Required**|Select this option to make the field required.|
|**Min. length**|The minimum number of characters that the user can enter.|
|**Max. length**|The maximum number of characters that the user can enter.|
|**Placeholder**|Support text displayed inside the field. This is not the default value for the field. May be used as a guideline for the user.|

## Radio

**Radio field** is used to display a group of radio buttons. It has similar funcationality to the **Select field**, so u can use option's values in the formula/expression.

!!!
This field is required by default.
!!!

```html
<label for="..."><input type="radio"...>Option A</label>
<label for="..."><input type="radio"...>Option B</label>
<label for="..."><input type="radio"...>Option C</label>
```

## Checkbox Group (Multi Checkbox)

**Checkbox Group** is used to display a group of checkboxes. It is possible to define selection limit (i.e. customer can choose max 2 options).

## Color Swatches

**Color Swatches** are shown as square thumbnails, which may be selected by a customer. Each options has a numeric value to use in a formula.

## Image Swatches

**Image Swatches** works like Color Swatches, but you can define images instead of colors.

## Image Select

**Image Select** works similar to Image Swatches, but you can define Caption/Title for each image option.

## Static: HTML

Displays content from a HTML code. It is filtered by ``wp_kses_post``

## Static: Attachment

You can add file/media attachment, to be downloaded by the Customer on product page.

## Static: Heading

You can add heading h1-h6 in product page

## Static: Paragraph

You can add text in paragraph tag in product page

## Static: Hidden

You can add hidden input fields to store predefined values. This field does not accept user input.

```html
<input type="hidden"...>
```

## Static: Link

You can add link to any website, file attachment, URL.

