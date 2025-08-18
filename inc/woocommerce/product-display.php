<?php
/**
 * WooCommerce Product Display helpers (modularized)
 * - Color & Size variant helpers
 * - Product badges helper + single badges hook
 * - Optional: custom variation display rendering helpers
 *
 * Keep this module focused on small, reusable UI helpers for product display.
 */

if (!defined('ABSPATH')) { exit; }

/**
 * Product Color Variants Helper
 */
function eshop_get_product_color_variants($product, $limit = 4) {
    if (!$product->is_type('variable')) {
        return array();
    }
    
    $available_variations = $product->get_available_variations();
    $color_attribute = null;
    $colors = array();
    
    // Find color attribute
    foreach ($product->get_variation_attributes() as $attribute_name => $options) {
        $attr_lower = strtolower($attribute_name);
        if (strpos($attr_lower, 'color') !== false || strpos($attr_lower, 'colour') !== false) {
            $color_attribute = $attribute_name;
            break;
        }
    }
    
    if (!$color_attribute || empty($available_variations)) {
        return array();
    }
    
    $colors_shown = array();
    $color_count = 0;
    
    foreach ($available_variations as $variation) {
        if ($color_count >= $limit) break;
        
        $color_value = $variation['attributes']['attribute_' . strtolower(str_replace('pa_', '', $color_attribute))];
        
        if (!in_array($color_value, $colors_shown) && $color_value) {
            $colors_shown[] = $color_value;
            
            // Get color hex value
            $color_hex = '#ccc'; // Default
            if (taxonomy_exists($color_attribute)) {
                $term = get_term_by('slug', $color_value, $color_attribute);
                if ($term) {
                    $term_color = get_term_meta($term->term_id, 'color', true);
                    if ($term_color) {
                        $color_hex = $term_color;
                    } else {
                        // Fallback color mapping
                        $color_map = array(
                            'black' => '#000000',
                            'white' => '#ffffff',
                            'red' => '#dc2626',
                            'blue' => '#2563eb',
                            'green' => '#16a34a',
                            'yellow' => '#eab308',
                            'pink' => '#ec4899',
                            'purple' => '#9333ea',
                            'gray' => '#6b7280',
                            'brown' => '#92400e',
                            'beige' => '#f5f5dc',
                            'navy' => '#1e3a8a'
                        );
                        
                        $color_lower = strtolower($color_value);
                        foreach ($color_map as $name => $hex) {
                            if (strpos($color_lower, $name) !== false) {
                                $color_hex = $hex;
                                break;
                            }
                        }
                    }
                }
            }
            
            $colors[] = array(
                'name' => $color_value,
                'hex' => $color_hex
            );
            
            $color_count++;
        }
    }
    
    return $colors;
}

/**
 * Product Size Variants Helper
 */
function eshop_get_product_size_variants($product, $limit = 8) {
    if (!$product->is_type('variable')) {
        return array();
    }

    $available_variations = $product->get_available_variations();
    $size_attribute = null;

    // Find size attribute - look for 'size-selection' first, then fallback to 'size'
    foreach ($product->get_variation_attributes() as $attribute_name => $options) {
        $attr_lower = strtolower($attribute_name);
        // Priority order: size-selection, size_selection, size
        if (strpos($attr_lower, 'size-selection') !== false || strpos($attr_lower, 'size_selection') !== false) {
            $size_attribute = $attribute_name;
            break;
        } elseif (strpos($attr_lower, 'size') !== false && !$size_attribute) {
            $size_attribute = $attribute_name;
        }
    }

    if (!$size_attribute || empty($available_variations)) {
        return array();
    }

    $sizes_data = array();

    // Collect all size variations with their stock status
    foreach ($available_variations as $variation) {
        $variation_obj = wc_get_product($variation['variation_id']);
        if (!$variation_obj) continue;

        $size_value = $variation['attributes']['attribute_' . strtolower(str_replace('pa_', '', $size_attribute))];

        if ($size_value && !isset($sizes_data[$size_value])) {
            $sizes_data[$size_value] = array(
                'name' => $size_value,
                'slug' => $size_value,
                'in_stock' => $variation_obj->is_in_stock(),
                'variation_id' => $variation['variation_id']
            );
        }
    }

    // Sort sizes with smart sorting (numeric first, then alphabetic)
    uksort($sizes_data, function($a, $b) {
        $a_is_numeric = is_numeric($a);
        $b_is_numeric = is_numeric($b);

        if ($a_is_numeric && $b_is_numeric) {
            return (float)$a - (float)$b;
        }
        if ($a_is_numeric && !$b_is_numeric) {
            return -1;
        }
        if (!$a_is_numeric && $b_is_numeric) {
            return 1;
        }
        return strcmp($a, $b);
    });

    return array_slice($sizes_data, 0, $limit, true);
}

/**
 * Product Badges Helper
 */
function eshop_get_product_badges($product) {
    $badges = array();

    if ($product->is_on_sale()) {
        $badges[] = array('text' => __('SALE', 'eshop-theme'), 'class' => 'badge-sale', 'style' => 'background-color: #dc2626; color: white;');
    }
    if (!$product->is_in_stock()) {
        $badges[] = array('text' => __('OUT OF STOCK', 'eshop-theme'), 'class' => 'badge-out-of-stock', 'style' => 'background-color: #6b7280; color: white;');
    }

    // New in last 30 days
    $created_date = get_the_date('U', $product->get_id());
    if ($created_date > strtotime('-30 days')) {
        $badges[] = array('text' => __('NEW', 'eshop-theme'), 'class' => 'badge-new', 'style' => 'background-color: #16a34a; color: white;');
    }

    if ($product->is_featured()) {
        $badges[] = array('text' => __('HOT', 'eshop-theme'), 'class' => 'badge-hot', 'style' => 'background-color: #ea580c; color: white;');
    }

    if ($product->managing_stock() && $product->get_stock_quantity() <= 5 && $product->get_stock_quantity() > 0) {
        $badges[] = array('text' => __('LOW STOCK', 'eshop-theme'), 'class' => 'badge-low-stock', 'style' => 'background-color: #f59e0b; color: white;');
    }

    return $badges;
}

/**
 * Custom Single Product Badges Display (hooked)
 */
function eshop_custom_single_product_badges() {
    global $product;

    $badges = eshop_get_product_badges($product);
    if (empty($badges)) return;

    echo '<div class="single-product-badges absolute top-4 left-4 flex flex-col gap-2 z-10">';
    foreach ($badges as $badge) {
        echo '<span class="badge ' . esc_attr($badge['class']) . ' text-xs px-3 py-1 font-semibold rounded text-center" style="' . esc_attr($badge['style']) . '">';
        echo esc_html($badge['text']);
        echo '</span>';
    }
    echo '</div>';
}

// Replace default sale flash with our badges on single product
remove_action('woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10);
add_action('woocommerce_before_single_product_summary', 'eshop_custom_single_product_badges', 10);

