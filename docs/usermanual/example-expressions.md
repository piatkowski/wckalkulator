---
order: -502
label: "Formula Examples"
icon: code
---
# Formula Examples

In this section, we cover examples of how to use mathematical formulas and expressions. Some of the examples are based on questions asked by users.

---

### 1. How to add value to a price so that it is not multiplied by quantity?

By default, the unit price of the product is calculated. If the customer wants to buy, for example, 2 pieces of a product, the calculated price is multiplied by 2. What to do if we want to add a certain value once, regardless of the quantity purchased.
The solution is to divide value by ``{quantity}``. Let's see the example. ``{field_1}`` will by multiplied by quantity, but ``{field_2}`` is added once.
```
= {field_1} + {field_2} / {quantity}
```

---

### 2. How to add price for every character in text field ?

Let's say we have a text field that has to be between 1 and 10 characters long. 
The price of the product is to change depending on the number of characters entered.

Solution #1 - price per character is constans
```
= {product_price} + strlen({my_field:text}) * {my_field}
```

Solution #2 - price is different for each character
```
We need to define Global Parameter as an array. Let's name it "price_per_char",
so the array contains price per characters from 1 to 10:
[0, 0, 0, 0, 100, 250, 250, 350, 500, 1000]
```
Next we have to build the formula. We have ``{global:price_per_char}`` array and we need to get single value by index.
The index is ``strlen({my_field:text}) - 1``, so complete formula looks like this:
```
= {product_price} + {global:price_per_char}[strlen({my_field:text}) - 1]
```

Solution #3 - use Price Add-on mode and write multiple ``if`` and ``add`` expressions. For example:
```
if strlen({my_field:text}) <= 4
add 0

if strlen({my_field:text}) == 5
add 100

if strlen({my_field:text}) >= 6 and strlen({my_field:text}) <= 7
add 250

if strlen({my_field:text}) == 8
add 350

if strlen({my_field:text}) == 9
add 500

if strlen({my_field:text}) >= 10
add 1000
```

---

### 3. Each product has width and height. User gives only one dimension and the price is proportional to the calculated area.

User has to input width, but the height will be calculated with proportions from product dimensions.

First of all, set the product width and height in the product options, so we will be using ``{product_width}`` and ``{product_height}`` variables. <a href="https://www.youtube.com/watch?v=M2t0yf8ocbk" target="_blank">Video: How to use product dimensions</a>.
Create number field and name it ``width``. The height will be calculated as proportional to the product dimensions. Look at this equations:
```
height = width * product_height / product_width
area = width * height = width ^ 2 * product_height / product_width
price = area * price_per_unit
```
If the price per unit equals 99,00 USD, the result formula will be:
```
= 99.00 * {width} * {width} * {product_height} / {product_width}
```
The same formula with "height" field instead of "width"
```
= 99.00 * {height} * {height} * {product_width} / {product_height}
```

---

### 4. How to decrease product price for logged user by -10,00 USD or by -5 %

We have built-in variable ``{is_user_logged}`` which takes values 1 or 0.
So the result formula will be:
```
/* For -10,00 USD */
= {product_price} - 10.00 * {is_user_logged}
```
or
```
/* For -5% */
= {product_price} * (1 - 0.05 * {is_user_logged})
```

You must be careful when decreasing prices, because it may be 0 or negative. To protect this, use ``max()`` function.
```
/* For -10,00 USD + limit min. price to be 0.99 */
= max( {product_price} - 10.00 * {is_user_logged}; 0.99 )
```
or
```
/* For -5% + limit min. price to be 0.99  */
= max( {product_price} * (1 - 0.05 * {is_user_logged}); 0.99 )
```

---

### 5. How to use multicheckbox and conditional number fields ?

In this example we define multicheckbox field named `{multi_cb}` and three number fields `{num_a}, {num_b}, {num_c}`.

Multicheckbox `{multi_cb}` has three options:
- value: 1, option title: Option A
- value: 2, option title: Option B
- value: 3, option title: Option C

Each of number fields `{num_a}, {num_b}, {num_c}` are visible for one of three `{multi_cb}` options. So, we need to define visibility rules. For example field `{num_a}` has rule `{multi_cb} == 1`, which means that this field will be visible if user checked "Option A".

Next, we need to go to "Price Add-ons" to set formulas. To check if user has selected certain option we use `is_selected()` function. This is example how to calculate price based on selected option and number field value:

`if is_selected( {multi_cb}; 1 )` `add {num_a} * 100`

`if is_selected( {multi_cb}; 2 )` `add {num_b} * 250`

`if is_selected( {multi_cb}; 3 )` `add {num_c} * 500`

<img src="../../images/example-multi-checkbox-and-visibility.png" style="display:block; margin: 0 auto; max-width:500px" alt="" />