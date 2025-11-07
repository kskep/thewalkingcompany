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
require_once get_template_directory() . '/inc/woocommerce/size-transformation.php';
require_once get_template_directory() . '/inc/mega-menu-walker.php';
require_once get_template_directory() . '/inc/color-grouping-functions.php';
require_once get_template_directory() . '/inc/auth-functions.php';
require_once get_template_directory() . '/inc/front-fields.php';
require_once get_template_directory() . '/inc/front-page-meta.php';

/**
 * Enqueue Scripts and Styles
 */
function eshop_theme_scripts() {
    // Enqueue main stylesheet
    wp_enqueue_style('eshop-theme-style', get_stylesheet_uri(), array(), '1.0.0');

    // Google Fonts - Roboto Condensed for modern editorial look
    wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@300;400;500;600;700&display=swap', array(), null);

    // Ensure Tailwind utilities are available without relying on @import in style.css (some hosts block remote @import)
    wp_enqueue_style('tailwind-cdn', 'https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css', array(), '2.2.19');

    // Modular CSS - Load conditionally
    wp_enqueue_style('eshop-base', get_template_directory_uri() . '/css/base.css', array('eshop-theme-style'), '1.0.0');
    $header_css_ver = file_exists(get_template_directory() . '/css/components.header.css') ? filemtime(get_template_directory() . '/css/components.header.css') : '1.0.0';
    $mega_css_ver = file_exists(get_template_directory() . '/css/components.mega-menu.css') ? filemtime(get_template_directory() . '/css/components.mega-menu.css') : '1.0.0';
    wp_enqueue_style('eshop-header', get_template_directory_uri() . '/css/components.header.css', array('eshop-theme-style'), $header_css_ver);
    wp_enqueue_style('eshop-mega-menu', get_template_directory_uri() . '/css/components.mega-menu.css', array('eshop-theme-style'), $mega_css_ver);
    wp_enqueue_style('eshop-hero', get_template_directory_uri() . '/css/components.hero-slider.css', array('eshop-theme-style'), '1.0.0');

    // Page-specific CSS
    if (is_front_page()) {
        wp_enqueue_style('eshop-page-front', get_template_directory_uri() . '/css/pages.front.css', array('eshop-theme-style'), '1.0.0');
    }

    if (is_cart() || is_checkout()) {
        wp_enqueue_style('eshop-cart-checkout', get_template_directory_uri() . '/css/pages.cart-checkout.css', array('eshop-theme-style'), '1.0.0');
    }

    // Load filter CSS (cache-busted)
    $filters_css_path = get_template_directory() . '/css/components/filters.css';
    $filters_css_ver = file_exists($filters_css_path) ? filemtime($filters_css_path) : '1.0.0';
    wp_enqueue_style('eshop-filters', get_template_directory_uri() . '/css/components/filters.css', array('eshop-theme-style'), $filters_css_ver);

    if (is_shop() || is_product_category() || is_product_tag()) {
        // New archive page CSS
        $archive_css_path = get_template_directory() . '/css/pages.archive-magazine.css';
        $archive_css_ver = file_exists($archive_css_path) ? filemtime($archive_css_path) : '1.0.0';
        wp_enqueue_style('eshop-archive-magazine', get_template_directory_uri() . '/css/pages.archive-magazine.css', array('eshop-theme-style'), $archive_css_ver);

        // Product Card component CSS (no dependency on old shop CSS)
        wp_enqueue_style('eshop-product-card', get_template_directory_uri() . '/css/components/product-card.css', array('eshop-theme-style'), filemtime(get_template_directory() . '/css/components/product-card.css'));

        // Price slider (noUiSlider) for filters
        wp_enqueue_style('nouislider', 'https://cdn.jsdelivr.net/npm/nouislider@15.7.1/dist/nouislider.min.css', array(), '15.7.1');
        wp_enqueue_script('nouislider', 'https://cdn.jsdelivr.net/npm/nouislider@15.7.1/dist/nouislider.min.js', array(), '15.7.1', true);
    }

    if (is_product()) {
        // Magazine Style Single Product CSS
        $single_product_magazine_path = get_template_directory() . '/css/pages/single-product-magazine.css';
        if (file_exists($single_product_magazine_path)) {
            wp_enqueue_style('eshop-single-product-magazine', get_template_directory_uri() . '/css/pages/single-product-magazine.css', array('eshop-theme-style'), filemtime($single_product_magazine_path));
        }

        $single_product_css_path = get_template_directory() . '/css/pages/single-product.css';
        $single_product_css_ver = file_exists($single_product_css_path) ? filemtime($single_product_css_path) : '1.0.0';
        wp_enqueue_style('eshop-single-product', get_template_directory_uri() . '/css/pages/single-product.css', array('eshop-theme-style'), $single_product_css_ver);

        // Fallback standalone CSS that does not rely on Tailwind or CSS variables
        $single_product_standalone_path = get_template_directory() . '/css/pages/single-product.standalone.css';
        if (file_exists($single_product_standalone_path)) {
            wp_enqueue_style('eshop-single-product-standalone', get_template_directory_uri() . '/css/pages/single-product.standalone.css', array(), filemtime($single_product_standalone_path));
        }

        // Demo Modern CSS - Important refinements for modern single product layout
        $demo_modern_css_path = get_template_directory() . '/css/pages/single-product.demo-modern.css';
        if (file_exists($demo_modern_css_path)) {
            wp_enqueue_style('eshop-single-product-demo-modern', get_template_directory_uri() . '/css/pages/single-product.demo-modern.css', array('eshop-single-product'), filemtime($demo_modern_css_path));
        }
        
        // Product Gallery Component CSS with proper versioning
        $product_gallery_css_path = get_template_directory() . '/css/components/product-gallery.css';
        $product_gallery_css_ver = file_exists($product_gallery_css_path) ? filemtime($product_gallery_css_path) : '1.0.0';
        wp_enqueue_style('eshop-product-gallery', get_template_directory_uri() . '/css/components/product-gallery.css', array('eshop-theme-style'), $product_gallery_css_ver);
        
        // Size Selection Component CSS with proper versioning
        $size_selection_css_path = get_template_directory() . '/css/components/size-selection.css';
        $size_selection_css_ver = file_exists($size_selection_css_path) ? filemtime($size_selection_css_path) : '1.0.0';
        wp_enqueue_style('eshop-size-selection', get_template_directory_uri() . '/css/components/size-selection.css', array('eshop-theme-style'), $size_selection_css_ver);
        
        wp_enqueue_style('eshop-color-variants', get_template_directory_uri() . '/css/components/color-variants.css', array('eshop-theme-style'), '1.0.0');

        // Trust badges and sticky ATC components
        $trust_css_path = get_template_directory() . '/css/components/trust-badges.css';
        if (file_exists($trust_css_path)) {
            wp_enqueue_style('eshop-trust-badges', get_template_directory_uri() . '/css/components/trust-badges.css', array('eshop-theme-style'), filemtime($trust_css_path));
        }
        $sticky_atc_css_path = get_template_directory() . '/css/components/sticky-atc.css';
        if (file_exists($sticky_atc_css_path)) {
            wp_enqueue_style('eshop-sticky-atc', get_template_directory_uri() . '/css/components/sticky-atc.css', array('eshop-theme-style'), filemtime($sticky_atc_css_path));
        }
        
        // Product Gallery Component JS with proper versioning
        $product_gallery_js_path = get_template_directory() . '/js/components/product-gallery.js';
        $product_gallery_js_ver = file_exists($product_gallery_js_path) ? filemtime($product_gallery_js_path) : '1.0.0';
        wp_enqueue_script('eshop-product-gallery', get_template_directory_uri() . '/js/components/product-gallery.js', array('jquery', 'swiper'), $product_gallery_js_ver, true);

        // Magazine Style Gallery JS (load after main gallery)
        $product_gallery_magazine_js_path = get_template_directory() . '/js/components/product-gallery-magazine.js';
        $product_gallery_magazine_js_ver = file_exists($product_gallery_magazine_js_path) ? filemtime($product_gallery_magazine_js_path) : '1.0.0';
        wp_enqueue_script('eshop-product-gallery-magazine', get_template_directory_uri() . '/js/components/product-gallery-magazine.js', array('jquery', 'eshop-product-gallery'), $product_gallery_magazine_js_ver, true);
        
        wp_enqueue_script('eshop-color-variants', get_template_directory_uri() . '/js/components/color-variants.js', array('jquery', 'eshop-theme-script'), '1.0.0', true);
        wp_enqueue_script('eshop-size-selection', get_template_directory_uri() . '/js/components/size-selection.js', array('jquery', 'eshop-theme-script'), '1.0.0', true);
        wp_enqueue_script('size-transformation', get_template_directory_uri() . '/js/components/size-transformation.js', array('jquery', 'eshop-theme-script'), '1.0.0', true);
        // Sticky ATC and single-page interactions
        $single_product_js_path = get_template_directory() . '/js/single-product.js';
        if (file_exists($single_product_js_path)) {
            wp_enqueue_script('eshop-single-product', get_template_directory_uri() . '/js/single-product.js', array('jquery', 'eshop-theme-script'), filemtime($single_product_js_path), true);
        }

        // Magazine Style Sticky ATC JS
        $sticky_atc_magazine_js_path = get_template_directory() . '/js/components/sticky-atc-magazine.js';
        $sticky_atc_magazine_js_ver = file_exists($sticky_atc_magazine_js_path) ? filemtime($sticky_atc_magazine_js_path) : '1.0.0';
        wp_enqueue_script('eshop-sticky-atc-magazine', get_template_directory_uri() . '/js/components/sticky-atc-magazine.js', array('jquery'), $sticky_atc_magazine_js_ver, true);
    }

    // External CSS
    wp_enqueue_style('google-fonts-ibm-open', 'https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@400;500;600;700&family=Open+Sans:wght@400;600&display=swap', array(), null);
    wp_enqueue_style('material-icons', 'https://fonts.googleapis.com/icon?family=Material+Icons', array(), null);
    wp_enqueue_style('font-awesome', 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css', array(), '6.4.0');
    wp_enqueue_style('feather-icons', 'https://cdn.jsdelivr.net/npm/feather-icons@4.29.0/dist/feather.css', array(), '4.29.0');
    wp_enqueue_style('swiper', 'https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css', array(), '10.3.1');

    // JavaScript
    wp_enqueue_script('heroicons', 'https://cdn.jsdelivr.net/npm/heroicons@2.0.18/24/outline/index.js', array(), '2.0.18', true);
    wp_enqueue_script('gsap', 'https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js', array(), '3.12.5', true);
    wp_enqueue_script('swiper', 'https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js', array(), '10.3.1', true);
    wp_enqueue_script('eshop-theme-script', get_template_directory_uri() . '/js/theme.js', array('jquery', 'gsap', 'swiper'), '1.0.0', true);
    // Mega Menu JS (modular)
    wp_enqueue_script('eshop-mega-menu', get_template_directory_uri() . '/js/mega-menu.js', array('jquery', 'eshop-theme-script'), '1.0.0', true);
    // Product UI interactions (split from theme.js)
    wp_enqueue_script('eshop-product-ui', get_template_directory_uri() . '/js/product-ui.js', array('jquery', 'eshop-theme-script'), '1.0.0', true);

    // Flying Cart Component
    if (class_exists('WooCommerce')) {
        wp_enqueue_style('eshop-flying-cart', get_template_directory_uri() . '/css/components/flying-cart.css', array(), '1.0.0');
        wp_enqueue_script('eshop-flying-cart', get_template_directory_uri() . '/js/components/flying-cart.js', array('jquery', 'eshop-theme-script'), '1.0.0', true);
        
        // Wishlist Component
        wp_enqueue_style('eshop-wishlist', get_template_directory_uri() . '/css/components/wishlist.css', array('eshop-theme-style'), '1.0.0');
    }

    // Filter component JavaScript (full version)
    if (is_shop() || is_product_category() || is_product_tag()) {
        $filters_js_rel = '/js/components/filters.js';
        $filters_js_file = get_template_directory() . $filters_js_rel;
        $filters_js_ver = file_exists($filters_js_file) ? filemtime($filters_js_file) : '1.0.0';
        wp_enqueue_script('eshop-filters', get_template_directory_uri() . $filters_js_rel, array('jquery', 'nouislider', 'eshop-theme-script'), $filters_js_ver, true);
        // Enqueue size transformation script on shop/archive pages
        wp_enqueue_script('size-transformation', get_template_directory_uri() . '/js/components/size-transformation.js', array('jquery', 'eshop-theme-script'), '1.0.0', true);
    }

    // Also load on shop pages specifically
    if (is_shop() || is_product_category() || is_product_tag()) {
        // Additional shop-specific initialization if needed
    }

    // Localize script for AJAX with page context (shop/category/tag)
    $context_taxonomy = '';
    $context_terms = array();
    if (class_exists('WooCommerce')) {
        if (is_product_category()) {
            $term = get_queried_object();
            if ($term && !is_wp_error($term)) {
                $context_taxonomy = 'product_cat';
                $context_terms = array((int) $term->term_id);
            }
        } elseif (is_product_tag()) {
            $term = get_queried_object();
            if ($term && !is_wp_error($term)) {
                $context_taxonomy = 'product_tag';
                $context_terms = array((int) $term->term_id);
            }
        }
    }

    wp_localize_script('eshop-theme-script', 'eshop_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('eshop_nonce'),
        'context_taxonomy' => $context_taxonomy,
        'context_terms' => $context_terms,
    ));
    
    // Add custom script for related products initialization
    if (is_product()) {
        wp_add_inline_script('eshop-theme-script', '
            jQuery(document).ready(function($) {
                // Initialize product sliders in related products when page loads
                setTimeout(function() {
                    if (typeof initializeProductCardSliders === "function") {
                        initializeProductCardSliders();
                    }
                    
                    // Initialize wishlist functionality
                    if (typeof initWishlistButtons === "function") {
                        initWishlistButtons();
                    } else {
                        // Fallback initialization for wishlist buttons
                        $(".add-to-wishlist").each(function() {
                            var $btn = $(this);
                            var productId = $btn.data("product-id");
                            if (productId && !$btn.hasClass("initialized")) {
                                $btn.addClass("initialized");
                            }
                        });
                    }
                }, 500);
            });
        ');
    }
}
add_action('wp_enqueue_scripts', 'eshop_theme_scripts');

// Hide WooCommerce archive page titles; we use breadcrumbs instead
add_filter('woocommerce_show_page_title', '__return_false');

// Removed duplicate eshop_related_products_args (defined in inc/woocommerce-functions.php)

/**
 * Handle custom filter parameters for WooCommerce
 * Priority 5 to run early, before our products_per_page override
 */
function eshop_handle_custom_filters($query) {
    if (!is_admin() && $query->is_main_query() && (is_shop() || is_product_category() || is_product_tag())) {

        // Price filter
        if (isset($_GET['min_price']) && !empty($_GET['min_price'])) {
            $query->set('meta_query', array_merge(
                $query->get('meta_query', array()),
                array(
                    array(
                        'key' => '_price',
                        'value' => floatval($_GET['min_price']),
                        'compare' => '>=',
                        'type' => 'NUMERIC'
                    )
                )
            ));
        }

        if (isset($_GET['max_price']) && !empty($_GET['max_price'])) {
            $query->set('meta_query', array_merge(
                $query->get('meta_query', array()),
                array(
                    array(
                        'key' => '_price',
                        'value' => floatval($_GET['max_price']),
                        'compare' => '<=',
                        'type' => 'NUMERIC'
                    )
                )
            ));
        }

        // Category filter
        if (isset($_GET['product_cat']) && !empty($_GET['product_cat'])) {
            $raw = sanitize_text_field(wp_unslash($_GET['product_cat']));
            $tokens = array_filter(array_map('trim', explode(',', $raw)));
            $all_numeric = !empty($tokens) && count(array_filter($tokens, 'is_numeric')) === count($tokens);
            $terms = $all_numeric ? array_map('intval', $tokens) : array_map('sanitize_text_field', $tokens);

            $query->set('tax_query', array_merge(
                $query->get('tax_query', array()),
                array(
                    array(
                        'taxonomy' => 'product_cat',
                        'field' => $all_numeric ? 'term_id' : 'slug',
                        'terms' => $terms,
                        'operator' => 'IN'
                    )
                )
            ));
        }

        // On sale filter
        if (isset($_GET['on_sale']) && $_GET['on_sale'] === '1') {
            $query->set('post__in', wc_get_product_ids_on_sale());
        }

        // Your custom product attribute filters
        $your_attributes = array('pa_box', 'pa_color', 'pa_pick-pattern', 'pa_select-size', 'pa_size-selection');
        $tax_query = $query->get('tax_query', array());

        foreach ($your_attributes as $attribute) {
            if (isset($_GET[$attribute]) && !empty($_GET[$attribute])) {
                $terms = explode(',', sanitize_text_field($_GET[$attribute]));
                $tax_query[] = array(
                    'taxonomy' => $attribute,
                    'field' => 'slug',
                    'terms' => $terms,
                    'operator' => 'IN'
                );
            }
        }

        if (!empty($tax_query)) {
            $tax_query['relation'] = 'AND';
            $query->set('tax_query', $tax_query);
        }
    }
}
add_action('pre_get_posts', 'eshop_handle_custom_filters', 5);

/**
 * Force 12 products per page for all product categories including clothing
 * Priority 999 to ensure it runs last and overrides everything else
 */
function eshop_force_12_products_per_page($query) {
    if (!is_admin() && $query->is_main_query() && (is_shop() || is_product_category() || is_product_tag())) {
        // Force 12 products per page, overriding any WooCommerce or other theme settings
        $query->set('posts_per_page', 12);
        
        // Also remove any potential pagination offset that might cause inconsistency
        if ($query->get('offset')) {
            $paged = max(1, absint($query->get('paged')));
            $offset = ($paged - 1) * 12;
            $query->set('offset', $offset);
        }
        
        // CRITICAL FIX: Exclude out-of-stock products at query level
        // This ensures we get exactly 12 IN-STOCK products per page
        $meta_query = $query->get('meta_query', array());
        if (!is_array($meta_query)) {
            $meta_query = array();
        }
        
        // Check if stock status filter already exists
        $has_stock_filter = false;
        foreach ($meta_query as $clause) {
            if (isset($clause['key']) && $clause['key'] === '_stock_status') {
                $has_stock_filter = true;
                break;
            }
        }
        
        // Only add stock filter if not already present
        if (!$has_stock_filter) {
            $meta_query[] = array(
                'key' => '_stock_status',
                'value' => 'instock',
                'compare' => '='
            );
            
            if (count($meta_query) > 1) {
                $meta_query['relation'] = 'AND';
            }
            
            $query->set('meta_query', $meta_query);
        }
    }
}
add_action('pre_get_posts', 'eshop_force_12_products_per_page', 999);

/**
 * Override WooCommerce products per page setting to ensure consistency
 * Multiple filters to catch all possible WooCommerce pagination methods
 */
function eshop_override_wc_products_per_page($cols) {
    return 12;
}
add_filter('loop_shop_per_page', 'eshop_override_wc_products_per_page', 999);

/**
 * Force products per page via option filter (catches WooCommerce settings)
 */
function eshop_force_wc_catalog_per_page($value, $option) {
    if (is_shop() || is_product_category() || is_product_tag()) {
        return 12;
    }
    return $value;
}
add_filter('option_woocommerce_catalog_rows', '__return_false', 999);
add_filter('option_woocommerce_catalog_columns', '__return_false', 999);

/**
 * Override the WooCommerce products per page completely
 */
function eshop_force_catalog_page_size() {
    return 12;
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
    if (class_exists('WooCommerce') && (is_shop() || is_product_category() || is_product_tag())) {
        get_template_part('template-parts/components/filter-modal');
    }
}
add_action('wp_footer', 'eshop_include_filter_modal');

/**
 * Include sticky add-to-cart bar on single product pages
 */
function eshop_include_sticky_atc() {
    if (class_exists('WooCommerce') && is_product()) {
        get_template_part('template-parts/components/sticky-atc');
    }
}
add_action('wp_footer', 'eshop_include_sticky_atc', 15);

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
 * Get available attribute terms from current query results
 * This gets terms only from products that match the current context
 */
function eshop_get_available_attribute_terms($taxonomy) {
    // First, get the product IDs that match the current context
    $product_ids = eshop_get_current_context_product_ids();

    if (empty($product_ids)) {
        return array();
    }

    global $wpdb;

    // Create placeholders for the product IDs
    $placeholders = implode(',', array_fill(0, count($product_ids), '%d'));

    // Query to get attribute terms only from the current product set
    $sql = "
        SELECT DISTINCT t.term_id, t.name, t.slug, COUNT(DISTINCT tr.object_id) as count
        FROM {$wpdb->term_relationships} tr
        INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id AND tt.taxonomy = %s
        INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
        WHERE tr.object_id IN ($placeholders)
        GROUP BY t.term_id, t.name, t.slug
        HAVING count > 0
        ORDER BY t.name ASC
    ";

    $query_params = array_merge(array($taxonomy), $product_ids);
    $results = $wpdb->get_results($wpdb->prepare($sql, $query_params), ARRAY_A);

    // Add color values for color attributes
    if ($taxonomy === 'pa_color' && !empty($results)) {
        foreach ($results as &$result) {
            $result['color'] = get_term_meta($result['term_id'], 'color', true);
        }
    }

    return $results ? $results : array();
}

/**
 * Get product IDs that match the current context (category, filters, etc.)
 */
function eshop_get_current_context_product_ids() {
    global $wpdb;

    // Start with basic product query
    $where_clauses = array("p.post_status = 'publish'", "p.post_type = 'product'");
    $join_clauses = array();

    // Add current category context (including child categories)
    if (is_product_category()) {
        $current_category = get_queried_object();
        if ($current_category && isset($current_category->term_id)) {
            // Get all child categories of the current category
            $child_categories = get_terms(array(
                'taxonomy' => 'product_cat',
                'child_of' => $current_category->term_id,
                'hide_empty' => true,
                'fields' => 'ids'
            ));
            
            // Include current category and all its children
            $category_ids = array($current_category->term_id);
            if (!empty($child_categories) && !is_wp_error($child_categories)) {
                $category_ids = array_merge($category_ids, $child_categories);
            }
            
            // Create placeholders for the category IDs
            $category_placeholders = implode(',', array_fill(0, count($category_ids), '%d'));
            
            $join_clauses[] = "INNER JOIN {$wpdb->term_relationships} tr_cat ON p.ID = tr_cat.object_id";
            $join_clauses[] = "INNER JOIN {$wpdb->term_taxonomy} tt_cat ON tr_cat.term_taxonomy_id = tt_cat.term_taxonomy_id AND tt_cat.taxonomy = 'product_cat'";
            $where_clauses[] = $wpdb->prepare("tt_cat.term_id IN ($category_placeholders)", $category_ids);
        }
    }

    // Add price filter if set
    if (isset($_GET['min_price']) && !empty($_GET['min_price'])) {
        $join_clauses[] = "LEFT JOIN {$wpdb->postmeta} pm_price ON p.ID = pm_price.post_id AND pm_price.meta_key = '_price'";
        $where_clauses[] = $wpdb->prepare("CAST(pm_price.meta_value AS DECIMAL(10,2)) >= %f", floatval($_GET['min_price']));
    }

    if (isset($_GET['max_price']) && !empty($_GET['max_price'])) {
        if (!in_array("LEFT JOIN {$wpdb->postmeta} pm_price ON p.ID = pm_price.post_id AND pm_price.meta_key = '_price'", $join_clauses)) {
            $join_clauses[] = "LEFT JOIN {$wpdb->postmeta} pm_price ON p.ID = pm_price.post_id AND pm_price.meta_key = '_price'";
        }
        $where_clauses[] = $wpdb->prepare("CAST(pm_price.meta_value AS DECIMAL(10,2)) <= %f", floatval($_GET['max_price']));
    }

    // Add category filter from URL if set (but not if we're already on a category page)
    if (!is_product_category() && isset($_GET['product_cat']) && !empty($_GET['product_cat'])) {
        $categories = explode(',', sanitize_text_field($_GET['product_cat']));
        $category_placeholders = implode(',', array_fill(0, count($categories), '%s'));
        $join_clauses[] = "LEFT JOIN {$wpdb->term_relationships} tr_url_cat ON p.ID = tr_url_cat.object_id";
        $join_clauses[] = "LEFT JOIN {$wpdb->term_taxonomy} tt_url_cat ON tr_url_cat.term_taxonomy_id = tt_url_cat.term_taxonomy_id AND tt_url_cat.taxonomy = 'product_cat'";
        $join_clauses[] = "LEFT JOIN {$wpdb->terms} t_url_cat ON tt_url_cat.term_id = t_url_cat.term_id";
        $where_clauses[] = $wpdb->prepare("t_url_cat.slug IN ($category_placeholders)", $categories);
    }

    // Add on sale filter if set
    if (isset($_GET['on_sale']) && $_GET['on_sale'] === '1') {
        $sale_ids = wc_get_product_ids_on_sale();
        if (!empty($sale_ids)) {
            $sale_placeholders = implode(',', array_fill(0, count($sale_ids), '%d'));
            $where_clauses[] = $wpdb->prepare("p.ID IN ($sale_placeholders)", $sale_ids);
        } else {
            return array(); // No sale products
        }
    }

    // Add attribute filters
    $your_attributes = array('pa_box', 'pa_color', 'pa_pick-pattern', 'pa_select-size', 'pa_size-selection');
    $attr_join_count = 0;

    foreach ($your_attributes as $attr_taxonomy) {
        if (isset($_GET[$attr_taxonomy]) && !empty($_GET[$attr_taxonomy])) {
            $attr_terms = explode(',', sanitize_text_field($_GET[$attr_taxonomy]));
            $attr_placeholders = implode(',', array_fill(0, count($attr_terms), '%s'));
            $attr_join_count++;

            $join_clauses[] = "INNER JOIN {$wpdb->term_relationships} tr_attr{$attr_join_count} ON p.ID = tr_attr{$attr_join_count}.object_id";
            $join_clauses[] = "INNER JOIN {$wpdb->term_taxonomy} tt_attr{$attr_join_count} ON tr_attr{$attr_join_count}.term_taxonomy_id = tt_attr{$attr_join_count}.term_taxonomy_id AND tt_attr{$attr_join_count}.taxonomy = '{$attr_taxonomy}'";
            $join_clauses[] = "INNER JOIN {$wpdb->terms} t_attr{$attr_join_count} ON tt_attr{$attr_join_count}.term_id = t_attr{$attr_join_count}.term_id";
            $where_clauses[] = $wpdb->prepare("t_attr{$attr_join_count}.slug IN ($attr_placeholders)", $attr_terms);
        }
    }

    // Build and execute the query to get product IDs
    $joins = !empty($join_clauses) ? implode(' ', array_unique($join_clauses)) : '';
    $where = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';

    $sql = "
        SELECT DISTINCT p.ID
        FROM {$wpdb->posts} p
        {$joins}
        {$where}
    ";

    $product_ids = $wpdb->get_col($sql);

    return $product_ids ? array_map('intval', $product_ids) : array();
}

/**
 * Get available categories from current query results
 */
function eshop_get_available_categories() {
    // If we're on a category page, don't show category filter (it's redundant)
    if (is_product_category()) {
        return array();
    }

    // Get product IDs that match current context
    $product_ids = eshop_get_current_context_product_ids();

    if (empty($product_ids)) {
        return array();
    }

    global $wpdb;

    // Create placeholders for the product IDs
    $placeholders = implode(',', array_fill(0, count($product_ids), '%d'));

    // Query to get categories only from the current product set
    $sql = "
        SELECT DISTINCT t.term_id, t.name, t.slug, COUNT(DISTINCT tr.object_id) as count
        FROM {$wpdb->term_relationships} tr
        INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id AND tt.taxonomy = 'product_cat'
        INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
        WHERE tr.object_id IN ($placeholders)
        GROUP BY t.term_id, t.name, t.slug
        HAVING count > 0
        ORDER BY t.name ASC
    ";

    $results = $wpdb->get_results($wpdb->prepare($sql, $product_ids), ARRAY_A);

    return $results ? $results : array();
}






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
// Disabled: We render a new componentized toolbar/filters directly in the archive template
// add_action('wp', 'eshop_customize_shop_toolbar_hooks');

function eshop_render_shop_toolbar() {
    echo '<div class="shop-toolbar flex items-center justify-between mb-6 pb-4 border-b border-gray-200">';
    // Left: Filter button (opens off-canvas drawer) - Updated to flat design
    echo '<button id="open-filters" class="filter-toggle-btn-flat flex items-center gap-2 px-4 py-2 bg-transparent hover:bg-gray-100 transition-all duration-200" aria-label="Open Filters">';
    echo '<i class="fas fa-sliders-h text-sm" aria-hidden="true"></i>';
    echo '<span class="text-sm font-medium uppercase tracking-wide">' . esc_html__('Filters', 'eshop-theme') . '</span>';
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

/**
 * Transform size labels from full names to abbreviations
 * Per SINGLE_PRODUCT_PLAN.txt specification
 *
 * @param string $size_label The original size label
 * @return string The abbreviated size label
 */
function eshop_transform_size_label( $size_label ) {
    $size_map = array(
        'XSmall/Small'  => 'XS/S',
        'One Size'      => 'OS',
        'XSmall'        => 'XS',
        'Small'         => 'S',
        'Medium'        => 'M',
        'Large'         => 'L',
        'XLarge'        => 'XL',
        'XXLarge'       => 'XXL',
        'XXXLarge'      => 'XXXL',
        'Small/Medium'  => 'S/M',
        'Medium/Large'  => 'M/L',
        'Large/XLarge'  => 'L/XL',
    );
    
    // Return mapped value if exists, otherwise return original
    return isset( $size_map[ $size_label ] ) ? $size_map[ $size_label ] : $size_label;
}