<?php
/**
 * Product Filters Class
 * 
 * Handles custom filtering logic for WooCommerce products.
 * Only shows and counts products/variations that are actually in stock.
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
     * Attribute taxonomies that represent size (aliases across catalog data).
     */
    public static function get_size_attribute_aliases() {
        return array('pa_size', 'pa_select-size', 'pa_size-selection');
    }

    /**
     * Parse attribute filters from the request, supporting any pa_* taxonomy.
     */
    public static function get_attribute_filters_from_request($request) {
        if (empty($request) || !is_array($request)) {
            return array();
        }

        $filters = array();
        $size_aliases = self::get_size_attribute_aliases();
        $size_terms = array();

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
                if (in_array($key, $size_aliases, true)) {
                    $size_terms = array_merge($size_terms, $terms);
                } else {
                    $filters[$key] = $terms;
                }
            }
        }

        if (!empty($size_terms)) {
            $filters['__size_alias'] = array_values(array_unique($size_terms));
        }

        return $filters;
    }

    /**
     * Handle custom filter parameters for WooCommerce
     * Priority 5 to run early, before our products_per_page override
     */
    public static function handle_custom_filters($query) {
        if (!is_admin() && $query->is_main_query() && (is_shop() || is_product_category() || is_product_tag())) {

            $attribute_filters = self::get_attribute_filters_from_request($_GET);

            // DEBUG: Temporarily output to see what's happening
            if (!empty($attribute_filters) && isset($_GET['filter_debug'])) {
                global $wpdb;
                echo '<pre style="background:#fff;padding:10px;border:2px solid red;position:fixed;top:0;left:0;z-index:99999;max-height:400px;overflow:auto;font-size:11px;">';
                echo "Attribute filters from request:\n";
                print_r($attribute_filters);
                
                $context_ids = self::get_base_context_product_ids();
                echo "\nContext product IDs (category/page): " . count($context_ids) . "\n";
                
                $ids = self::get_products_with_instock_variations($attribute_filters);
                echo "Products with in-stock variations: " . count($ids) . "\n";
                echo "IDs: " . implode(', ', array_slice($ids, 0, 10)) . (count($ids) > 10 ? '...' : '') . "\n";
                
                // Check a specific product to see its variations and stock
                if (!empty($ids)) {
                    $sample_id = $ids[0];
                    echo "\n--- Sample product ID: {$sample_id} ---\n";
                    
                    // Get all variations with their stock and size
                    $variations = $wpdb->get_results($wpdb->prepare("
                        SELECT 
                            v.ID,
                            pm_stock.meta_value as stock_status,
                            pm_size.meta_value as size_value
                        FROM {$wpdb->posts} v
                        LEFT JOIN {$wpdb->postmeta} pm_stock ON v.ID = pm_stock.post_id AND pm_stock.meta_key = '_stock_status'
                        LEFT JOIN {$wpdb->postmeta} pm_size ON v.ID = pm_size.post_id AND pm_size.meta_key LIKE 'attribute_pa_%%size%%'
                        WHERE v.post_parent = %d AND v.post_type = 'product_variation'
                    ", $sample_id), ARRAY_A);
                    
                    echo "Variations:\n";
                    foreach ($variations as $var) {
                        echo "  ID: {$var['ID']}, Size: {$var['size_value']}, Stock: {$var['stock_status']}\n";
                    }
                }
                
                echo '</pre>';
            }

            $meta_query = $query->get('meta_query', array());
            $tax_query = $query->get('tax_query', array());

            // Price filter
            if (isset($_GET['min_price']) && !empty($_GET['min_price'])) {
                $meta_query[] = array(
                    'key' => '_price',
                    'value' => floatval($_GET['min_price']),
                    'compare' => '>=',
                    'type' => 'NUMERIC'
                );
            }

            if (isset($_GET['max_price']) && !empty($_GET['max_price'])) {
                $meta_query[] = array(
                    'key' => '_price',
                    'value' => floatval($_GET['max_price']),
                    'compare' => '<=',
                    'type' => 'NUMERIC'
                );
            }

            if (!empty($meta_query)) {
                $query->set('meta_query', $meta_query);
            }

            // Category filter
            if (isset($_GET['product_cat']) && !empty($_GET['product_cat'])) {
                $raw = sanitize_text_field(wp_unslash($_GET['product_cat']));
                $tokens = array_filter(array_map('trim', explode(',', $raw)));
                $all_numeric = !empty($tokens) && count(array_filter($tokens, 'is_numeric')) === count($tokens);
                $terms = $all_numeric ? array_map('intval', $tokens) : array_map('sanitize_text_field', $tokens);
                
                $tax_query[] = array(
                    'taxonomy' => 'product_cat',
                    'field' => $all_numeric ? 'term_id' : 'slug',
                    'terms' => $terms,
                    'operator' => 'IN',
                    'include_children' => true
                );
            }

            // On sale filter
            if (isset($_GET['on_sale']) && $_GET['on_sale'] === '1') {
                $sale_ids = wc_get_product_ids_on_sale();
                if (!empty($sale_ids)) {
                    $existing_post_in = $query->get('post__in');
                    if (!empty($existing_post_in)) {
                        $intersected = array_values(array_intersect($existing_post_in, $sale_ids));
                        $query->set('post__in', !empty($intersected) ? $intersected : array(-1));
                    } else {
                        $query->set('post__in', $sale_ids);
                    }
                } else {
                    $query->set('post__in', array(-1));
                }
            }

            // If we have attribute filters, get only products with IN-STOCK variations matching those filters
            if (!empty($attribute_filters)) {
                $in_stock_product_ids = self::get_products_with_instock_variations($attribute_filters);

                if (!empty($in_stock_product_ids)) {
                    $existing_post_in = $query->get('post__in');
                    if (!empty($existing_post_in)) {
                        $intersected = array_values(array_intersect($existing_post_in, $in_stock_product_ids));
                        $query->set('post__in', !empty($intersected) ? $intersected : array(-1));
                    } else {
                        $query->set('post__in', $in_stock_product_ids);
                    }
                    
                    // Debug: Show what post__in was set to
                    if (isset($_GET['filter_debug'])) {
                        $final_post_in = $query->get('post__in');
                        echo '<pre style="background:#efe;padding:10px;border:2px solid green;position:fixed;top:750px;left:0;z-index:99997;max-height:150px;overflow:auto;font-size:10px;">';
                        echo "Final post__in set: " . count($final_post_in) . " products\n";
                        echo "IDs: " . implode(', ', array_slice($final_post_in, 0, 10)) . "...\n";
                        echo '</pre>';
                    }
                } else {
                    // No products with in-stock variations match
                    $query->set('post__in', array(-1));
                }
            }

            if (!empty($tax_query)) {
                if (count($tax_query) > 1 && !isset($tax_query['relation'])) {
                    $tax_query['relation'] = 'AND';
                }
                $query->set('tax_query', $tax_query);
            }
        }
    }

    /**
     * Get product IDs that have IN-STOCK variations matching ALL the given attribute filters.
     * Each filter must be satisfied by the SAME variation that is in stock.
     * 
     * For example: If filtering by color=red AND size=38, we only return products
     * that have a variation with BOTH color=red AND size=38 AND that variation is in stock.
     *
     * @param array $attribute_filters Array of taxonomy => terms filters
     * @param array|null $context_product_ids Optional. Limit to these product IDs. If null, uses current page context.
     * @return array Product IDs
     */
    public static function get_products_with_instock_variations($attribute_filters, $context_product_ids = null) {
        if (empty($attribute_filters) || !is_array($attribute_filters)) {
            return array();
        }

        global $wpdb;

        // Get context product IDs if not provided (from current page context like category)
        if ($context_product_ids === null) {
            $context_product_ids = self::get_base_context_product_ids();
        }
        
        // If context is empty, no products to filter
        if (empty($context_product_ids)) {
            return array();
        }

        // Sanitize and prepare filters
        $sanitized_filters = array();
        foreach ($attribute_filters as $taxonomy => $terms) {
            $clean_terms = array_filter(array_map('sanitize_text_field', (array) $terms));
            if (!empty($clean_terms)) {
                $sanitized_filters[$taxonomy] = $clean_terms;
            }
        }

        if (empty($sanitized_filters)) {
            return array();
        }

        // Handle size aliases
        $size_alias_key = '__size_alias';
        $size_aliases = self::get_size_attribute_aliases();
        $size_terms = isset($sanitized_filters[$size_alias_key]) ? $sanitized_filters[$size_alias_key] : array();
        unset($sanitized_filters[$size_alias_key]);

        // Build query for variable products via their variations
        // The key: SAME variation must satisfy ALL attribute filters AND be in stock
        $variation_joins = array();
        $variation_where = array(
            "v.post_type = 'product_variation'",
            "v.post_status = 'publish'",
            "p.post_type = 'product'",
            "p.post_status = 'publish'"
        );
        
        // Separate params for JOINs and WHERE to maintain correct order
        $join_params = array();
        $where_params = array();

        // Limit to context products (e.g., current category)
        $context_placeholders = implode(',', array_fill(0, count($context_product_ids), '%d'));
        $variation_where[] = "v.post_parent IN ({$context_placeholders})";
        $where_params = array_merge($where_params, $context_product_ids);

        // Join to parent product
        $variation_joins[] = "INNER JOIN {$wpdb->posts} p ON v.post_parent = p.ID";

        // Join to stock status - must be in stock
        $variation_joins[] = "INNER JOIN {$wpdb->postmeta} pm_stock ON v.ID = pm_stock.post_id AND pm_stock.meta_key = '_stock_status'";
        $variation_where[] = "pm_stock.meta_value = 'instock'";

        // Add joins for each attribute filter - all must match on the SAME variation
        $attr_index = 0;
        foreach ($sanitized_filters as $taxonomy => $terms) {
            $attr_index++;
            $meta_key = 'attribute_' . $taxonomy;
            
            $variation_joins[] = "INNER JOIN {$wpdb->postmeta} pm_attr{$attr_index} ON v.ID = pm_attr{$attr_index}.post_id AND pm_attr{$attr_index}.meta_key = %s";
            $join_params[] = $meta_key;
            
            $placeholders = implode(',', array_fill(0, count($terms), '%s'));
            $variation_where[] = "pm_attr{$attr_index}.meta_value IN ({$placeholders})";
            $where_params = array_merge($where_params, $terms);
        }

        // Handle size aliases - check any of the size attribute meta keys
        if (!empty($size_terms)) {
            $attr_index++;
            $size_meta_conditions = array();
            
            foreach ($size_aliases as $alias) {
                $meta_key = 'attribute_' . $alias;
                $size_meta_conditions[] = "pm_attr{$attr_index}.meta_key = %s";
                $join_params[] = $meta_key;
            }
            
            $variation_joins[] = "INNER JOIN {$wpdb->postmeta} pm_attr{$attr_index} ON v.ID = pm_attr{$attr_index}.post_id AND (" . implode(' OR ', $size_meta_conditions) . ")";
            
            $placeholders = implode(',', array_fill(0, count($size_terms), '%s'));
            $variation_where[] = "pm_attr{$attr_index}.meta_value IN ({$placeholders})";
            $where_params = array_merge($where_params, $size_terms);
        }

        // Execute query for variable products
        $variable_product_ids = array();
        if ($attr_index > 0) {
            $variation_sql = "
                SELECT DISTINCT v.post_parent
                FROM {$wpdb->posts} v
                " . implode("\n", $variation_joins) . "
                WHERE " . implode("\n AND ", $variation_where);

            // Combine params in correct order: JOINs first, then WHERE
            $all_params = array_merge($join_params, $where_params);

            // Debug: output the SQL
            if (isset($_GET['filter_debug'])) {
                echo '<pre style="background:#ffe;padding:10px;border:2px solid orange;position:fixed;top:420px;left:0;z-index:99998;max-height:300px;overflow:auto;font-size:10px;">';
                echo "SQL Query:\n" . $variation_sql . "\n\n";
                echo "Params:\n";
                print_r($all_params);
                echo '</pre>';
            }

            $variable_product_ids = $wpdb->get_col($wpdb->prepare($variation_sql, $all_params));
        }

        // Also check simple products that have these attribute terms and are in stock
        $simple_product_ids = self::get_simple_products_with_attributes($sanitized_filters, $size_terms, $size_aliases, $context_product_ids);

        return array_values(array_unique(array_merge(
            $variable_product_ids ? $variable_product_ids : array(),
            $simple_product_ids ? $simple_product_ids : array()
        )));
    }

    /**
     * Get simple products that have the given attribute terms and are in stock.
     *
     * @param array $filters Taxonomy => terms filters
     * @param array $size_terms Size terms to check
     * @param array $size_aliases Size taxonomy aliases
     * @param array $context_product_ids Product IDs to limit search to
     * @return array Product IDs
     */
    private static function get_simple_products_with_attributes($filters, $size_terms, $size_aliases, $context_product_ids = array()) {
        if (empty($filters) && empty($size_terms)) {
            return array();
        }
        
        if (empty($context_product_ids)) {
            return array();
        }

        global $wpdb;

        $joins = array();
        $where = array(
            "p.post_type = 'product'",
            "p.post_status = 'publish'"
        );
        $params = array();

        // Limit to context products
        $context_placeholders = implode(',', array_fill(0, count($context_product_ids), '%d'));
        $where[] = "p.ID IN ({$context_placeholders})";
        $params = array_merge($params, $context_product_ids);

        // Must be in stock
        $joins[] = "INNER JOIN {$wpdb->postmeta} pm_stock ON p.ID = pm_stock.post_id AND pm_stock.meta_key = '_stock_status'";
        $where[] = "pm_stock.meta_value = 'instock'";

        // Must NOT be a variable product (use NOT EXISTS for reliability)
        $where[] = "NOT EXISTS (
            SELECT 1 FROM {$wpdb->term_relationships} tr_type
            INNER JOIN {$wpdb->term_taxonomy} tt_type ON tr_type.term_taxonomy_id = tt_type.term_taxonomy_id
            INNER JOIN {$wpdb->terms} t_type ON tt_type.term_id = t_type.term_id
            WHERE tr_type.object_id = p.ID
            AND tt_type.taxonomy = 'product_type'
            AND t_type.slug = 'variable'
        )";

        // Add attribute term joins
        $attr_index = 0;
        foreach ($filters as $taxonomy => $terms) {
            $attr_index++;
            
            $joins[] = "INNER JOIN {$wpdb->term_relationships} tr_attr{$attr_index} ON p.ID = tr_attr{$attr_index}.object_id";
            $joins[] = "INNER JOIN {$wpdb->term_taxonomy} tt_attr{$attr_index} ON tr_attr{$attr_index}.term_taxonomy_id = tt_attr{$attr_index}.term_taxonomy_id AND tt_attr{$attr_index}.taxonomy = %s";
            $params[] = $taxonomy;
            $joins[] = "INNER JOIN {$wpdb->terms} t_attr{$attr_index} ON tt_attr{$attr_index}.term_id = t_attr{$attr_index}.term_id";
            
            $placeholders = implode(',', array_fill(0, count($terms), '%s'));
            $where[] = "t_attr{$attr_index}.slug IN ({$placeholders})";
            $params = array_merge($params, $terms);
        }

        // Handle size aliases for simple products
        if (!empty($size_terms)) {
            $attr_index++;
            
            $joins[] = "INNER JOIN {$wpdb->term_relationships} tr_attr{$attr_index} ON p.ID = tr_attr{$attr_index}.object_id";
            
            $tax_placeholders = implode(',', array_fill(0, count($size_aliases), '%s'));
            $joins[] = "INNER JOIN {$wpdb->term_taxonomy} tt_attr{$attr_index} ON tr_attr{$attr_index}.term_taxonomy_id = tt_attr{$attr_index}.term_taxonomy_id AND tt_attr{$attr_index}.taxonomy IN ({$tax_placeholders})";
            $params = array_merge($params, $size_aliases);
            $joins[] = "INNER JOIN {$wpdb->terms} t_attr{$attr_index} ON tt_attr{$attr_index}.term_id = t_attr{$attr_index}.term_id";
            
            $placeholders = implode(',', array_fill(0, count($size_terms), '%s'));
            $where[] = "t_attr{$attr_index}.slug IN ({$placeholders})";
            $params = array_merge($params, $size_terms);
        }

        if ($attr_index === 0) {
            return array();
        }

        $sql = "
            SELECT DISTINCT p.ID
            FROM {$wpdb->posts} p
            " . implode("\n", $joins) . "
            WHERE " . implode("\n AND ", $where);

        return $wpdb->get_col($wpdb->prepare($sql, $params));
    }

    /**
     * Get available attribute terms for the filter UI.
     * 
     * CRITICAL: Only returns terms that have AT LEAST ONE in-stock product/variation
     * within the current context (category, other active filters).
     * 
     * The count represents how many products have an IN-STOCK variation with that term.
     *
     * @param string $taxonomy The attribute taxonomy (e.g., 'pa_color', 'pa_select-size')
     * @return array Array of term data with term_id, name, slug, count, and optionally color
     */
    public static function get_available_attribute_terms($taxonomy) {
        global $wpdb;

        // Get base product IDs from current context (category page, other filters, etc.)
        $context_product_ids = self::get_base_context_product_ids();

        if (empty($context_product_ids)) {
            return array();
        }

        $product_placeholders = implode(',', array_fill(0, count($context_product_ids), '%d'));
        $attr_meta_key = 'attribute_' . $taxonomy;

        // Get terms from IN-STOCK variations of products in the current context
        // Each term is only counted once per parent product, even if multiple variations have it
        $sql = "
            SELECT 
                t.term_id,
                t.name,
                t.slug,
                COUNT(DISTINCT v.post_parent) as count
            FROM {$wpdb->posts} v
            INNER JOIN {$wpdb->posts} p ON v.post_parent = p.ID
            INNER JOIN {$wpdb->postmeta} pm_stock ON v.ID = pm_stock.post_id 
                AND pm_stock.meta_key = '_stock_status'
            INNER JOIN {$wpdb->postmeta} pm_attr ON v.ID = pm_attr.post_id 
                AND pm_attr.meta_key = %s
            INNER JOIN {$wpdb->terms} t ON t.slug = pm_attr.meta_value AND pm_attr.meta_value != ''
            INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id AND tt.taxonomy = %s
            WHERE v.post_type = 'product_variation'
                AND v.post_status = 'publish'
                AND p.post_type = 'product'
                AND p.post_status = 'publish'
                AND v.post_parent IN ({$product_placeholders})
                AND pm_stock.meta_value = 'instock'
            GROUP BY t.term_id, t.name, t.slug
            HAVING count > 0
            ORDER BY t.name ASC
        ";

        $params = array_merge(array($attr_meta_key, $taxonomy), $context_product_ids);
        $variation_results = $wpdb->get_results($wpdb->prepare($sql, $params), ARRAY_A);

        // Also get terms from simple products that are in stock
        // Use a subquery to properly identify simple products (not variable products)
        $simple_sql = "
            SELECT 
                t.term_id,
                t.name,
                t.slug,
                COUNT(DISTINCT p.ID) as count
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm_stock ON p.ID = pm_stock.post_id 
                AND pm_stock.meta_key = '_stock_status'
            INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
            INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id 
                AND tt.taxonomy = %s
            INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
            WHERE p.post_type = 'product'
                AND p.post_status = 'publish'
                AND p.ID IN ({$product_placeholders})
                AND pm_stock.meta_value = 'instock'
                AND NOT EXISTS (
                    SELECT 1 FROM {$wpdb->term_relationships} tr_type
                    INNER JOIN {$wpdb->term_taxonomy} tt_type ON tr_type.term_taxonomy_id = tt_type.term_taxonomy_id
                    INNER JOIN {$wpdb->terms} t_type ON tt_type.term_id = t_type.term_id
                    WHERE tr_type.object_id = p.ID
                    AND tt_type.taxonomy = 'product_type'
                    AND t_type.slug = 'variable'
                )
            GROUP BY t.term_id, t.name, t.slug
            HAVING count > 0
        ";

        $simple_params = array_merge(array($taxonomy), $context_product_ids);
        $simple_results = $wpdb->get_results($wpdb->prepare($simple_sql, $simple_params), ARRAY_A);

        // Merge results, combining counts for same terms
        $merged = array();
        foreach (array_merge($variation_results ?: array(), $simple_results ?: array()) as $row) {
            $term_id = $row['term_id'];
            if (isset($merged[$term_id])) {
                $merged[$term_id]['count'] += (int) $row['count'];
            } else {
                $merged[$term_id] = array(
                    'term_id' => $term_id,
                    'name' => $row['name'],
                    'slug' => $row['slug'],
                    'count' => (int) $row['count']
                );
            }
        }

        // Sort by name
        usort($merged, function($a, $b) {
            return strcasecmp($a['name'], $b['name']);
        });

        // Add color values for color attributes
        if ($taxonomy === 'pa_color' && !empty($merged)) {
            foreach ($merged as &$item) {
                $item['color'] = get_term_meta($item['term_id'], 'color', true);
            }
        }

        return array_values($merged);
    }

    /**
     * Get available categories from current query results.
     * Only counts products that are in stock.
     *
     * @return array Array of category data
     */
    public static function get_available_categories() {
        // If we're on a category page, don't show category filter (it's redundant)
        if (is_product_category()) {
            return array();
        }

        // Get product IDs that match current context and are in stock
        $product_ids = self::get_base_context_product_ids();

        if (empty($product_ids)) {
            return array();
        }

        global $wpdb;

        $placeholders = implode(',', array_fill(0, count($product_ids), '%d'));

        // Get categories from products that are in stock
        $sql = "
            SELECT DISTINCT 
                t.term_id, 
                t.name, 
                t.slug, 
                COUNT(DISTINCT p.ID) as count
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} pm_stock ON p.ID = pm_stock.post_id 
                AND pm_stock.meta_key = '_stock_status'
            INNER JOIN {$wpdb->term_relationships} tr ON p.ID = tr.object_id
            INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id 
                AND tt.taxonomy = 'product_cat'
            INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
            WHERE p.ID IN ({$placeholders})
                AND pm_stock.meta_value = 'instock'
            GROUP BY t.term_id, t.name, t.slug
            HAVING count > 0
            ORDER BY t.name ASC
        ";

        $results = $wpdb->get_results($wpdb->prepare($sql, $product_ids), ARRAY_A);

        return $results ?: array();
    }

    /**
     * Get base product IDs for the current context (category, search, etc.)
     * WITHOUT applying attribute filters. This is used to determine what
     * filter options should be available.
     *
     * @return array Product IDs
     */
    public static function get_base_context_product_ids() {
        global $wpdb;

        $where = array(
            "p.post_type = 'product'",
            "p.post_status = 'publish'"
        );
        $joins = array();
        $params = array();

        // If on a category page, limit to that category and its children
        if (is_product_category()) {
            $current_category = get_queried_object();
            if ($current_category && isset($current_category->term_id)) {
                // Get all child categories
                $child_categories = get_terms(array(
                    'taxonomy' => 'product_cat',
                    'child_of' => $current_category->term_id,
                    'hide_empty' => true,
                    'fields' => 'ids'
                ));

                $category_ids = array($current_category->term_id);
                if (!empty($child_categories) && !is_wp_error($child_categories)) {
                    $category_ids = array_merge($category_ids, $child_categories);
                }

                $cat_placeholders = implode(',', array_fill(0, count($category_ids), '%d'));
                $joins[] = "INNER JOIN {$wpdb->term_relationships} tr_cat ON p.ID = tr_cat.object_id";
                $joins[] = "INNER JOIN {$wpdb->term_taxonomy} tt_cat ON tr_cat.term_taxonomy_id = tt_cat.term_taxonomy_id AND tt_cat.taxonomy = 'product_cat'";
                $where[] = "tt_cat.term_id IN ({$cat_placeholders})";
                $params = array_merge($params, $category_ids);
            }
        }

        // Apply category filter from URL
        if (!is_product_category() && isset($_GET['product_cat']) && !empty($_GET['product_cat'])) {
            $categories = array_filter(array_map('sanitize_text_field', explode(',', $_GET['product_cat'])));
            if (!empty($categories)) {
                $cat_placeholders = implode(',', array_fill(0, count($categories), '%s'));
                $joins[] = "INNER JOIN {$wpdb->term_relationships} tr_url_cat ON p.ID = tr_url_cat.object_id";
                $joins[] = "INNER JOIN {$wpdb->term_taxonomy} tt_url_cat ON tr_url_cat.term_taxonomy_id = tt_url_cat.term_taxonomy_id AND tt_url_cat.taxonomy = 'product_cat'";
                $joins[] = "INNER JOIN {$wpdb->terms} t_url_cat ON tt_url_cat.term_id = t_url_cat.term_id";
                $where[] = "t_url_cat.slug IN ({$cat_placeholders})";
                $params = array_merge($params, $categories);
            }
        }

        // Apply price filters
        if (isset($_GET['min_price']) && !empty($_GET['min_price'])) {
            $joins[] = "INNER JOIN {$wpdb->postmeta} pm_price ON p.ID = pm_price.post_id AND pm_price.meta_key = '_price'";
            $where[] = "CAST(pm_price.meta_value AS DECIMAL(10,2)) >= %f";
            $params[] = floatval($_GET['min_price']);
        }

        if (isset($_GET['max_price']) && !empty($_GET['max_price'])) {
            if (!isset($_GET['min_price']) || empty($_GET['min_price'])) {
                $joins[] = "INNER JOIN {$wpdb->postmeta} pm_price ON p.ID = pm_price.post_id AND pm_price.meta_key = '_price'";
            }
            $where[] = "CAST(pm_price.meta_value AS DECIMAL(10,2)) <= %f";
            $params[] = floatval($_GET['max_price']);
        }

        // Apply on sale filter
        if (isset($_GET['on_sale']) && $_GET['on_sale'] === '1') {
            $sale_ids = wc_get_product_ids_on_sale();
            if (empty($sale_ids)) {
                return array();
            }
            $sale_placeholders = implode(',', array_fill(0, count($sale_ids), '%d'));
            $where[] = "p.ID IN ({$sale_placeholders})";
            $params = array_merge($params, $sale_ids);
        }

        // Build query
        $sql = "
            SELECT DISTINCT p.ID
            FROM {$wpdb->posts} p
            " . implode("\n", $joins) . "
            WHERE " . implode("\n AND ", $where);

        if (!empty($params)) {
            $product_ids = $wpdb->get_col($wpdb->prepare($sql, $params));
        } else {
            $product_ids = $wpdb->get_col($sql);
        }

        return $product_ids ? array_map('intval', $product_ids) : array();
    }

    /**
     * Get product IDs that match the current context including all filters.
     * Used for determining what products to display.
     *
     * @return array Product IDs
     */
    public static function get_current_context_product_ids() {
        // Start with base context
        $product_ids = self::get_base_context_product_ids();

        if (empty($product_ids)) {
            return array();
        }

        // Apply attribute filters
        $attribute_filters = self::get_attribute_filters_from_request($_GET);

        if (!empty($attribute_filters)) {
            $filtered_ids = self::get_products_with_instock_variations($attribute_filters);
            $product_ids = array_values(array_intersect($product_ids, $filtered_ids));
        }

        return $product_ids;
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
add_action('admin_init', function () {
    if (isset($_GET['sync_stock']) && $_GET['sync_stock'] === '1' && current_user_can('manage_woocommerce')) {
        $result = eshop_sync_variation_stock_status();
        wp_die('Stock sync complete. Updated: ' . $result['updated'] . ', Skipped: ' . $result['skipped']);
    }
});
