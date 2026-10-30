<?php

namespace WCKalkulator;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\ExpressionLanguage\SyntaxError;

/**
 * Class ExpressionParser
 *
 * @package WCKalkulator
 * @author Krzysztof Piątkowski
 * @license GPLv2
 * @since 1.0.0
 */
class ExpressionParser
{
    /**
     * Calculation modes:
     */
    private const MODE_UNDEFINED = 0;
    private const MODE_ONELINE = 1;
    private const MODE_CONDITIONAL = 2;
    private const MODE_ADDON = 3;
    /**
     * Error messages
     *
     * @var string
     */
    public $error;
    /**
     * Expression as array
     *
     * @var array
     */
    private $expr;
    /**
     * @var array|string
     */
    private $base_expr;
    /**
     * Array of var names and values
     *
     * @var array
     */
    private $vars = array();
    /**
     * Array of var names
     * @var array
     */
    private $var_names = array();
    /**
     * Calculation mode: oneline|conditional|addon|undefined
     * @var int
     */
    private $mode = 0;
    /**
     * If expression is valid
     *
     * @var bool
     */
    private $is_valid = false;

    /**
     * Instance of ExpressionLanguage
     *
     * @var ExpressionLanguage|null
     */
    private $expression = null;

    /** @var string Result contract: price, formula, or stock_multiplier. */
    private $result_type = 'price';

    /**
     * ExpressionParser constructor.
     *
     * @param array $expr - the expression
     * @param array $vars - variables used in the expression
     * @since 1.0.0
     */
    function __construct($expr, $vars, $result_type = 'price')
    {
        $this->error = '';
        if (in_array($result_type, array('price', 'formula', 'stock_multiplier'), true)) {
            $this->result_type = $result_type;
        }
        if (!is_array($vars)) {
            $vars = array();
        }
        foreach ($vars as $key => $val) {
            $var_name = str_replace(':', '__p__', $key);
            if (substr($key, -5) === ':text') {
                $this->vars[$var_name] = (string)$val;
            } elseif (is_array($val)) {
                $this->vars[$var_name] = $val;
            } else {
                //error_log($var_name . ' = ' . print_r($val, true));
                $this->vars[$var_name] = floatval($val);
            }
        }
        if (!empty($this->vars)) {
            $this->var_names = array_keys($this->vars);
        }

        $this->expr = $expr;
        if (!is_array($expr) || !isset($expr['mode']) || !is_string($expr['mode']) || !array_key_exists('expr', $expr)) {
            $this->error = __('ExpressionParser: Invalid expression data.', 'wc-kalkulator');
            $this->expression = new ExpressionLanguage();
            $this->register_functions();
            return;
        }
        $this->base_expr = $this->expr;

        $this->detect_mode();
        $this->expr = $this->prepare_expression();

        if (is_array($this->expr) && $this->is_mode_valid()) {
            $has_required_vars = $this->check_required_variables();

            if ($has_required_vars) {
                $this->is_valid = true;
            } else {
                $this->error .= __("-", "wc-kalkulator") . "\n";
            }
        }

        if (!$this->is_mode_valid()) {
            $this->error .= __("ExpressionParser: Invalid calculation mode.", "wc-kalkulator") . "\n";
        }

        if (!is_array($this->expr)) {
            $this->error .= __("ExpressionParser: Prepared expression has incorrect type.", "wc-kalkulator") . "\n";
        }

        //Create ExpressionLanguage instance
        $this->expression = new ExpressionLanguage();
        $this->register_functions();
        if ($this->is_valid) {
            try {
                foreach ($this->prepared_expression_strings() as $prepared_expression) {
                    $this->expression->lint($prepared_expression, $this->var_names);
                }
            } catch (SyntaxError $e) {
                $this->is_valid = false;
                $this->error .= $e->getMessage();
            } catch (\Throwable $e) {
                $this->is_valid = false;
                $this->error .= __('ExpressionParser: Expression is not valid.', 'wc-kalkulator');
            }
        }

    }

    /**
     * Detect the calculation mode
     *
     * @return int
     * @since 1.0.0
     */
    private function detect_mode()
    {
        if ($this->expr["mode"] === "oneline") {
            $this->mode = self::MODE_ONELINE;
        } elseif ($this->expr["mode"] === "conditional") {
            $this->mode = self::MODE_CONDITIONAL;
        } elseif ($this->expr["mode"] === "addon") {
            $this->mode = self::MODE_ADDON;
        } else {
            $this->mode = self::MODE_UNDEFINED;
        }

        return $this->mode;
    }

    /**
     * Prepare the Expression (oneline or conditional)
     *
     * @return array|bool
     * @since 1.0.0
     */
    private function prepare_expression()
    {
        if ($this->mode === self::MODE_ONELINE) {
            return $this->prepare_inline_expression();
        }
        if ($this->mode === self::MODE_CONDITIONAL || $this->mode === self::MODE_ADDON) {
            return $this->prepare_conditional_expression();
        }
        return false;
    }

    /**
     * Prepare the single-line expression from string
     *
     * @return array|bool
     * @since 1.0.0
     */
    private function prepare_inline_expression()
    {
        if (array_key_exists("expr", $this->expr)) {
            return array(
                'eq' => $this->str_clean($this->expr["expr"])
            );
        }
        return false;
    }

    /**
     * Prepare the Expression from the lines array
     *
     * @return array|bool
     * @since 1.0.0
     */
    private function prepare_conditional_expression()
    {
        $prepared = array();
        if (is_array($this->expr["expr"])) {
            foreach ($this->expr["expr"] as $expr) {
                if ($expr["type"] === "condition" || $expr["type"] === "else") {
                    $prepared[] = array(
                        "if" => $this->str_clean($expr["if"]),
                        "then" => $this->str_clean($expr["then"])
                    );
                }
            }
        } else {
            return false;
        }
        return $prepared;
    }

    /**
     * Helper function for clearing strings
     *
     * @param $str
     * @return mixed
     * @since 1.0.0
     */
    private function str_clean($str)
    {
        $str = html_entity_decode((string) $str, ENT_QUOTES, 'UTF-8');
        $result = '';
        $quote = '';
        $length = strlen($str);
        for ($index = 0; $index < $length; $index++) {
            $char = $str[$index];
            if ($quote !== '') {
                $result .= $char;
                if ($char === '\\' && $index + 1 < $length) {
                    $result .= $str[++$index];
                } elseif ($char === $quote) {
                    $quote = '';
                }
                continue;
            }
            if ($char === "'" || $char === '"') {
                $quote = $char;
                $result .= $char;
                continue;
            }
            if (substr($str, $index, 9) === 'constant(') {
                $result .= '(';
                $index += 8;
                continue;
            }
            if (substr($str, $index, 4) === 'acf(') {
                $result .= 'wck_integration_acf_get_field(';
                $index += 3;
                continue;
            }
            if ($char === '{' || $char === '}') {
                continue;
            }
            if ($char === ',') {
                $result .= '.';
                continue;
            }
            if ($char === ';') {
                $result .= ',';
                continue;
            }
            if ($char === ':') {
                $result .= '__p__';
                continue;
            }
            $result .= $char;
        }
        return $result;
    }

    /**
     * Prepare the Expression from the lines array (Product Addons)
     *
     * @return array|bool
     * @since 1.3.0
     */
    private function prepare_addon_expression()
    {
        $prepared = array();
        if (is_array($this->expr["expr"])) {
            foreach ($this->expr["expr"] as $expr) {
                if ($expr["type"] === "condition" || $expr["type"] === "else") {
                    $prepared[] = array(
                        "if" => $this->str_clean($expr["if"]),
                        "then" => $this->str_clean($expr["then"])
                    );
                }
            }
        } else {
            return false;
        }
        return $prepared;
    }

    /**
     * Check if the calculation mode is valid
     *
     * @return bool
     * @since 1.0.0
     */
    private function is_mode_valid()
    {
        return in_array($this->mode, array(
            self::MODE_ONELINE,
            self::MODE_CONDITIONAL,
            self::MODE_ADDON
        ));
    }

    /**
     * Check if we have passed all required Variables to calculate the Expression
     *
     * @return bool
     * @since 1.0.0
     */
    private function check_required_variables()
    {
        $strings = array();
        $this->collect_expression_strings($this->base_expr, $strings);
        foreach ($strings as $source) {
            foreach ($this->extract_placeholders($source) as $variable) {
                $variable = str_replace(':', '__p__', $variable);
                if (!in_array($variable, $this->var_names, true)) {
                    return false;
                }
            }
        }
        return count($strings) > 0;
    }

    /** Return the exact strings that will be evaluated by ExpressionLanguage. */
    private function prepared_expression_strings()
    {
        if ($this->mode === self::MODE_ONELINE) {
            return isset($this->expr['eq']) && is_string($this->expr['eq']) ? array($this->expr['eq']) : array();
        }
        $strings = array();
        if (is_array($this->expr)) {
            foreach ($this->expr as $row) {
                if (!is_array($row)) {
                    continue;
                }
                foreach (array('if', 'then') as $key) {
                    if (isset($row[$key]) && is_string($row[$key])) {
                        $strings[] = $row[$key];
                    }
                }
            }
        }
        return $strings;
    }

    /** Collect scalar expression strings from either expression mode. */
    private function collect_expression_strings($value, &$strings)
    {
        if (is_array($value)) {
            foreach ($value as $item) {
                $this->collect_expression_strings($item, $strings);
            }
        } elseif (is_string($value)) {
            $strings[] = $value;
        }
    }

    /** Read {variable} references outside quoted string literals. */
    private function extract_placeholders($source)
    {
        $variables = array();
        $quote = '';
        $length = strlen($source);
        for ($index = 0; $index < $length; $index++) {
            $char = $source[$index];
            if ($quote !== '') {
                if ($char === '\\') {
                    $index++;
                } elseif ($char === $quote) {
                    $quote = '';
                }
                continue;
            }
            if ($char === "'" || $char === '"') {
                $quote = $char;
                continue;
            }
            if ($char !== '{') {
                continue;
            }
            $end = strpos($source, '}', $index + 1);
            if ($end === false) {
                return array();
            }
            $variable = substr($source, $index + 1, $end - $index - 1);
            if ($variable === '' || strpos($variable, '{') !== false) {
                return array();
            }
            $variables[] = $variable;
            $index = $end;
        }
        return $variables;
    }

    /**
     * Extend ExpressionLanguage Component to use math functions
     *
     * @since 1.2.0
     */
    private function register_functions()
    {
        $functions = array('round', 'ceil', 'floor', 'abs', 'max', 'min', 'pow', 'sqrt', 'strlen', 'in_array');
        foreach ($functions as $function) {
            $this->expression->addFunction(ExpressionFunction::fromPhp($function));
        }

        $this->expression->register('is_selected', function ($array, $value) {
            return sprintf('\WCKalkulator\ExpressionParser::func_is_selected(%1$s, %2$s)', $array, $value);
        }, function ($arguments, $array, $value) {
            return \WCKalkulator\ExpressionParser::func_is_selected($array, $value);
        });

        if(class_exists('ACF') and function_exists('get_field')) {
            include __DIR__ . '/Integrations/ACF.php';
            $this->expression->addFunction(ExpressionFunction::fromPhp('wck_integration_acf_get_field'));
        }
    }

    /**
     * Checks if the instance of parser is valid - has required fields and the correct mode
     *
     * @return bool
     * @since 1.0.0
     */
    public function is_ready()
    {
        return $this->is_valid;
    }

    /**
     * Calculate the Expression
     *
     * @return array
     * @since 1.0.0
     */
    public function execute()
    {
        if (!$this->is_valid) {
            return $this->calc_error("ExpressionParser: invalid data!");
        }

        /*
         * 1. Evaluate single-line expression
         */
        if ($this->mode === self::MODE_ONELINE) {
            return $this->calc_or_fail($this->expr['eq']);
        }

        /*
         * 2. Evaluate conditional expression
         */
        if ($this->mode === self::MODE_CONDITIONAL) {
            try {
                foreach ($this->expr as $expr) {
                    $condition_value = $this->expression->evaluate($expr['if'], $this->vars);
                    if ($condition_value === true) {
                        return $this->calc_or_fail($expr['then']);
                    }
                }
            } catch (SyntaxError $e) {
                return $this->calc_error($e->getMessage());
            } catch (\DivisionByZeroError $e) {
                return $this->calc_error(__("ExpressionParser: Division by zero", "wc-kalkulator"));
            }
            return $this->calc_error(__("ExpressionParser: Undefined result!", "wc-kalkulator"));
        }

        /*
         * 3. Evaluate price addons and return the sum of addons
         */
        if ($this->mode === self::MODE_ADDON) {
            try {
                $addons_price = $this->expression->evaluate('product_price', $this->vars);
                if (!$this->is_numeric_result($addons_price)) {
                    return $this->calc_error(__('ExpressionParser: Invalid price result.', 'wc-kalkulator'));
                }
                foreach ($this->expr as $expr) {
                    $condition_value = $this->expression->evaluate($expr['if'], $this->vars);
                    if ($condition_value === true) {
                        $addon = $this->expression->evaluate($expr['then'], $this->vars);
                        if (!$this->is_numeric_result($addon)) {
                            return $this->calc_error(__('ExpressionParser: Invalid price result.', 'wc-kalkulator'));
                        }
                        $addons_price += $addon;
                        if (!$this->is_numeric_result($addons_price)) {
                            return $this->calc_error(__('ExpressionParser: Invalid price result.', 'wc-kalkulator'));
                        }
                    }
                }
            } catch (SyntaxError $e) {
                return $this->calc_error($e->getMessage());
            } catch (\DivisionByZeroError $e) {
                return $this->calc_error(__("ExpressionParser: Division by zero", "wc-kalkulator"));
            } catch (\Throwable $e) {
                return $this->calc_error(__('ExpressionParser: Invalid calculation result.', 'wc-kalkulator'));
            }

            /*
             * Return calculated addons price
             */
            $validated_price = $this->validate_result($addons_price);
            if ($validated_price === false) {
                return $this->calc_error(__('ExpressionParser: Invalid price result.', 'wc-kalkulator'));
            }

            return Ajax::response('success', $validated_price);
        }

        /*
         * 4. Return error if calculation mode is not matching
         */
        return $this->calc_error(__("ExpressionParser: Undefined calculation mode!", "wc-kalkulator"));
    }

    /**
     * Return the calculation error message
     *
     * @param string $msg
     * @return array
     * @since 1.0.0
     */
    public function calc_error($msg = "")
    {
        return Ajax::response('error', $msg);
    }

    /**
     * Calculate the numeric value or return error
     *
     * @param $expr
     * @return array
     * @since 1.0.0
     */
    private function calc_or_fail($expr)
    {
        try {
            $value = $this->expression->evaluate($expr, $this->vars);
            $validated_value = $this->validate_result($value);
            if ($validated_value === false) {
                return $this->calc_error(__('ExpressionParser: Invalid calculation result.', 'wc-kalkulator'));
            }
            return Ajax::response('success', $validated_value);
        } catch (SyntaxError $e) {
            return $this->calc_error($e->getMessage());
        } catch (\DivisionByZeroError $e) {
            return $this->calc_error(__("Division by zero.", "wc-kalkulator"));
        } catch (\Throwable $e) {
            return $this->calc_error(__('ExpressionParser: Invalid calculation result.', 'wc-kalkulator'));
        }
    }

    /**
     * Validate and normalize evaluator output for the requested result contract.
     *
     * @param mixed $value
     * @return float|int|false
     */
    private function validate_result($value)
    {
        if (!$this->is_numeric_result($value)) {
            return false;
        }

        if ($this->result_type === 'price') {
            if ((float) $value <= 0) {
                return false;
            }
            $decimals = function_exists('wc_get_price_decimals') ? absint(wc_get_price_decimals()) : 2;
            $normalized = round((float) $value, min($decimals, 8));
            return $normalized > 0 ? $normalized : false;
        }

        if ($this->result_type === 'stock_multiplier') {
            if ((float) $value <= 0 || (float) $value > 1000000) {
                return false;
            }
            return (float) $value;
        }

        return $value;
    }

    /**
     * Check a raw evaluator result before it is used in further arithmetic.
     *
     * @param mixed $value
     * @return bool
     */
    private function is_numeric_result($value)
    {
        return (is_int($value) || is_float($value))
            && is_finite((float) $value)
            && abs((float) $value) <= 1000000000000;
    }

    /**
     * Function for ExpressionLanguage
     * Checks if value is in array (for multi checkbox field)
     *
     * @param $array
     * @param $value
     * @return bool
     * @since 1.4.6
     */
    public static function func_is_selected($array, $value)
    {
        if (is_array($array)) {
            $value = floatval($value);
            foreach ($array as $val) {
                if (floatval($val) === $value)
                    return true;
            }
        } else {
            return $array === $value;
        }
        return false;
    }

}
