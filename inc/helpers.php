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
            $item_url = (string) $item->url;

            // Prefer the actual linked post/page title when URL points to a WP object.
            $linked_post_id = function_exists('url_to_postid') ? (int) url_to_postid($item_url) : 0;
            if ($linked_post_id > 0) {
                $linked_post_title = get_the_title($linked_post_id);
                if (!empty($linked_post_title)) {
                    $fallback_title = $linked_post_title;
                }
            }

            $url_path = wp_parse_url($item_url, PHP_URL_PATH);
            if ($fallback_title === '' && !empty($url_path)) {
                $decoded_path = trim((string) rawurldecode((string) $url_path), '/');

                if ($decoded_path !== '') {
                    // Attempt page title lookup by full path and common variants.
                    $candidate_paths = array($decoded_path);
                    if (strpos($decoded_path, '/') !== false) {
                        $segments = array_values(array_filter(explode('/', $decoded_path), 'strlen'));
                        if (!empty($segments)) {
                            $candidate_paths[] = end($segments); // last segment only
                            if (count($segments) > 1) {
                                $candidate_paths[] = implode('/', array_slice($segments, 1)); // strip language-like prefix
                            }
                        }
                    }

                    foreach (array_unique($candidate_paths) as $candidate_path) {
                        $linked_page = get_page_by_path($candidate_path, OBJECT, 'page');
                        if ($linked_page instanceof WP_Post && !empty($linked_page->post_title)) {
                            $fallback_title = $linked_page->post_title;
                            break;
                        }
                    }

                    // Attempt taxonomy term name lookup for common taxonomy URL bases.
                    if ($fallback_title === '') {
                        $path_segments = array_values(array_filter(explode('/', $decoded_path), 'strlen'));
                        if (count($path_segments) >= 2) {
                            $taxonomy_base = sanitize_title($path_segments[0]);
                            $taxonomy_map = array(
                                'product-category' => 'product_cat',
                                'product-tag'      => 'product_tag',
                                'category'         => 'category',
                            );

                            if (isset($taxonomy_map[$taxonomy_base])) {
                                $term_slug = sanitize_title(end($path_segments));
                                $term = get_term_by('slug', $term_slug, $taxonomy_map[$taxonomy_base]);
                                if ($term && !is_wp_error($term) && !empty($term->name)) {
                                    $fallback_title = $term->name;
                                }
                            }
                        }
                    }

                    // Last resort: humanized slug text.
                    if ($fallback_title === '') {
                        $last_segment = basename($decoded_path);
                        if ($last_segment !== '') {
                            $decoded_segment = str_replace(array('-', '_'), ' ', $last_segment);
                            $decoded_segment = preg_replace('/\s+/', ' ', $decoded_segment);
                            $decoded_segment = trim((string) $decoded_segment);

                            if ($decoded_segment !== '' && preg_match('/^[a-z0-9\s]+$/i', $decoded_segment)) {
                                $decoded_segment = ucwords(strtolower($decoded_segment));
                            }

                            $fallback_title = $decoded_segment;
                        }
                    }
                }
            }
        }

        return $fallback_title !== '' ? esc_html($fallback_title) : $title;
    }
}
add_filter('nav_menu_item_title', 'eshop_fill_empty_nav_menu_item_title', 10, 4);
