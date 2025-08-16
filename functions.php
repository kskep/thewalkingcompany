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
 * Include modular function files
 */
require_once get_template_directory() . '/inc/theme-setup.php';
require_once get_template_directory() . '/inc/wishlist-functions.php';
require_once get_template_directory() . '/inc/woocommerce-functions.php';

/**
 * Enqueue Scripts and Styles
 */
function eshop_theme_scripts() {
    // Enqueue main stylesheet
    wp_enqueue_style('eshop-theme-style', get_stylesheet_uri(), array(), '1.0.0');
    
    // Modular CSS - Load conditionally
    wp_enqueue_style('eshop-base', get_template_directory_uri() . '/css/base.css', array('eshop-theme-style'), '1.0.0');
    wp_enqueue_style('eshop-header', get_template_directory_uri() . '/css/components.header.css', array('eshop-theme-style'), '1.0.0');
    wp_enqueue_style('eshop-hero', get_template_directory_uri() . '/css/components.hero-slider.css', array('eshop-theme-style'), '1.0.0');
    
    // Page-specific CSS
    if (is_front_page()) {
        wp_enqueue_style('eshop-page-front', get_template_directory_uri() . '/css/pages.front.css', array('eshop-theme-style'), '1.0.0');
    }
    
    if (is_cart() || is_checkout()) {
        wp_enqueue_style('eshop-cart-checkout', get_template_directory_uri() . '/css/pages.cart-checkout.css', array('eshop-theme-style'), '1.0.0');
    }
    
    if (is_shop() || is_product_category() || is_product_tag()) {
        wp_enqueue_style('eshop-shop', get_template_directory_uri() . '/css/pages.shop.css', array('eshop-theme-style'), '1.0.0');
    }
    
    // External CSS
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', array(), '6.4.0');
    wp_enqueue_style('feather-icons', 'https://cdn.jsdelivr.net/npm/feather-icons@4.29.0/dist/feather.css', array(), '4.29.0');
    wp_enqueue_style('swiper', 'https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css', array(), '10.3.1');
    
    // JavaScript
    wp_enqueue_script('heroicons', 'https://cdn.jsdelivr.net/npm/heroicons@2.0.18/24/outline/index.js', array(), '2.0.18', true);
    wp_enqueue_script('gsap', 'https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js', array(), '3.12.5', true);
    wp_enqueue_script('swiper', 'https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js', array(), '10.3.1', true);
    wp_enqueue_script('eshop-theme-script', get_template_directory_uri() . '/js/theme.js', array('jquery', 'gsap', 'swiper'), '1.0.0', true);
    
    // Localize script for AJAX
    wp_localize_script('eshop-theme-script', 'eshop_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('eshop_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'eshop_theme_scripts');

/**
 * Admin styles
 */
function eshop_admin_styles($hook_suffix) {
    wp_enqueue_style('eshop-admin-acf', get_template_directory_uri() . '/css/admin.acf.css', array(), '1.0.0');
}
add_action('admin_enqueue_scripts', 'eshop_admin_styles');