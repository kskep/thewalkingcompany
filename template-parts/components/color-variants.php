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

// Get data from the template args
$variants = $args['variants'] ?? array();
$current_id = $args['current_id'] ?? 0;

// Fallback: if no variants passed, try to get them from global product
if (empty($variants) && !empty($current_id)) {
    $variants = eshop_get_product_color_group_variants($current_id);
}

// Don't display if only one or no variants
if (count($variants) <= 1) {
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
    <div>
        <span class="block-label"><?php _e('Color Story', 'eshop-theme'); ?></span>
        <div class="color-palette">
            <?php foreach ($variants as $variant) :
                $variant_classes = array('swatch');
                
                if ($variant['is_current']) {
                    $variant_classes[] = 'selected';
                }
                
                if (!$variant['in_stock']) {
                    $variant_classes[] = 'disabled';
                }
                
                $class_string = implode(' ', $variant_classes);
                $color_name = $variant['name'] ?: __('Unnamed Color', 'eshop-theme');
                $color_hex = $variant['hex'] ?: '#f8c5d8';
                
                // Create gradient style for color swatch
                $gradient_style = '';
                if ($variant['hex']) {
                    $gradient_style = sprintf(
                        'background: radial-gradient(circle at 30%% 30%%, rgba(255, 255, 255, 0.65) 0%%, %s 72%%); background-color: %s;',
                        $color_hex,
                        $color_hex
                    );
                } else {
                    $gradient_style = sprintf(
                        'background: radial-gradient(circle at 30%% 30%%, rgba(255, 255, 255, 0.65) 0%%, %s 72%%); background-color: %s;',
                        $color_hex,
                        $color_hex
                    );
                }
            ?>
                <button type="button"
                        class="<?php echo esc_attr($class_string); ?>"
                        data-product-id="<?php echo esc_attr($variant['id']); ?>"
                        data-url="<?php echo esc_url($variant['url']); ?>"
                        data-color-name="<?php echo esc_attr($color_name); ?>"
                        data-price="<?php echo esc_attr($variant['price']); ?>"
                        data-in-stock="<?php echo $variant['in_stock'] ? '1' : '0'; ?>"
                        title="<?php echo esc_attr($color_name); ?>"
                        aria-label="<?php echo esc_attr(sprintf(__('Select %s color variant', 'eshop-theme'), $color_name)); ?>"
                        <?php echo !$variant['in_stock'] ? 'disabled' : ''; ?>>
                    
                    <span class="tone" style="<?php echo esc_attr($gradient_style); ?>"></span>
                    <span class="swatch-name"><?php echo esc_html($color_name); ?></span>
                </button>
            <?php endforeach; ?>
        </div>
    </div>
</div>