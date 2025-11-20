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
    // Reuse safe attribute reader from color variants if available
    $read_attr = function(array $variation, string $attrTax) {
        $candidates = array(
            'attribute_' . sanitize_title($attrTax),
            'attribute_' . sanitize_title(str_replace('pa_', '', $attrTax))
        );
        foreach ($candidates as $key) {
            if (isset($variation['attributes'][$key])) {
                return $variation['attributes'][$key];
            }
        }
        return '';
    };

    foreach ($available_variations as $variation) {
        $variation_obj = wc_get_product($variation['variation_id']);
        if (!$variation_obj) continue;

    $size_value = $read_attr($variation, $size_attribute);

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
    // Normalize to a WC_Product instance to avoid method calls on unexpected types.
    if ($product instanceof WC_Product_Variation) {
        $product = wc_get_product($product->get_parent_id()) ?: $product;
    } elseif (!$product instanceof WC_Product) {
        if (is_numeric($product)) {
            $product = wc_get_product((int) $product);
        } elseif (is_string($product)) {
            $product = wc_get_product($product);
        } elseif (is_array($product)) {
            if (isset($product['product']) && $product['product'] instanceof WC_Product) {
                $product = $product['product'];
            } elseif (isset($product['product_id'])) {
                $product = wc_get_product((int) $product['product_id']);
            }
        }
    }

    if (!$product instanceof WC_Product) {
        $queried_id = get_the_ID() ?: get_queried_object_id();
        if ($queried_id) {
            $product = wc_get_product($queried_id);
        }
    }

    if (!$product instanceof WC_Product) {
        return array();
    }

    $badges = array();

    if ($product->is_on_sale()) {
        $badges[] = array('text' => __('SALE', 'eshop-theme'), 'class' => 'badge-sale', 'style' => 'background-color: #dc2626; color: white;');
    }
    if (!$product->is_in_stock()) {
        $badges[] = array('text' => __('OUT OF STOCK', 'eshop-theme'), 'class' => 'badge-out-of-stock', 'style' => 'background-color: #6b7280; color: white;');
    }

    // New in last 30 days
    $created_date = $product->get_date_created();
    if ($created_date && $created_date->getTimestamp() > strtotime('-30 days')) {
        $badges[] = array('text' => __('NEW', 'eshop-theme'), 'class' => 'badge-new', 'style' => 'background-color: #16a34a; color: white;');
    }

    if ($product->is_featured()) {
        $badges[] = array('text' => __('HOT', 'eshop-theme'), 'class' => 'badge-hot', 'style' => 'background-color: #ea580c; color: white;');
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

