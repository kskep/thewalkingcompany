<?php
/**
 * E-Shop Theme Functions
 * 
 * @package E-Shop Theme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Theme Setup
 */
function eshop_theme_setup() {
    // Add theme support for various features
    add_theme_support('post-thumbnails');
    add_theme_support('title-tag');
    add_theme_support('custom-logo');
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ));
    
    // Add WooCommerce support
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
    
    // Register navigation menus
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'eshop-theme'),
        'footer' => __('Footer Menu', 'eshop-theme'),
    ));
}
add_action('after_setup_theme', 'eshop_theme_setup');

/**
 * Enqueue Scripts and Styles
 */
function eshop_theme_scripts() {
    // Enqueue main stylesheet
    wp_enqueue_style('eshop-theme-style', get_stylesheet_uri(), array(), '1.0.0');
    
    // Enqueue Font Awesome for icons
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', array(), '6.4.0');
    
    // Enqueue Feather Icons
    wp_enqueue_style('feather-icons', 'https://cdn.jsdelivr.net/npm/feather-icons@4.29.0/dist/feather.css', array(), '4.29.0');

    // Swiper slider CSS (modern slider)
    wp_enqueue_style('swiper', 'https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css', array(), '10.3.1');
    
    // Enqueue Heroicons (via CDN)
    wp_enqueue_script('heroicons', 'https://cdn.jsdelivr.net/npm/heroicons@2.0.18/24/outline/index.js', array(), '2.0.18', true);

    // GSAP for animations
    wp_enqueue_script('gsap', 'https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js', array(), '3.12.5', true);
    
    // Swiper slider JS
    wp_enqueue_script('swiper', 'https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js', array(), '10.3.1', true);
    
    // Enqueue custom JavaScript
    wp_enqueue_script('eshop-theme-script', get_template_directory_uri() . '/js/theme.js', array('jquery', 'gsap', 'swiper'), '1.0.0', true);
    
    // Localize script for AJAX
    wp_localize_script('eshop-theme-script', 'eshop_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('eshop_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'eshop_theme_scripts');

/**
 * Add custom colors to Gutenberg
 */
function eshop_theme_gutenberg_colors() {
    add_theme_support('editor-color-palette', array(
        array(
            'name' => __('Primary Pink', 'eshop-theme'),
            'slug' => 'primary-pink',
            'color' => '#EE81B3',
        ),
        array(
            'name' => __('Primary Pink Light', 'eshop-theme'),
            'slug' => 'primary-pink-light',
            'color' => '#F2A1C7',
        ),
        array(
            'name' => __('Primary Pink Dark', 'eshop-theme'),
            'slug' => 'primary-pink-dark',
            'color' => '#E55A9F',
        ),
        array(
            'name' => __('Dark Gray', 'eshop-theme'),
            'slug' => 'dark-gray',
            'color' => '#2D3748',
        ),
        array(
            'name' => __('Darker Gray', 'eshop-theme'),
            'slug' => 'darker-gray',
            'color' => '#1A202C',
        ),
        array(
            'name' => __('White', 'eshop-theme'),
            'slug' => 'white',
            'color' => '#FFFFFF',
        ),
    ));
}
add_action('after_setup_theme', 'eshop_theme_gutenberg_colors');

/**
 * Register Widget Areas
 */
function eshop_theme_widgets_init() {
    register_sidebar(array(
        'name' => __('Sidebar', 'eshop-theme'),
        'id' => 'sidebar-1',
        'description' => __('Add widgets here.', 'eshop-theme'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget' => '</section>',
        'before_title' => '<h2 class="widget-title">',
        'after_title' => '</h2>',
    ));
    
    register_sidebar(array(
        'name' => __('Footer Widget Area', 'eshop-theme'),
        'id' => 'footer-widgets',
        'description' => __('Add footer widgets here.', 'eshop-theme'),
        'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h3 class="footer-widget-title">',
        'after_title' => '</h3>',
    ));
}
add_action('widgets_init', 'eshop_theme_widgets_init');

/**
 * Custom excerpt length
 */
function eshop_theme_excerpt_length($length) {
    return 20;
}
add_filter('excerpt_length', 'eshop_theme_excerpt_length');

/**
 * Custom excerpt more
 */
function eshop_theme_excerpt_more($more) {
    return '...';
}
add_filter('excerpt_more', 'eshop_theme_excerpt_more');

/**
 * Add custom body classes
 */
function eshop_theme_body_classes($classes) {
    if (is_woocommerce() || is_cart() || is_checkout() || is_account_page()) {
        $classes[] = 'woocommerce-page';
    }
    return $classes;
}
add_filter('body_class', 'eshop_theme_body_classes');