<?php
/**
 * Product Information Accordions Component - Magazine Style
 *
 * Displays product information in accordion format matching demo design
 *
 * @package thewalkingtheme
 */

defined('ABSPATH') || exit;

global $product;

if (!$product) {
    return;
}
?>

<div class="product-accordions-container">

    <!-- Size & Fit Guide -->
    <div class="product-accordion">
        <button class="accordion-header" aria-expanded="false">
            <span><?php _e('Size & Fit Guide', 'eshop-theme'); ?></span>
            <i class="fas fa-chevron-down accordion-icon"></i>
        </button>
        <div class="accordion-panel" aria-hidden="true">
            <div class="accordion-content">
                <p><?php _e('These shoes run true to size. We recommend ordering your usual shoe size for the best fit.', 'eshop-theme'); ?></p>
                <ul>
                    <li><?php _e('Medium width (D for men, B for women)', 'eshop-theme'); ?></li>
                    <li><?php _e('Heel-to-toe drop: 8mm', 'eshop-theme'); ?></li>
                    <li><?php _e('Weight: 10.5 oz (men\'s size 9)', 'eshop-theme'); ?></li>
                    <li><?php _e('For wide feet, consider ordering a half size up', 'eshop-theme'); ?></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Shipping & Returns -->
    <div class="product-accordion">
        <button class="accordion-header" aria-expanded="false">
            <span><?php _e('Shipping & Returns', 'eshop-theme'); ?></span>
            <i class="fas fa-chevron-down accordion-icon"></i>
        </button>
        <div class="accordion-panel" aria-hidden="true">
            <div class="accordion-content">
                <p><strong><?php _e('Free Shipping:', 'eshop-theme'); ?></strong> <?php _e('On orders over $75. Delivered in 3-5 business days.', 'eshop-theme'); ?></p>
                <p><strong><?php _e('Express Shipping:', 'eshop-theme'); ?></strong> <?php _e('$12.95. Delivered in 1-2 business days.', 'eshop-theme'); ?></p>
                <p><strong><?php _e('Returns:', 'eshop-theme'); ?></strong> <?php _e('30-day return policy. Items must be in original condition with tags attached.', 'eshop-theme'); ?></p>
                <p><strong><?php _e('Exchanges:', 'eshop-theme'); ?></strong> <?php _e('Free exchanges for different sizes or colors.', 'eshop-theme'); ?></p>
            </div>
        </div>
    </div>

</div>

<script>
// Initialize product accordions when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Simple accordion toggle - matching demo HTML structure
    const accordionHeaders = document.querySelectorAll('.accordion-header');
    
    accordionHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const expanded = this.getAttribute('aria-expanded') === 'true';
            const panel = this.nextElementSibling;
            
            // Toggle state
            this.setAttribute('aria-expanded', String(!expanded));
            panel.setAttribute('aria-hidden', String(expanded));
            
            // Rotate icon
            const icon = this.querySelector('.accordion-icon');
            if (icon) {
                icon.style.transform = expanded ? 'rotate(0deg)' : 'rotate(180deg)';
            }
        });
    });
});
</script>