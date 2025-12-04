<?php
/**
 * Header Actions Template Part
 *
 * @package E-Shop Theme
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="header-actions hidden lg:flex items-center space-x-2">
    
    <!-- Search - Temporarily hidden but keeping functionality -->
    <!-- <button class="search-toggle p-2 text-dark hover:text-primary transition-colors duration-200" aria-label="Search">
        <i class="fas fa-search icon"></i>
    </button> -->
    
    <!-- Wishlist -->
    <?php if (class_exists('WooCommerce')) : ?>
    <div class="wishlist-wrapper relative">
        <?php
        $wishlist_count        = eshop_get_wishlist_count();
        $wishlist_count_label  = eshop_get_wishlist_count_display();
        $wishlist_products     = eshop_get_wishlist_products();
        $wishlist_has_items    = !empty($wishlist_products);
        ?>
        <button class="wishlist-toggle p-2 text-dark hover:text-primary transition-colors duration-200 relative" aria-label="Wishlist">
            <i class="far fa-heart icon"></i>
            <span class="wishlist-count absolute -top-1 -right-1 bg-primary text-white text-xs rounded-full w-5 h-5 flex items-center justify-center <?php echo $wishlist_count > 0 ? '' : 'hidden'; ?>">
                <?php echo esc_html($wishlist_count_label); ?>
            </span>
        </button>
        
        <!-- Wishlist Dropdown -->
        <div class="wishlist-dropdown absolute right-0 top-full mt-2 w-80 bg-white border border-gray-200 shadow-lg z-50 hidden">
            <div class="p-4">
                <h3 class="text-lg font-semibold mb-3 text-dark"><?php _e('Wishlist', 'eshop-theme'); ?></h3>
                <div class="wishlist-items">
                    <?php echo eshop_get_wishlist_dropdown_items_html(); ?>
                </div>
                <div class="wishlist-view-all mt-4 pt-3 border-t border-gray-200 <?php echo $wishlist_has_items ? '' : 'hidden'; ?>">
                    <a href="<?php echo esc_url(home_url('/wishlist')); ?>" class="block w-full text-center bg-primary text-white py-2 hover:bg-primary-dark transition-colors duration-200">
                        <?php _e('View All', 'eshop-theme'); ?>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Account Dropdown -->
    <?php if (class_exists('WooCommerce')) : ?>
    <div class="account-wrapper relative">
        <button class="account-toggle p-2 text-dark hover:text-primary transition-colors duration-200" aria-label="Account">
            <i class="far fa-user icon"></i>
        </button>
        
        <!-- Account Dropdown -->
        <div class="account-dropdown absolute right-0 top-full mt-2 w-48 bg-white border border-gray-200 shadow-lg z-50 hidden">
            <div class="py-2">
                <?php
                $account_items = eshop_get_account_menu_items();
                foreach ($account_items as $key => $item) :
                    $css_class = 'block px-4 py-2 text-sm text-dark hover:bg-gray-50 hover:text-primary transition-colors duration-200';
                    $data_action = '';
                    
                    if (isset($item['action'])) {
                        $css_class .= ' modal-trigger';
                        $data_action = ' data-action="' . esc_attr($item['action']) . '"';
                    }
                ?>
                    <a href="<?php echo esc_url($item['url']); ?>" class="<?php echo $css_class; ?>"<?php echo $data_action; ?>>
                        <?php echo esc_html($item['title']); ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Enhanced Minicart -->
    <?php if (class_exists('WooCommerce')) : ?>
    <div class="minicart-wrapper relative">
        <button class="minicart-toggle p-2 text-dark hover:text-primary transition-colors duration-200 relative" aria-label="Shopping Cart">
            <i class="fas fa-shopping-bag icon"></i>
            <span class="cart-count absolute -top-1 -right-1 bg-primary text-white text-xs rounded-full w-5 h-5 flex items-center justify-center <?php echo WC()->cart->get_cart_contents_count() > 0 ? '' : 'hidden'; ?>">
                <?php echo WC()->cart->get_cart_contents_count(); ?>
            </span>
        </button>
        
        <!-- Minicart Dropdown -->
        <div class="minicart-dropdown absolute right-0 top-full mt-2 w-80 bg-white border border-gray-200 shadow-lg z-50 hidden">
            <div class="p-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-lg font-semibold text-dark"><?php _e('Shopping Cart', 'eshop-theme'); ?></h3>
                    <span class="cart-total text-primary font-semibold"><?php echo WC()->cart->get_cart_total(); ?></span>
                </div>
                
                <div class="minicart-items max-h-64 overflow-y-auto">
                    <?php if (WC()->cart->get_cart_contents_count() > 0) : ?>
                        <?php foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) :
                            $product = $cart_item['data'];
                            $product_id = $cart_item['product_id'];
                            $quantity = $cart_item['quantity'];
                        ?>
                            <div class="minicart-item flex items-center space-x-3 py-3 border-b border-gray-100 last:border-b-0" data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>">
                                <div class="w-12 h-12 flex-shrink-0">
                                    <?php echo $product->get_image(array(48, 48)); ?>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-medium text-dark truncate"><?php echo $product->get_name(); ?></h4>
                                    <div class="flex items-center justify-between mt-1">
                                        <div class="minicart-qty-controls flex items-center">
                                            <button type="button" class="minicart-qty-btn qty-minus p-1 text-gray-400 hover:text-primary" data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>" aria-label="Decrease quantity">
                                                <i class="fas fa-minus text-xs"></i>
                                            </button>
                                            <span class="qty-value mx-2 text-sm font-medium"><?php echo $quantity; ?></span>
                                            <button type="button" class="minicart-qty-btn qty-plus p-1 text-gray-400 hover:text-primary" data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>" aria-label="Increase quantity">
                                                <i class="fas fa-plus text-xs"></i>
                                            </button>
                                        </div>
                                        <span class="text-sm text-primary font-semibold"><?php echo wc_price($product->get_price() * $quantity); ?></span>
                                    </div>
                                </div>
                                <button class="remove-from-cart text-gray-400 hover:text-red-500 transition-colors" data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>" aria-label="Remove item">
                                    <i class="fas fa-times text-xs"></i>
                                </button>
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
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
