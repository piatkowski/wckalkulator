<?php

namespace WCKalkulator\Fields;

use WCKalkulator\View;

/**
 * Class AbstractField
 *
 * @package WCKalkulator
 */
abstract class AbstractField
{
    protected $groups;

    public final function __construct()
    {
        $required = array("parameters", "data", "default_data", "type", "admin_title", "use_expression", "group");

        foreach ($required as $property) {
            if (!property_exists($this, $property))
                throw new \LogicException(get_class($this) . ' must have a "' . $property . '" property.');
        }

        $this->groups = array(
            'input' => __('Input Fields', 'wc-kalkulator'),
            'select' => __('Select Fields', 'wc-kalkulator'),
            'picker' => __('Picker Fields', 'wc-kalkulator'),
            'upload' => __('Upload Fields', 'wc-kalkulator'),
            'static' => __('Static Fields', 'wc-kalkulator'),
            'special' => __('Special Fields', 'wc-kalkulator'),
            'other' => __('Other Fields', 'wc-kalkulator')
        );

        if (!array_key_exists($this->group, $this->groups)) {
            throw new \LogicException(get_class($this) . ' has unknown group property.');
        }

    }

    /**
     * @param $data
     */
    public function fromArray($data)
    {
        $this->data = $data;
        $this->default_data();
    }

    protected function default_data()
    {
        $data_keys = array_keys($this->data);
        foreach ($this->default_data as $param => $default_value) {
            if (!in_array($param, $data_keys)) {
                $this->data[$param] = $default_value;
            }
        }
    }

    /**
     * @return array
     */
    public function prepared_data()
    {
        return array(
            'type' => $this->type(),
            'title' => $this->data("title"),
            'before_title' => $this->data("before_title"),
            'after_title' => $this->data("after_title"),
            'hint' => $this->html_hint(),
            'name' => "wck[" . $this->data("name") . "]",
            'id' => 'wck_' . $this->data("name"),
            'css_class' => $this->data("css_class"),
            'required' => ($this->is_required() || $this->is_required_when_visible() ? ' required' : ''), // add "required" attribute
            'is_required' => $this->is_required() ? '1' : '0', // is always required
            'show_required_asterisk' => $this->is_required() || $this->is_required_when_visible()
        );
    }

    /**
     * @return string
     */
    public function type()
    {
        return $this->type;
    }

    /**
     * @param string $key
     * @return array|string|null
     */
    public function data($key = '')
    {
        if ($key === '') {
            return $this->data;
        }

        if (!empty($this->data[$key])) {
            return $this->data[$key];
        }

        return;
    }

    /**
     * @return string
     */
    public function html_hint()
    {
        if ($this->data("hint") != '')
            return '<span class="dashicons dashicons-editor-help wck-field-tip" title="' . esc_html($this->data("hint")) . '"></span>';
        return '';
    }

    /**
     * Returns true if the field is always required
     *
     * @return mixed
     */
    public function is_required()
    {
        return !($this->group() === 'static' || $this->type() === 'formula') && $this->data["required"] === '1';
    }

    /**
     * Returns true if the field is required when visible
     *
     * @return mixed
     * @since 1.5.0
     */
    public function is_required_when_visible()
    {
        return $this->data["required"] === '2';
    }

    /**
     * @param $json
     */
    public function fromJSON($json)
    {
        $this->data = json_decode($json, true);
        $this->default_data();
    }

    /**
     * @return false|string
     */
    public function toJSON()
    {
        return wp_json_encode($this->data);
    }

    /**
     * @param $type
     * @return bool
     */
    public function is_type($type)
    {
        return is_array($type) ? in_array($this->type, $type) : $this->type === $type;
    }

    /**
     * @param string $param
     * @return string
     */
    public function render_for_admin($param = '')
    {
        return View::render('fields/admin', array(
            'admin_fields' => $this->admin_fields(),
            'title' => $this->admin_title(),
            'type' => $this->type(),
            'use_expression' => $this->use_expression() ? 'true' : 'false',
            'group' => $this->group(),
            'show_title' => $this->show_title()
        ));
    }

    abstract public function admin_fields($param = '');

    abstract public function order_item_value($value);

    /**
     * Checks if we need to hide "Title" field on admin page
     *
     * @return bool
     */
    public function show_title()
    {
        /**
         * If property exists return its value
         */
        if (property_exists($this, 'show_title')) {
            return $this->show_title;
        }

        /**
         * If field's group is not static, return true (Title field should be visible)
         */
        if ($this->group() !== 'static') {
            return true;
        }

        /**
         * In other case (static group) return false (Title field should be hidden)
         */
        return false;
    }

    /**
     * @return string
     */
    public function admin_title()
    {
        return $this->admin_title;
    }

    /**
     * @return mixed
     */
    public function use_expression()
    {
        return $this->use_expression;
    }

    abstract public function render_for_product($param = '');

    abstract public function render_for_cart($param = '');

    abstract public function validate($value);

    /**
     * @return mixed
     */
    public function group()
    {
        return $this->group;
    }

    public function group_title()
    {
        return $this->groups[$this->group];
    }

    /**
     * @return mixed
     */
    public function icon()
    {
        return $this->icon;
    }

    public function enqueue_scripts()
    {
    }

    /**
     * @return array
     */
    public function localize_script()
    {
        return array();
    }

}