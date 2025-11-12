<?php
/**
 * Color Variants Display Component
 * 
 * Displays color variant selector for products with grouped-sku
 *
 * @package E-Shop Theme
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// DEBUG: Log template loading
error_log('DEBUG: color-variants.php template loaded for product ID: ' . ($current_id ?? 'unknown'));
error_log('DEBUG: Variants count: ' . (isset($variants) ? count($variants) : 'not set'));

// Get data from the template args
$variants = $args['variants'] ?? array();
$current_id = $args['current_id'] ?? 0;

// Fallback: if no variants passed, try to get them from global product
if (empty($variants) && !empty($current_id)) {
    $variants = eshop_get_product_color_group_variants($current_id);
    error_log('DEBUG: Fallback - loaded ' . count($variants) . ' variants for product ID: ' . $current_id);
}

// Don't display if only one or no variants
if (count($variants) <= 1) {
    error_log('DEBUG: Not enough variants to display in template, returning early');
    return;
}

// Find current variant for selected state display
$current_variant = null;
foreach ($variants as $variant) {
    if ($variant['is_current']) {
        $current_variant = $variant;
        break;
    }
}
?>

<div class="product-color-variants" data-product-id="<?php echo esc_attr($current_id); ?>">
    <h4 class="color-variants-title"><?php _e('Available Colors', 'eshop-theme'); ?></h4>
    
    <div class="color-variants-container">
        <?php foreach ($variants as $variant) : 
            $variant_classes = array('color-variant');
            
            if ($variant['is_current']) {
                $variant_classes[] = 'selected';
            }
            
            if (!$variant['in_stock']) {
                $variant_classes[] = 'out-of-stock';
            }
            
            $class_string = implode(' ', $variant_classes);
            $color_name = $variant['name'] ?: __('Unnamed Color', 'eshop-theme');
        ?>
            <div class="<?php echo esc_attr($class_string); ?>" 
                 data-product-id="<?php echo esc_attr($variant['id']); ?>"
                 data-url="<?php echo esc_url($variant['url']); ?>"
                 data-color-name="<?php echo esc_attr($color_name); ?>"
                 data-price="<?php echo esc_attr($variant['price']); ?>"
                 data-in-stock="<?php echo $variant['in_stock'] ? '1' : '0'; ?>"
                 title="<?php echo esc_attr($color_name); ?>"
                 role="button"
                 tabindex="0"
                 aria-label="<?php echo esc_attr(sprintf(__('Select %s color variant', 'eshop-theme'), $color_name)); ?>">
                
                <?php if ($variant['image']) : ?>
                    <img src="<?php echo esc_url($variant['image']); ?>" 
                         alt="<?php echo esc_attr($color_name); ?>" 
                         loading="lazy" />
                <?php elseif ($variant['hex']) : ?>
                    <div class="color-swatch" 
                         style="background-color: <?php echo esc_attr($variant['hex']); ?>"></div>
                <?php else : ?>
                    <!-- Fallback for variants without image or color -->
                    <div class="color-swatch" 
                         style="background: linear-gradient(45deg, #f0f0f0 25%, #e0e0e0 25%, #e0e0e0 50%, #f0f0f0 50%, #f0f0f0 75%, #e0e0e0 75%); background-size: 8px 8px;"></div>
                <?php endif; ?>
                
                <?php if (!$variant['in_stock']) : ?>
                    <span class="sr-only"><?php _e('Out of stock', 'eshop-theme'); ?></span>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    
</div>