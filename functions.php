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
require_once get_template_directory() . '/inc/helpers.php'; // [NEW] Helper functions
require_once get_template_directory() . '/inc/enqueue-scripts.php'; // [NEW] Enqueue scripts & styles
require_once get_template_directory() . '/inc/class-product-filters.php'; // [NEW] Product Filters Class
require_once get_template_directory() . '/inc/wishlist-functions.php';
require_once get_template_directory() . '/inc/woocommerce-functions.php';
require_once get_template_directory() . '/inc/woocommerce/product-display.php';
require_once get_template_directory() . '/inc/woocommerce/size-transformation.php';
require_once get_template_directory() . '/inc/mega-menu-walker.php';
require_once get_template_directory() . '/inc/color-grouping-functions.php';
require_once get_template_directory() . '/inc/auth-functions.php';
require_once get_template_directory() . '/inc/front-fields.php';
require_once get_template_directory() . '/inc/front-page-meta.php';

// Clear PHP opcache on theme activation - TEMPORARY DEBUG
if (function_exists('opcache_reset')) {
    opcache_reset();
}

// Hide WooCommerce archive page titles; we use breadcrumbs instead
add_filter('woocommerce_show_page_title', '__return_false');

/**
 * Force 15 products per page for all product categories including clothing
 * Priority 999 to ensure it runs last and overrides everything else
 */
function eshop_force_15_products_per_page($query) {
    if (!is_admin() && $query->is_main_query() && (is_shop() || is_product_category() || is_product_tag())) {
        // Force 15 products per page, overriding any WooCommerce or other theme settings
        $query->set('posts_per_page', 15);
        
        // Also remove any potential pagination offset that might cause inconsistency
        if ($query->get('offset')) {
            $paged = max(1, absint($query->get('paged')));
            $offset = ($paged - 1) * 15;
            $query->set('offset', $offset);
        }
    }
}
add_action('pre_get_posts', 'eshop_force_15_products_per_page', 999);

/**
 * Override WooCommerce products per page setting to ensure consistency
 * Multiple filters to catch all possible WooCommerce pagination methods
 */
function eshop_override_wc_products_per_page($cols) {
    return 15;
}
add_filter('loop_shop_per_page', 'eshop_override_wc_products_per_page', 999);

/**
 * Force products per page via option filter (catches WooCommerce settings)
 */
function eshop_force_wc_catalog_per_page($value, $option) {
    if (is_shop() || is_product_category() || is_product_tag()) {
        return 15;
    }
    return $value;
}
add_filter('option_woocommerce_catalog_rows', '__return_false', 999);
add_filter('option_woocommerce_catalog_columns', '__return_false', 999);

/**
 * Override the WooCommerce products per page completely
 */
function eshop_force_catalog_page_size() {
    return 15;
}
add_filter('loop_shop_per_page', 'eshop_force_catalog_page_size', 999);

/**
 * Custom Product Gallery Integration
 * Replace default WooCommerce gallery with our custom component
 */
function eshop_custom_product_gallery() {
    // Remove default WooCommerce product images
    remove_action('woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20);

    // Add our custom gallery component
    add_action('woocommerce_before_single_product_summary', 'eshop_show_custom_product_gallery', 20);
    
    // Remove breadcrumbs from single product pages
    remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);
}
add_action('init', 'eshop_custom_product_gallery');

/**
 * Configure WooCommerce gallery features for magazine style
 */
function eshop_configure_wc_gallery_features() {
    // Keep WooCommerce gallery lightbox (user wants it)
    add_theme_support('wc-product-gallery-lightbox');

    // Keep WooCommerce gallery zoom but style it our way
    add_theme_support('wc-product-gallery-zoom');

    // Enable slider for proper mobile navigation
    add_theme_support('wc-product-gallery-slider');
}
add_action('after_setup_theme', 'eshop_configure_wc_gallery_features', 100);

/**
 * Display custom product gallery
 */
function eshop_show_custom_product_gallery() {
    get_template_part('template-parts/components/product-gallery');
}

/**
 * Include filter modal on WooCommerce archive pages
 */
function eshop_include_filter_modal() {
    // Render legacy filter drawer only if explicitly enabled via filter
    if (!apply_filters('eshop_enable_legacy_filters', false)) {
        return;
    }
    if (class_exists('WooCommerce') && (is_shop() || is_product_category() || is_product_tag())) {
        get_template_part('template-parts/components/filter-modal');
    }
}
add_action('wp_footer', 'eshop_include_filter_modal');

/**
 * Inline critical CSS for single product as last-resort fallback
 * Ensures visible layout even if external CSS fails to load on host/CDN
 */
function eshop_single_product_inline_critical_css() {
        if (!function_exists('is_product') || !is_product()) { return; }
        ?>
        <style id="eshop-single-product-critical">
            .product-main-container{display:grid;grid-template-columns:1fr 1fr;gap:48px;max-width:1630px;padding:24px;margin:0 auto}
            @media(max-width:768px){.product-main-container{grid-template-columns:1fr;gap:24px;padding:16px}}
        </style>
        <?php
}
add_action('wp_head', 'eshop_single_product_inline_critical_css', 100);

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
        
        // Add our custom toolbar
        add_action('woocommerce_before_shop_loop', 'eshop_render_custom_toolbar', 20);
        
        // Add filter modal at the bottom of the page
        add_action('wp_footer', 'eshop_render_filter_modal');
    }
}
add_action('wp', 'eshop_customize_shop_toolbar_hooks');

/**
 * Render custom shop toolbar
 */
function eshop_render_custom_toolbar() {
    get_template_part('template-parts/components/shop-toolbar');
}

/**
 * Render filter modal
 */
function eshop_render_filter_modal() {
    get_template_part('template-parts/components/product-archive-filters');
}

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
