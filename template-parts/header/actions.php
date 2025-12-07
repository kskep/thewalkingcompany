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
        <div class="minicart-dropdown absolute right-0 top-full mt-2 w-[360px] md:w-[520px] max-h-[85vh] bg-white border border-gray-100 shadow-xl z-50 hidden rounded-md overflow-hidden ring-1 ring-black ring-opacity-5">
            <?php get_template_part('template-parts/header/minicart-content'); ?>
        </div>
    </div>
    <?php endif; ?>
</div>
