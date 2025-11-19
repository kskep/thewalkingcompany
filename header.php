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

                    <!-- Mobile Menu Toggle (visible on mobile only, left side) -->
                    <button class="mobile-menu-toggle lg:hidden p-2 text-dark hover:text-primary transition-colors duration-200" aria-label="Menu">
                        <i class="fas fa-bars text-xl"></i>
                    </button>

                    <!-- Language Switcher (hidden on mobile) -->
                    <div class="language-switcher hidden lg:flex items-center space-x-2 text-sm">
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

                    <!-- Header Actions (hidden on mobile, visible on desktop) -->
                    <?php get_template_part('template-parts/header/actions'); ?>
                </div>
            </div>
        </div>

        <!-- Bottom Row: Navigation Menu (desktop only) -->
        <div class="header-bottom hidden lg:block">
            <div class="container mx-auto px-4">
                <div class="flex justify-center py-4">
                    <!-- Main Navigation -->
                    <nav id="site-navigation" class="main-navigation">
                        <?php
                        wp_nav_menu(array(
                            'theme_location' => 'primary',
                            'menu_id' => 'primary-menu',
                            'menu_class' => 'flex space-x-8 text-sm font-medium uppercase tracking-wide',
                            'container' => false,
                            'fallback_cb' => false,
                            'walker' => new Eshop_Mega_Menu_Walker(),
                        ));
                        ?>
                    </nav>
                </div>
            </div>
        </div>
            
        <!-- Mobile Navigation -->
        <nav id="mobile-navigation" class="mobile-navigation lg:hidden hidden">
            <div class="mobile-menu-wrapper bg-white border-t border-gray-200 py-4">
                <div class="container mx-auto px-4">
                    <!-- Main Menu Items -->
                    <?php
                    wp_nav_menu(array(
                        'theme_location' => 'primary',
                        'menu_id' => 'mobile-menu',
                        'menu_class' => 'mobile-menu space-y-3',
                        'container' => false,
                        'fallback_cb' => false,
                    ));
                    ?>
                    
                    <!-- Creative Divider -->
                    <div class="mobile-menu-divider my-6 flex items-center justify-center">
                        <div class="flex-1 h-px bg-gradient-to-r from-transparent via-gray-300 to-transparent"></div>
                        <i class="fas fa-heart text-primary mx-4 text-xs"></i>
                        <div class="flex-1 h-px bg-gradient-to-r from-transparent via-gray-300 to-transparent"></div>
                    </div>
                    
                    <!-- Mobile Utility Menu Items -->
                    <div class="mobile-utility-menu">
                        
                        <?php if (class_exists('WooCommerce')) : ?>
                            <!-- Wishlist -->
                            <a href="<?php echo home_url('/wishlist'); ?>" class="mobile-utility-item">
                                <i class="far fa-heart"></i>
                                <span class="flex-1">
                                    <?php _e('WISHLIST', 'eshop-theme'); ?>
                                </span>
                                <?php if (eshop_get_wishlist_count() > 0) : ?>
                                    <span class="bg-primary text-white text-xs rounded-full px-2 py-1 font-semibold">
                                        <?php echo eshop_get_wishlist_count(); ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                            
                            <!-- My Account - Single Item -->
                            <?php if (is_user_logged_in()) : ?>
                                <a href="<?php echo wc_get_account_endpoint_url('dashboard'); ?>" class="mobile-utility-item">
                                    <i class="far fa-user"></i>
                                    <span class="flex-1">
                                        <?php _e('MY ACCOUNT', 'eshop-theme'); ?>
                                    </span>
                                </a>
                            <?php else : ?>
                                <a href="#" class="mobile-utility-item modal-trigger" data-action="login">
                                    <i class="fas fa-sign-in-alt"></i>
                                    <span class="flex-1">
                                        <?php _e('LOGIN / REGISTER', 'eshop-theme'); ?>
                                    </span>
                                </a>
                            <?php endif; ?>
                            
                            <!-- Shopping Cart -->
                            <a href="<?php echo wc_get_cart_url(); ?>" class="mobile-utility-item">
                                <i class="fas fa-shopping-bag"></i>
                                <span class="flex-1">
                                    <?php _e('SHOPPING CART', 'eshop-theme'); ?>
                                </span>
                                <?php if (WC()->cart->get_cart_contents_count() > 0) : ?>
                                    <span class="bg-primary text-white text-xs rounded-full px-2 py-1 font-semibold">
                                        <?php echo WC()->cart->get_cart_contents_count(); ?>
                                    </span>
                                <?php endif; ?>
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Mobile Language Switcher -->
                    <div class="mobile-language-switcher flex items-center justify-center space-x-2 text-sm pt-4 mt-4 border-t border-gray-200">
                        <a href="#" class="text-gray-600 hover:text-primary transition-colors font-medium">EN</a>
                        <span class="text-gray-300">|</span>
                        <a href="#" class="text-gray-600 hover:text-primary transition-colors font-medium">EL</a>
                    </div>
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

    <?php
    // Include authentication modal for logged-out users
    if (!is_user_logged_in()) {
        get_template_part('template-parts/components/auth-modal');
    }
    ?>

    <div id="content" class="site-content">