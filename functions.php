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
require_once get_template_directory() . '/inc/woocommerce/product-display.php';

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
        // Price slider (noUiSlider) for filters
        wp_enqueue_style('nouislider', 'https://cdn.jsdelivr.net/npm/nouislider@15.7.1/dist/nouislider.min.css', array(), '15.7.1');
        wp_enqueue_script('nouislider', 'https://cdn.jsdelivr.net/npm/nouislider@15.7.1/dist/nouislider.min.js', array(), '15.7.1', true);
    }

    if (is_product()) {
        wp_enqueue_style('eshop-single-product', get_template_directory_uri() . '/css/pages.single-product.css', array('eshop-theme-style'), '1.0.0');
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
    // Product UI interactions (split from theme.js)
    wp_enqueue_script('eshop-product-ui', get_template_directory_uri() . '/js/product-ui.js', array('jquery', 'eshop-theme-script'), '1.0.0', true);

    // Localize script for AJAX
    wp_localize_script('eshop-theme-script', 'eshop_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('eshop_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'eshop_theme_scripts');

// Hide WooCommerce archive page titles; we use breadcrumbs instead
add_filter('woocommerce_show_page_title', '__return_false');


/**
 * Shop toolbar and Filters modal via hooks to ensure availability on all archive pages
 */
function eshop_customize_shop_toolbar_hooks() {
    if (!class_exists('WooCommerce')) { return; }
    // Only modify on catalog/archive contexts
    if (is_shop() || is_product_category() || is_product_tag()) {
        // Remove default result count and ordering so we can render our own toolbar
        remove_action('woocommerce_before_shop_loop', 'woocommerce_result_count', 20);
        remove_action('woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30);
        // Add our toolbar early
        add_action('woocommerce_before_shop_loop', 'eshop_render_shop_toolbar', 10);
        // Filter drawer is now rendered directly in the template
    }
}
add_action('wp', 'eshop_customize_shop_toolbar_hooks');

function eshop_render_shop_toolbar() {
    echo '<div class="shop-toolbar flex items-center justify-between mb-6 pb-4 border-b border-gray-200">';
    // Left: Filter button (opens off-canvas drawer)
    echo '<button id="open-filters" class="filter-toggle-btn flex items-center gap-2 px-4 py-2 border border-gray-300 hover:border-primary transition-all duration-200" aria-label="Open Filters">';
    echo '<i class="fas fa-sliders-h text-sm" aria-hidden="true"></i>';
    echo '<span class="hidden sm:inline text-sm font-medium uppercase tracking-wide">' . esc_html__('Filters', 'eshop-theme') . '</span>';
    echo '</button>';
    // Right: Sorting
    echo '<div class="shop-ordering">';
    woocommerce_catalog_ordering();
    echo '</div>';
    echo '</div>';
}

// Old modal function removed - using off-canvas drawer instead

/**
 * Admin styles
 */
function eshop_admin_styles($hook_suffix) {
    wp_enqueue_style('eshop-admin-acf', get_template_directory_uri() . '/css/admin.acf.css', array(), '1.0.0');
}
add_action('admin_enqueue_scripts', 'eshop_admin_styles');

/**
 * Newsletter Signup Handler
 */
function handle_newsletter_signup() {
    // Verify nonce
    if (!wp_verify_nonce($_POST['newsletter_nonce'], 'newsletter_signup')) {
        wp_die('Security check failed');
    }

    $email = sanitize_email($_POST['newsletter_email']);

    if (!is_email($email)) {
        wp_redirect(add_query_arg('newsletter', 'invalid', wp_get_referer()));
        exit;
    }

    // Store in options (you might want to use a proper database table)
    $subscribers = get_option('newsletter_subscribers', array());
    if (!in_array($email, $subscribers)) {
        $subscribers[] = $email;
        update_option('newsletter_subscribers', $subscribers);

        // Send notification email to admin
        wp_mail(
            get_option('admin_email'),
            'New Newsletter Subscription',
            "New subscriber: {$email}"
        );
    }

    wp_redirect(add_query_arg('newsletter', 'success', wp_get_referer()));
    exit;
}
add_action('admin_post_newsletter_signup', 'handle_newsletter_signup');
add_action('admin_post_nopriv_newsletter_signup', 'handle_newsletter_signup');