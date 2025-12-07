<?php
/**
 * Product Filters Class
 * 
 * Handles custom filtering logic for WooCommerce products.
 * 
 * @package E-Shop Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

class Eshop_Product_Filters {

    /**
     * Initialize hooks
     */
    public static function init() {
        add_action('pre_get_posts', array(__CLASS__, 'handle_custom_filters'), 5);
    }

    /**
     * Handle custom filter parameters for WooCommerce
     * Priority 5 to run early, before our products_per_page override
     */
    public static function handle_custom_filters($query) {
        if (!is_admin() && $query->is_main_query() && (is_shop() || is_product_category() || is_product_tag())) {

            // Price filter
            if (isset($_GET['min_price']) && !empty($_GET['min_price'])) {
                $query->set('meta_query', array_merge(
                    $query->get('meta_query', array()),
                    array(
                        array(
                            'key' => '_price',
                            'value' => floatval($_GET['min_price']),
                            'compare' => '>=',
                            'type' => 'NUMERIC'
                        )
                    )
                ));
            }

            if (isset($_GET['max_price']) && !empty($_GET['max_price'])) {
                $query->set('meta_query', array_merge(
                    $query->get('meta_query', array()),
                    array(
                        array(
                            'key' => '_price',
                            'value' => floatval($_GET['max_price']),
                            'compare' => '<=',
                            'type' => 'NUMERIC'
                        )
                    )
                ));
            }

            // Category filter
            if (isset($_GET['product_cat']) && !empty($_GET['product_cat'])) {
                $raw = sanitize_text_field(wp_unslash($_GET['product_cat']));
                $tokens = array_filter(array_map('trim', explode(',', $raw)));
                $all_numeric = !empty($tokens) && count(array_filter($tokens, 'is_numeric')) === count($tokens);
                $terms = $all_numeric ? array_map('intval', $tokens) : array_map('sanitize_text_field', $tokens);
                // Support multi-select: build OR relation for product_cat then AND with other taxonomies later
                $cat_clause = array(
                    'taxonomy' => 'product_cat',
                    'field' => $all_numeric ? 'term_id' : 'slug',
                    'terms' => $terms,
                    'operator' => 'IN',
                    'include_children' => true
                );
                $tax_query = $query->get('tax_query', array());
                $tax_query[] = $cat_clause;
                $query->set('tax_query', $tax_query);
            }

            // On sale filter
            if (isset($_GET['on_sale']) && $_GET['on_sale'] === '1') {
                $query->set('post__in', wc_get_product_ids_on_sale());
            }

            // Custom product attribute filters
            $your_attributes = array('pa_box', 'pa_color', 'pa_pick-pattern', 'pa_select-size', 'pa_size-selection');
            $tax_query = $query->get('tax_query', array());

            foreach ($your_attributes as $attribute) {
                if (isset($_GET[$attribute]) && !empty($_GET[$attribute])) {
                    $terms = explode(',', sanitize_text_field($_GET[$attribute]));
                    $tax_query[] = array(
                        'taxonomy' => $attribute,
                        'field' => 'slug',
                        'terms' => $terms,
                        'operator' => 'IN'
                    );
                }
            }

            if (!empty($tax_query)) {
                // If multiple clauses present, ensure relation AND
                if (!isset($tax_query['relation'])) {
                    $tax_query['relation'] = 'AND';
                }
                $query->set('tax_query', $tax_query);
            }
        }
    }

    /**
     * Get available attribute terms from current query results
     * This gets terms only from products that match the current context
     * AND have in-stock variations for that attribute term
     */
    public static function get_available_attribute_terms($taxonomy) {
        // First, get the product IDs that match the current context
        $product_ids = self::get_current_context_product_ids();

        if (empty($product_ids)) {
            return array();
        }

        global $wpdb;

        // Create placeholders for the product IDs
        $placeholders = implode(',', array_fill(0, count($product_ids), '%d'));

        // Query to get attribute terms only from in-stock variations or simple products
        // This query:
        // 1. Gets terms from simple products that are in stock
        // 2. Gets terms from variations that are in stock (for variable products)
        $sql = "
            SELECT DISTINCT t.term_id, t.name, t.slug, COUNT(DISTINCT counted_product.ID) as count
            FROM {$wpdb->terms} t
            INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id AND tt.taxonomy = %s
            INNER JOIN {$wpdb->term_relationships} tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
            INNER JOIN (
                -- Simple products in stock
                SELECT p.ID, p.ID as countable_id
                FROM {$wpdb->posts} p
                INNER JOIN {$wpdb->postmeta} pm_stock ON p.ID = pm_stock.post_id 
                    AND pm_stock.meta_key = '_stock_status' 
                    AND pm_stock.meta_value = 'instock'
                WHERE p.post_type = 'product' 
                    AND p.post_status = 'publish'
                    AND p.ID IN ($placeholders)
                    AND p.ID NOT IN (
                        SELECT DISTINCT post_parent FROM {$wpdb->posts} WHERE post_type = 'product_variation'
                    )
                
                UNION ALL
                
                -- Variations in stock (count parent product once per attribute term)
                SELECT DISTINCT v.post_parent as ID, v.post_parent as countable_id
                FROM {$wpdb->posts} v
                INNER JOIN {$wpdb->postmeta} pm_var_stock ON v.ID = pm_var_stock.post_id 
                    AND pm_var_stock.meta_key = '_stock_status' 
                    AND pm_var_stock.meta_value = 'instock'
                INNER JOIN {$wpdb->term_relationships} tr_var ON v.ID = tr_var.object_id
                INNER JOIN {$wpdb->term_taxonomy} tt_var ON tr_var.term_taxonomy_id = tt_var.term_taxonomy_id 
                    AND tt_var.taxonomy = %s
                WHERE v.post_type = 'product_variation'
                    AND v.post_status = 'publish'
                    AND v.post_parent IN ($placeholders)
            ) as counted_product ON tr.object_id = counted_product.ID OR tr.object_id IN (
                SELECT v2.ID FROM {$wpdb->posts} v2 
                WHERE v2.post_type = 'product_variation' 
                AND v2.post_parent = counted_product.ID
            )
            GROUP BY t.term_id, t.name, t.slug
            HAVING count > 0
            ORDER BY t.name ASC
        ";

        // Build query params: taxonomy, product_ids (simple), taxonomy again (for variations), product_ids (variations)
        $query_params = array_merge(
            array($taxonomy), 
            $product_ids, 
            array($taxonomy), 
            $product_ids
        );
        
        $results = $wpdb->get_results($wpdb->prepare($sql, $query_params), ARRAY_A);

        // Add color values for color attributes
        if ($taxonomy === 'pa_color' && !empty($results)) {
            foreach ($results as &$result) {
                $result['color'] = get_term_meta($result['term_id'], 'color', true);
            }
        }

        return $results ? $results : array();
    }

    /**
     * Get available categories from current query results
     */
    public static function get_available_categories() {
        // If we're on a category page, don't show category filter (it's redundant)
        if (is_product_category()) {
            return array();
        }

        // Get product IDs that match current context
        $product_ids = self::get_current_context_product_ids();

        if (empty($product_ids)) {
            return array();
        }

        global $wpdb;

        // Create placeholders for the product IDs
        $placeholders = implode(',', array_fill(0, count($product_ids), '%d'));

        // Query to get categories only from the current product set
        $sql = "
            SELECT DISTINCT t.term_id, t.name, t.slug, COUNT(DISTINCT tr.object_id) as count
            FROM {$wpdb->term_relationships} tr
            INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id AND tt.taxonomy = 'product_cat'
            INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
            WHERE tr.object_id IN ($placeholders)
            GROUP BY t.term_id, t.name, t.slug
            HAVING count > 0
            ORDER BY t.name ASC
        ";

        $results = $wpdb->get_results($wpdb->prepare($sql, $product_ids), ARRAY_A);

        return $results ? $results : array();
    }

    /**
     * Get product IDs that match the current context (category, filters, etc.)
     */
    /**
     * Get product IDs that match the current context (category, filters, etc.)
     */
    public static function get_current_context_product_ids() {
        global $wpdb;

        // Start with basic product query
        $where_clauses = array("p.post_status = 'publish'", "p.post_type = 'product'");
        $join_clauses = array();

        // Only consider products that are currently in stock
        $join_clauses[] = "INNER JOIN {$wpdb->postmeta} pm_stock ON p.ID = pm_stock.post_id AND pm_stock.meta_key = '_stock_status'";
        $where_clauses[] = "pm_stock.meta_value = 'instock'";

        // Add current category context (including child categories)
        if (is_product_category()) {
            $current_category = get_queried_object();
            if ($current_category && isset($current_category->term_id)) {
                // Get all child categories of the current category
                $child_categories = get_terms(array(
                    'taxonomy' => 'product_cat',
                    'child_of' => $current_category->term_id,
                    'hide_empty' => true,
                    'fields' => 'ids'
                ));
                
                // Include current category and all its children
                $category_ids = array($current_category->term_id);
                if (!empty($child_categories) && !is_wp_error($child_categories)) {
                    $category_ids = array_merge($category_ids, $child_categories);
                }
                
                // Create placeholders for the category IDs
                $category_placeholders = implode(',', array_fill(0, count($category_ids), '%d'));
                
                $join_clauses[] = "INNER JOIN {$wpdb->term_relationships} tr_cat ON p.ID = tr_cat.object_id";
                $join_clauses[] = "INNER JOIN {$wpdb->term_taxonomy} tt_cat ON tr_cat.term_taxonomy_id = tt_cat.term_taxonomy_id AND tt_cat.taxonomy = 'product_cat'";
                $where_clauses[] = $wpdb->prepare("tt_cat.term_id IN ($category_placeholders)", $category_ids);
            }
        }

        // Add price filter if set
        if (isset($_GET['min_price']) && !empty($_GET['min_price'])) {
            $join_clauses[] = "LEFT JOIN {$wpdb->postmeta} pm_price ON p.ID = pm_price.post_id AND pm_price.meta_key = '_price'";
            $where_clauses[] = $wpdb->prepare("CAST(pm_price.meta_value AS DECIMAL(10,2)) >= %f", floatval($_GET['min_price']));
        }

        if (isset($_GET['max_price']) && !empty($_GET['max_price'])) {
            if (!in_array("LEFT JOIN {$wpdb->postmeta} pm_price ON p.ID = pm_price.post_id AND pm_price.meta_key = '_price'", $join_clauses)) {
                $join_clauses[] = "LEFT JOIN {$wpdb->postmeta} pm_price ON p.ID = pm_price.post_id AND pm_price.meta_key = '_price'";
            }
            $where_clauses[] = $wpdb->prepare("CAST(pm_price.meta_value AS DECIMAL(10,2)) <= %f", floatval($_GET['max_price']));
        }

        // Add category filter from URL if set (but not if we're already on a category page)
        if (!is_product_category() && isset($_GET['product_cat']) && !empty($_GET['product_cat'])) {
            $categories = explode(',', sanitize_text_field($_GET['product_cat']));
            $category_placeholders = implode(',', array_fill(0, count($categories), '%s'));
            $join_clauses[] = "LEFT JOIN {$wpdb->term_relationships} tr_url_cat ON p.ID = tr_url_cat.object_id";
            $join_clauses[] = "LEFT JOIN {$wpdb->term_taxonomy} tt_url_cat ON tr_url_cat.term_taxonomy_id = tt_url_cat.term_taxonomy_id AND tt_url_cat.taxonomy = 'product_cat'";
            $join_clauses[] = "LEFT JOIN {$wpdb->terms} t_url_cat ON tt_url_cat.term_id = t_url_cat.term_id";
            $where_clauses[] = $wpdb->prepare("t_url_cat.slug IN ($category_placeholders)", $categories);
        }

        // Add on sale filter if set
        if (isset($_GET['on_sale']) && $_GET['on_sale'] === '1') {
            $sale_ids = wc_get_product_ids_on_sale();
            if (!empty($sale_ids)) {
                $sale_placeholders = implode(',', array_fill(0, count($sale_ids), '%d'));
                $where_clauses[] = $wpdb->prepare("p.ID IN ($sale_placeholders)", $sale_ids);
            } else {
                return array(); // No sale products
            }
        }

        // Add attribute filters
        $your_attributes = array('pa_box', 'pa_color', 'pa_pick-pattern', 'pa_select-size', 'pa_size-selection');
        $attr_join_count = 0;

        foreach ($your_attributes as $attr_taxonomy) {
            if (isset($_GET[$attr_taxonomy]) && !empty($_GET[$attr_taxonomy])) {
                $attr_terms = explode(',', sanitize_text_field($_GET[$attr_taxonomy]));
                $attr_placeholders = implode(',', array_fill(0, count($attr_terms), '%s'));
                $attr_join_count++;

                $join_clauses[] = "INNER JOIN {$wpdb->term_relationships} tr_attr{$attr_join_count} ON p.ID = tr_attr{$attr_join_count}.object_id";
                $join_clauses[] = "INNER JOIN {$wpdb->term_taxonomy} tt_attr{$attr_join_count} ON tr_attr{$attr_join_count}.term_taxonomy_id = tt_attr{$attr_join_count}.term_taxonomy_id AND tt_attr{$attr_join_count}.taxonomy = '{$attr_taxonomy}'";
                $join_clauses[] = "INNER JOIN {$wpdb->terms} t_attr{$attr_join_count} ON tt_attr{$attr_join_count}.term_id = t_attr{$attr_join_count}.term_id";
                $where_clauses[] = $wpdb->prepare("t_attr{$attr_join_count}.slug IN ($attr_placeholders)", $attr_terms);
            }
        }

        // Build and execute the query to get product IDs
        $joins = !empty($join_clauses) ? implode(' ', array_unique($join_clauses)) : '';
        $where = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

        $sql = "
            SELECT DISTINCT p.ID
            FROM {$wpdb->posts} p
            {$joins}
            {$where}
        ";

        $product_ids = $wpdb->get_col($sql);

        return $product_ids ? array_map('intval', $product_ids) : array();
    }
}

// Initialize the class
Eshop_Product_Filters::init();

/**
 * Wrapper functions for backward compatibility
 */
function eshop_get_available_attribute_terms($taxonomy) {
    return Eshop_Product_Filters::get_available_attribute_terms($taxonomy);
}

function eshop_get_available_categories() {
    return Eshop_Product_Filters::get_available_categories();
}

function eshop_get_current_context_product_ids() {
    return Eshop_Product_Filters::get_current_context_product_ids(); 
}
