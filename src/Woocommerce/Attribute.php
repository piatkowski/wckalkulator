<?php

namespace WCKalkulator\Woocommerce;

/**
 * Class Attribute
 *
 * @package WCKalkulator\Woocommerce
 * @author Krzysztof Piątkowski
 * @license GPLv2
 * @since 1.5.0
 */
class Attribute
{
    /**
     * @var array Store the value of attributes sent in POST request
     */
    private static $values = array();

    /**
     * Initialize class. Add actions and filters
     *
     * @return void
     * @since 1.5.0
     */
    public static function init()
    {
        add_action('woocommerce_loaded', array(__CLASS__, 'woocommerce_loaded'));
    }

    /**
     * Action triggered when WooCommerce is loaded
     *
     * @return void
     * @since 1.5.0
     */
    public static function woocommerce_loaded()
    {
        if(current_user_can('manage_woocommerce')) {
            $attributes = wc_get_attribute_taxonomies();
            if (!empty($attributes)) {
                foreach ($attributes as $attribute) {
                    add_action('pa_' . $attribute->attribute_name . '_add_form_fields', array(__CLASS__, 'add_form'));
                    add_action('pa_' . $attribute->attribute_name . '_edit_form_fields', array(__CLASS__, 'edit_form'), 10, 2);
                    add_action('manage_edit-pa_' . $attribute->attribute_name . '_columns', array(__CLASS__, 'add_columns'));
                    add_filter('manage_pa_' . $attribute->attribute_name . '_custom_column', array(__CLASS__, 'column_content'), 10, 3);
                    add_action('created_pa_' . $attribute->attribute_name, array(__CLASS__, 'save'));
                    add_action('edited_pa_' . $attribute->attribute_name, array(__CLASS__, 'save'));
                }
            }
        }
    }

    /**
     * Add fields to the "Add Form"
     *
     * @return void
     * @since 1.5.0
     */
    public static function add_form($taxonomy)
    {
        ?>
        <div class="form-field">
            <label for="wck_value"><?php _e('Field Value for WCK', 'wc-kalkulator'); ?></label>
            <input type="number" name="wck_value" step="any"/>
            <p><?php _e('Numeric Value of the attribute to be used in WC Kalkulator Formula', 'wc-kalkulator'); ?></p>
        </div>
        <?php
    }

    /**
     * Add fields to the "Edit Form"
     *
     * @return void
     * @since 1.5.0
     */
    public static function edit_form($term, $taxonomy)
    {
        $value = get_term_meta($term->term_id, 'wck_value', true);
        ?>
        <tr class="form-field">
            <th><label for="wck_value"><?php _e('Field Value for WCK', 'wc-kalkulator'); ?></label></th>
            <td>
                <input name="wck_value" id="wck_value" type="number" step="any" value="<?php echo esc_attr($value) ?>"/>
                <p class="description"><?php _e('Numeric Value of the attribute to be used in WC Kalkulator Formula', 'wc-kalkulator'); ?></p>
            </td>
        </tr>
        <?php
    }

    /**
     * Save term meta data
     *
     * @return void
     * @since 1.5.0
     */
    public static function save($term_id)
    {
        update_term_meta(
            $term_id,
            'wck_value',
            floatval($_POST['wck_value'])
        );
    }

    /**
     * Add custom column to the wp table list
     *
     * @return array
     * @since 1.5.0
     */
    public static function add_columns($columns)
    {
        $columns['wck_value'] = 'Value for WCK';
        return $columns;
    }

    /**
     * Returns custom columns values
     *
     * @return int|float
     * @since 1.5.0
     */
    public static function column_content($content, $column, $term_id)
    {
        if ($column === 'wck_value') {
            $value = get_term_meta($term_id, 'wck_value', true);
            return empty($value) ? 0 : $value;
        }
    }

    /**
     * Get single attribute term
     *
     * @param $taxonomy
     * @param $slug
     * @return int[]|string|string[]|\WP_Error|\WP_Term[]
     */
    public static function get_term($taxonomy, $term_slug)
    {
        $args = array(
            'hide_empty' => false,
            'slug' => $term_slug,
            'taxonomy' => 'pa_' . $taxonomy,
            'number' => 1
        );
        return get_terms($args);
    }

    /**
     * Return array of Product Attributes and numeric value if attribute is in $_POST request
     *
     * @return array
     * @since 1.5.0
     */
    public static function from_request()
    {
        $output = array();
        if (!empty($_POST)) {
            $attributes = wc_get_attribute_taxonomies();
            foreach ($attributes as $attribute) {
                $name = 'attribute_pa_' . $attribute->attribute_name;
                $var_name = 'pa:' . $attribute->attribute_name;
                $value = null;
                $terms = null;
                if (isset($_POST[$name])) {
                    $value = sanitize_text_field($_POST[$name]);
                    $terms = self::get_term($attribute->attribute_name, $value);
                    if (!empty($terms)) {
                        $value = floatval(get_term_meta($terms[0]->term_id, 'wck_value', true));
                    }
                }
                $output[$var_name] = empty($value) ? 0 : $value;
                $output[$var_name . '_id'] = empty($terms) ? 0 : $terms[0]->term_id;
            }
        }
        return $output;
    }

}