<?php

namespace WCKalkulator;

/**
 * Class FieldsetAssignment
 *
 * @package WCKalkulator
 * @author Krzysztof Piątkowski
 * @license GPLv2
 * @since 1.1.0
 */
class FieldsetAssignment
{

    const TYPE_ALL = 1;
    const TYPE_ALL_EXCEPT = 2;
    const TYPE_ONLY_SELECTED = 3;

    /**
     * All defined assign types
     *
     * @var array
     * @since 1.1.0
     */
    private static $type;

    /**
     * Initialize value of properties ($type)
     *
     * @since 1.1.0
     */
    public static function init()
    {
        self::$type = array(
            self::TYPE_ALL => __('All Products', 'wc-kalkulator'),
            self::TYPE_ALL_EXCEPT => __('All Products, except below:', 'wc-kalkulator'),
            self::TYPE_ONLY_SELECTED => __('Only selected below:', 'wc-kalkulator')
        );
    }

    /**
     * Return all assignment types as an array
     *
     * @return array
     * @since 1.1.0
     */
    public static function all()
    {
        return self::$type;
    }

    /**
     * Return title by the type's $id
     *
     * @param int $id
     * @return string|null
     * @since 1.1.0
     */
    public static function get($id)
    {
        return self::has($id) ? self::$type[$id] : null;
    }

    /**
     * Check if $id exists in the $type array
     *
     * @param $id
     * @return bool
     * @since 1.1.0
     */
    public static function has($id)
    {
        return isset(self::$type[$id]);
    }

    /**
     * This method returns Fieldset Post ID assigned to the Product
     * Returns ID with the highest priority if multiple Posts are found.
     *
     * @param $product_id
     * @return int
     * @since 1.1.0
     */
    public static function match($product_id)
    {

        if ((int)$product_id === 0)
            return 0;
        /*
         * Return cached fieldset
         */
        $cached = Cache::get('FieldsetAssignment_match_' . $product_id);
        if ($cached)
            return $cached;

        /*
         * Get categories IDs
         */
        $category_id = array();

        $terms = get_the_terms($product_id, 'product_cat');

        if (is_array($terms)) {
            foreach ($terms as $term) {
                $category_id[] = $term->term_id;
            }
        }
        /*
         * Get tags IDs
         */
        $tag_id = array();

        $terms = get_the_terms($product_id, 'product_tag');

        if (is_array($terms)) {
            foreach ($terms as $term) {
                $tag_id[] = $term->term_id;
            }
        }
        /*
         * Get product attributes IDs
         */
        $attribute_id = array();
        $taxonomies = Cache::get('FieldsetAssignment_taxonomies');
        if (empty($taxonomies)) {
            $taxonomies = get_taxonomies(null, 'objects');
            Cache::store('FieldsetAssignment_taxonomies', $taxonomies);
        }
        foreach ($taxonomies as $taxonomy) {
            $attributes = get_the_terms($product_id, $taxonomy->name);
            if (is_array($attributes)) {
                foreach ($attributes as $attr) {
                    $attribute_id[] = $attr->term_id;
                }
            }
        }
        /*
         * Get all fieldsets
         */
        $posts = Cache::get('FieldsetAssignment_all_fieldsets');
        if (empty($posts)) {
            $posts = get_posts(array(
                'post_type' => FieldsetPostType::POST_TYPE,
                'per_page' => -1,
                'numberposts' => -1,
                'post_status' => 'publish'
            ));
            Cache::store('FieldsetAssignment_all_fieldsets', $posts);
        }

        $matching = null;
        $max_priority = -INF;

        foreach ($posts as $post) {
            $assign = Cache::get('FieldsetAssignment_fieldset_assign' . $post->ID);
            if(empty($assign)) {
                $assign = array(
                    'type' => get_post_meta($post->ID, '_wck_assign_type', true),
                    'products' => (array)get_post_meta($post->ID, '_wck_assign_products', true),
                    'categories' => (array)get_post_meta($post->ID, '_wck_assign_categories', true),
                    'tags' => (array)get_post_meta($post->ID, '_wck_assign_tags', true),
                    'attributes' => (array)get_post_meta($post->ID, '_wck_assign_attributes', true),
                    'priority' => get_post_meta($post->ID, '_wck_assign_priority', true)
                );
                Cache::store('FieldsetAssignment_fieldset_assign' . $post->ID, $assign);
            }

            $has_match = false;
            switch ($assign['type']) {

                case FieldsetAssignment::TYPE_ALL:
                    $has_match = true;
                    break;

                case FieldsetAssignment::TYPE_ALL_EXCEPT:
                    if (!in_array($product_id, $assign['products'])
                        && count(array_intersect($category_id, $assign['categories'])) === 0
                        && count(array_intersect($tag_id, $assign['tags'])) === 0
                        && count(array_intersect($attribute_id, $assign['attributes'])) === 0) {
                        $has_match = true;
                    }
                    break;

                case FieldsetAssignment::TYPE_ONLY_SELECTED:
                    if (in_array($product_id, $assign['products'])
                        || count(array_intersect($category_id, $assign['categories'])) > 0
                        || count(array_intersect($tag_id, $assign['tags'])) > 0
                        || count(array_intersect($attribute_id, $assign['attributes'])) > 0) {
                        $has_match = true;
                    }
                    break;
            }

            if ($has_match && $assign['priority'] > $max_priority) {
                $matching = $post->ID;
                $max_priority = $assign['priority'];
            }

        }

        Cache::store('FieldsetAssignment_match_' . $product_id, $matching);
        return $matching;
    }

    /**
     * Product ids as input. Method outputs array of readable products titles, example: [16] => 'Product Title (#16)'
     *
     * @param $products
     * @return array
     * @since 1.1.0
     */
    public static function products_readable($products)
    {
        $result = array();
        if (is_array($products)) {
            $products = wc_get_products(array(
                'include' => $products
            ));

            foreach ($products as $product) {
                $id = $product->get_id();
                $result[$id] = $product->get_title() . ' (#' . $id . ')';
            }
        }
        return $result;
    }

    /**
     * Category ids as input. Method outputs array of readable category titles, example: [16] => 'Category Title (2)'
     *
     * @param $categories
     * @return array
     * @since 1.1.0
     */
    public static function categories_readable($categories)
    {
        $result = array();
        if (is_array($categories)) {
            $terms = get_terms(array(
                'taxonomy' => array('product_cat'),
                'orderby' => 'id',
                'order' => 'ASC',
                'hide_empty' => false,
                'fields' => 'all',
                'include' => $categories
            ));
            if ($terms) {
                foreach ($terms as $term) {
                    $term->formatted_name = '';
                    if ($term->parent) {
                        $ancestors = array_reverse(get_ancestors($term->term_id, 'product_cat'));
                        foreach ($ancestors as $ancestor) {
                            $ancestor_term = get_term($ancestor, 'product_cat');
                            if ($ancestor_term) {
                                $term->formatted_name .= $ancestor_term->name . ' > ';
                            }
                        }
                    }

                    $term->formatted_name .= $term->name . ' (' . $term->count . ')';
                    $result[$term->term_id] = $term->formatted_name;
                }
            }
        }
        return $result;
    }


    /**
     * Tags ids as input. Method outputs array of readable tags titles, example: [16] => 'Custom Tag'
     *
     * @param $tags
     * @return array
     * @since 1.1.0
     */
    public static function tags_readable($tags)
    {
        $result = array();
        if (is_array($tags)) {
            $terms = get_terms(array(
                'taxonomy' => array('product_tag'),
                'orderby' => 'id',
                'order' => 'ASC',
                'hide_empty' => false,
                'fields' => 'all',
                'include' => $tags
            ));
            if ($terms) {
                foreach ($terms as $term) {
                    $term->formatted_name = '#' . $term->name . ' (' . $term->count . ')';
                    $result[$term->term_id] = $term->formatted_name;
                }
            }
        }
        return $result;
    }

    /**
     * Attributes ids as input. Method outputs array of readable attributes labels, example: [0] => 'Blue'
     *
     * @param $attributes
     * @return array
     * @since 1.4.0
     */
    public static function attributes_readable($attributes)
    {
        $result = array();
        $taxonomies = array();
        foreach (get_taxonomies() as $taxonomy) {
            if (substr($taxonomy, 0, 3) === 'pa_') {
                $taxonomies[] = $taxonomy;
            }
        }
        if (is_array($attributes)) {
            $terms = get_terms(array(
                'taxonomy' => $taxonomies,
                'orderby' => 'id',
                'order' => 'ASC',
                'hide_empty' => false,
                'fields' => 'all',
                'include' => $attributes
            ));
            if ($terms) {
                foreach ($terms as $term) {
                    $parent = get_taxonomy($term->taxonomy)->label;
                    $term->formatted_name = $parent . ': ' . $term->name;
                    $result[$term->term_id] = $term->formatted_name;
                }
            }
        }
        return $result;
    }

}