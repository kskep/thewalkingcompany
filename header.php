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
        <div class="container mx-auto px-4">
            <div class="flex items-center justify-between py-4">
                
                <!-- Logo -->
                <div class="site-branding">
                    <?php if (has_custom_logo()) : ?>
                        <div class="custom-logo">
                            <?php the_custom_logo(); ?>
                        </div>
                    <?php else : ?>
                        <h1 class="site-title text-2xl font-bold">
                            <a href="<?php echo esc_url(home_url('/')); ?>" class="text-primary hover:text-primary-dark transition-colors duration-200">
                                <?php bloginfo('name'); ?>
                            </a>
                        </h1>
                        <?php
                        $description = get_bloginfo('description', 'display');
                        if ($description || is_customize_preview()) :
                        ?>
                            <p class="site-description text-sm text-gray-600 mt-1"><?php echo $description; ?></p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                
                <!-- Navigation -->
                <nav id="site-navigation" class="main-navigation hidden lg:block">
                    <?php
                    wp_nav_menu(array(
                        'theme_location' => 'primary',
                        'menu_id' => 'primary-menu',
                        'menu_class' => 'flex space-x-6',
                        'container' => false,
                        'fallback_cb' => false,
                    ));
                    ?>
                </nav>
                
                <!-- Header Actions -->
                <div class="header-actions flex items-center space-x-4">
                    
                    <!-- Search -->
                    <button class="search-toggle p-2 text-dark hover:text-primary transition-colors duration-200" aria-label="Search">
                        <i class="fas fa-search icon"></i>
                    </button>
                    
                    <!-- WooCommerce Cart (if WooCommerce is active) -->
                    <?php if (class_exists('WooCommerce')) : ?>
                        <a href="<?php echo wc_get_cart_url(); ?>" class="cart-link p-2 text-dark hover:text-primary transition-colors duration-200 relative">
                            <i class="fas fa-shopping-cart icon"></i>
                            <?php if (WC()->cart->get_cart_contents_count() > 0) : ?>
                                <span class="cart-count absolute -top-1 -right-1 bg-primary text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">
                                    <?php echo WC()->cart->get_cart_contents_count(); ?>
                                </span>
                            <?php endif; ?>
                        </a>
                    <?php endif; ?>
                    
                    <!-- User Account -->
                    <?php if (class_exists('WooCommerce')) : ?>
                        <a href="<?php echo get_permalink(get_option('woocommerce_myaccount_page_id')); ?>" class="account-link p-2 text-dark hover:text-primary transition-colors duration-200">
                            <i class="far fa-user icon"></i>
                        </a>
                    <?php endif; ?>
                    
                    <!-- Mobile Menu Toggle -->
                    <button class="mobile-menu-toggle lg:hidden p-2 text-dark hover:text-primary transition-colors duration-200" aria-label="Menu">
                        <i class="fas fa-bars icon"></i>
                    </button>
                </div>
            </div>
            
            <!-- Mobile Navigation -->
            <nav id="mobile-navigation" class="mobile-navigation lg:hidden hidden">
                <div class="mobile-menu-wrapper bg-white border-t border-gray-200 py-4">
                    <?php
                    wp_nav_menu(array(
                        'theme_location' => 'primary',
                        'menu_id' => 'mobile-menu',
                        'menu_class' => 'mobile-menu space-y-2',
                        'container' => false,
                        'fallback_cb' => false,
                    ));
                    ?>
                </div>
            </nav>
            
            <!-- Search Form -->
            <div id="search-form" class="search-form-wrapper hidden">
                <div class="search-form-container bg-white border-t border-gray-200 py-4">
                    <form role="search" method="get" class="search-form flex" action="<?php echo home_url('/'); ?>">
                        <div class="flex-1 relative">
                            <input type="search" 
                                   class="search-field w-full px-4 py-2 border border-gray-300 rounded-l-md focus:outline-none focus:border-primary" 
                                   placeholder="<?php echo esc_attr_x('Search products, posts...', 'placeholder', 'eshop-theme'); ?>" 
                                   value="<?php echo get_search_query(); ?>" 
                                   name="s" />
                        </div>
                        <button type="submit" class="search-submit bg-primary text-white px-6 py-2 rounded-r-md hover:bg-primary-dark transition-colors duration-200">
                            <i class="fas fa-search icon-sm"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </header>

    <div id="content" class="site-content">