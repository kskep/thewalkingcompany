<?php
/**
 * Product Card uses component
 */

defined('ABSPATH') || exit;

global $product;

if (empty($product) || !$product->is_visible()) {
    return;
}

// Note: Out-of-stock products are now filtered at query level in functions.php
// This ensures we get exactly 12 in-stock products per page

// Wrap component in <li class="product"> to match UL grid semantics
?>
 <?php wc_product_class('product', $product); ?>>
  <?php get_template_part('template-parts/components/product-card'); ?>
