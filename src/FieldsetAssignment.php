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

        // These values are request-local, but a single request may switch sites
        // in multisite. Invalidate them when the assignment source changes.
        add_action('save_post_' . FieldsetPostType::POST_TYPE, array(__CLASS__, 'invalidate_cache'), 100);
        add_action('before_delete_post', array(__CLASS__, 'invalidate_deleted_fieldset_cache'));
        add_action('trashed_post', array(__CLASS__, 'invalidate_cache'));
        add_action('untrashed_post', array(__CLASS__, 'invalidate_cache'));
        add_action('set_object_terms', array(__CLASS__, 'invalidate_product_assignment_cache'));
    }

    /** Clear this site's request-local assignment results after fieldset changes. */
    public static function invalidate_cache()
    {
        Cache::forget_prefix(self::cache_prefix());
    }

    /** Clear assignment cache only when a fieldset was deleted. */
    public static function invalidate_deleted_fieldset_cache($post_id)
    {
        if (get_post_type($post_id) === FieldsetPostType::POST_TYPE) {
            self::invalidate_cache();
        }
    }

    /** Clear product matches after category, tag, or attribute assignment changes. */
    public static function invalidate_product_assignment_cache($object_id, $terms, $tt_ids, $taxonomy)
    {
        if (is_string($taxonomy) && (strpos($taxonomy, 'product_') === 0 || strpos($taxonomy, 'pa_') === 0)) {
            self::invalidate_cache();
        }
    }

    /** Prefix all assignment cache keys with the active site ID. */
    private static function cache_prefix()
    {
        $blog_id = function_exists('get_current_blog_id') ? (int) get_current_blog_id() : 0;
        return 'FieldsetAssignment_' . $blog_id . '_';
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
        $prefix = self::cache_prefix();
        $cache_key = $prefix . 'match_' . $product_id;
        if (Cache::has($cache_key)) {
            return Cache::get($cache_key);
        }

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
        $taxonomies_key = $prefix . 'taxonomies';
        $taxonomies = Cache::get($taxonomies_key);
        if (!Cache::has($taxonomies_key)) {
            $taxonomies = get_taxonomies(array('object_type' => array('product')), 'objects');
            foreach ($taxonomies as $name => $taxonomy) {
                if (strpos($name, 'pa_') !== 0) {
                    unset($taxonomies[$name]);
                }
            }
            Cache::store($taxonomies_key, $taxonomies);
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
        $posts_key = $prefix . 'all_fieldsets';
        $posts = Cache::get($posts_key);
        if (!Cache::has($posts_key)) {
            $posts = get_posts(array(
                'post_type' => FieldsetPostType::POST_TYPE,
                'numberposts' => -1,
                'post_status' => 'publish'
            ));
            Cache::store($posts_key, $posts);
        }

        $matching = null;
        $max_priority = -INF;

        foreach ($posts as $post) {
            $assign_key = $prefix . 'fieldset_assign_' . $post->ID;
            $assign = Cache::get($assign_key);
            if (!Cache::has($assign_key)) {
                $assign = array(
                    'type' => get_post_meta($post->ID, '_wck_assign_type', true),
                    'products' => (array)get_post_meta($post->ID, '_wck_assign_products', true),
                    'categories' => (array)get_post_meta($post->ID, '_wck_assign_categories', true),
                    'tags' => (array)get_post_meta($post->ID, '_wck_assign_tags', true),
                    'attributes' => (array)get_post_meta($post->ID, '_wck_assign_attributes', true),
                    'priority' => get_post_meta($post->ID, '_wck_assign_priority', true)
                );
                Cache::store($assign_key, $assign);
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

        Cache::store($cache_key, $matching);
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
