---
order: -390
label: "Calculation Modes"
icon: number
---

# Calculation Modes

The Plugin comes with several calculation modes. You can choose one of mentioned below:
* ``OFF`` - price calculation is turned off. This mode can be use to get user input without changing the product price
* ``Single-line Formula`` - it is a simple single-line math expression without any logical conditions
* ``Conditional Expression`` - if the condition ``if`` is met, calculate the price according to the assigned formula in ``=`` field. You can use multiple conditions. The ``else`` formula is used when none of the conditions are met.
* ``Price Add-ons`` - if the condition ``if`` is met, the product price is increased by ``add`` value. You can use multiple conditions with all available functions and operators. Both ``if`` and ``add`` field accepts math and logical expression with variables.