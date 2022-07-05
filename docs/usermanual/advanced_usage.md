---
order: -510
label: "Advanced Usage"
icon: rocket
---

# Advanced Usage

### How to get input text length (character count) ?

Add text field with name **field_name**.

You can get input text using ``{field_name:text}``. 
There's ``strlen()`` function which returns text length, so ``strlen({field_name:text})`` returns input characters count.

### How to use Multi Checkbox field ?

Let's add multi checkbox field named ``field_mcb``. You can use in expression:
* ``{field_mcb:sum}`` - sum of selected values
* ``{field_mcb:min}`` - minimal selected value
* ``{field_mcb:max}`` - maximal selected value

### How to use Range Date Picker field ?

Range date picker has two input fields - "from" and "to" dates. Let's add a new field named ``rdp``
* ``{rdp:date_from}`` - date "from" as unix timestamp
* ``{rdp:date_to}`` - date "to" as unix timestamp
* ``{rdp:days}`` - number of days between two dates (absolute integer)

### How deal with Upload field ?

Add Upload/Image Upload field named ``file``
* ``{file}`` - returns field price defined in the "Price" option
* ``{file:size}`` - returns input file size in MB

!!! :zap: [Donate](https://www.paypal.com/donate/?hosted_button_id=5DNZK72H5YCBY) :zap:
This plugin is absolutely FREE with PRO features. It will always be free, so please donate if you like it!

[!button variant="light" icon=":heart:" text="I like it!"](https://www.paypal.com/donate/?hosted_button_id=5DNZK72H5YCBY)&nbsp;
[!button variant="light" icon=":coffee:" text="Just coffee"](https://www.buymeacoffee.com/piatkowski)
!!!