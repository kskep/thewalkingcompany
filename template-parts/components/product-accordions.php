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

    <!-- Product Features -->
    <div class="product-accordion">
        <button class="accordion-header" aria-expanded="false">
            <span><?php _e('Product Features', 'eshop-theme'); ?></span>
            <i class="fas fa-chevron-down accordion-icon"></i>
        </button>
        <div class="accordion-panel" aria-hidden="true">
            <div class="accordion-content">
                <?php
                // Check if product has attributes to display
                $attributes = $product->get_attributes();
                $has_attributes = !empty($attributes);

                if ($has_attributes) {
                    // Start with default features
                    echo '<ul>';
                    echo '<li>' . __('Premium leather upper with breathable mesh panels', 'eshop-theme') . '</li>';
                    echo '<li>' . __('Advanced cushioning system for all-day comfort', 'eshop-theme') . '</li>';
                    echo '<li>' . __('Durable rubber outsole with traction pattern', 'eshop-theme') . '</li>';
                    echo '<li>' . __('Moisture-wicking interior lining', 'eshop-theme') . '</li>';
                    echo '<li>' . __('Removable insole for custom orthotics', 'eshop-theme') . '</li>';
                    echo '<li>' . __('Reflective details for low-light visibility', 'eshop-theme') . '</li>';
                    echo '<li>' . __('Weight: 10.5 oz per shoe (men\'s size 9)', 'eshop-theme') . '</li>';

                    // Add product-specific attributes
                    foreach ($attributes as $attribute) {
                        if ($attribute->get_variation()) {
                            continue; // Skip variation attributes
                        }
                        $attribute_name = $attribute->get_name();
                        $attribute_label = wc_attribute_label($attribute_name, $product);
                        $attribute_values = wp_get_post_terms($product->get_id(), $attribute_name, array('fields' => 'names'));

                        if (!empty($attribute_values)) {
                            echo '<li><strong>' . esc_html($attribute_label) . ':</strong> ' . esc_html(implode(', ', $attribute_values)) . '</li>';
                        }
                    }
                    echo '</ul>';
                } else {
                    // Default features if no attributes
                    echo '<ul>';
                    echo '<li>' . __('Premium leather upper with breathable mesh panels', 'eshop-theme') . '</li>';
                    echo '<li>' . __('Advanced cushioning system for all-day comfort', 'eshop-theme') . '</li>';
                    echo '<li>' . __('Durable rubber outsole with traction pattern', 'eshop-theme') . '</li>';
                    echo '<li>' . __('Moisture-wicking interior lining', 'eshop-theme') . '</li>';
                    echo '<li>' . __('Removable insole for custom orthotics', 'eshop-theme') . '</li>';
                    echo '<li>' . __('Reflective details for low-light visibility', 'eshop-theme') . '</li>';
                    echo '<li>' . __('Weight: 10.5 oz per shoe (men\'s size 9)', 'eshop-theme') . '</li>';
                    echo '</ul>';
                }
                ?>
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