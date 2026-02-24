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

if (!function_exists('eshop_fill_empty_nav_menu_item_title')) {
    /**
     * Populate empty menu labels from the linked object title/name.
     *
     * Some imported menu items (notably taxonomy links) can end up with
     * blank post titles, producing empty <a> tags in mobile/footer menus.
     *
     * @param string   $title Filtered menu title.
     * @param WP_Post  $item  Menu item object.
     * @param stdClass $args  Menu arguments.
     * @param int      $depth Menu depth.
     * @return string
     */
    function eshop_fill_empty_nav_menu_item_title($title, $item, $args, $depth) {
        $normalized_title = trim(wp_strip_all_tags((string) $title));
        if ($normalized_title !== '') {
            return $title;
        }

        $theme_location = (is_object($args) && isset($args->theme_location)) ? (string) $args->theme_location : '';
        $allowed_locations = array('primary', 'footer-main', 'footer-account');
        if ($theme_location !== '' && !in_array($theme_location, $allowed_locations, true)) {
            return $title;
        }

        $fallback_title = '';

        if (!empty($item->type) && $item->type === 'taxonomy' && !empty($item->object_id) && !empty($item->object)) {
            $term = get_term((int) $item->object_id, (string) $item->object);
            if ($term && !is_wp_error($term) && !empty($term->name)) {
                $fallback_title = $term->name;
            }
        } elseif (!empty($item->type) && $item->type === 'post_type' && !empty($item->object_id)) {
            $post_title = get_the_title((int) $item->object_id);
            if (!empty($post_title)) {
                $fallback_title = $post_title;
            }
        } elseif (!empty($item->type) && $item->type === 'post_type_archive' && !empty($item->object)) {
            $post_type_obj = get_post_type_object((string) $item->object);
            if ($post_type_obj && !empty($post_type_obj->labels->name)) {
                $fallback_title = $post_type_obj->labels->name;
            }
        }

        if ($fallback_title === '' && !empty($item->object_id)) {
            $fallback_title = get_the_title((int) $item->object_id);
        }

        if ($fallback_title === '' && !empty($item->url)) {
            $url_path = wp_parse_url((string) $item->url, PHP_URL_PATH);
            if (!empty($url_path)) {
                $url_path = trim((string) $url_path, '/');
                $last_segment = $url_path !== '' ? basename($url_path) : '';
                if ($last_segment !== '') {
                    $decoded_segment = rawurldecode($last_segment);
                    $decoded_segment = str_replace(array('-', '_'), ' ', $decoded_segment);
                    $decoded_segment = preg_replace('/\s+/', ' ', $decoded_segment);
                    $fallback_title = trim((string) $decoded_segment);
                }
            }
        }

        return $fallback_title !== '' ? esc_html($fallback_title) : $title;
    }
}
add_filter('nav_menu_item_title', 'eshop_fill_empty_nav_menu_item_title', 10, 4);
