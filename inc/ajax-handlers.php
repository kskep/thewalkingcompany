<?php
/**
 * AJAX Handlers
 * 
 * Handles all AJAX endpoints for the theme including cart operations and product filtering.
 * 
 * @package E-Shop Theme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Flying Cart AJAX Functions
 */

/**
 * Remove cart item via AJAX
 * 
 * @return void Sends JSON response
 */
function eshop_remove_cart_item_ajax() {
    // Verify nonce for security
    if (!check_ajax_referer('eshop_nonce', 'nonce', false)) {
        wp_send_json_error(array(
            'message' => __('Security check failed', 'eshop-theme')
        ));
    }

    // Check if WooCommerce is active
    if (!class_exists('WooCommerce')) {
        wp_send_json_error(array(
            'message' => __('WooCommerce is not active', 'eshop-theme')
        ));
    }

    $cart_item_key = sanitize_text_field($_POST['cart_item_key']);

    if (empty($cart_item_key)) {
        wp_send_json_error(array(
            'message' => __('Invalid cart item', 'eshop-theme')
        ));
    }

    if (WC()->cart->remove_cart_item($cart_item_key)) {
        WC()->cart->calculate_totals();

        wp_send_json_success(array(
            'message' => __('Item removed from cart', 'eshop-theme'),
            'fragments' => apply_filters('woocommerce_add_to_cart_fragments', array()),
            'cart_hash' => apply_filters('woocommerce_add_to_cart_hash', WC()->cart->get_cart_hash())
        ));
    } else {
        wp_send_json_error(array(
            'message' => __('Error removing item from cart', 'eshop-theme')
        ));
    }
}
add_action('wp_ajax_remove_cart_item', 'eshop_remove_cart_item_ajax');
add_action('wp_ajax_nopriv_remove_cart_item', 'eshop_remove_cart_item_ajax');

/**
 * Get flying cart content via AJAX
 * 
 * @return void Sends JSON response
 */
function eshop_get_flying_cart_content_ajax() {
    // Verify nonce for security
    if (!check_ajax_referer('eshop_nonce', 'nonce', false)) {
        wp_send_json_error(array(
            'message' => __('Security check failed', 'eshop-theme')
        ));
    }

    // Check if WooCommerce is active
    if (!class_exists('WooCommerce')) {
        wp_send_json_error(array(
            'message' => __('WooCommerce is not active', 'eshop-theme')
        ));
    }

    ob_start();
    get_template_part('template-parts/components/flying-cart');
    $html = ob_get_clean();

    wp_send_json_success(array(
        'html' => $html
    ));
}
add_action('wp_ajax_get_flying_cart_content', 'eshop_get_flying_cart_content_ajax');
add_action('wp_ajax_nopriv_get_flying_cart_content', 'eshop_get_flying_cart_content_ajax');

/**
 * Update cart item quantity via AJAX
 * 
 * @return void Sends JSON response
 */
function eshop_update_cart_quantity_ajax() {
    // Verify nonce for security
    if (!check_ajax_referer('eshop_nonce', 'nonce', false)) {
        wp_send_json_error(array(
            'message' => __('Security check failed', 'eshop-theme')
        ));
    }

    // Check if WooCommerce is active
    if (!class_exists('WooCommerce')) {
        wp_send_json_error(array(
            'message' => __('WooCommerce is not active', 'eshop-theme')
        ));
    }

    $cart_item_key = sanitize_text_field($_POST['cart_item_key']);
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

    if (empty($cart_item_key)) {
        wp_send_json_error(array(
            'message' => __('Invalid cart item', 'eshop-theme')
        ));
    }

    // If quantity is 0 or less, remove the item
    if ($quantity <= 0) {
        WC()->cart->remove_cart_item($cart_item_key);
    } else {
        WC()->cart->set_quantity($cart_item_key, $quantity, true);
    }

    WC()->cart->calculate_totals();

    // Get updated cart data
    $cart_count = WC()->cart->get_cart_contents_count();
    $cart_total = WC()->cart->get_cart_total();

    wp_send_json_success(array(
        'message' => __('Cart updated', 'eshop-theme'),
        'cart_count' => $cart_count,
        'cart_total' => $cart_total,
        'fragments' => apply_filters('woocommerce_add_to_cart_fragments', array()),
        'cart_hash' => apply_filters('woocommerce_add_to_cart_hash', WC()->cart->get_cart_hash())
    ));
}
add_action('wp_ajax_update_cart_quantity', 'eshop_update_cart_quantity_ajax');
add_action('wp_ajax_nopriv_update_cart_quantity', 'eshop_update_cart_quantity_ajax');

/**
 * Update gift wrap quantity via AJAX (checkout).
 *
 * @return void Sends JSON response
 */
function eshop_set_gift_wrap_qty_ajax() {
    if (!check_ajax_referer('eshop_nonce', 'nonce', false)) {
        wp_send_json_error(array(
            'message' => __('Security check failed', 'eshop-theme')
        ));
    }

    if (!class_exists('WooCommerce')) {
        wp_send_json_error(array(
            'message' => __('WooCommerce is not active', 'eshop-theme')
        ));
    }

    if (!WC()->session) {
        wp_send_json_error(array(
            'message' => __('Session not available', 'eshop-theme')
        ));
    }

    $gift_wrap_qty = isset($_POST['gift_wrap_qty']) ? absint($_POST['gift_wrap_qty']) : 0;
    $gift_extra_bag_qty = isset($_POST['gift_extra_bag_qty']) ? absint($_POST['gift_extra_bag_qty']) : 0;

    WC()->session->set('eshop_gift_wrap_qty', $gift_wrap_qty);
    WC()->session->set('eshop_gift_extra_bag_qty', $gift_extra_bag_qty);

    if (WC()->cart) {
        WC()->cart->calculate_totals();
    }

    wp_send_json_success(array(
        'message' => __('Gift wrap updated', 'eshop-theme')
    ));
}
add_action('wp_ajax_set_gift_wrap_qty', 'eshop_set_gift_wrap_qty_ajax');
add_action('wp_ajax_nopriv_set_gift_wrap_qty', 'eshop_set_gift_wrap_qty_ajax');

/**
 * AJAX handler for product filtering
 * 
 * Note: AJAX filtering is currently disabled in favor of standard page navigation.
 * The actions are commented out but the function is preserved for potential future use.
 * 
 * @return void Sends JSON response
 */
function eshop_filter_products() {
    // Accept both JSON payloads and standard form-encoded POST
    $data = null;

    if (!empty($_POST)) {
        // Form-encoded request (preferred for WP admin-ajax)
        $data = array(
            'nonce'   => isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '',
            'filters' => isset($_POST['filters']) ? $_POST['filters'] : array(),
            'paged'   => isset($_POST['paged']) ? intval($_POST['paged']) : 1,
            'orderby' => isset($_POST['orderby']) ? sanitize_text_field(wp_unslash($_POST['orderby'])) : 'date',
        );
    } else {
        // JSON body fallback
        $raw_data = file_get_contents('php://input');
        $decoded = json_decode($raw_data, true);
        $data = is_array($decoded) ? $decoded : array();
    }

    // Verify nonce
    if (!isset($data['nonce']) || !wp_verify_nonce($data['nonce'], 'eshop_nonce')) {
        wp_send_json_error(array('message' => 'Invalid nonce'));
    }

    $filters = isset($data['filters']) && is_array($data['filters']) ? $data['filters'] : array();
    $paged = isset($data['paged']) ? intval($data['paged']) : 1;
    $orderby = isset($data['orderby']) ? sanitize_text_field($data['orderby']) : 'date';

    // Build WP_Query args
    // Force products per page for consistency across all categories
    // This must match the value set in functions.php
    $computed_per_page = function_exists('eshop_get_catalog_products_per_page')
        ? eshop_get_catalog_products_per_page()
        : (wp_is_mobile() ? 14 : 15);

    $args = array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => $computed_per_page,
        'paged' => $paged,
        'orderby' => $orderby,
        'meta_query' => array(
            // REMOVED: Global stock filter was breaking single product page stock display
            // Stock status should be handled by individual product queries and templates
        ),
        'tax_query' => array(),
    );

    // Respect current archive context if provided by client
    $context_tax = isset($_POST['context_taxonomy']) ? sanitize_text_field(wp_unslash($_POST['context_taxonomy'])) : (isset($data['context_taxonomy']) ? sanitize_text_field($data['context_taxonomy']) : '');
    $context_terms = array();
    if (isset($_POST['context_terms'])) {
        $context_terms = is_array($_POST['context_terms']) ? array_map('intval', $_POST['context_terms']) : array();
    } elseif (isset($data['context_terms'])) {
        $context_terms = is_array($data['context_terms']) ? array_map('intval', $data['context_terms']) : array();
    }
    
    // If user has selected specific categories via the filter, use those instead of context
    // This allows filtering down to child categories when on a parent category page
    $has_category_filter = !empty($filters['product_cat']);
    
    if ($context_tax && !empty($context_terms) && !$has_category_filter) {
        // No category filter applied, so use the archive context
        // If this is a product category, include all child categories
        $all_category_terms = array();
        if ($context_tax === 'product_cat') {
            foreach ($context_terms as $term_id) {
                $all_category_terms[] = $term_id;
                // Get child categories
                $child_categories = get_terms(array(
                    'taxonomy' => 'product_cat',
                    'child_of' => $term_id,
                    'hide_empty' => true,
                    'fields' => 'ids'
                ));
                if (!empty($child_categories) && !is_wp_error($child_categories)) {
                    $all_category_terms = array_merge($all_category_terms, $child_categories);
                }
            }
        } else {
            $all_category_terms = $context_terms;
        }
        
        $args['tax_query'][] = array(
            'taxonomy' => $context_tax,
            'field' => 'term_id',
            'terms' => $all_category_terms,
            'include_children' => false, // We've already included children manually
            'operator' => 'IN',
        );
    }

    // Handle ordering
    switch ($orderby) {
        case 'price':
            $args['meta_key'] = '_price';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'ASC';
            break;
        case 'price-desc':
            $args['meta_key'] = '_price';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'DESC';
            break;
        case 'popularity':
            $args['meta_key'] = 'total_sales';
            $args['orderby'] = 'meta_value_num';
            $args['order'] = 'DESC';
            break;
        case 'date':
            $args['orderby'] = 'date';
            $args['order'] = 'DESC';
            break;
        default:
            $args['orderby'] = 'menu_order';
            $args['order'] = 'ASC';
    }

    // Price filter
    if (!empty($filters['min_price']) || !empty($filters['max_price'])) {
        $price_query = array('key' => '_price', 'type' => 'NUMERIC');

        if (!empty($filters['min_price']) && !empty($filters['max_price'])) {
            $price_query['value'] = array(floatval($filters['min_price']), floatval($filters['max_price']));
            $price_query['compare'] = 'BETWEEN';
        } elseif (!empty($filters['min_price'])) {
            $price_query['value'] = floatval($filters['min_price']);
            $price_query['compare'] = '>=';
        } elseif (!empty($filters['max_price'])) {
            $price_query['value'] = floatval($filters['max_price']);
            $price_query['compare'] = '<=';
        }

        $args['meta_query'][] = $price_query;
    }

    // Category filter
    // When user explicitly selects categories, show only products in those categories (and their children)
    if (!empty($filters['product_cat'])) {
        $cat_terms = $filters['product_cat'];
        // Support both IDs and slugs if ever passed
        $all_numeric = is_array($cat_terms) && count(array_filter($cat_terms, 'is_numeric')) === count($cat_terms);
        
        // Expand to include child categories for each selected category
        $expanded_cat_terms = array();
        if ($all_numeric) {
            $cat_term_ids = array_map('intval', $cat_terms);
            foreach ($cat_term_ids as $cat_id) {
                $expanded_cat_terms[] = $cat_id;
                // Get all child categories recursively
                $child_cats = get_terms(array(
                    'taxonomy' => 'product_cat',
                    'child_of' => $cat_id,
                    'hide_empty' => true,
                    'fields' => 'ids'
                ));
                if (!empty($child_cats) && !is_wp_error($child_cats)) {
                    $expanded_cat_terms = array_merge($expanded_cat_terms, $child_cats);
                }
            }
            $expanded_cat_terms = array_unique($expanded_cat_terms);
        } else {
            $expanded_cat_terms = array_map('sanitize_text_field', $cat_terms);
        }
        
        $args['tax_query'][] = array(
            'taxonomy' => 'product_cat',
            'field' => $all_numeric ? 'term_id' : 'slug',
            'terms' => $expanded_cat_terms,
            'operator' => 'IN',
            'include_children' => false, // We've manually expanded above
        );
    }

    // Attribute filters
    $attribute_filters = array();

    foreach ($filters as $key => $values) {
        if (strpos($key, 'pa_') === 0 && !empty($values)) {
            $sanitized_values = array_filter(array_map('wc_clean', (array) $values));
            if (!empty($sanitized_values)) {
                $attribute_filters[$key] = $sanitized_values;
                $args['tax_query'][] = array(
                    'taxonomy' => $key,
                    'field' => 'slug',
                    'terms' => $sanitized_values,
                    'operator' => 'IN',
                );
            }
        }
    }

    // If we have attribute filters, get only products with IN-STOCK variations matching those filters
    if (!empty($attribute_filters) && class_exists('Eshop_Product_Filters')) {
        $in_stock_product_ids = Eshop_Product_Filters::get_products_with_instock_variations($attribute_filters);

        if (!empty($in_stock_product_ids)) {
            if (!empty($args['post__in'])) {
                $intersected = array_values(array_intersect($args['post__in'], $in_stock_product_ids));
                $args['post__in'] = !empty($intersected) ? $intersected : array(-1);
            } else {
                $args['post__in'] = $in_stock_product_ids;
            }
        } else {
            // No products with in-stock variations match
            $args['post__in'] = array(-1);
        }
    }

    // Stock status filter - Override default if user explicitly filters
    if (!empty($filters['stock_status'])) {
        // Find and replace the default instock filter
        foreach ($args['meta_query'] as $key => $clause) {
            if (isset($clause['key']) && $clause['key'] === '_stock_status') {
                unset($args['meta_query'][$key]);
                break;
            }
        }
        
        // Add user's selected stock status
        $args['meta_query'][] = array(
            'key' => '_stock_status',
            'value' => $filters['stock_status'],
            'compare' => 'IN',
        );
    }

    // On sale filter - use WooCommerce helper to ensure variable products are included
    if (!empty($filters['on_sale'])) {
        $on_sale_ids = wc_get_product_ids_on_sale();
        $on_sale_ids = array_map('intval', is_array($on_sale_ids) ? $on_sale_ids : array());
        if (!empty($on_sale_ids)) {
            if (!empty($args['post__in'])) {
                // Intersect with any existing post__in
                $args['post__in'] = array_values(array_intersect($args['post__in'], $on_sale_ids));
                if (empty($args['post__in'])) {
                    // Force no results
                    $args['post__in'] = array(-1);
                }
            } else {
                $args['post__in'] = $on_sale_ids;
            }
        } else {
            // No sale products at all; force empty result
            $args['post__in'] = array(-1);
        }
    }

    // Note: Do not use legacy _visibility meta; modern WooCommerce handles catalog visibility via taxonomy/queries.

    // If we have multiple tax queries, ensure AND relation so context is preserved
    if (!empty($args['tax_query'])) {
        // Count only numeric entries
        $tax_items = array_values(array_filter($args['tax_query'], 'is_array'));
        if (count($tax_items) > 1) {
            $args['tax_query']['relation'] = 'AND';
        }
    }

    $query = new WP_Query($args);

    ob_start();

    if ($query->have_posts()) {
        // Start the products grid container
        echo '<ul class="products-grid" id="products-grid">';

        while ($query->have_posts()) {
            $query->the_post();
            wc_get_template_part('content', 'product');
        }

        echo '</ul>'; // Close products-grid

        // Build pagination HTML separately
        $pagination_html = '';
        if ($query->max_num_pages > 1) {
            $pagination_html = '<nav class="woocommerce-pagination" aria-label="Pagination">' .
                paginate_links(array(
                    'total' => $query->max_num_pages,
                    'current' => $paged,
                    'format' => '?paged=%#%',
                    'prev_text' => '&laquo;',
                    'next_text' => '&raquo;',
                )) .
            '</nav>';
        }
    } else {
        echo '<div class="no-products-found text-center py-12">';
        echo '<div class="mb-6"><i class="fas fa-search text-6xl text-gray-300"></i></div>';
        echo '<h3 class="text-2xl font-semibold text-gray-900 mb-4">' . __('No products found', 'eshop-theme') . '</h3>';
        echo '<p class="text-gray-600 mb-6">' . __('Try adjusting your filters or search terms', 'eshop-theme') . '</p>';
        echo '</div>';
    }

    $products_html = ob_get_clean();

    // Generate result count
    $total_products = $query->found_posts;
    $products_per_page = $args['posts_per_page'];
    $current_page = $paged;

    $first = ($current_page - 1) * $products_per_page + 1;
    $last = min($current_page * $products_per_page, $total_products);

    if ($total_products == 1) {
        $result_count = __('Showing the single result', 'eshop-theme');
    } elseif ($total_products <= $products_per_page || -1 === $products_per_page) {
        $result_count = sprintf(__('Showing all %d results', 'eshop-theme'), $total_products);
    } else {
        $result_count = sprintf(__('Showing %1$d–%2$d of %3$d results', 'eshop-theme'), $first, $last, $total_products);
    }

    wp_reset_postdata();

    wp_send_json_success(array(
        'products' => $products_html,
        'pagination' => isset($pagination_html) ? $pagination_html : '',
        'result_count' => $result_count,
        'found_posts' => $total_products,
    ));
}
// AJAX actions for product filtering are currently disabled
// Standard page navigation is used instead
// Uncomment the lines below to enable AJAX filtering:
// add_action('wp_ajax_filter_products', 'eshop_filter_products');
// add_action('wp_ajax_nopriv_filter_products', 'eshop_filter_products');
