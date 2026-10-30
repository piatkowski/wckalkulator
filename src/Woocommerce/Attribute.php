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
        $attributes = wc_get_attribute_taxonomies();
        if (!empty($attributes)) {
            foreach ($attributes as $attribute) {
                add_action('pa_' . $attribute->attribute_name . '_add_form_fields', array(__CLASS__, 'add_form'));
                add_action('pa_' . $attribute->attribute_name . '_edit_form_fields', array(__CLASS__, 'edit_form'), 10, 2);
                add_action('manage_edit-pa_' . $attribute->attribute_name . '_columns', array(__CLASS__, 'add_columns'));
                add_filter('manage_pa_' . $attribute->attribute_name . '_custom_column', array(__CLASS__, 'column_content'), 10, 3);
                add_action('created_pa_' . $attribute->attribute_name, array(__CLASS__, 'save_created'));
                add_action('edited_pa_' . $attribute->attribute_name, array(__CLASS__, 'save_edited'));
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
            <?php wp_nonce_field('wck_attribute_term_value', '_wck_attribute_term_nonce'); ?>
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
                <?php wp_nonce_field('wck_attribute_term_value', '_wck_attribute_term_nonce'); ?>
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
    public static function save_created($term_id)
    {
        self::save_term($term_id, 'manage_terms');
    }

    public static function save_edited($term_id)
    {
        self::save_term($term_id, 'edit_terms');
    }

    /** Backward-compatible edit callback; core taxonomy hooks use the scoped wrappers above. */
    public static function save($term_id)
    {
        self::save_term($term_id, 'edit_terms');
    }

    private static function save_term($term_id, $required_capability)
    {
        $term_id = is_scalar($term_id) ? absint($term_id) : 0;
        if (!$term_id || !isset($_POST['_wck_attribute_term_nonce'], $_POST['wck_value'])
            || !is_scalar($_POST['_wck_attribute_term_nonce']) || !is_scalar($_POST['wck_value'])
            || !wp_verify_nonce(wp_unslash((string) $_POST['_wck_attribute_term_nonce']), 'wck_attribute_term_value')) {
            return;
        }
        $term = get_term($term_id);
        if (!$term || is_wp_error($term) || strpos($term->taxonomy, 'pa_') !== 0) {
            return;
        }
        $taxonomy = get_taxonomy($term->taxonomy);
        $capability = $taxonomy && isset($taxonomy->cap->{$required_capability})
            ? $taxonomy->cap->{$required_capability} : '';
        if ($capability === '' || !current_user_can($capability)) {
            return;
        }

        $raw_value = trim((string) wp_unslash($_POST['wck_value']));
        if ($raw_value !== '' && (!is_numeric($raw_value) || !is_finite((float) $raw_value))) {
            return;
        }
        update_term_meta($term_id, 'wck_value', $raw_value === '' ? 0 : (float) $raw_value);
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
     * Return attribute values from explicit cart data or the current request.
     *
     * @return array
     * @since 1.5.0
     */
    public static function from_request($attributes = null)
    {
        $output = array();
        if ($attributes === null) {
            if (empty($_POST)) {
                return $output;
            }
            $attributes = $_POST;
        }

        if (is_array($attributes)) {
            $attribute_values = $attributes;
            foreach (wc_get_attribute_taxonomies() as $attribute) {
                $name = 'attribute_pa_' . $attribute->attribute_name;
                $var_name = 'pa:' . $attribute->attribute_name;
                $value = null;
                $terms = null;
                if (isset($attribute_values[$name]) && is_scalar($attribute_values[$name])) {
                    $value = sanitize_text_field($attribute_values[$name]);
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
