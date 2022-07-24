---
order: -500
label: "Expression Syntax"
icon: code
---
# Expression Syntax

## Field names

```
{field_name} - local field
{fiel_name:function} - built-in field's function
{global:parameter_name} - global paramter
{global:parameter_name}[2] - access global parameter array. The value of an array at index 2
{global:parameter_name}["foo"] - access global parameter json object. The value of key "foo"
```

## Arithmetic operators

```
+ addition
- subtraction
* multiplication
/ division
% modulus
** pow
```

## Comparision Operators

```
== equal
!= not equal
< less than
> greater than
<= less than or equal to
>= greater than or equal to
```

## Logical Operators

```
not
and
or
```

```
!       - not
&&      - and
||      - or
```

## Math Functions

```
round(x; p)      - round "x" with the precision of "p"
ceil(x)         - round up to the integer number
floor(x)        - round down to the integer number
abs(x)          - absolute number
max(a; b,...)    - maximal value
min(a; b,...)    - minimal value
pow(x; p)        - "x" raised to the power of "p" (x^p) 
sqrt(x)         - square root of "x"
strlen(x)       - get length of a text; use with text fields
in_array(v; arr) - true if value "v" in in array "arr"
```

!!! :zap: [Donate](https://www.paypal.com/donate/?hosted_button_id=5DNZK72H5YCBY) :zap:
This plugin is absolutely FREE with PRO features. It will always be free, so please donate if you like it!

[!button variant="light" icon=":heart:" text="I like it!"](https://www.paypal.com/donate/?hosted_button_id=5DNZK72H5YCBY)&nbsp;
[!button variant="light" icon=":coffee:" text="Just coffee"](https://www.buymeacoffee.com/piatkowski)
!!!