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
        <!-- Label removed as per new design -->
        <div class="color-thumbnails-row">
            <?php foreach ($variants as $variant) :
                $variant_classes = array('color-thumbnail-swatch');
                
                if ($variant['is_current']) {
                    $variant_classes[] = 'selected';
                }
                
                if (!$variant['in_stock']) {
                    $variant_classes[] = 'disabled';
                }
                
                $class_string = implode(' ', $variant_classes);
                $color_name = $variant['name'] ?: __('Unnamed Color', 'eshop-theme');
                
                // Use featured image or fallback placeholder
                $image_src = $variant['image'];
                if (empty($image_src)) {
                    $image_src = wc_placeholder_img_src();
                }
            ?>
                <div class="color-swatch-wrapper">
                    <button type="button"
                            class="<?php echo esc_attr($class_string); ?>"
                            onclick="window.location.href='<?php echo esc_url($variant['url']); ?>'"
                            title="<?php echo esc_attr($color_name); ?>"
                            aria-label="<?php echo esc_attr(sprintf(__('Select %s color variant', 'eshop-theme'), $color_name)); ?>"
                            <?php echo !$variant['in_stock'] ? 'disabled' : ''; ?>>
                        
                        <img src="<?php echo esc_url($image_src); ?>" alt="<?php echo esc_attr($color_name); ?>" />
                    </button>
                    <span class="color-name"><?php echo esc_html($color_name); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>