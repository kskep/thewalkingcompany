<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div id="page" class="site">
    
    <!-- Header -->
    <header id="masthead" class="site-header">

        <!-- Top Row: Language | Logo | Account/Cart -->
        <div class="header-top border-b border-gray-100">
            <div class="container mx-auto px-4">
                <div class="flex items-center justify-between py-3">

                    <!-- Language Switcher -->
                    <div class="language-switcher flex items-center space-x-2 text-sm">
                        <a href="#" class="text-gray-600 hover:text-primary transition-colors font-medium">EN</a>
                        <span class="text-gray-300">|</span>
                        <a href="#" class="text-gray-600 hover:text-primary transition-colors font-medium">EL</a>
                    </div>

                    <!-- Logo -->
                    <div class="site-branding">
                        <a href="<?php echo esc_url(home_url('/')); ?>" class="block">
                            <img src="https://walk.thewebplace.gr/wp-content/uploads/2023/01/twc-logo-pink.png"
                                 alt="<?php bloginfo('name'); ?>"
                                 class="h-12 w-auto">
                        </a>
                    </div>

                    <!-- Header Actions -->
                    <div class="header-actions flex items-center space-x-2">
                    
                    <!-- Search -->
                    <button class="search-toggle p-2 text-dark hover:text-primary transition-colors duration-200" aria-label="Search">
                        <i class="fas fa-search icon"></i>
                    </button>
                    
                    <!-- Wishlist -->
                    <?php if (class_exists('WooCommerce')) : ?>
                        <div class="wishlist-wrapper relative">
                            <button class="wishlist-toggle p-2 text-dark hover:text-primary transition-colors duration-200 relative" aria-label="Wishlist">
                                <i class="far fa-heart icon"></i>
                                <span class="wishlist-count absolute -top-1 -right-1 bg-primary text-white text-xs rounded-full w-5 h-5 flex items-center justify-center <?php echo eshop_get_wishlist_count() > 0 ? '' : 'hidden'; ?>">
                                    <?php echo eshop_get_wishlist_count(); ?>
                                </span>
                            </button>
                            
                            <!-- Wishlist Dropdown -->
                            <div class="wishlist-dropdown absolute right-0 top-full mt-2 w-80 bg-white border border-gray-200 shadow-lg z-50 hidden">
                                <div class="p-4">
                                    <h3 class="text-lg font-semibold mb-3 text-dark"><?php _e('Wishlist', 'eshop-theme'); ?></h3>
                                    <div class="wishlist-items">
                                        <?php
                                        $wishlist_products = eshop_get_wishlist_products();
                                        if (!empty($wishlist_products)) :
                                            foreach ($wishlist_products as $product_id) :
                                                $product = wc_get_product($product_id);
                                                if ($product) :
                                        ?>
                                            <div class="wishlist-item flex items-center space-x-3 py-2 border-b border-gray-100 last:border-b-0">
                                                <div class="w-12 h-12 flex-shrink-0">
                                                    <?php echo $product->get_image(array(48, 48)); ?>
                                                </div>
                                                <div class="flex-1 min-w-0">
                                                    <h4 class="text-sm font-medium text-dark truncate"><?php echo $product->get_name(); ?></h4>
                                                    <p class="text-sm text-primary font-semibold"><?php echo $product->get_price_html(); ?></p>
                                                </div>
                                                <button class="remove-from-wishlist text-gray-400 hover:text-red-500 transition-colors" data-product-id="<?php echo $product_id; ?>">
                                                    <i class="fas fa-times text-xs"></i>
                                                </button>
                                            </div>
                                        <?php
                                                endif;
                                            endforeach;
                                        else :
                                        ?>
                                            <p class="text-gray-500 text-center py-4"><?php _e('Your wishlist is empty', 'eshop-theme'); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($wishlist_products)) : ?>
                                        <div class="mt-4 pt-3 border-t border-gray-200">
                                            <a href="<?php echo home_url('/wishlist'); ?>" class="block w-full text-center bg-primary text-white py-2 hover:bg-primary-dark transition-colors duration-200">
                                                <?php _e('View All', 'eshop-theme'); ?>
                                            </a>
                                        </div>
                                    <?php endif; ?>
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
                                    ?>
                                        <a href="<?php echo esc_url($item['url']); ?>" class="block px-4 py-2 text-sm text-dark hover:bg-gray-50 hover:text-primary transition-colors duration-200">
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
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Bottom Row: Navigation Menu -->
        <div class="header-bottom">
            <div class="container mx-auto px-4">
                <div class="flex justify-center py-4">
                    <!-- Main Navigation -->
                    <nav id="site-navigation" class="main-navigation hidden lg:block">
                        <?php
                        // Debug: Check if menu location has a menu assigned
                        $locations = get_nav_menu_locations();
                        $menu_id = isset($locations['primary']) ? $locations['primary'] : 0;

                        if ($menu_id && wp_get_nav_menu_object($menu_id)) {
                            // Menu exists and is assigned
                            wp_nav_menu(array(
                                'theme_location' => 'primary',
                                'menu_id' => 'primary-menu',
                                'menu_class' => 'flex space-x-8 text-sm font-medium uppercase tracking-wide',
                                'container' => false,
                                'fallback_cb' => false,
                            ));
                        } else {
                            // Debug fallback - show if no menu is assigned
                            echo '<div class="debug-menu-info text-red-500 text-sm">';
                            echo 'Debug: No menu assigned to primary location. ';
                            echo 'Menu ID: ' . $menu_id . ' | ';
                            echo 'Available locations: ' . implode(', ', array_keys($locations));
                            echo '</div>';
                        }
                        ?>
                    </nav>

                    <!-- Mobile Menu Toggle (visible on mobile) -->
                    <button class="mobile-menu-toggle lg:hidden p-2 text-dark hover:text-primary transition-colors duration-200" aria-label="Menu">
                        <i class="fas fa-bars icon"></i>
                        <span class="ml-2 text-sm font-medium">MENU</span>
                    </button>
                </div>
            </div>
        </div>
            
        <!-- Mobile Navigation -->
        <nav id="mobile-navigation" class="mobile-navigation lg:hidden hidden">
            <div class="mobile-menu-wrapper bg-white border-t border-gray-200 py-4">
                <div class="container mx-auto px-4">
                    <?php
                    // Use same menu check as desktop
                    $locations = get_nav_menu_locations();
                    $menu_id = isset($locations['primary']) ? $locations['primary'] : 0;

                    if ($menu_id && wp_get_nav_menu_object($menu_id)) {
                        wp_nav_menu(array(
                            'theme_location' => 'primary',
                            'menu_id' => 'mobile-menu',
                            'menu_class' => 'mobile-menu space-y-3',
                            'container' => false,
                            'fallback_cb' => false,
                        ));
                    } else {
                        echo '<div class="debug-menu-info text-red-500 text-sm">Mobile: No menu assigned to primary location.</div>';
                    }
                    ?>
                </div>
            </div>
        </nav>
            
            <!-- Search Form -->
            <div id="search-form" class="search-form-wrapper hidden">
                <div class="search-form-container bg-white border-t border-gray-200 py-4">
                    <form role="search" method="get" class="search-form flex" action="<?php echo home_url('/'); ?>">
                        <div class="flex-1 relative">
                            <input type="search" 
                                   class="search-field w-full px-4 py-2 border border-gray-300 focus:outline-none focus:border-primary" 
                                   placeholder="<?php echo esc_attr_x('Search products, posts...', 'placeholder', 'eshop-theme'); ?>" 
                                   value="<?php echo get_search_query(); ?>" 
                                   name="s" />
                        </div>
                        <button type="submit" class="search-submit bg-primary text-white px-6 py-2 hover:bg-primary-dark transition-colors duration-200">
                            <i class="fas fa-search icon-sm"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <div id="content" class="site-content">