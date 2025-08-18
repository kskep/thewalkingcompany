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
        // Ensure modal exists in footer for these pages
        add_action('wp_footer', 'eshop_render_filters_modal_footer');
    }
}
add_action('wp', 'eshop_customize_shop_toolbar_hooks');

function eshop_render_shop_toolbar() {
    echo '<div class="shop-toolbar flex items-center justify-between mb-6 pb-4 border-b border-gray-200">';
    // Left: Filter icon button (opens modal)
    echo '<button class="eshop-modal-open flex items-center gap-2 px-4 py-2 border border-gray-300 hover:border-gray-400 transition-colors" data-target="#filters-modal" aria-label="Open Filters">';
    echo '<i class="fas fa-sliders-h text-sm" aria-hidden="true"></i>';
    echo '<span class="hidden sm:inline">' . esc_html__('Filters', 'eshop-theme') . '</span>';
    echo '</button>';
    // Right: Sorting
    echo '<div class="shop-ordering">';
    woocommerce_catalog_ordering();
    echo '</div>';
    echo '</div>';
}

function eshop_render_filters_modal_footer() {
    if (!(is_shop() || is_product_category() || is_product_tag())) { return; }
    // Reuse the modal component and render a comprehensive filters UI
    get_template_part('template-parts/components/modal', null, array(
        'id' => 'filters-modal',
        'title' => __('Filters', 'eshop-theme'),
        'size' => 'lg',
        'content_cb' => function () {
            echo '<div class="active-filters mb-6" style="display:none;">';
            echo '<h4 class="text-sm font-semibold text-gray-900 mb-3">' . esc_html__('Active Filters', 'eshop-theme') . '</h4>';
            echo '<div class="active-filters-list space-y-2"></div>';
            echo '</div>';

            // Price Range Filter
            echo '<div class="filter-section mb-6">';
            echo '<h4 class="filter-title text-sm font-semibold text-gray-900 mb-3 pb-2 border-b border-gray-100">' . esc_html__('Price Range', 'eshop-theme') . '</h4>';
            echo '<div class="price-filter">';
            echo '<div class="price-inputs mb-3">';
            echo '<div id="price-slider" class="w-full"></div>';
            echo '<div class="flex items-center gap-2 mt-3">';
            echo '<input type="number" id="min-price" class="w-full px-3 py-2 border border-gray-300 text-sm focus:outline-none focus:border-primary" placeholder="' . esc_attr__('Min', 'eshop-theme') . '">';
            echo '<span class="flex items-center text-gray-400">-</span>';
            echo '<input type="number" id="max-price" class="w-full px-3 py-2 border border-gray-300 text-sm focus:outline-none focus:border-primary" placeholder="' . esc_attr__('Max', 'eshop-theme') . '">';
            echo '</div>';
            echo '</div>';
            echo '</div>';
            echo '</div>';

            // Categories Filter (top-level)
            $categories = get_terms(array('taxonomy' => 'product_cat', 'hide_empty' => true, 'parent' => 0));
            if (!empty($categories) && !is_wp_error($categories)) {
                echo '<div class="filter-section mb-6">';
                echo '<h4 class="filter-title text-sm font-semibold text-gray-900 mb-3 pb-2 border-b border-gray-100">' . esc_html__('Categories', 'eshop-theme') . '</h4>';
                echo '<div class="category-filter space-y-2">';
                foreach ($categories as $category) {
                    echo '<label class="flex items-center space-x-2 cursor-pointer hover:bg-gray-50 p-1">';
                    echo '<input type="checkbox" name="product_cat[]" value="' . esc_attr($category->term_id) . '" class="text-primary border-gray-300">';
                    echo '<span class="text-sm text-gray-700">' . esc_html($category->name) . '</span>';
                    echo '<span class="text-xs text-gray-400">(' . intval($category->count) . ')</span>';
                    echo '</label>';
                }
                echo '</div>';
                echo '</div>';
            }

            // Stock Status Filter
            echo '<div class="filter-section mb-6">';
            echo '<h4 class="filter-title text-sm font-semibold text-gray-900 mb-3 pb-2 border-b border-gray-100">' . esc_html__('Availability', 'eshop-theme') . '</h4>';
            echo '<div class="stock-filter space-y-2">';
            echo '<label class="flex items-center space-x-2 cursor-pointer hover:bg-gray-50 p-1">';
            echo '<input type="checkbox" name="stock_status[]" value="instock" class="text-primary border-gray-300">';
            echo '<span class="text-sm text-gray-700">' . esc_html__('In Stock', 'eshop-theme') . '</span>';
            echo '</label>';
            echo '<label class="flex items-center space-x-2 cursor-pointer hover:bg-gray-50 p-1">';
            echo '<input type="checkbox" name="stock_status[]" value="outofstock" class="text-primary border-gray-300">';
            echo '<span class="text-sm text-gray-700">' . esc_html__('Out of Stock', 'eshop-theme') . '</span>';
            echo '</label>';
            echo '</div>';
            echo '</div>';

            // On Sale Filter
            echo '<div class="filter-section mb-6">';
            echo '<h4 class="filter-title text-sm font-semibold text-gray-900 mb-3 pb-2 border-b border-gray-100">' . esc_html__('Special Offers', 'eshop-theme') . '</h4>';
            echo '<div class="sale-filter space-y-2">';
            echo '<label class="flex items-center space-x-2 cursor-pointer hover:bg-gray-50 p-1">';
            echo '<input type="checkbox" name="on_sale" value="1" class="text-primary border-gray-300">';
            echo '<span class="text-sm text-gray-700">' . esc_html__('On Sale', 'eshop-theme') . '</span>';
            echo '</label>';
            echo '</div>';
            echo '</div>';

            // Attribute Filters (Color, Size, etc.)
            $attributes = wc_get_attribute_taxonomies();
            if (!empty($attributes)) {
                foreach ($attributes as $attribute) {
                    $taxonomy = 'pa_' . $attribute->attribute_name;
                    $terms = get_terms(array('taxonomy' => $taxonomy, 'hide_empty' => true));
                    if (!empty($terms) && !is_wp_error($terms)) {
                        echo '<div class="filter-section mb-6">';
                        echo '<h4 class="filter-title text-sm font-semibold text-gray-900 mb-3 pb-2 border-b border-gray-100">' . esc_html(wc_attribute_label($attribute->attribute_name)) . '</h4>';
                        echo '<div class="attribute-filter space-y-2" data-attribute="' . esc_attr($attribute->attribute_name) . '">';
                        foreach ($terms as $term) {
                            echo '<label class="flex items-center space-x-2 cursor-pointer hover:bg-gray-50 p-1">';
                            echo '<input type="checkbox" name="' . esc_attr($taxonomy) . '[]" value="' . esc_attr($term->slug) . '" class="text-primary border-gray-300">';
                            echo '<span class="text-sm text-gray-700">' . esc_html($term->name) . '</span>';
                            echo '<span class="text-xs text-gray-400">(' . intval($term->count) . ')</span>';
                            echo '</label>';
                        }
                        echo '</div>';
                        echo '</div>';
                    }
                }
            }

            // Footer actions inside modal content so buttons are visible
            echo '<div class="pt-2 mt-4 border-t border-gray-200">';
            echo '<div class="flex space-x-3">';
            echo '<button class="clear-filters flex-1 bg-gray-100 text-gray-700 py-3 hover:bg-gray-200 transition-colors">' . esc_html__('Clear All', 'eshop-theme') . '</button>';
            echo '<button class="apply-filters flex-1 bg-primary text-white py-3 hover:bg-primary-dark transition-colors">' . esc_html__('Apply Filters', 'eshop-theme') . '</button>';
            echo '</div>';
            echo '</div>';
        },
    ));
}

/**
 * Admin styles
 */
function eshop_admin_styles($hook_suffix) {
    wp_enqueue_style('eshop-admin-acf', get_template_directory_uri() . '/css/admin.acf.css', array(), '1.0.0');
}
add_action('admin_enqueue_scripts', 'eshop_admin_styles');