<?php
/**
 * WooCommerce Functions
 * 
 * @package E-Shop Theme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enhanced Cart Functions
 */

// Get cart fragment for AJAX updates
function eshop_cart_fragment($fragments) {
    ob_start();
    ?>
    <span class="cart-count <?php echo WC()->cart->get_cart_contents_count() > 0 ? '' : 'hidden'; ?>">
        <?php echo WC()->cart->get_cart_contents_count(); ?>
    </span>
    <?php
    $fragments['.cart-count'] = ob_get_clean();

    ob_start();
    ?>
    <span class="cart-total">
        <?php echo WC()->cart->get_cart_total(); ?>
    </span>
    <?php
    $fragments['.cart-total'] = ob_get_clean();

    // Flying cart count badge
    ob_start();
    ?>
    <span class="cart-count-badge <?php echo WC()->cart->get_cart_contents_count() > 0 ? 'visible' : 'hidden'; ?>" data-count="<?php echo WC()->cart->get_cart_contents_count(); ?>">
        <?php echo WC()->cart->get_cart_contents_count(); ?>
    </span>
    <?php
    $fragments['.cart-count-badge'] = ob_get_clean();

    // Flying cart total amount
    ob_start();
    ?>
    <span class="cart-total-amount"><?php echo WC()->cart->get_cart_total(); ?></span>
    <?php
    $fragments['.cart-total-amount'] = ob_get_clean();

    // Flying cart shipping information
    $cart_total = WC()->cart->get_cart_contents_total();
    $free_shipping_threshold = eshop_get_free_shipping_threshold();
    $remaining_for_free_shipping = max(0, $free_shipping_threshold - $cart_total);

    ob_start();
    if ($remaining_for_free_shipping > 0) : ?>
        <div class="shipping-info">
            <div class="shipping-message">
                <i class="fas fa-truck shipping-icon"></i>
                <span class="shipping-text">
                    <?php echo sprintf(__('Add %s more for FREE shipping!', 'eshop-theme'), wc_price($remaining_for_free_shipping)); ?>
                </span>
            </div>
            <div class="shipping-progress">
                <div class="shipping-progress-bar">
                    <div class="shipping-progress-fill" style="width: <?php echo min(100, ($cart_total / $free_shipping_threshold) * 100); ?>%"></div>
                </div>
            </div>
        </div>
    <?php else : ?>
        <div class="shipping-info free-shipping-achieved">
            <div class="shipping-message">
                <i class="fas fa-check-circle shipping-icon"></i>
                <span class="shipping-text"><?php _e('Congratulations! You qualify for FREE shipping!', 'eshop-theme'); ?></span>
            </div>
        </div>
    <?php endif;
    $fragments['.shipping-info'] = ob_get_clean();
    
    // Update entire minicart content
    ob_start();
    ?>
    <div class="minicart-items max-h-64 overflow-y-auto">
        <?php if (WC()->cart->get_cart_contents_count() > 0) : ?>
            <?php foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) :
                $product = $cart_item['data'];
                $product_id = $cart_item['product_id'];
                $quantity = $cart_item['quantity'];
            ?>
                <div class="minicart-item flex items-center space-x-3 py-3 border-b border-gray-100 last:border-b-0">
                    <div class="w-12 h-12 flex-shrink-0">
                        <?php echo $product->get_image(array(48, 48)); ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="text-sm font-medium text-dark truncate"><?php echo $product->get_name(); ?></h4>
                        <p class="text-xs text-gray-500"><?php echo sprintf('%s × %s', $quantity, wc_price($product->get_price())); ?></p>
                        <p class="text-sm text-primary font-semibold"><?php echo wc_price($product->get_price() * $quantity); ?></p>
                    </div>
                    <a href="<?php echo wc_get_cart_remove_url($cart_item_key); ?>" class="remove-from-cart text-gray-400 hover:text-red-500 transition-colors" data-cart-item-key="<?php echo $cart_item_key; ?>">
                        <i class="fas fa-times text-xs"></i>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <p class="text-gray-500 text-center py-8"><?php _e('Your cart is empty', 'eshop-theme'); ?></p>
        <?php endif; ?>
    </div>
    
    <?php if (WC()->cart->get_cart_contents_count() > 0) : ?>
        <div class="mt-4 pt-3 border-t border-gray-200 space-y-2">
            <a href="<?php echo wc_get_cart_url(); ?>" class="block w-full text-center bg-gray-100 text-dark py-2 hover:bg-gray-200 transition-colors duration-200">
                <?php _e('View Cart', 'eshop-theme'); ?>
            </a>
            <a href="<?php echo wc_get_checkout_url(); ?>" class="block w-full text-center bg-primary text-white py-2 hover:bg-primary-dark transition-colors duration-200">
                <?php _e('Checkout', 'eshop-theme'); ?>
            </a>
        </div>
    <?php endif; ?>
    <?php
    $fragments['.minicart-dropdown .p-4'] = ob_get_clean();
    
    return $fragments;
}
add_filter('woocommerce_add_to_cart_fragments', 'eshop_cart_fragment');



/**
 * Flying Cart AJAX Functions
 */

// Remove cart item via AJAX
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

// Get flying cart content via AJAX
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

    if (empty($html)) {
        wp_send_json_error(array(
            'message' => __('Failed to load cart content', 'eshop-theme')
        ));
    }

    wp_send_json_success(array(
        'html' => $html
    ));
}
add_action('wp_ajax_get_flying_cart_content', 'eshop_get_flying_cart_content_ajax');
add_action('wp_ajax_nopriv_get_flying_cart_content', 'eshop_get_flying_cart_content_ajax');

/**
 * Flying Cart Settings
 */

// Add flying cart settings to WooCommerce settings
function eshop_flying_cart_settings($settings) {
    $flying_cart_settings = array(
        array(
            'name' => __('Flying Cart Settings', 'eshop-theme'),
            'type' => 'title',
            'desc' => __('Configure the flying cart component settings.', 'eshop-theme'),
            'id'   => 'flying_cart_settings'
        ),
        array(
            'name'     => __('Free Shipping Threshold', 'eshop-theme'),
            'desc'     => __('Minimum order amount for free shipping message in flying cart. Leave empty to use WooCommerce free shipping settings.', 'eshop-theme'),
            'id'       => 'eshop_flying_cart_free_shipping_threshold',
            'type'     => 'number',
            'default'  => '50',
            'custom_attributes' => array(
                'min'  => '0',
                'step' => '0.01'
            )
        ),
        array(
            'type' => 'sectionend',
            'id'   => 'flying_cart_settings'
        )
    );

    return array_merge($settings, $flying_cart_settings);
}
add_filter('woocommerce_get_settings_general', 'eshop_flying_cart_settings');

// Update the helper function to use the setting
function eshop_get_free_shipping_threshold() {
    // Check for custom setting first
    $custom_threshold = get_option('eshop_flying_cart_free_shipping_threshold');
    if (!empty($custom_threshold) && is_numeric($custom_threshold)) {
        return floatval($custom_threshold);
    }

    // Try to get from WooCommerce free shipping settings
    $shipping_zones = WC_Shipping_Zones::get_zones();
    foreach ($shipping_zones as $zone) {
        foreach ($zone['shipping_methods'] as $method) {
            if ($method->id === 'free_shipping' && $method->enabled === 'yes') {
                $min_amount = $method->get_option('min_amount');
                if (!empty($min_amount) && is_numeric($min_amount)) {
                    return floatval($min_amount);
                }
            }
        }
    }

    // Fallback to default
    return apply_filters('eshop_free_shipping_threshold', 50);
}

/**
 * Override WooCommerce product loop structure
 */

// Remove default WooCommerce product loop start/end
remove_action('woocommerce_output_content_wrapper', 'woocommerce_output_content_wrapper', 10);
remove_action('woocommerce_output_content_wrapper_end', 'woocommerce_output_content_wrapper_end', 10);

// Override product loop start to output our custom grid container
function eshop_woocommerce_product_loop_start($html) {
    // Use UL to match magazine demo markup
    return '<ul class="products-grid" id="products-grid">';
}
add_filter('woocommerce_product_loop_start', 'eshop_woocommerce_product_loop_start');

// Override product loop end to close our custom grid container
function eshop_woocommerce_product_loop_end($html) {
    return '</ul>';
}
add_filter('woocommerce_product_loop_end', 'eshop_woocommerce_product_loop_end');

// Set default shop columns to 4
function eshop_woocommerce_loop_columns() {
    return 4;
}
add_filter('loop_shop_columns', 'eshop_woocommerce_loop_columns');

// Remove default WooCommerce styles that conflict with our grid
function eshop_dequeue_woocommerce_styles() {
    wp_dequeue_style('woocommerce-layout');
    wp_dequeue_style('woocommerce-smallscreen');
    wp_dequeue_style('woocommerce-general');
}
add_action('wp_enqueue_scripts', 'eshop_dequeue_woocommerce_styles', 100);

// Remove default WooCommerce sale flash (we handle it in product-card.php)
add_filter('woocommerce_sale_flash', '__return_empty_string', 10, 3);

/**
 * Single Product summary customizations: remove rating and move Add to Cart
 */
function eshop_customize_single_product_summary_hooks() {
    if (!function_exists('is_product') || !is_product()) { return; }
    // Remove reviews/rating from summary
    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10);
    // Prevent duplicate Add to Cart in summary; we'll render it in our Product Actions block
    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
}
add_action('wp', 'eshop_customize_single_product_summary_hooks');

// Drop the Additional Information tab to clean up the product details area
function eshop_remove_additional_information_tab($tabs) {
    if (isset($tabs['additional_information'])) {
        unset($tabs['additional_information']);
    }
    return $tabs;
}
add_filter('woocommerce_product_tabs', 'eshop_remove_additional_information_tab', 98);

/**
 * Add Wishlist next to Add to Cart across product types
 * DISABLED - Now handled directly in add-to-cart templates for better control
 */
// function eshop_purchase_actions_open() {
//     if (!function_exists('is_product') || !is_product()) { return; }
//     echo '<div class="purchase-actions">';
// }
// add_action('woocommerce_before_add_to_cart_button', 'eshop_purchase_actions_open', 5);

// function eshop_purchase_actions_wishlist_button() {
//     if (!function_exists('is_product') || !is_product()) { return; }
//     if (function_exists('eshop_wishlist_button_enhanced')) {
//         // Use enhanced wishlist button with our classes
//         eshop_wishlist_button_enhanced(null, true, 'add-to-wishlist');
//     }
// }
// add_action('woocommerce_after_add_to_cart_button', 'eshop_purchase_actions_wishlist_button', 10);

// function eshop_purchase_actions_close() {
//     if (!function_exists('is_product') || !is_product()) { return; }
//     echo '</div>';
// }
// add_action('woocommerce_after_add_to_cart_button', 'eshop_purchase_actions_close', 1000);

/**
 * Override WooCommerce image sizes for better quality
 */
function eshop_woocommerce_image_dimensions() {
    global $pagenow;

    if (!isset($_GET['activated']) || $pagenow != 'themes.php') {
        return;
    }

    // Set WooCommerce image sizes to higher resolution
    update_option('woocommerce_thumbnail_image_width', 400);
    update_option('woocommerce_thumbnail_image_height', 400);
    update_option('woocommerce_thumbnail_cropping', '1:1');

    update_option('woocommerce_single_image_width', 800);
    update_option('woocommerce_single_image_height', 800);

    update_option('woocommerce_gallery_thumbnail_image_width', 150);
    update_option('woocommerce_gallery_thumbnail_image_height', 150);
}
add_action('after_switch_theme', 'eshop_woocommerce_image_dimensions', 1);

/**
 * Use higher quality images in product loops
 */
function eshop_woocommerce_single_product_image_thumbnail_html($html, $post_thumbnail_id) {
    global $product;

    if (!$product) {
        return $html;
    }

    // Use our custom high-quality image size
    $image = wp_get_attachment_image($post_thumbnail_id, 'product-thumbnail-hq', false, array(
        'class' => 'w-full h-full object-cover transition-transform duration-300 group-hover:scale-105',
        'alt' => $product->get_name()
    ));

    return $image;
}
add_filter('woocommerce_single_product_image_thumbnail_html', 'eshop_woocommerce_single_product_image_thumbnail_html', 10, 2);

/**
 * AJAX handler for product filtering
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
            'orderby' => isset($_POST['orderby']) ? sanitize_text_field(wp_unslash($_POST['orderby'])) : 'menu_order',
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
    $orderby = isset($data['orderby']) ? sanitize_text_field($data['orderby']) : 'menu_order';

    // Build WP_Query args
    // Force 12 products per page for consistency across all categories
    // This must match the value set in eshop_force_12_products_per_page() in functions.php
    $computed_per_page = 12;

    $args = array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => $computed_per_page,
        'paged' => $paged,
        'orderby' => $orderby,
        'meta_query' => array(
            // CRITICAL: Exclude out-of-stock products to ensure consistent count
            array(
                'key' => '_stock_status',
                'value' => 'instock',
                'compare' => '='
            )
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
    foreach ($filters as $key => $values) {
        if (strpos($key, 'pa_') === 0 && !empty($values)) {
            $args['tax_query'][] = array(
                'taxonomy' => $key,
                'field' => 'slug',
                'terms' => $values,
                'operator' => 'IN',
            );
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
// AJAX actions for product filtering are now disabled
// Standard page navigation is used instead
// add_action('wp_ajax_filter_products', 'eshop_filter_products');
// add_action('wp_ajax_nopriv_filter_products', 'eshop_filter_products');


/**
 * Ensure catalog queries only load in-stock products
 * Adds a defensive meta query at the WooCommerce level so we always fetch
 * the same number of products as we render.
 */
function eshop_require_instock_products_in_catalog($meta_query, $query) {
    if (is_admin() && !wp_doing_ajax()) {
        return $meta_query;
    }

    $resolve_query_var = static function ($source, $key) {
        if (!is_object($source)) {
            return null;
        }

        if (method_exists($source, 'get')) {
            $value = $source->get($key);
            if (null !== $value) {
                return $value;
            }
        }

        if (method_exists($source, 'get_main_query')) {
            $main_query = $source->get_main_query();
            if ($main_query instanceof WP_Query) {
                if (method_exists($main_query, 'get')) {
                    $value = $main_query->get($key);
                    if (null !== $value) {
                        return $value;
                    }
                }
            }
        }

        if ($source instanceof WP_Query) {
            $vars = $source->query_vars;
        } elseif (method_exists($source, 'get_main_query')) {
            $main_query = $source->get_main_query();
            $vars = ($main_query instanceof WP_Query) ? $main_query->query_vars : array();
        } else {
            $vars = array();
        }

        return (is_array($vars) && array_key_exists($key, $vars)) ? $vars[$key] : null;
    };

    $bypass_stock_filter = $resolve_query_var($query, 'eshop_bypass_stock_filter');
    if (!empty($bypass_stock_filter)) {
        return $meta_query;
    }

    $post_types = $resolve_query_var($query, 'post_type');
    if (empty($post_types)) {
        $post_types = array('product');
    }

    $post_types = (array) $post_types;
    if (!in_array('product', $post_types, true)) {
        return $meta_query;
    }

    $has_stock_clause = false;
    foreach ($meta_query as $clause) {
        if (!is_array($clause)) {
            continue;
        }
        if (isset($clause['key']) && $clause['key'] === '_stock_status') {
            $has_stock_clause = true;
            break;
        }
    }

    if (!$has_stock_clause) {
        $meta_query[] = array(
            'key' => '_stock_status',
            'value' => 'instock',
            'compare' => '=',
        );
    }

    return $meta_query;
}
add_filter('woocommerce_product_query_meta_query', 'eshop_require_instock_products_in_catalog', 20, 2);






/**
 * Enhanced Related Products - Display 4 products
 */

/**
 * Helpers: counts for sale and featured products (used by filters UI)
 */
if (!function_exists('eshop_get_sale_products_count')) {
    function eshop_get_sale_products_count() {
        if (!class_exists('WooCommerce')) { return 0; }
        
        // Get all sale product IDs
        $sale_products = wc_get_product_ids_on_sale();
        if (empty($sale_products) || !is_array($sale_products)) {
            return 0;
        }
        
        // Get current context product IDs (respects category, other filters, etc.)
        $context_product_ids = eshop_get_current_context_product_ids();
        
        // If no context products, return 0
        if (empty($context_product_ids)) {
            return 0;
        }
        
        // Count only sale products that are in the current context
        $context_sale_products = array_intersect($sale_products, $context_product_ids);
        
        return count($context_sale_products);
    }
}

if (!function_exists('eshop_get_featured_products_count')) {
    function eshop_get_featured_products_count() {
        if (!class_exists('WooCommerce')) { return 0; }
        
        // Get all featured product IDs
        $featured_products = wc_get_featured_product_ids();
        if (empty($featured_products) || !is_array($featured_products)) {
            return 0;
        }
        
        // Get current context product IDs (respects category, other filters, etc.)
        $context_product_ids = eshop_get_current_context_product_ids();
        
        // If no context products, return 0
        if (empty($context_product_ids)) {
            return 0;
        }
        
        // Count only featured products that are in the current context
        $context_featured_products = array_intersect($featured_products, $context_product_ids);
        
        return count($context_featured_products);
    }
}

if (!function_exists('eshop_get_stock_status_count')) {
    /**
     * Count products matching a specific stock status.
     *
     * @param string $status Stock status key (instock, outofstock, onbackorder).
     * @return int Number of published products with the requested status.
     */
    function eshop_get_stock_status_count($status) {
        global $wpdb;

        $allowed_statuses = array('instock', 'outofstock', 'onbackorder');
        $status = sanitize_key($status);

        if (!in_array($status, $allowed_statuses, true)) {
            return 0;
        }

        $query = $wpdb->prepare(
            "SELECT COUNT(DISTINCT p.ID)
             FROM {$wpdb->posts} p
             INNER JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
             WHERE p.post_type = 'product'
               AND p.post_status = 'publish'
               AND pm.meta_key = '_stock_status'
               AND pm.meta_value = %s",
            $status
        );

        $count = $wpdb->get_var($query);

        return $count ? (int) $count : 0;
    }
}


// Remove default related products output
remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);

/**
 * Account Menu Functions
 */

// Get account menu items
function eshop_get_account_menu_items() {
    $items = array();
    
    if (is_user_logged_in()) {
        $items['dashboard'] = array(
            'title' => __('Dashboard', 'eshop-theme'),
            'url' => wc_get_account_endpoint_url('dashboard')
        );
        $items['orders'] = array(
            'title' => __('Orders', 'eshop-theme'),
            'url' => wc_get_account_endpoint_url('orders')
        );
        $items['downloads'] = array(
            'title' => __('Downloads', 'eshop-theme'),
            'url' => wc_get_account_endpoint_url('downloads')
        );
        $items['edit-address'] = array(
            'title' => __('Addresses', 'eshop-theme'),
            'url' => wc_get_account_endpoint_url('edit-address')
        );
        $items['edit-account'] = array(
            'title' => __('Account Details', 'eshop-theme'),
            'url' => wc_get_account_endpoint_url('edit-account')
        );
        $items['customer-logout'] = array(
            'title' => __('Logout', 'eshop-theme'),
            'url' => wc_logout_url()
        );
    } else {
        // Modal-based authentication options for logged-out users
        $items['login'] = array(
            'title' => __('Login', 'eshop-theme'),
            'url' => '#',
            'action' => 'open-login-modal'
        );
        $items['register'] = array(
            'title' => __('Register', 'eshop-theme'),
            'url' => '#',
                        'action' => 'open-register-modal'
        );
    }
    
    return $items;
}

/**
 * Custom Related Products from Same Category and Parent Category
 * Uses the twc-card component from product archive
 */
function eshop_output_related_products_from_categories() {
    global $product;
    
    if (!$product) {
        return;
    }
    
    // Debug: Output a visible marker
    echo '<!-- Related Products Function Called -->';
    
    $product_id = $product->get_id();
    $original_product = $product; // Preserve current product context
    
    // Get all product categories
    $categories = wp_get_post_terms($product_id, 'product_cat', array('fields' => 'ids'));
    
    if (empty($categories)) {
        return; // No categories, no related products
    }

    // Include parent categories
    $all_category_ids = $categories;
    foreach ($categories as $cat_id) {
        $ancestors = get_ancestors($cat_id, 'product_cat');
        if (!empty($ancestors)) {
            $all_category_ids = array_merge($all_category_ids, $ancestors);
        }
    }
    $all_category_ids = array_unique($all_category_ids);
    
    // Query products from same category and parent categories
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => 8, // Get 8 products for 2 rows of 4
        'post__not_in' => array($product_id),
        'orderby' => 'rand'
    );
    
    // Add category filter if we have categories
    if (!empty($all_category_ids)) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'product_cat',
                'field' => 'term_id',
                'terms' => $all_category_ids,
                'operator' => 'IN'
            )
        );
    }
    
    $related_query = new WP_Query($args);
    
    // Fallback: if no products found in same category, get any products
    if (!$related_query->have_posts()) {
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => 8,
            'post__not_in' => array($product_id),
            'orderby' => 'rand'
        );
        $related_query = new WP_Query($args);
    }
    
    if ($related_query->have_posts()) {
        ?>
        <section class="related-products-section magazine-related">
            <div class="magazine-container">
                <h2 class="related-products-heading">
                    <?php _e('You May Also Like', 'thewalkingtheme'); ?>
                </h2>

                <ul class="products-grid related-products-grid" id="related-products-grid">
                    <?php
                    while ($related_query->have_posts()) {
                        $related_query->the_post();

                        $related_product = wc_get_product(get_the_ID());
                        if (!$related_product || !$related_product->is_visible()) {
                            continue;
                        }

                        // Set global product for template
                        $GLOBALS['product'] = $related_product;

                        // Use our custom product card component
                        get_template_part('template-parts/components/product-card');
                    }
                    ?>
                </ul>
            </div>
        </section>
        <?php
    }
    
    wp_reset_postdata();
    if (function_exists('wc_reset_product_data')) {
        wc_reset_product_data();
    }

    // Restore original product for the remainder of the single product template
    if ($original_product instanceof WC_Product) {
        $original_post = get_post($original_product->get_id());
        if ($original_post && function_exists('wc_setup_product_data')) {
            wc_setup_product_data($original_post);
        }
    }
    $product = $original_product;
}
add_action('woocommerce_after_single_product_summary', 'eshop_output_related_products_from_categories', 20);
