<?php
/**
 * Theme Setup Functions
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
        'name' => __('Footer Widget Area', 'eshop-theme'),
        'id' => 'footer-widgets',
        'description' => __('Add footer widgets here.', 'eshop-theme'),
        'before_widget' => '<div id="%1$s" class="footer-widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<h3 class="footer-widget-title">',
        'after_title' => '</h3>',
    ));

    // Shop Filters widget area for reusable filters modal/component
    register_sidebar(array(
        'name' => __('Shop Filters', 'eshop-theme'),
        'id' => 'shop-filters',
        'description' => __('Widgets shown inside the shop filters modal.', 'eshop-theme'),
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget' => '</section>',
        'before_title' => '<h3 class="widget-title text-sm font-semibold mb-3">',
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

/**
 * Remove page titles
 */
function eshop_remove_page_titles($title) {
    if (is_page() && !is_front_page()) {
        return '';
    }
    return $title;
}
add_filter('the_title', 'eshop_remove_page_titles');