<?php
/**
 * Enhanced Wishlist Functionality - 2025 Standards
 * 
 * @package thewalkingtheme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Initialize wishlist session
 */
function eshop_init_wishlist() {
    if (!session_id()) {
        session_start();
    }
    if (!isset($_SESSION['eshop_wishlist'])) {
        $_SESSION['eshop_wishlist'] = array();
    }
    
    // For logged-in users, sync with user meta
    if (is_user_logged_in()) {
        eshop_sync_wishlist_with_user_meta();
    }
}
add_action('init', 'eshop_init_wishlist');

/**
 * Sync session wishlist with user meta for logged-in users
 */
function eshop_sync_wishlist_with_user_meta() {
    $user_id = get_current_user_id();
    if (!$user_id) return;
    
    $user_wishlist = get_user_meta($user_id, 'eshop_wishlist', true);
    if (!is_array($user_wishlist)) {
        $user_wishlist = array();
    }
    
    // Merge session wishlist with user's saved wishlist
    if (isset($_SESSION['eshop_wishlist']) && is_array($_SESSION['eshop_wishlist'])) {
        $merged_wishlist = array_unique(array_merge($user_wishlist, $_SESSION['eshop_wishlist']));
        update_user_meta($user_id, 'eshop_wishlist', $merged_wishlist);
        $_SESSION['eshop_wishlist'] = $merged_wishlist;
    } else {
        $_SESSION['eshop_wishlist'] = $user_wishlist;
    }
}

/**
 * Enhanced Add to wishlist AJAX handler
 */
function eshop_add_to_wishlist() {
    check_ajax_referer('eshop_nonce', 'nonce');
    
    $product_id = intval($_POST['product_id']);
    if (!$product_id) {
        wp_send_json_error(array(
            'message' => __('Invalid product ID', 'thewalkingtheme')
        ));
    }
    
    // Check if product exists
    $product = wc_get_product($product_id);
    if (!$product) {
        wp_send_json_error(array(
            'message' => __('Product not found', 'thewalkingtheme')
        ));
    }
    
    if (!isset($_SESSION['eshop_wishlist'])) {
        $_SESSION['eshop_wishlist'] = array();
    }
    
    $action = '';
    $message = '';
    
    if (!in_array($product_id, $_SESSION['eshop_wishlist'])) {
        $_SESSION['eshop_wishlist'][] = $product_id;
        $action = 'added';
        $message = sprintf(__('%s added to wishlist', 'thewalkingtheme'), $product->get_name());
        
        // Save to user meta if logged in
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            update_user_meta($user_id, 'eshop_wishlist', $_SESSION['eshop_wishlist']);
        }
    } else {
        $_SESSION['eshop_wishlist'] = array_diff($_SESSION['eshop_wishlist'], array($product_id));
        $action = 'removed';
        $message = sprintf(__('%s removed from wishlist', 'thewalkingtheme'), $product->get_name());
        
        // Update user meta if logged in
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            update_user_meta($user_id, 'eshop_wishlist', $_SESSION['eshop_wishlist']);
        }
    }
    
    wp_send_json_success(array(
        'action' => $action,
        'message' => $message,
        'count' => count($_SESSION['eshop_wishlist']),
        'product_id' => $product_id,
        'product_name' => $product->get_name(),
        'is_in_wishlist' => $action === 'added'
    ));
}
add_action('wp_ajax_add_to_wishlist', 'eshop_add_to_wishlist');
add_action('wp_ajax_nopriv_add_to_wishlist', 'eshop_add_to_wishlist');

/**
 * Get wishlist count
 */
function eshop_get_wishlist_count() {
    if (!isset($_SESSION['eshop_wishlist'])) {
        return 0;
    }
    return count($_SESSION['eshop_wishlist']);
}

/**
 * Check if product is in wishlist
 */
function eshop_is_in_wishlist($product_id) {
    if (!isset($_SESSION['eshop_wishlist'])) {
        return false;
    }
    return in_array($product_id, $_SESSION['eshop_wishlist']);
}

/**
 * Get wishlist products
 */
function eshop_get_wishlist_products() {
    if (!isset($_SESSION['eshop_wishlist']) || empty($_SESSION['eshop_wishlist'])) {
        return array();
    }
    return $_SESSION['eshop_wishlist'];
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
    
    $is_in_wishlist = eshop_is_in_wishlist($product_id);
    $default_class = 'add-to-wishlist modern-action-btn';
    $classes = $default_class . ' ' . $button_class;
    
    if ($is_in_wishlist) {
        $classes .= ' active in-wishlist';
    }
    
    $button_text = $is_in_wishlist 
        ? __('Saved', 'thewalkingtheme') 
        : __('Save', 'thewalkingtheme');
    
    $aria_label = $is_in_wishlist 
        ? sprintf(__('Remove %s from wishlist', 'thewalkingtheme'), $product->get_name())
        : sprintf(__('Add %s to wishlist', 'thewalkingtheme'), $product->get_name());
    
    ?>
    <button class="<?php echo esc_attr($classes); ?>" 
            data-product-id="<?php echo esc_attr($product_id); ?>"
            data-product-name="<?php echo esc_attr($product->get_name()); ?>"
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
    if (!isset($_SESSION['eshop_wishlist'])) {
        return false;
    }
    
    if (in_array($product_id, $_SESSION['eshop_wishlist'])) {
        $_SESSION['eshop_wishlist'] = array_diff($_SESSION['eshop_wishlist'], array($product_id));
        
        // Update user meta if logged in
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            update_user_meta($user_id, 'eshop_wishlist', $_SESSION['eshop_wishlist']);
        }
        
        return true;
    }
    
    return false;
}

/**
 * Clear entire wishlist
 */
function eshop_clear_wishlist() {
    $_SESSION['eshop_wishlist'] = array();
    
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