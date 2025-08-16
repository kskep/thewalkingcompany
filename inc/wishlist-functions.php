<?php
/**
 * Wishlist Functionality
 * 
 * @package E-Shop Theme
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
}
add_action('init', 'eshop_init_wishlist');

/**
 * Add to wishlist AJAX handler
 */
function eshop_add_to_wishlist() {
    check_ajax_referer('eshop_nonce', 'nonce');
    
    $product_id = intval($_POST['product_id']);
    if (!$product_id) {
        wp_die();
    }
    
    if (!isset($_SESSION['eshop_wishlist'])) {
        $_SESSION['eshop_wishlist'] = array();
    }
    
    if (!in_array($product_id, $_SESSION['eshop_wishlist'])) {
        $_SESSION['eshop_wishlist'][] = $product_id;
        $action = 'added';
    } else {
        $_SESSION['eshop_wishlist'] = array_diff($_SESSION['eshop_wishlist'], array($product_id));
        $action = 'removed';
    }
    
    wp_send_json_success(array(
        'action' => $action,
        'count' => count($_SESSION['eshop_wishlist'])
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
 * Display wishlist button
 */
function eshop_wishlist_button($product_id = null) {
    if (!$product_id) {
        global $product;
        $product_id = $product->get_id();
    }
    
    $is_in_wishlist = eshop_is_in_wishlist($product_id);
    $icon_class = $is_in_wishlist ? 'fas fa-heart text-red-500' : 'far fa-heart text-gray-400';
    $button_class = $is_in_wishlist ? 'add-to-wishlist in-wishlist' : 'add-to-wishlist';
    
    ?>
    <button class="<?php echo $button_class; ?> w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-sm hover:shadow-md transition-all duration-200 hover:scale-110" data-product-id="<?php echo $product_id; ?>" title="<?php _e('Add to Wishlist', 'eshop-theme'); ?>">
        <i class="<?php echo $icon_class; ?> text-sm"></i>
    </button>
    <?php
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
 * Add wishlist button to single product page
 */
add_action('woocommerce_single_product_summary', 'eshop_add_wishlist_to_single', 35);
function eshop_add_wishlist_to_single() {
    global $product;
    echo '<div class="single-product-wishlist mt-4">';
    echo '<button class="add-to-wishlist inline-flex items-center text-gray-600 hover:text-primary transition-colors duration-200" data-product-id="' . $product->get_id() . '">';
    echo '<i class="' . (eshop_is_in_wishlist($product->get_id()) ? 'fas' : 'far') . ' fa-heart mr-2"></i>';
    echo __('Add to Wishlist', 'eshop-theme');
    echo '</button>';
    echo '</div>';
}