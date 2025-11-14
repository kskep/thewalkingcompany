<?php
/**
 * Enhanced Wishlist Functionality - 2025 Standards
 * Storage: Cookies for guests, user meta for logged-in users.
 * Avoids native PHP sessions to align with WordPress & caching best practices.
 * 
 * @package thewalkingtheme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Configuration for cookie-based wishlist storage
// Guest wishlist storage removed; only logged-in users can save.
// Define constants for clarity of intent if needed later.
if (!defined('ESHOP_WISHLIST_ENABLED_FOR_GUESTS')) {
    define('ESHOP_WISHLIST_ENABLED_FOR_GUESTS', false);
}

/**
 * Read wishlist from cookie (guest users)
 */
// Legacy cookie reader (now returns empty because guests cannot store wishlist)
function eshop_get_cookie_wishlist() { return array(); }

/**
 * Persist wishlist to cookie (guest users)
 */
function eshop_set_cookie_wishlist($wishlist) { /* no-op for guests */ }

/**
 * Public storage getters/setters
 */
function eshop_get_wishlist() {
    if (!is_user_logged_in()) {
        return array();
    }
    $user_ids = get_user_meta(get_current_user_id(), 'eshop_wishlist', true);
    if (!is_array($user_ids)) {
        $user_ids = array();
    }
    return array_values(array_unique(array_map('absint', $user_ids)));
}

function eshop_set_wishlist($wishlist) {
    if (!is_user_logged_in()) {
        return; // guests cannot persist
    }
    $wishlist = is_array($wishlist) ? $wishlist : array();
    $wishlist = array_values(array_unique(array_map('absint', $wishlist)));
    $wishlist = array_filter($wishlist);
    update_user_meta(get_current_user_id(), 'eshop_wishlist', $wishlist);
}


/**
 * Sync session wishlist with user meta for logged-in users
 */
function eshop_sync_wishlist_with_user_meta() { /* no-op now */ }

// Also merge immediately at login
// Login merge removed (no guest storage)

/**
 * Enhanced Add to wishlist AJAX handler
 */
function eshop_add_to_wishlist() {
    check_ajax_referer('eshop_nonce', 'nonce');

    if (!is_user_logged_in()) {
        wp_send_json_error(array(
            'requires_auth' => true,
            'message' => __('Please create an account or log in to use wishlist.', 'eshop-theme'),
            'redirect' => wc_get_page_permalink('myaccount'),
        ));
    }
    
    $product_id = intval($_POST['product_id']);
    if (!$product_id) {
        wp_send_json_error(array(
            'message' => __('Invalid product ID', 'eshop-theme')
        ));
    }
    
    // Check if product exists
    $product = wc_get_product($product_id);
    if (!$product) {
        wp_send_json_error(array(
            'message' => __('Product not found', 'eshop-theme')
        ));
    }
    $product_name_plain = wp_strip_all_tags($product->get_name());

    $current = eshop_get_wishlist();
    $action = '';
    $message = '';

    if (!in_array($product_id, $current)) {
        $current[] = $product_id;
        $action = 'added';
        $message = sprintf(__('%s added to wishlist', 'eshop-theme'), $product_name_plain);
    } else {
        $current = array_diff($current, array($product_id));
        $action = 'removed';
        $message = sprintf(__('%s removed from wishlist', 'eshop-theme'), $product_name_plain);
    }

    // Persist changes
    eshop_set_wishlist($current);

    $wishlist_count      = count($current);
    $is_in_wishlist      = ($action === 'added');
    $product_name        = $product_name_plain;
    $button_text         = $is_in_wishlist ? __('Saved', 'eshop-theme') : __('Save', 'eshop-theme');
    $aria_label          = $is_in_wishlist
        ? sprintf(__('Remove %s from wishlist', 'eshop-theme'), $product_name)
        : sprintf(__('Add %s to wishlist', 'eshop-theme'), $product_name);
    $dropdown_html       = eshop_get_wishlist_dropdown_items_html();
    $notification_type   = $is_in_wishlist ? 'success' : 'info';

    wp_send_json_success(array(
        'action'            => $action,
        'message'           => $message,
        'notification_type' => $notification_type,
        'count'             => $wishlist_count,
        'count_label'       => eshop_get_wishlist_count_display(),
        'product_id'        => $product_id,
        'product_name'      => $product_name,
        'is_in_wishlist'    => $is_in_wishlist,
        'button_text'       => $button_text,
        'aria_label'        => $aria_label,
        'icon'              => $is_in_wishlist ? 'favorite' : 'favorite_border',
        'dropdown_html'     => $dropdown_html,
        'has_items'         => $wishlist_count > 0,
    ));
}
add_action('wp_ajax_add_to_wishlist', 'eshop_add_to_wishlist');
add_action('wp_ajax_nopriv_add_to_wishlist', 'eshop_add_to_wishlist');

// Add toggle_wishlist handlers that use the same function
add_action('wp_ajax_toggle_wishlist', 'eshop_add_to_wishlist');
add_action('wp_ajax_nopriv_toggle_wishlist', 'eshop_add_to_wishlist');

/**
 * AJAX handler to remove product from wishlist
 */
add_action('wp_ajax_remove_from_wishlist', 'eshop_remove_from_wishlist_ajax');
add_action('wp_ajax_nopriv_remove_from_wishlist', 'eshop_remove_from_wishlist_ajax');

/**
 * Handle AJAX remove from wishlist request
 */
function eshop_remove_from_wishlist_ajax() {
    // Verify nonce for security
    if (!wp_verify_nonce($_POST['nonce'], 'eshop_nonce')) {
        wp_send_json_error('Invalid security token');
        return;
    }

    if (!is_user_logged_in()) {
        wp_send_json_error(array(
            'requires_auth' => true,
            'message' => __('You must be logged in to modify wishlist.', 'eshop-theme'),
            'redirect' => wc_get_page_permalink('myaccount'),
        ));
    }

    $product_id = intval($_POST['product_id']);
    
    if (!$product_id) {
        wp_send_json_error('Invalid product ID');
        return;
    }

    // Check if product exists
    $product = wc_get_product($product_id);
    if (!$product || !$product->exists()) {
        wp_send_json_error('Product not found');
        return;
    }

    // Remove from wishlist
    $removed = eshop_remove_from_wishlist($product_id);
    
    if ($removed) {
        // Get updated count and info
        $wishlist_count = eshop_get_wishlist_count();
        
        wp_send_json_success(array(
            'message' => sprintf(__('Removed "%s" from wishlist', 'eshop-theme'), $product->get_name()),
            'count' => $wishlist_count,
            'count_label' => eshop_get_wishlist_count_display(),
            'product_id' => $product_id,
            'is_in_wishlist' => false
        ));
    } else {
        wp_send_json_error('Product was not in wishlist');
    }
}

/**
 * Get wishlist count
 */
function eshop_get_wishlist_count() {
    return count(eshop_get_wishlist());
}

/**
 * Check if product is in wishlist
 */
function eshop_is_in_wishlist($product_id) {
    $product_id = absint($product_id);
    if (!$product_id) return false;
    $list = eshop_get_wishlist();
    return in_array($product_id, $list, true);
}

/**
 * Get wishlist products
 */
function eshop_get_wishlist_products() {
    return eshop_get_wishlist();
}

/**
 * Render wishlist dropdown items markup
 */
function eshop_get_wishlist_dropdown_items_html() {
    $wishlist_products = eshop_get_wishlist_products();

    ob_start();

    if (!empty($wishlist_products)) {
        foreach ($wishlist_products as $product_id) {
            $product = wc_get_product($product_id);
            if (!$product) {
                continue;
            }

            $product_name = wp_strip_all_tags($product->get_name());
            $product_price = $product->get_price_html();
            ?>
            <div class="wishlist-item flex items-center space-x-3 py-2 border-b border-gray-100 last:border-b-0">
                <div class="w-12 h-12 flex-shrink-0">
                    <?php echo wp_kses_post($product->get_image(array(48, 48))); ?>
                </div>
                <div class="flex-1 min-w-0">
                    <h4 class="text-sm font-medium text-dark truncate">
                        <a href="<?php echo esc_url(get_permalink($product_id)); ?>" class="hover:text-primary transition-colors">
                            <?php echo esc_html($product_name); ?>
                        </a>
                    </h4>
                    <p class="text-sm text-primary font-semibold"><?php echo wp_kses_post($product_price); ?></p>
                </div>
                <button class="remove-from-wishlist text-gray-400 hover:text-red-500 transition-colors" data-product-id="<?php echo esc_attr($product_id); ?>" aria-label="<?php echo esc_attr(sprintf(__('Remove %s from wishlist', 'eshop-theme'), $product_name)); ?>">
                    <i class="fas fa-times text-xs"></i>
                </button>
            </div>
            <?php
        }
    } else {
        ?>
        <p class="text-gray-500 text-center py-4"><?php esc_html_e('Your wishlist is empty', 'eshop-theme'); ?></p>
        <?php
    }

    return ob_get_clean();
}

/**
 * Enhanced wishlist button for single product page
 */
function eshop_wishlist_button_enhanced($product_id = null, $show_text = true, $button_class = '') {
    if (!$product_id) {
        global $product;
        if (!$product) return;
        $product_id = $product->get_id();
    }
    
    $product = wc_get_product($product_id);
    if (!$product) return;
    
    $is_in_wishlist = is_user_logged_in() ? eshop_is_in_wishlist($product_id) : false;
    $default_class = 'add-to-wishlist modern-action-btn';
    $classes = $default_class . ' ' . $button_class;
    
    if ($is_in_wishlist) {
        $classes .= ' active in-wishlist';
    }
    
    $button_text = !is_user_logged_in()
        ? __('Save', 'eshop-theme')
        : ($is_in_wishlist ? __('Saved', 'eshop-theme') : __('Save', 'eshop-theme'));
    
    if (!is_user_logged_in()) {
        $aria_label = sprintf(__('Log in to save %s to wishlist', 'eshop-theme'), $product->get_name());
    } else {
        $aria_label = $is_in_wishlist 
            ? sprintf(__('Remove %s from wishlist', 'eshop-theme'), $product->get_name())
            : sprintf(__('Add %s to wishlist', 'eshop-theme'), $product->get_name());
    }
    
    ?>
    <button class="<?php echo esc_attr($classes); ?>" 
            data-product-id="<?php echo esc_attr($product_id); ?>"
            data-product-name="<?php echo esc_attr($product->get_name()); ?>"
        <?php if (!is_user_logged_in()) : ?>data-requires-auth="true"<?php endif; ?>
            aria-label="<?php echo esc_attr($aria_label); ?>"
            title="<?php echo esc_attr($aria_label); ?>">
        <svg class="w-5 h-5" 
             fill="<?php echo $is_in_wishlist ? 'currentColor' : 'none'; ?>" 
             stroke="currentColor" 
             viewBox="0 0 24 24" 
             xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" 
                  stroke-linejoin="round" 
                  stroke-width="2" 
                  d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
        </svg>
        <?php if ($show_text) : ?>
            <span class="wishlist-text"><?php echo esc_html($button_text); ?></span>
        <?php endif; ?>
    </button>
    <?php
}

if (!function_exists('eshop_wishlist_button')) {
    /**
     * Legacy shim for previous wishlist button helper
     */
    function eshop_wishlist_button($product_id = null) {
        eshop_wishlist_button_enhanced($product_id, false, 'add-to-wishlist');
    }
}

/**
 * Get wishlist count for display
 */
function eshop_get_wishlist_count_display() {
    $count = eshop_get_wishlist_count();
    if ($count > 99) {
        return '99+';
    }
    return $count;
}

/**
 * Remove product from wishlist (helper function)
 */
function eshop_remove_from_wishlist($product_id) {
    $product_id = absint($product_id);
    if (!$product_id) return false;
    $list = eshop_get_wishlist();
    if (!in_array($product_id, $list, true)) {
        return false;
    }
    $list = array_values(array_diff($list, array($product_id)));
    eshop_set_wishlist($list);
    return true;
}

/**
 * Clear entire wishlist
 */
function eshop_clear_wishlist() {
    // Clear cookie
    eshop_set_cookie_wishlist(array());
    // Clear user meta if logged in
    if (is_user_logged_in()) {
        $user_id = get_current_user_id();
        delete_user_meta($user_id, 'eshop_wishlist');
    }
}

/**
 * Get wishlist products with full product objects
 */
function eshop_get_wishlist_products_full() {
    $product_ids = eshop_get_wishlist_products();
    $products = array();
    
    foreach ($product_ids as $product_id) {
        $product = wc_get_product($product_id);
        if ($product && $product->exists()) {
            $products[] = $product;
        }
    }
    
    return $products;
}

/**
 * Add wishlist button to product loops
 */
add_action('woocommerce_after_shop_loop_item', 'eshop_add_wishlist_to_loop', 15);
function eshop_add_wishlist_to_loop() {
    global $product;
    echo '<div class="product-actions flex items-center justify-between mt-2">';
    echo '<div class="flex-1"></div>';
    eshop_wishlist_button($product->get_id());
    echo '</div>';
}

/**
 * Enhanced single product wishlist integration
 * Called via woocommerce_single_product_summary hook in content-single-product.php
 */
function eshop_add_wishlist_to_single_enhanced() {
    global $product;
    if (!$product) return;
    
    // This is handled in the content-single-product.php template
    // We're keeping this function for backwards compatibility
}