<?php
/**
 * Custom Walker for Mega Menu with Product Category Images
 */

class Eshop_Mega_Menu_Walker extends Walker_Nav_Menu {

    private $mega_menu_items = array();

    // Start Level - for sub-menu containers
    function start_lvl(&$output, $depth = 0, $args = null) {
        // Don't output anything here - we'll handle mega menu separately
    }

    // End Level
    function end_lvl(&$output, $depth = 0, $args = null) {
        // Don't output anything here - we'll handle mega menu separately
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

            $data_attr = $has_children ? ' data-mega-menu="' . sanitize_title($item->title) . '"' : '';

            $output .= '<li' . $class_names . $data_attr . '>';
            $output .= '<a href="' . esc_url($item->url) . '" class="nav-link">';
            $output .= esc_html($item->title);
            $output .= '</a>';

        } elseif ($depth === 1) {
            // Store mega menu items for later output
            $parent_id = $item->menu_item_parent;
            $parent_item = null;

            // Find parent item to get its title
            foreach ($args->menu->posts as $menu_item) {
                if ($menu_item->ID == $parent_id) {
                    $parent_item = $menu_item;
                    break;
                }
            }

            if ($parent_item) {
                $parent_slug = sanitize_title($parent_item->post_title);

                if (!isset($this->mega_menu_items[$parent_slug])) {
                    $this->mega_menu_items[$parent_slug] = array();
                }

                $category_image = $this->get_category_image($item);
                $has_real_image = $this->has_category_image($item);

                $this->mega_menu_items[$parent_slug][] = array(
                    'title' => $item->title,
                    'url' => $item->url,
                    'image' => $category_image,
                    'has_real_image' => $has_real_image
                );
            }
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

    /**
     * Output mega menu containers
     */
    public function get_mega_menu_containers() {
        $output = '';

        foreach ($this->mega_menu_items as $parent_slug => $items) {
            $output .= '<div class="mega-menu-container" data-mega-menu="' . esc_attr($parent_slug) . '">';
            $output .= '<div class="mega-menu-inner">';
            $output .= '<div class="mega-menu-grid">';

            foreach ($items as $item) {
                $output .= '<div class="mega-menu-item">';
                $output .= '<a href="' . esc_url($item['url']) . '" class="mega-menu-link">';
                $output .= '<div class="mega-menu-image-wrapper">';

                if ($item['has_real_image']) {
                    $output .= '<img src="' . esc_url($item['image']) . '" alt="' . esc_attr($item['title']) . '">';
                } else {
                    $output .= '<div class="placeholder">';
                    $output .= '<i class="fas fa-image"></i>';
                    $output .= '</div>';
                }

                $output .= '</div>';
                $output .= '<div class="mega-menu-title">' . esc_html($item['title']) . '</div>';
                $output .= '</a>';
                $output .= '</div>';
            }

            $output .= '</div>'; // Close mega-menu-grid
            $output .= '</div>'; // Close mega-menu-inner
            $output .= '</div>'; // Close mega-menu-container
        }

        return $output;
    }
}
