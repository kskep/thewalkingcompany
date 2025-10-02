<?php
/**
 * Size Selection Component Template - 2025 Standards
 * 
 * Displays size attribute selection with circular button interface
 * Supports both shoe sizes (numeric) and clothing sizes (text abbreviations)
 *
 * @package thewalkingtheme
 */

defined('ABSPATH') || exit;

global $product;

if (!$product || !$product->is_type('variable')) {
    return;
}

// Get size variants using our helper function
$size_variants = function_exists('eshop_get_product_size_variants') 
    ? eshop_get_product_size_variants($product, 12) 
    : array();

if (empty($size_variants)) {
    return;
}

// Get the size attribute name for this product
$size_attribute_name = eshop_get_size_attribute_name($product);
if (!$size_attribute_name) {
    return;
}

// Detect attribute type based on sizes
$attribute_type = 'auto';
$has_numeric = false;
$has_text = false;

foreach ($size_variants as $size) {
    if (is_numeric($size['name'])) {
        $has_numeric = true;
    } else {
        $has_text = true;
    }
}

if ($has_numeric && !$has_text) {
    $attribute_type = 'shoe';
} elseif ($has_text && !$has_numeric) {
    $attribute_type = 'clothing';
}

// Check if there's size guide information
$size_guide_url = get_post_meta($product->get_id(), '_size_guide_url', true);
$size_guide_info = get_post_meta($product->get_id(), '_size_guide_info', true);

?>

<div class=\"size-selection-container\" 
     data-product-id=\"<?php echo esc_attr($product->get_id()); ?>\"
     data-attribute-type=\"<?php echo esc_attr($attribute_type); ?>\"
     data-attribute-name=\"<?php echo esc_attr($size_attribute_name); ?>\"
     <?php if ($size_guide_url) : ?>
     data-size-guide-url=\"<?php echo esc_url($size_guide_url); ?>\"
     <?php endif; ?>>
     
    <!-- Size Selection Label -->
    <label class=\"size-selection-label\" for=\"size-selection-<?php echo esc_attr($product->get_id()); ?>\">
        <?php _e('Size', 'thewalkingtheme'); ?>
        <span class=\"selected-size\"></span>
    </label>
    
    <!-- Size Type Info (for accessibility and clarity) -->
    <?php if ($attribute_type === 'shoe') : ?>
    <div class=\"size-type-info\">
        <span class=\"size-type-badge\"><?php _e('Shoe Sizes', 'thewalkingtheme'); ?></span>
    </div>
    <?php elseif ($attribute_type === 'clothing') : ?>
    <div class=\"size-type-info\">
        <span class=\"size-type-badge\"><?php _e('Clothing Sizes', 'thewalkingtheme'); ?></span>
    </div>
    <?php endif; ?>
    
    <!-- Size Options Container -->
    <div class=\"size-options-container\" id=\"size-selection-<?php echo esc_attr($product->get_id()); ?>\">
        <div class=\"size-options\" role=\"radiogroup\" aria-label=\"<?php esc_attr_e('Select size', 'thewalkingtheme'); ?>\">
            <?php foreach ($size_variants as $size_value => $size_data) : 
                $option_classes = array('size-option');
                
                if (!$size_data['in_stock']) {
                    $option_classes[] = 'out-of-stock';
                }
                
                // Add type-specific classes
                if ($attribute_type === 'shoe') {
                    $option_classes[] = 'shoe-size';
                } elseif ($attribute_type === 'clothing') {
                    $option_classes[] = 'clothing-size';
                }
                
                $display_name = $size_data['name'];
                $full_size_name = $size_data['name'];
                
                // Handle clothing size abbreviations
                if ($attribute_type === 'clothing') {
                    $abbreviations = array(
                        'Extra Small' => 'XS',
                        'Small' => 'S',
                        'Medium' => 'M',
                        'Large' => 'L',
                        'Extra Large' => 'XL',
                        'Double Extra Large' => 'XXL',
                        'Triple Extra Large' => 'XXXL'
                    );
                    
                    if (isset($abbreviations[$display_name])) {
                        $display_name = $abbreviations[$display_name];
                    }
                }
            ?>
                <button type=\"button\" 
                        class=\"<?php echo esc_attr(implode(' ', $option_classes)); ?>\"
                        data-size=\"<?php echo esc_attr($size_value); ?>\"
                        data-full-size=\"<?php echo esc_attr($full_size_name); ?>\"
                        data-variation-id=\"<?php echo esc_attr($size_data['variation_id']); ?>\"
                        data-in-stock=\"<?php echo $size_data['in_stock'] ? '1' : '0'; ?>\"
                        <?php if (!$size_data['in_stock']) : ?>
                        disabled
                        aria-label=\"<?php echo esc_attr(sprintf(__('%s - Out of stock', 'thewalkingtheme'), $full_size_name)); ?>\"
                        <?php else : ?>
                        aria-label=\"<?php echo esc_attr(sprintf(__('Select size %s', 'thewalkingtheme'), $full_size_name)); ?>\"
                        <?php endif; ?>
                        role=\"radio\"
                        aria-checked=\"false\"
                        tabindex=\"0\">
                    <?php echo esc_html($display_name); ?>
                </button>
            <?php endforeach; ?>
        </div>
    </div>
    
    <!-- Size Guide Link -->
    <div class=\"size-guide-container\">
        <?php if ($size_guide_url) : ?>
            <a href=\"#\" class=\"size-guide-link\" data-size-guide-url=\"<?php echo esc_url($size_guide_url); ?>\">
                <svg width=\"16\" height=\"16\" viewBox=\"0 0 24 24\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\">
                    <path d=\"M12 2L13.09 8.26L22 9L13.09 9.74L12 16L10.91 9.74L2 9L10.91 8.26L12 2Z\" stroke=\"currentColor\" stroke-width=\"2\" stroke-linecap=\"round\" stroke-linejoin=\"round\"/>
                </svg>
                <?php _e('Size Guide', 'thewalkingtheme'); ?>
            </a>
        <?php endif; ?>
        
        <?php if (!$size_guide_url && count($size_variants) > 6) : ?>
            <button type=\"button\" class=\"size-chart-trigger\">
                <svg width=\"16\" height=\"16\" viewBox=\"0 0 24 24\" fill=\"none\" xmlns=\"http://www.w3.org/2000/svg\">
                    <rect x=\"3\" y=\"3\" width=\"18\" height=\"18\" rx=\"2\" stroke=\"currentColor\" stroke-width=\"2\"/>
                    <path d=\"M9 9h6m-6 4h6m-6 4h4\" stroke=\"currentColor\" stroke-width=\"2\"/>
                </svg>
                <?php _e('Size Chart', 'thewalkingtheme'); ?>
            </button>
        <?php endif; ?>
    </div>
    
    <!-- Size Information (hidden by default) -->
    <?php if ($size_guide_info) : ?>
    <div class=\"size-info\">
        <h4><?php _e('Size Information', 'thewalkingtheme'); ?></h4>
        <div class=\"size-info-content\">
            <?php echo wp_kses_post($size_guide_info); ?>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Error Display -->
    <div class=\"size-selection-error\" role=\"alert\" aria-live=\"polite\">
        <!-- Error messages will be inserted here by JavaScript -->
    </div>
    
    <!-- Success Display -->
    <div class=\"size-selection-success\" role=\"status\" aria-live=\"polite\">
        <?php _e('Size selected successfully', 'thewalkingtheme'); ?>
    </div>
    
</div>

<?php

/**
 * Helper function to get size attribute name for a product
 */
function eshop_get_size_attribute_name($product) {
    if (!$product || !$product->is_type('variable')) {
        return false;
    }
    
    $attributes = $product->get_variation_attributes();
    
    // Priority order for size attributes
    $size_priorities = array(
        'pa_size-selection',
        'pa_size_selection', 
        'pa_select-size',
        'pa_select_size',
        'pa_size'
    );
    
    // First, check priority attributes
    foreach ($size_priorities as $priority_attr) {
        if (isset($attributes[$priority_attr])) {
            return $priority_attr;
        }
    }
    
    // Fallback: find any attribute with 'size' in the name
    foreach ($attributes as $attribute_name => $options) {
        if (strpos(strtolower($attribute_name), 'size') !== false) {
            return $attribute_name;
        }
    }
    
    return false;
}

?>