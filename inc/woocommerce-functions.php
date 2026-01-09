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
    get_template_part('template-parts/header/minicart-content');
    $fragments['.eshop-minicart-inner'] = ob_get_clean();
    
    return $fragments;
}
add_filter('woocommerce_add_to_cart_fragments', 'eshop_cart_fragment');

/**
 * Gift wrap helpers and checkout fee.
 */
function eshop_get_gift_wrap_price() {
    return (float) apply_filters('eshop_gift_wrap_price', 1.5);
}

function eshop_get_gift_wrap_title() {
    return apply_filters('eshop_gift_wrap_title', __('Επαναχρησιμοποιήσιμη Τσάντα LUIGI 38x49x14cm', 'eshop-theme'));
}

function eshop_get_gift_wrap_description() {
    return apply_filters(
        'eshop_gift_wrap_description',
        __('Η συσκευασία δώρου που έχετε επιλέξει, συσκευάζεται στην παραγγελία σας μαζί με τα προϊόντα. Δεν παραλαμβάνετε τα προϊόντα συσκευασμένα, προκειμένου να αποτρέπεται η φθορά της συσκευασίας κατά τη μεταφορά του δέματος.', 'eshop-theme')
    );
}

function eshop_get_gift_wrap_image_url() {
    return apply_filters('eshop_gift_wrap_image_url', wc_placeholder_img_src('woocommerce_thumbnail'));
}

function eshop_get_gift_wrap_markup() {
    $gift_wrap_qty = 0;
    if (function_exists('WC') && WC()->session) {
        $gift_wrap_qty = (int) WC()->session->get('eshop_gift_wrap_qty', 0);
    }
    $gift_wrap_price = eshop_get_gift_wrap_price();
    $gift_wrap_image = eshop_get_gift_wrap_image_url();
    $gift_wrap_title = eshop_get_gift_wrap_title();
    $gift_wrap_desc = eshop_get_gift_wrap_description();

    ob_start();
    ?>
    <div class="gift-wrap-section mb-6">
        <button type="button" class="gift-wrap-toggle w-full flex items-center justify-between text-left">
            <span class="gift-wrap-toggle-text"><?php _e('ΕΠΙΛΕΞΤΕ ΣΥΣΚΕΥΑΣΙΑ ΔΩΡΟΥ', 'eshop-theme'); ?></span>
            <i class="fas fa-chevron-down gift-wrap-toggle-icon"></i>
        </button>
        <div class="gift-wrap-panel mt-3">
            <p class="gift-wrap-description"><?php echo esc_html($gift_wrap_desc); ?></p>
            <div class="gift-wrap-item mt-4">
                <div class="gift-wrap-thumb">
                    <img src="<?php echo esc_url($gift_wrap_image); ?>" alt="<?php echo esc_attr($gift_wrap_title); ?>">
                </div>
                <div class="gift-wrap-info">
                    <div class="gift-wrap-name"><?php echo esc_html($gift_wrap_title); ?></div>
                    <div class="gift-wrap-price"><?php echo wc_price($gift_wrap_price); ?></div>
                </div>
                <div class="gift-wrap-qty">
                    <button type="button" class="gift-wrap-qty-btn gift-wrap-minus" aria-label="<?php esc_attr_e('Decrease gift wrap quantity', 'eshop-theme'); ?>">
                        <i class="fas fa-minus"></i>
                    </button>
                    <input type="number" name="gift_wrap_qty" class="gift-wrap-input" min="0" max="99" step="1" value="<?php echo esc_attr($gift_wrap_qty); ?>">
                    <button type="button" class="gift-wrap-qty-btn gift-wrap-plus" aria-label="<?php esc_attr_e('Increase gift wrap quantity', 'eshop-theme'); ?>">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function eshop_capture_gift_wrap_qty($posted_data) {
    if (!class_exists('WooCommerce') || !WC()->session) {
        return;
    }

    $data = array();
    parse_str($posted_data, $data);
    $qty = isset($data['gift_wrap_qty']) ? absint($data['gift_wrap_qty']) : 0;
    WC()->session->set('eshop_gift_wrap_qty', $qty);
}
add_action('woocommerce_checkout_update_order_review', 'eshop_capture_gift_wrap_qty');

function eshop_apply_gift_wrap_fee($cart) {
    if (is_admin() && !defined('DOING_AJAX')) {
        return;
    }

    if (!class_exists('WooCommerce') || !WC()->session) {
        return;
    }

    $qty = absint(WC()->session->get('eshop_gift_wrap_qty', 0));
    if ($qty < 1) {
        return;
    }

    $price = eshop_get_gift_wrap_price();
    if ($price <= 0) {
        return;
    }

    $label = sprintf(__('Συσκευασία δώρου (%d)', 'eshop-theme'), $qty);
    $cart->add_fee($label, $price * $qty, false);
}
add_action('woocommerce_cart_calculate_fees', 'eshop_apply_gift_wrap_fee', 20, 1);

function eshop_render_gift_wrap_checkout_section() {
    if (!empty($GLOBALS['eshop_has_checkout_gift_wrap'])) {
        return;
    }

    echo eshop_get_gift_wrap_markup();
    $GLOBALS['eshop_has_checkout_gift_wrap'] = true;
}
add_action('woocommerce_review_order_before_cart_contents', 'eshop_render_gift_wrap_checkout_section', 5);

function eshop_inject_gift_wrap_checkout_block($block_content, $block) {
    if (!function_exists('is_checkout') || !is_checkout()) {
        return $block_content;
    }

    if (!empty($GLOBALS['eshop_has_checkout_gift_wrap'])) {
        return $block_content;
    }

    $markup = eshop_get_gift_wrap_markup();
    $GLOBALS['eshop_has_checkout_gift_wrap'] = true;

    return $markup . $block_content;
}
add_filter('render_block_woocommerce/checkout-order-summary', 'eshop_inject_gift_wrap_checkout_block', 10, 2);

function eshop_is_checkout_block_context() {
    if (function_exists('is_checkout') && is_checkout()) {
        return true;
    }

    if (function_exists('has_block')) {
        return has_block('woocommerce/checkout') || has_block('woocommerce/checkout-block');
    }

    return false;
}

function eshop_inject_gift_wrap_into_blocks($block_content, $block) {
    if (empty($block['blockName'])) {
        return $block_content;
    }

    if (!eshop_is_checkout_block_context()) {
        return $block_content;
    }

    if (!empty($GLOBALS['eshop_has_checkout_gift_wrap'])) {
        return $block_content;
    }

    $block_name = (string) $block['blockName'];
    $matches_summary = strpos($block_name, 'woocommerce/checkout-order-summary') !== false;
    $matches_totals = strpos($block_name, 'woocommerce/checkout-totals') !== false;

    if (!$matches_summary && !$matches_totals) {
        return $block_content;
    }

    $markup = eshop_get_gift_wrap_markup();
    $GLOBALS['eshop_has_checkout_gift_wrap'] = true;

    return $markup . $block_content;
}
add_filter('render_block', 'eshop_inject_gift_wrap_into_blocks', 10, 2);

function eshop_output_gift_wrap_template() {
    if (!eshop_is_checkout_block_context()) {
        return;
    }

    if (!empty($GLOBALS['eshop_has_checkout_gift_wrap'])) {
        return;
    }

    echo '<template id="eshop-gift-wrap-template">' . eshop_get_gift_wrap_markup() . '</template>';
}
add_action('wp_footer', 'eshop_output_gift_wrap_template', 20);

function eshop_save_gift_wrap_meta($order, $data) {
    if (!class_exists('WooCommerce') || !WC()->session) {
        return;
    }

    $qty = absint(WC()->session->get('eshop_gift_wrap_qty', 0));
    if ($qty > 0) {
        $order->update_meta_data('_eshop_gift_wrap_qty', $qty);
    }
}
add_action('woocommerce_checkout_create_order', 'eshop_save_gift_wrap_meta', 20, 2);

function eshop_clear_gift_wrap_session($order_id) {
    if (class_exists('WooCommerce') && WC()->session) {
        WC()->session->__unset('eshop_gift_wrap_qty');
    }
}
add_action('woocommerce_checkout_order_processed', 'eshop_clear_gift_wrap_session', 20, 1);

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

// Set default shop columns to 5
function eshop_woocommerce_loop_columns() {
    return 5;
}
add_filter('loop_shop_columns', 'eshop_woocommerce_loop_columns');

// Inform WooCommerce defaults used by wc_get_default_products_per_row()/rows per page
function eshop_woocommerce_default_products_per_row() {
    return 5;
}
add_filter('woocommerce_default_products_per_row', 'eshop_woocommerce_default_products_per_row');

function eshop_woocommerce_default_product_rows_per_page() {
    return 3; // 5 columns × 3 rows = 15 products
}
add_filter('woocommerce_default_product_rows_per_page', 'eshop_woocommerce_default_product_rows_per_page');

// Remove default WooCommerce styles that conflict with our grid
function eshop_dequeue_woocommerce_styles() {
    wp_dequeue_style('woocommerce-layout');
    wp_dequeue_style('woocommerce-smallscreen');
    wp_dequeue_style('woocommerce-general');
}
add_action('wp_enqueue_scripts', 'eshop_dequeue_woocommerce_styles', 100);

// Remove default WooCommerce sale flash actions (we handle it in product-card.php)
remove_action('woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10);
remove_action('woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10);
add_filter('woocommerce_sale_flash', '__return_empty_string', 10, 3);

/**
 * Single Product summary customizations: remove rating and move Add to Cart
 */
function eshop_customize_single_product_summary_hooks() {
    if (!function_exists('is_product') || !is_product()) { return; }

    // Avoid duplicate summary output because the template renders these manually
    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_title', 5);
    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20);

    // Keep ratings hidden to match the concept layout
    remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10);
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
// DISABLED: This global filter was forcing all products to appear as out-of-stock
// Stock status should be handled by individual queries and WooCommerce natively
// add_filter('woocommerce_product_query_meta_query', 'eshop_require_instock_products_in_catalog', 20, 2);






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
                    <?php _e('You May Also Like', 'eshop-theme'); ?>
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

                        // Wrap product card in li for valid HTML
                        echo '<li class="product-grid-item">';
                        // Use our custom product card component
                        get_template_part('template-parts/components/product-card');
                        echo '</li>';
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

/**
 * Remove erroneously injected product summary content from the short description.
 * This is a corrective filter to clean up the output before the final wrapper is applied.
 */
function eshop_remove_summary_from_short_description($description) {
    // The pattern looks for the start of the injected price row and removes everything from there to the end.
    $pattern = '/<div class="price-row".*/s';
    $cleaned_description = preg_replace($pattern, '', $description);
    
    // If cleaning resulted in an empty string, return the original to be safe.
    return $cleaned_description !== '' ? $cleaned_description : $description;
}
add_filter('woocommerce_short_description', 'eshop_remove_summary_from_short_description', 0);

/**
 * Clean up the short description from duplicate wrappers.
 * This can happen if content is copy-pasted from a visual editor with its own wrappers.
 */
function eshop_clean_short_description_wrapper($description) {
    $wrapper_class = 'product_feautures_item_title features_title_place';
    $div_pattern = '/<div class="' . preg_quote($wrapper_class, '/') . '">/i';

    // If the wrapper is present multiple times, clean it up.
    if (preg_match_all($div_pattern, $description, $matches) && count($matches[0]) > 1) {
        // This regex will find the content inside the deepest nested div.
        $content_pattern = '/(?:<div class="' . preg_quote($wrapper_class, '/') . '">\s*)+(.+?)(?:\s*<\/div>)+/is';
        
        if (preg_match($content_pattern, $description, $content_match)) {
            $inner_content = trim($content_match[1]);
            // Return the content wrapped in a single, clean div.
            return '<div class="' . $wrapper_class . '">' . $inner_content . '</div>';
        }
    }

    // If there's no duplication, return the description as is.
    return $description;
}
add_filter('woocommerce_short_description', 'eshop_clean_short_description_wrapper', 1);

/**
 * Ensure product context is properly set for single product pages
 * This function fixes the fatal error when $product is not properly initialized
 */
function eshop_ensure_product_context() {
    if (is_product() && !isset($GLOBALS['product'])) {
        $product_id = get_the_ID();
        if ($product_id && get_post_type($product_id) === 'product') {
            $GLOBALS['product'] = wc_get_product($product_id);
        }
    }
}
add_action('wp', 'eshop_ensure_product_context');
add_action('woocommerce_after_single_product_summary', 'eshop_output_related_products_from_categories', 20);

/**
 * Make "Newest" the default catalog ordering everywhere.
 */
function eshop_default_catalog_orderby_value($default) {
    return 'date';
}
add_filter('woocommerce_default_catalog_orderby', 'eshop_default_catalog_orderby_value');

/**
 * Ensure the "Newest" option always sorts by descending publish date.
 */
function eshop_force_newest_catalog_ordering($args, $orderby, $order) {
    if ('date' === $orderby) {
        $args['orderby'] = 'date';
        $args['order'] = 'DESC';
        unset($args['meta_key']);
    }

    return $args;
}
add_filter('woocommerce_get_catalog_ordering_args', 'eshop_force_newest_catalog_ordering', 10, 3);

/**
 * Remove the rating-based catalog ordering option everywhere WooCommerce exposes it.
 */
function eshop_remove_rating_catalog_ordering($options) {
    if (isset($options['rating'])) {
        unset($options['rating']);
    }

    return $options;
}
add_filter('woocommerce_default_catalog_orderby_options', 'eshop_remove_rating_catalog_ordering');
add_filter('woocommerce_catalog_orderby', 'eshop_remove_rating_catalog_ordering');
