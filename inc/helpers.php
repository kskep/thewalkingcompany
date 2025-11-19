<?php
/**
 * Theme Helper Functions
 * 
 * @package E-Shop Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Get color hex value from color name (fallback when ACF is not available)
 */
function eshop_get_color_from_name($color_name) {
    // Convert color name to lowercase for comparison
    $color_name = strtolower(trim($color_name));
    
    // Load color mapping from config file
    $color_map = require get_template_directory() . '/inc/config-colors.php';
    
    // Check if exact match exists
    if (isset($color_map[$color_name])) {
        return $color_map[$color_name];
    }
    
    // Check for partial matches for composite colors
    foreach ($color_map as $color_key => $hex_value) {
        if (strpos($color_name, $color_key) !== false || strpos($color_key, $color_name) !== false) {
            return $hex_value;
        }
    }
    
    // Default fallback color - a neutral pink/rose
    return '#f8c5d8';
}

if (!function_exists('eshop_get_product_archive_breadcrumbs')) {
    /**
     * Build breadcrumb trail for product archive contexts.
     */
    function eshop_get_product_archive_breadcrumbs() {
        $breadcrumbs = [];

        $breadcrumbs[] = [
            'label' => __('Home', 'eshop-theme'),
            'url' => home_url('/')
        ];

        $shop_page_id = wc_get_page_id('shop');
        $shop_label = __('Shop', 'eshop-theme');
        $shop_url = $shop_page_id && $shop_page_id !== -1 ? get_permalink($shop_page_id) : home_url('/');
        if ($shop_page_id && $shop_page_id !== -1) {
            $maybe_title = get_the_title($shop_page_id);
            if ($maybe_title) {
                $shop_label = $maybe_title;
            }
        }
        $breadcrumbs[] = [
            'label' => $shop_label,
            'url' => $shop_url
        ];

        $current_term = get_queried_object();
        if ($current_term && !is_wp_error($current_term) && isset($current_term->term_id)) {
            $parents = [];
            $parent = get_term($current_term->parent, $current_term->taxonomy);
            while ($parent && !is_wp_error($parent) && $parent->term_id) {
                array_unshift($parents, $parent);
                $parent = get_term($parent->parent, $current_term->taxonomy);
            }

            foreach ($parents as $parent_term) {
                $breadcrumbs[] = [
                    'label' => $parent_term->name,
                    'url' => get_term_link($parent_term)
                ];
            }

            $breadcrumbs[] = [
                'label' => $current_term->name,
                'url' => get_term_link($current_term)
            ];
        }

        return $breadcrumbs;
    }
}
