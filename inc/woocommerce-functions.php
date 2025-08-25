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
            'fragments' => apply_filters('woocommerce_add_to_cart_fragments', array())
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
    return '<div class="products-grid grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6" id="products-grid">';
}
add_filter('woocommerce_product_loop_start', 'eshop_woocommerce_product_loop_start');

// Override product loop end to close our custom grid container
function eshop_woocommerce_product_loop_end($html) {
    return '</div>';
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
    check_ajax_referer('eshop_nonce', 'nonce');

    $filters = isset($_POST['filters']) ? $_POST['filters'] : array();
    $paged = isset($_POST['paged']) ? intval($_POST['paged']) : 1;
    $orderby = isset($_POST['orderby']) ? sanitize_text_field($_POST['orderby']) : 'menu_order';

    // Build WP_Query args
    $args = array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => wc_get_default_products_per_row() * wc_get_default_product_rows_per_page(),
        'paged' => $paged,
        'orderby' => $orderby,
        'meta_query' => array(),
        'tax_query' => array(),
    );

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
    if (!empty($filters['product_cat'])) {
        $args['tax_query'][] = array(
            'taxonomy' => 'product_cat',
            'field' => 'term_id',
            'terms' => array_map('intval', $filters['product_cat']),
            'operator' => 'IN',
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

    // Stock status filter
    if (!empty($filters['stock_status'])) {
        $args['meta_query'][] = array(
            'key' => '_stock_status',
            'value' => $filters['stock_status'],
            'compare' => 'IN',
        );
    }

    // On sale filter
    if (!empty($filters['on_sale'])) {
        $args['meta_query'][] = array(
            'key' => '_sale_price',
            'value' => '',
            'compare' => '!=',
        );
    }

    // Note: Do not use legacy _visibility meta; modern WooCommerce handles catalog visibility via taxonomy/queries.

    $query = new WP_Query($args);

    ob_start();

    if ($query->have_posts()) {
        // Start the products grid container
        echo '<div class="products-grid grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6" id="products-grid">';

        while ($query->have_posts()) {
            $query->the_post();
            wc_get_template_part('content', 'product');
        }

        echo '</div>'; // Close products-grid

        // Pagination
        if ($query->max_num_pages > 1) {
            echo '<div class="pagination-wrapper mt-8">';
            echo paginate_links(array(
                'total' => $query->max_num_pages,
                'current' => $paged,
                'format' => '?paged=%#%',
                'prev_text' => '<i class="fas fa-chevron-left"></i>',
                'next_text' => '<i class="fas fa-chevron-right"></i>',
            ));
            echo '</div>';
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
        'result_count' => $result_count,
        'found_posts' => $total_products,
    ));
}
add_action('wp_ajax_filter_products', 'eshop_filter_products');
add_action('wp_ajax_nopriv_filter_products', 'eshop_filter_products');






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
        $items['login'] = array(
            'title' => __('Login', 'eshop-theme'),
            'url' => wc_get_page_permalink('myaccount')
        );
        $items['register'] = array(
            'title' => __('Register', 'eshop-theme'),
            'url' => wc_get_page_permalink('myaccount')
        );
    }
    
    return $items;
}