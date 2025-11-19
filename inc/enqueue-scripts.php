<?php
/**
 * Enqueue Scripts and Styles
 * 
 * @package E-Shop Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

function eshop_theme_scripts() {
    // Enqueue main stylesheet
    wp_enqueue_style('eshop-theme-style', get_stylesheet_uri(), array(), '1.0.0');

    // Google Fonts - Roboto Condensed for modern editorial look
    wp_enqueue_style('google-fonts', 'https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@300;400;500;600;700&display=swap', array(), null);

    // Ensure Tailwind utilities are available without relying on @import in style.css (some hosts block remote @import)
    wp_enqueue_style('tailwind-cdn', 'https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css', array(), '2.2.19');

    // Modular CSS - Load conditionally
    wp_enqueue_style('eshop-base', get_template_directory_uri() . '/css/base.css', array('eshop-theme-style'), '1.0.0');
    
    // Pagination component CSS (loaded globally for use on all paginated content)
    $pagination_css_ver = file_exists(get_template_directory() . '/css/components/pagination.css') ? filemtime(get_template_directory() . '/css/components/pagination.css') : '1.0.0';
    wp_enqueue_style('eshop-pagination', get_template_directory_uri() . '/css/components/pagination.css', array('eshop-theme-style'), $pagination_css_ver);
    
    $header_css_ver = file_exists(get_template_directory() . '/css/components/header.css') ? filemtime(get_template_directory() . '/css/components/header.css') : '1.0.0';
    $mega_css_ver = file_exists(get_template_directory() . '/css/components/mega-menu.css') ? filemtime(get_template_directory() . '/css/components/mega-menu.css') : '1.0.0';
    wp_enqueue_style('eshop-header', get_template_directory_uri() . '/css/components/header.css', array('eshop-theme-style'), $header_css_ver);
    wp_enqueue_style('eshop-mega-menu', get_template_directory_uri() . '/css/components/mega-menu.css', array('eshop-theme-style'), $mega_css_ver);
    wp_enqueue_style('eshop-hero', get_template_directory_uri() . '/css/components/hero-slider.css', array('eshop-theme-style'), '1.0.0');

    // Page-specific CSS
    if (is_front_page()) {
        wp_enqueue_style('eshop-page-front', get_template_directory_uri() . '/css/pages.front.css', array('eshop-theme-style'), '1.0.0');
    }

    if (is_page() && !is_front_page()) {
        $static_page_css_path = get_template_directory() . '/css/pages.static-page.css';
        $static_page_css_ver = file_exists($static_page_css_path) ? filemtime($static_page_css_path) : '1.0.0';
        wp_enqueue_style('eshop-static-page', get_template_directory_uri() . '/css/pages.static-page.css', array('eshop-theme-style'), $static_page_css_ver);
    }

    if (is_cart() || is_checkout()) {
        wp_enqueue_style('eshop-cart-checkout', get_template_directory_uri() . '/css/pages.cart-checkout.css', array('eshop-theme-style'), '1.0.0');
    }

    // Load legacy filter CSS only if enabled via filter (default off)
    $enable_legacy_filters = apply_filters('eshop_enable_legacy_filters', false);
    if ($enable_legacy_filters) {
        $filters_css_path = get_template_directory() . '/css/components/filters.css';
        $filters_css_ver = file_exists($filters_css_path) ? filemtime($filters_css_path) : '1.0.0';
        wp_enqueue_style('eshop-filters', get_template_directory_uri() . '/css/components/filters.css', array('eshop-theme-style'), $filters_css_ver);
    }

    // -- START MODIFICATION --
    if (is_shop() || is_product_category() || is_product_tag()) {
        // Product Card component CSS (no dependency on old shop CSS)
        wp_enqueue_style('eshop-product-card', get_template_directory_uri() . '/css/components/product-card.css', array('eshop-theme-style'), filemtime(get_template_directory() . '/css/components/product-card.css'));

        // Cards Grid CSS (concept design grid layout)
        $cards_grid_css_path = get_template_directory() . '/css/components/cards-grid.css';
        $cards_grid_css_ver = file_exists($cards_grid_css_path) ? filemtime($cards_grid_css_path) : '1.0.0';
        wp_enqueue_style('eshop-cards-grid', get_template_directory_uri() . '/css/components/cards-grid.css', array('eshop-theme-style'), $cards_grid_css_ver);

        // Product Archive Filters CSS (matching concept design toolbar)
        $product_archive_filters_css_path = get_template_directory() . '/css/components/product-archive-filters.css';
        $product_archive_filters_css_ver = file_exists($product_archive_filters_css_path) ? filemtime($product_archive_filters_css_path) : '1.0.0';
        wp_enqueue_style('eshop-product-archive-filters', get_template_directory_uri() . '/css/components/product-archive-filters.css', array('eshop-theme-style'), $product_archive_filters_css_ver);

        // Price slider (noUiSlider) for filters
        wp_enqueue_style('nouislider', 'https://cdn.jsdelivr.net/npm/nouislider@15.7.1/dist/nouislider.min.css', array(), '15.7.1');
        wp_enqueue_script('nouislider', 'https://cdn.jsdelivr.net/npm/nouislider@15.7.1/dist/nouislider.min.js', array(), '15.7.1', true);

        // Product Archive Filters JavaScript
        $product_archive_filters_js_path = get_template_directory() . '/js/components/product-archive-filters.js';
        $product_archive_filters_js_ver = file_exists($product_archive_filters_js_path) ? filemtime($product_archive_filters_js_path) : '1.0.0';
        wp_enqueue_script('eshop-product-archive-filters', get_template_directory_uri() . '/js/components/product-archive-filters.js', array('jquery', 'eshop-theme-script'), $product_archive_filters_js_ver, true);
    }

    if (is_product()) {
        // Reuse product card styles for related products grid
        $product_card_css_path = get_template_directory() . '/css/components/product-card.css';
        if (file_exists($product_card_css_path)) {
            wp_enqueue_style('eshop-product-card', get_template_directory_uri() . '/css/components/product-card.css', array('eshop-theme-style'), filemtime($product_card_css_path));
        }

        // Ensure related/upsell sections use the same grid as archives
        $cards_grid_css_path = get_template_directory() . '/css/components/cards-grid.css';
        if (file_exists($cards_grid_css_path)) {
            // Use a unique handle to avoid duplicate registration conflicts
            wp_enqueue_style('eshop-cards-grid', get_template_directory_uri() . '/css/components/cards-grid.css', array('eshop-theme-style'), filemtime($cards_grid_css_path));
        }

    // Updated Single Product CSS
        $single_product_css_path = get_template_directory() . '/css/pages/single-product.css';
        $single_product_css_ver = file_exists($single_product_css_path) ? filemtime($single_product_css_path) : '1.0.0';
        wp_enqueue_style('eshop-single-product', get_template_directory_uri() . '/css/pages/single-product.css', array('eshop-theme-style'), $single_product_css_ver);
        
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
        
        // Single Product Enhancements (Wishlist & Stock Updates)
        $sp_enhancements_js_path = get_template_directory() . '/js/components/single-product-enhancements.js';
        $sp_enhancements_js_ver = file_exists($sp_enhancements_js_path) ? filemtime($sp_enhancements_js_path) : '1.0.0';
        wp_enqueue_script('eshop-single-product-enhancements', get_template_directory_uri() . '/js/components/single-product-enhancements.js', array('jquery', 'eshop-theme-script'), $sp_enhancements_js_ver, true);
        
        // Single product swatches handler for concept design
        $sp_swatches_js_ver = file_exists(get_template_directory() . '/js/components/single-product-swatches.js') ? filemtime(get_template_directory() . '/js/components/single-product-swatches.js') : '1.0.0';
        wp_enqueue_script('eshop-single-product-swatches', get_template_directory_uri() . '/js/components/single-product-swatches.js', array('jquery', 'eshop-theme-script'), $sp_swatches_js_ver, true);

        // Product Card interactions (media dots, overlays) for related/upsell cards
        $product_card_js_path = get_template_directory() . '/js/components/product-card.js';
        if (file_exists($product_card_js_path)) {
            $product_card_js_ver = filemtime($product_card_js_path);
            wp_enqueue_script('eshop-product-card', get_template_directory_uri() . '/js/components/product-card.js', array('jquery', 'eshop-theme-script'), $product_card_js_ver, true);
        }
    }
    // -- END MODIFICATION --

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
        // Enqueue legacy filters JS only if enabled via filter (default off)
        if ($enable_legacy_filters) {
            $filters_js_rel = '/js/components/filters.js';
            $filters_js_file = get_template_directory() . $filters_js_rel;
            $filters_js_ver = file_exists($filters_js_file) ? filemtime($filters_js_file) : '1.0.0';
            wp_enqueue_script('eshop-filters', get_template_directory_uri() . $filters_js_rel, array('jquery', 'nouislider', 'eshop-theme-script'), $filters_js_ver, true);
        }
        // Enqueue size transformation script on shop/archive pages
        wp_enqueue_script('size-transformation', get_template_directory_uri() . '/js/components/size-transformation.js', array('jquery', 'eshop-theme-script'), '1.0.0', true);
        
        // Product Card Interactive Features (newly created for concept design)
        $product_card_js_path = get_template_directory() . '/js/components/product-card.js';
        $product_card_js_ver = file_exists($product_card_js_path) ? filemtime($product_card_js_path) : '1.0.0';
        wp_enqueue_script('eshop-product-card', get_template_directory_uri() . '/js/components/product-card.js', array('jquery', 'eshop-theme-script'), $product_card_js_ver, true);
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
        'shop_url' => function_exists('wc_get_page_id') ? get_permalink(wc_get_page_id('shop')) : home_url('/shop/')
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
