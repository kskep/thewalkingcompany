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
     * Get all product attribute taxonomies (pa_*) available for filtering.
     */
    public static function get_filterable_attribute_taxonomies() {
        static $cached = null;

        if (is_array($cached)) {
            return $cached;
        }

        $fallback = array('pa_box', 'pa_color', 'pa_pick-pattern', 'pa_select-size', 'pa_size-selection');
        if (!function_exists('wc_get_attribute_taxonomies')) {
            $cached = $fallback;
            return $cached;
        }

        $taxonomies = wc_get_attribute_taxonomies();
        if (empty($taxonomies)) {
            $cached = $fallback;
            return $cached;
        }

        $names = array();
        foreach ($taxonomies as $taxonomy) {
            if (empty($taxonomy->attribute_name)) {
                continue;
            }
            $names[] = 'pa_' . $taxonomy->attribute_name;
        }

        $cached = !empty($names) ? $names : $fallback;
        return $cached;
    }

    /**
     * Parse attribute filters from the request, supporting any pa_* taxonomy.
     */
    public static function get_attribute_filters_from_request($request) {
        if (empty($request) || !is_array($request)) {
            return array();
        }

        $filters = array();
        foreach ($request as $key => $value) {
            if (strpos($key, 'pa_') !== 0) {
                continue;
            }
            if (!taxonomy_exists($key)) {
                continue;
            }
            if (is_array($value)) {
                $terms = array_filter(array_map('wc_clean', $value));
            } else {
                $terms = array_filter(array_map('wc_clean', explode(',', wp_unslash($value))));
            }
            if (!empty($terms)) {
                $filters[$key] = $terms;
            }
        }

        return $filters;
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
            $tax_query = $query->get('tax_query', array());
            $attribute_filters = self::get_attribute_filters_from_request($_GET);
            foreach ($attribute_filters as $attribute => $terms) {
                $tax_query[] = array(
                    'taxonomy' => $attribute,
                    'field' => 'slug',
                    'terms' => $terms,
                    'operator' => 'IN'
                );
            }

            if (!empty($attribute_filters)) {
                $in_stock_ids = self::get_product_ids_for_attribute_filters($attribute_filters);

                if (!empty($in_stock_ids)) {
                    $existing_post_in = $query->get('post__in');
                    if (!empty($existing_post_in)) {
                        $intersected = array_values(array_intersect($existing_post_in, $in_stock_ids));
                        $query->set('post__in', !empty($intersected) ? $intersected : array(-1));
                    } else {
                        $query->set('post__in', $in_stock_ids);
                    }
                } else {
                    $query->set('post__in', array(-1));
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
     * Return product IDs that have in-stock variations (or simple products) matching the given attribute filters.
     * Each taxonomy in $attribute_filters must be matched by the same variation, ensuring accurate stock-aware filtering.
     */
    public static function get_product_ids_for_attribute_filters($attribute_filters) {
        if (empty($attribute_filters) || !is_array($attribute_filters)) {
            return array();
        }

        global $wpdb;

        // Sanitize filter values
        $sanitized_filters = array();
        foreach ($attribute_filters as $taxonomy => $terms) {
            $clean_terms = array_filter(array_map('wc_clean', (array) $terms));
            if (!empty($clean_terms)) {
                $sanitized_filters[$taxonomy] = $clean_terms;
            }
        }

        if (empty($sanitized_filters)) {
            return array();
        }

        // Match variable products via their variations (same variation must satisfy all attribute filters)
        // Trust WooCommerce's _stock_status as the source of truth for stock availability.
        // WooCommerce syncs this value when stock changes.
        $variation_joins = array(
            "INNER JOIN {$wpdb->postmeta} pm_stock ON v.ID = pm_stock.post_id AND pm_stock.meta_key = '_stock_status'",
            "INNER JOIN {$wpdb->posts} p ON v.post_parent = p.ID AND p.post_type = 'product' AND p.post_status = 'publish'"
        );
        $variation_where = array(
            "v.post_type = 'product_variation'",
            "v.post_status = 'publish'",
            "pm_stock.meta_value = 'instock'"
        );
        $variation_params = array();
        $variation_attr_count = 0;

        foreach ($sanitized_filters as $taxonomy => $terms) {
            $variation_attr_count++;
            $meta_key = 'attribute_' . $taxonomy;
            $variation_joins[] = "INNER JOIN {$wpdb->postmeta} pm_attr{$variation_attr_count} ON v.ID = pm_attr{$variation_attr_count}.post_id AND pm_attr{$variation_attr_count}.meta_key = '" . esc_sql($meta_key) . "'";
            $placeholders = implode(',', array_fill(0, count($terms), '%s'));
            $variation_where[] = "pm_attr{$variation_attr_count}.meta_value IN ({$placeholders})";
            $variation_params = array_merge($variation_params, $terms);
        }

        $variation_ids = array();
        if ($variation_attr_count > 0) {
            $variation_sql = "
                SELECT DISTINCT v.post_parent
                FROM {$wpdb->posts} v
                " . implode(' ', array_unique($variation_joins)) . "
                WHERE " . implode(' AND ', $variation_where) . "
            ";

            $variation_ids = $wpdb->get_col($wpdb->prepare($variation_sql, $variation_params));
        }

        // Match simple products that carry these attribute terms and are in stock
        // Match simple products that carry these attribute terms and are in stock
        // Trust WooCommerce's _stock_status as the source of truth
        $simple_joins = array(
            "INNER JOIN {$wpdb->postmeta} pm_stock ON p.ID = pm_stock.post_id AND pm_stock.meta_key = '_stock_status'"
        );
        $simple_where = array(
            "p.post_type = 'product'",
            "p.post_status = 'publish'",
            "pm_stock.meta_value = 'instock'"
        );
        $simple_params = array();
        $simple_attr_count = 0;

        foreach ($sanitized_filters as $taxonomy => $terms) {
            $simple_attr_count++;
            $simple_joins[] = "INNER JOIN {$wpdb->term_relationships} tr_attr{$simple_attr_count} ON p.ID = tr_attr{$simple_attr_count}.object_id";
            $simple_joins[] = "INNER JOIN {$wpdb->term_taxonomy} tt_attr{$simple_attr_count} ON tr_attr{$simple_attr_count}.term_taxonomy_id = tt_attr{$simple_attr_count}.term_taxonomy_id AND tt_attr{$simple_attr_count}.taxonomy = '" . esc_sql($taxonomy) . "'";
            $simple_joins[] = "INNER JOIN {$wpdb->terms} t_attr{$simple_attr_count} ON tt_attr{$simple_attr_count}.term_id = t_attr{$simple_attr_count}.term_id";
            $placeholders = implode(',', array_fill(0, count($terms), '%s'));
            $simple_where[] = "t_attr{$simple_attr_count}.slug IN ({$placeholders})";
            $simple_params = array_merge($simple_params, $terms);
        }

        $simple_ids = array();
        if ($simple_attr_count > 0) {
            $simple_sql = "
                SELECT DISTINCT p.ID
                FROM {$wpdb->posts} p
                " . implode(' ', array_unique($simple_joins)) . "
                WHERE " . implode(' AND ', $simple_where) . "
            ";

            $simple_ids = $wpdb->get_col($wpdb->prepare($simple_sql, $simple_params));
        }

        return array_values(array_unique(array_merge($variation_ids, $simple_ids)));
    }

    /**
     * Get available attribute terms from current query results
     * This gets terms only from products that match the current context
     * AND have in-stock variations for that attribute term
     * 
     * Relies on WooCommerce's _stock_status meta for stock availability.
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

        // Trust WooCommerce's _stock_status as the source of truth for stock availability
        $stock_condition = "pm_stock.meta_value = 'instock'";

        // Query to get attribute terms from products in current context
        // For simple products: use term_relationships
        // For variable products: use postmeta on variations
        $sql = "
            SELECT DISTINCT t.term_id, t.name, t.slug, COUNT(DISTINCT product_id) as count
            FROM (
                -- Simple products with this attribute term
                -- We exclude variable products here because their availability is determined 
                -- by their variations (see second part of UNION)
                SELECT p.ID as product_id, tr.term_taxonomy_id
                FROM {$wpdb->posts} p
                INNER JOIN {$wpdb->postmeta} pm_stock ON p.ID = pm_stock.post_id 
                    AND pm_stock.meta_key = '_stock_status'
                INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
                INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id 
                    AND tt.taxonomy = %s
                -- Join to check product type
                LEFT JOIN {$wpdb->term_relationships} tr_type ON p.ID = tr_type.object_id
                LEFT JOIN {$wpdb->term_taxonomy} tt_type ON tr_type.term_taxonomy_id = tt_type.term_taxonomy_id AND tt_type.taxonomy = 'product_type'
                LEFT JOIN {$wpdb->terms} t_type ON tt_type.term_id = t_type.term_id
                WHERE p.post_type = 'product' 
                    AND p.post_status = 'publish'
                    AND p.ID IN ($placeholders)
                    AND {$stock_condition}
                    AND (t_type.slug IS NULL OR t_type.slug != 'variable')
                
                UNION
                
                -- Variable product variations with this attribute term
                SELECT DISTINCT v.post_parent as product_id, tt2.term_taxonomy_id
                FROM {$wpdb->posts} v
                INNER JOIN {$wpdb->postmeta} pm_stock ON v.ID = pm_stock.post_id 
                    AND pm_stock.meta_key = '_stock_status'
                INNER JOIN {$wpdb->postmeta} pm_attr ON v.ID = pm_attr.post_id 
                    AND pm_attr.meta_key = %s
                INNER JOIN {$wpdb->terms} t2 ON t2.slug = pm_attr.meta_value
                INNER JOIN {$wpdb->term_taxonomy} tt2 ON t2.term_id = tt2.term_id 
                    AND tt2.taxonomy = %s
                WHERE v.post_type = 'product_variation' 
                    AND v.post_status = 'publish'
                    AND v.post_parent IN ($placeholders)
                    AND {$stock_condition}
            ) matched
            INNER JOIN {$wpdb->term_taxonomy} tt ON matched.term_taxonomy_id = tt.term_taxonomy_id
            INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
            GROUP BY t.term_id, t.name, t.slug
            HAVING count > 0
            ORDER BY t.name ASC
        ";

        // Prepare attribute meta key (e.g., 'attribute_pa_size')
        $attr_meta_key = 'attribute_' . $taxonomy;

        // Query params: taxonomy, product_ids, attr_meta_key, taxonomy, product_ids
        $query_params = array_merge(
            array($taxonomy), 
            $product_ids, 
            array($attr_meta_key, $taxonomy), 
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
        $attr_join_count = 0;
        $attribute_filters = self::get_attribute_filters_from_request($_GET);

        foreach ($attribute_filters as $attr_taxonomy => $attr_terms) {
            $attr_terms = array_filter(array_map('sanitize_text_field', (array) $attr_terms));
            if (empty($attr_terms)) {
                continue;
            }

            $attribute_filters[$attr_taxonomy] = $attr_terms;
            $attr_placeholders = implode(',', array_fill(0, count($attr_terms), '%s'));
            $attr_join_count++;

            $join_clauses[] = "INNER JOIN {$wpdb->term_relationships} tr_attr{$attr_join_count} ON p.ID = tr_attr{$attr_join_count}.object_id";
            $join_clauses[] = "INNER JOIN {$wpdb->term_taxonomy} tt_attr{$attr_join_count} ON tr_attr{$attr_join_count}.term_taxonomy_id = tt_attr{$attr_join_count}.term_taxonomy_id AND tt_attr{$attr_join_count}.taxonomy = '{$attr_taxonomy}'";
            $join_clauses[] = "INNER JOIN {$wpdb->terms} t_attr{$attr_join_count} ON tt_attr{$attr_join_count}.term_id = t_attr{$attr_join_count}.term_id";
            $where_clauses[] = $wpdb->prepare("t_attr{$attr_join_count}.slug IN ($attr_placeholders)", $attr_terms);
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

        if (!empty($attribute_filters)) {
            $stock_filtered_ids = self::get_product_ids_for_attribute_filters($attribute_filters);
            $product_ids = (!empty($product_ids) && !empty($stock_filtered_ids)) ? array_values(array_intersect($product_ids, $stock_filtered_ids)) : array();
        }

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

/**
 * Force WooCommerce to re-sync stock status for all product variations.
 * This fixes inconsistencies where _stock_status doesn't match actual stock quantity.
 * 
 * Run this via WP-CLI: wp eval "eshop_sync_variation_stock_status();"
 * Or add ?sync_stock=1 to any admin page (requires admin privileges)
 * 
 * @param int|null $product_id Optional. Sync only a specific product.
 * @return array Summary of synced variations
 */
function eshop_sync_variation_stock_status($product_id = null) {
    if (!function_exists('wc_get_products')) {
        return array('error' => 'WooCommerce not active');
    }
    
    $synced = array('updated' => 0, 'skipped' => 0);
    
    $args = array(
        'type' => 'variable',
        'limit' => -1,
        'return' => 'ids',
    );
    
    if ($product_id) {
        $args['include'] = array($product_id);
    }
    
    $product_ids = wc_get_products($args);
    
    foreach ($product_ids as $pid) {
        $product = wc_get_product($pid);
        if (!$product || !$product->is_type('variable')) {
            continue;
        }
        
        $variations = $product->get_children();
        foreach ($variations as $variation_id) {
            $variation = wc_get_product($variation_id);
            if (!$variation) {
                continue;
            }
            
            // Get current values
            $manages_stock = $variation->get_manage_stock();
            $stock_qty = $variation->get_stock_quantity();
            $current_status = $variation->get_stock_status();
            $backorders = $variation->get_backorders();
            
            // Determine correct status
            if ($manages_stock) {
                if ($stock_qty > 0 || $backorders !== 'no') {
                    $correct_status = 'instock';
                } else {
                    $correct_status = 'outofstock';
                }
            } else {
                // Not managing stock - inherit from parent or keep current
                $correct_status = $current_status;
            }
            
            // Update if needed
            if ($current_status !== $correct_status) {
                $variation->set_stock_status($correct_status);
                $variation->save();
                $synced['updated']++;
            } else {
                $synced['skipped']++;
            }
        }
        
        // Also trigger parent product sync
        wc_delete_product_transients($pid);
    }
    
    return $synced;
}

// Allow admin to trigger stock sync via URL parameter
add_action('admin_init', function() {
    if (isset($_GET['sync_stock']) && $_GET['sync_stock'] === '1' && current_user_can('manage_woocommerce')) {
        $result = eshop_sync_variation_stock_status();
        wp_die('Stock sync complete. Updated: ' . $result['updated'] . ', Skipped: ' . $result['skipped']);
    }
});
