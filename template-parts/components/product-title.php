<?php
/**
 * Product Title & SKU Component
 * 
 * Displays product title with magazine-style typography and SKU
 *
 * @package thewalkingtheme
 */

defined('ABSPATH') || exit;

global $product;

if (!$product) {
    return;
}
?>

<div class="product-title-container">
    <h1 class="product-title">
        <?php echo esc_html($product->get_name()); ?>
    </h1>
    
    <?php if ($product->get_sku()) : ?>
        <div class="product-sku">
            <span class="sku-label"><?php _e('Κωδικός:', 'eshop-theme'); ?></span>
            <span class="sku-value"><?php echo esc_html($product->get_sku()); ?></span>
        </div>
    <?php endif; ?>
</div>