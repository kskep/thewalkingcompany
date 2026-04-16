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
        $raw_title = apply_filters('the_title', $item->title, $item->ID);
        $item_title = apply_filters('nav_menu_item_title', $raw_title, $item, $args, $depth);

        if ($depth === 0) {
            // Top level menu item
            $has_children = in_array('menu-item-has-children', $classes);
            $class_names = join(' ', apply_filters('nav_menu_css_class', array_filter($classes), $item, $args));
            $class_names = $class_names ? ' class="' . esc_attr($class_names) . '"' : '';

            $output .= '<li' . $class_names . '>';
            $output .= '<a href="' . esc_url($item->url) . '" class="nav-link">';
            $output .= esc_html($item_title);
            $output .= '</a>';

        } elseif ($depth === 1) {
            // Mega menu items (subcategories)
            $category_image = $this->get_category_image_markup($item, $item_title);
            $has_real_image = $this->has_category_image($item);

            $output .= '<div class="mega-menu-item">';
            $output .= '<a href="' . esc_url($item->url) . '" class="mega-menu-link">';
            $output .= '<div class="mega-menu-image-wrapper">';

            if ($has_real_image) {
                $output .= $category_image;
            } else {
                $output .= '<div class="placeholder">';
                $output .= '<i class="fas fa-image"></i>';
                $output .= '</div>';
            }

            $output .= '</div>';
            $output .= '<div class="mega-menu-title">';
            $output .= esc_html($item_title);
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
    private function get_category_image_markup($item, $title) {
        // Check if this is a product category
        if ($item->object === 'product_cat') {
            $term_id = $item->object_id;
            $thumbnail_id = get_term_meta($term_id, 'thumbnail_id', true);

            if ($thumbnail_id) {
                $image = wp_get_attachment_image($thumbnail_id, 'mega-menu-thumb', false, array(
                    'class' => 'mega-menu-image',
                    'alt' => $title,
                    'loading' => 'lazy',
                    'decoding' => 'async',
                ));

                if ($image) {
                    return $image;
                }
            }
        }

        return '';
    }

}
