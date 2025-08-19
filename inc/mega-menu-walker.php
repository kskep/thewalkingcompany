<?php
/**
 * Custom Walker for Mega Menu with Product Category Images
 */

class Eshop_Mega_Menu_Walker extends Walker_Nav_Menu {

    // Start Level - for sub-menu containers
    function start_lvl(&$output, $depth = 0, $args = null) {
        if ($depth === 0) {
            // This is the mega menu container (full viewport width, centered)
            $output .= '<div class="mega-menu-container">';
            $output .= '<div class="mega-menu-inner">';
            $output .= '<div class="mega-menu-grid">';
        }
    }

    // End Level
    function end_lvl(&$output, $depth = 0, $args = null) {
        if ($depth === 0) {
            $output .= '</div>'; // Close mega-menu-grid
            $output .= '</div>'; // Close mega-menu-inner
            $output .= '</div>'; // Close mega-menu-container
        }
    }
    
    // Start Element
    function start_el(&$output, $item, $depth = 0, $args = null, $id = 0) {
        $classes = empty($item->classes) ? array() : (array) $item->classes;
        $classes[] = 'menu-item-' . $item->ID;

        if ($depth === 0) {
            // Top level menu item
            $has_children = in_array('menu-item-has-children', $classes);
            $class_names = join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item, $args));
            $class_names = $class_names ? ' class="' . esc_attr($class_names) . '"' : '';

            $output .= '<li' . $class_names . '>';
            $output .= '<a href="' . esc_url($item->url) . '" class="nav-link">';
            $output .= esc_html($item->title);
            $output .= '</a>';

        } elseif ($depth === 1) {
            // Mega menu items (subcategories)
            $category_image = $this->get_category_image($item);
            $has_real_image = $this->has_category_image($item);

            $output .= '<div class="mega-menu-item">';
            $output .= '<a href="' . esc_url($item->url) . '" class="mega-menu-link">';
            $output .= '<div class="mega-menu-image-wrapper">';

            if ($has_real_image) {
                $output .= '<img src="' . esc_url($category_image) . '" alt="' . esc_attr($item->title) . '">';
            } else {
                $output .= '<div class="placeholder">';
                $output .= '<i class="fas fa-image"></i>';
                $output .= '</div>';
            }

            $output .= '</div>';
            $output .= '<div class="mega-menu-title">';
            $output .= esc_html($item->title);
            $output .= '</div>';
            $output .= '</a>';
            $output .= '</div>';
        }
    }
    
    // End Element
    function end_el(&$output, $item, $depth = 0, $args = null) {
        if ($depth === 0) {
            $output .= '</li>';
        }
    }
    
    /**
     * Check if category has a real image
     */
    private function has_category_image($item) {
        if ($item->object === 'product_cat') {
            $term_id = $item->object_id;
            $thumbnail_id = get_term_meta($term_id, 'thumbnail_id', true);
            return !empty($thumbnail_id);
        }
        return false;
    }

    /**
     * Get category image for menu item
     */
    private function get_category_image($item) {
        // Check if this is a product category
        if ($item->object === 'product_cat') {
            $term_id = $item->object_id;
            $thumbnail_id = get_term_meta($term_id, 'thumbnail_id', true);

            if ($thumbnail_id) {
                $image = wp_get_attachment_image_src($thumbnail_id, 'category-thumb');
                if ($image) {
                    return $image[0];
                }
            }
        }

        return $this->get_placeholder_image();
    }

    /**
     * Get placeholder image URL
     */
    private function get_placeholder_image() {
        // Return a data URI for a simple placeholder
        return 'data:image/svg+xml;base64,' . base64_encode('
            <svg width="64" height="64" viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                <rect width="64" height="64" fill="#F3F4F6"/>
                <path d="M32 20C28.6863 20 26 22.6863 26 26C26 29.3137 28.6863 32 32 32C35.3137 32 38 29.3137 38 26C38 22.6863 35.3137 20 32 20Z" fill="#9CA3AF"/>
                <path d="M20 44L24.5 36L32 42L39.5 32L44 44H20Z" fill="#9CA3AF"/>
            </svg>
        ');
    }


}
