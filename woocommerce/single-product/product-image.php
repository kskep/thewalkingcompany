<?php
/**
 * Product Image Template
 * Custom override to fix fatal error with get_image_id() on string
 * 
 * This template handles the product image gallery with proper error handling
 * and ensures compatibility with the magazine-style theme design.
 *
 * @package E-Shop Theme
 */

defined('ABSPATH') || exit;

global $product;

// Ensure we have a valid product object
if (!$product instanceof WC_Product) {
    // Try to get product from global or query
    if (isset($GLOBALS['product']) && $GLOBALS['product'] instanceof WC_Product) {
        $product = $GLOBALS['product'];
    } else {
        $product_id = get_the_ID();
        if ($product_id && get_post_type($product_id) === 'product') {
            $product = wc_get_product($product_id);
        }
    }
}

// If still no valid product, show placeholder
if (!$product instanceof WC_Product) {
    echo '<div class="product-gallery product-gallery--error">';
    echo '<div class="product-gallery__main">';
    echo '<div class="product-gallery__main-image-wrapper">';
    echo '<img src="' . esc_url(wc_placeholder_img_src('full')) . '" alt="' . esc_attr__('Product image placeholder', 'eshop-theme') . '" class="product-gallery__main-image-img">';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    return;
}

// Use our custom product gallery component
get_template_part('template-parts/components/product-gallery');