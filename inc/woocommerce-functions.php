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
 * Product Color Variants Helper
 */
function eshop_get_product_color_variants($product, $limit = 4) {
    if (!$product->is_type('variable')) {
        return array();
    }
    
    $available_variations = $product->get_available_variations();
    $color_attribute = null;
    $colors = array();
    
    // Find color attribute
    foreach ($product->get_variation_attributes() as $attribute_name => $options) {
        $attr_lower = strtolower($attribute_name);
        if (strpos($attr_lower, 'color') !== false || strpos($attr_lower, 'colour') !== false) {
            $color_attribute = $attribute_name;
            break;
        }
    }
    
    if (!$color_attribute || empty($available_variations)) {
        return array();
    }
    
    $colors_shown = array();
    $color_count = 0;
    
    foreach ($available_variations as $variation) {
        if ($color_count >= $limit) break;
        
        $color_value = $variation['attributes']['attribute_' . strtolower(str_replace('pa_', '', $color_attribute))];
        
        if (!in_array($color_value, $colors_shown) && $color_value) {
            $colors_shown[] = $color_value;
            
            // Get color hex value
            $color_hex = '#ccc'; // Default
            if (taxonomy_exists($color_attribute)) {
                $term = get_term_by('slug', $color_value, $color_attribute);
                if ($term) {
                    $term_color = get_term_meta($term->term_id, 'color', true);
                    if ($term_color) {
                        $color_hex = $term_color;
                    } else {
                        // Fallback color mapping
                        $color_map = array(
                            'black' => '#000000',
                            'white' => '#ffffff',
                            'red' => '#dc2626',
                            'blue' => '#2563eb',
                            'green' => '#16a34a',
                            'yellow' => '#eab308',
                            'pink' => '#ec4899',
                            'purple' => '#9333ea',
                            'gray' => '#6b7280',
                            'brown' => '#92400e',
                            'beige' => '#f5f5dc',
                            'navy' => '#1e3a8a'
                        );
                        
                        $color_lower = strtolower($color_value);
                        foreach ($color_map as $name => $hex) {
                            if (strpos($color_lower, $name) !== false) {
                                $color_hex = $hex;
                                break;
                            }
                        }
                    }
                }
            }
            
            $colors[] = array(
                'name' => $color_value,
                'hex' => $color_hex
            );
            
            $color_count++;
        }
    }
    
    return $colors;
}

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