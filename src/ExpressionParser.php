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

    /**
     * ExpressionParser constructor.
     *
     * @param array $expr - the expression
     * @param array $vars - variables used in the expression
     * @since 1.0.0
     */
    function __construct($expr, $vars)
    {
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

        /*
         * Fixed issue: https://wordpress.org/support/topic/conditional-expression-expressionparser-error/#post-15814485
         * str_replace on array in PHP8+ throws PHP Warning:  Array to string conversion
         */
        $this->expr = $expr;
        array_walk_recursive(
            $this->expr,
            function (&$value) {
                $value = str_replace(':', '__p__', $value);
            }
        );
        /*
         * End Fix
         */
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
        return
            str_replace(
                array('{', '}', ',', 'constant(', ';', ':', 'acf('),
                array('', '', '.', '(', ',', '__p__', 'wck_integration_acf_get_field('),
                html_entity_decode($str)
            );
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
        $expr_string = "";

        if ($this->mode === self::MODE_ONELINE) {
            $expr_string = $this->base_expr["expr"];
        }

        if ($this->mode === self::MODE_CONDITIONAL || $this->mode === self::MODE_ADDON) {
            foreach ($this->expr as $expr) {
                $expr_string .= $expr["if"] . $expr["then"];
            }
        }

        preg_match_all('/{([^}]+)}/m', $expr_string, $matched_vars);

        if (count($matched_vars) === 2) {
            $matched_vars = array_unique($matched_vars[1]);
            if (count($matched_vars) > 0) {
                foreach ($matched_vars as $var) {
                    if (!in_array($var, $this->var_names)) {
                        return false;
                    }
                }
                return true;
            } else {
                //return true if there's no variables in expression
                return true;
            }
        }
        return false;
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
            $addons_price = $this->expression->evaluate('product_price', $this->vars);
            try {
                foreach ($this->expr as $expr) {
                    $condition_value = $this->expression->evaluate($expr['if'], $this->vars);
                    if ($condition_value === true) {
                        $addons_price += $this->expression->evaluate($expr['then'], $this->vars);
                    }
                }
            } catch (SyntaxError $e) {
                return $this->calc_error($e->getMessage());
            } catch (\DivisionByZeroError $e) {
                return $this->calc_error(__("ExpressionParser: Division by zero", "wc-kalkulator"));
            }

            /*
             * Return calculated addons price
             */
            if ($addons_price < 0) {
                return $this->calc_error(__('The price is less than zero.', "wc-kalkulator") . ' =' . $addons_price);
            } elseif ($addons_price === 0) {
                return $this->calc_error(__('The price is equal zero.', "wc-kalkulator"));
            }

            return Ajax::response('success', $addons_price);
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
            if ($value < 0) {
                return $this->calc_error(__('The price is less than zero.', "wc-kalkulator") . ' =' . $value);
            } elseif ($value === 0) {
                return $this->calc_error(__('The price is equal zero.', "wc-kalkulator"));
            }
            return Ajax::response('success', $value);
        } catch (SyntaxError $e) {
            return $this->calc_error($e->getMessage());
        } catch (\DivisionByZeroError $e) {
            return $this->calc_error(__("Division by zero.", "wc-kalkulator"));
        }
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