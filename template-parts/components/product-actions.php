<?php
/**
 * Product Actions Component
 * 
 * Add to cart and wishlist buttons for single product page
 *
 * @package thewalkingtheme
 */

defined('ABSPATH') || exit;

global $product;

if (!$product) {
    return;
}

$product_id = $product->get_id();
$is_in_wishlist = function_exists('eshop_is_in_wishlist') ? eshop_is_in_wishlist($product_id) : false;
?>

<div class="product-actions-container">
    <!-- Main Action Buttons -->
    <div class="action-buttons-row">
        <!-- Add to Cart Button -->
        <button type="button" class="btn-primary add-to-cart-btn" 
                data-product-id="<?php echo esc_attr($product_id); ?>"
                <?php echo !$product->is_purchasable() || !$product->is_in_stock() ? 'disabled' : ''; ?>>
            <span class="btn-text"><?php _e('ΠΡΟΣΘΗΚΗ ΣΤΟ ΚΑΛΑΘΙ', 'eshop-theme'); ?></span>
            <div class="btn-loading" style="display: none;">
                <svg class="btn-spinner" width="20" height="20" viewBox="0 0 24 24" fill="none">
                    <path d="M12 2v4m0 12v4M4.93 4.93l2.83 2.83m8.48 8.48l2.83 2.83M2 12h4m12 0h4M4.93 19.07l2.83-2.83m8.48-8.48l2.83-2.83" 
                          stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                </svg>
            </div>
        </button>
        
        <!-- Wishlist Button -->
        <button type="button" class="btn-secondary wishlist-btn" 
                data-product-id="<?php echo esc_attr($product_id); ?>"
                data-nonce="<?php echo wp_create_nonce('eshop_nonce'); ?>">
            <svg class="heart-icon" width="20" height="20" viewBox="0 0 24 24" fill="<?php echo $is_in_wishlist ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="2">
                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
            </svg>
            <span class="wishlist-text"><?php echo $is_in_wishlist ? __('Αγαπημένα', 'eshop-theme') : __('Προσθήκη στα αγαπημένα', 'eshop-theme'); ?></span>
        </button>
    </div>
    
    <!-- Availability Message -->
    <div class="product-availability" id="product-availability">
        <?php if ($product->is_in_stock()) : ?>
            <span class="stock-status in-stock">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 12l2 2 4-4M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <?php _e('Σε απόθεμα', 'eshop-theme'); ?>
            </span>
        <?php else : ?>
            <span class="stock-status out-of-stock">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="15" y1="9" x2="9" y2="15"/>
                    <line x1="9" y1="9" x2="15" y2="15"/>
                </svg>
                <?php _e('Εξαντλήθηκε', 'eshop-theme'); ?>
            </span>
        <?php endif; ?>
    </div>
    
    <!-- Stock Information -->
    <?php if ($product->managing_stock() && $product->get_stock_quantity() > 0) : ?>
        <?php $stock_quantity = $product->get_stock_quantity(); ?>
        <div class="stock-quantity">
            <span class="quantity-label"><?php _e('Διαθέσιμα:', 'eshop-theme'); ?></span>
            <span class="quantity-value"><?php echo esc_html($stock_quantity); ?></span>
            <?php if ($stock_quantity <= 5) : ?>
                <span class="low-stock-notice"><?php _e('Χαμηλό απόθεμα!', 'eshop-theme'); ?></span>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <!-- Feedback Messages -->
    <div class="action-feedback" id="action-feedback" role="alert" aria-live="polite"></div>
</div>

<script>
// Initialize product actions when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    const actionsContainer = document.querySelector('.product-actions-container');
    if (actionsContainer && typeof ProductActions !== 'undefined') {
        new ProductActions(actionsContainer);
    }
});
</script>