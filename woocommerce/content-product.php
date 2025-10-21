<?php
/**
 * Product Card uses component
 */

defined('ABSPATH') || exit;

global $product;

if (empty($product) || !$product->is_visible()) {
    return;
}

// Hide out of stock products completely (same behavior as before)
if (!$product->is_in_stock()) {
    return;
}

// Wrap component in <li class="product"> to match UL grid semantics
?>
<li class="product">
  <?php get_template_part('template-parts/components/product-card'); ?>
</li>