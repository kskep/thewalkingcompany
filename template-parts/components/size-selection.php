<?php
/**
 * Size Selection Component
 * Renders circular size selector buttons for WooCommerce products
 * Handles attributes with slugs 'select-size' and 'size-selection'
 * 
 * @package TheWalkingCompany
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

global $product;

// Check if product exists
if (!$product) {
    return;
}

// Get product attributes
$attributes = $product->get_attributes();
$size_attribute = null;

// Look for size attributes with the specified slugs
foreach ($attributes as $attribute) {
    if ($attribute->get_name() === 'pa_select-size' || $attribute->get_name() === 'pa_size-selection') {
        $size_attribute = $attribute;
        break;
    }
}

// If no size attribute found, don't render component
if (!$size_attribute || !$size_attribute->get_visible()) {
    return;
}

// Get size terms
$size_terms = wc_get_product_terms($product->get_id(), $size_attribute->get_name(), array('all' => true));

if (empty($size_terms)) {
    return;
}

// Get current selected variation (if any)
$selected_variation = isset($_REQUEST['variation_id']) ? absint($_REQUEST['variation_id']) : 0;
$selected_size = '';

if ($selected_variation) {
    $variation = wc_get_product($selected_variation);
    if ($variation) {
        $selected_size = $variation->get_attribute($size_attribute->get_name());
    }
}

// Container for size selection
?>
<div class="size-selection-component" data-attribute-name="<?php echo esc_attr($size_attribute->get_name()); ?>">
    <h3 class="size-selection-label"><?php echo esc_html($size_attribute->get_name() === 'pa_select-size' ? 'Select Size' : 'Size Selection'); ?></h3>
    
    <div class="size-selector-buttons" role="radiogroup" aria-label="Product sizes">
        <?php foreach ($size_terms as $term) : 
            // Check if this size is in stock for any variation
            $size_in_stock = false;
            $variations_with_size = array();
            
            if ($product->is_type('variable')) {
                $variations = $product->get_available_variations();
                foreach ($variations as $variation_data) {
                    $variation = wc_get_product($variation_data['variation_id']);
                    if ($variation && $variation->get_attribute($size_attribute->get_name()) === $term->name) {
                        $variations_with_size[] = $variation_data['variation_id'];
                        if ($variation->is_in_stock()) {
                            $size_in_stock = true;
                        }
                    }
                }
            } else {
                // For simple products, just check if product is in stock
                $size_in_stock = $product->is_in_stock();
            }
            
            // Determine if this size is selected
            $is_selected = ($selected_size === $term->name);
            
            // Prepare CSS classes
            $button_classes = array(
                'size-selector-button',
                'size-' . sanitize_title($term->name)
            );
            
            if (!$size_in_stock) {
                $button_classes[] = 'out-of-stock';
            }
            
            if ($is_selected) {
                $button_classes[] = 'selected';
            }
            
            // Prepare ARIA attributes
            $aria_label = sprintf(
                'Size %s%s',
                esc_html($term->name),
                !$size_in_stock ? ' - Out of stock' : ''
            );
            
            $aria_checked = $is_selected ? 'true' : 'false';
            $tabindex = !$size_in_stock ? '-1' : '0';
            
            // Prepare data attributes for JavaScript
            $data_attributes = array(
                'size' => esc_attr($term->name),
                'attribute' => esc_attr($size_attribute->get_name())
            );
            
            if (!empty($variations_with_size)) {
                $data_attributes['variations'] = implode(',', $variations_with_size);
            }
            
            ?>
            <button 
                type="button"
                class="<?php echo esc_attr(implode(' ', $button_classes)); ?>"
                role="radio"
                aria-label="<?php echo esc_attr($aria_label); ?>"
                aria-checked="<?php echo esc_attr($aria_checked); ?>"
                tabindex="<?php echo esc_attr($tabindex); ?>"
                <?php foreach ($data_attributes as $key => $value) : ?>
                    data-<?php echo esc_attr($key); ?>="<?php echo esc_attr($value); ?>"
                <?php endforeach; ?>
                <?php disabled(!$size_in_stock, true); ?>
            >
                <span class="size-label"><?php echo esc_html($term->name); ?></span>
            </button>
        <?php endforeach; ?>
    </div>
    
    <?php if (!$product->is_type('variable')) : ?>
        <!-- For simple products, add a hidden input to store selected size -->
        <input type="hidden" name="<?php echo esc_attr($size_attribute->get_name()); ?>" class="selected-size-input" value="">
    <?php endif; ?>
    
    <!-- Size guide link (optional - can be enabled via theme options) -->
    <?php if (apply_filters('thewalking_show_size_guide', true)) : ?>
        <div class="size-guide-link">
            <button type="button" class="size-guide-trigger" aria-label="View size guide">
                <?php echo esc_html__('Size Guide', 'eshop-theme'); ?>
            </button>
        </div>
    <?php endif; ?>
</div>

<?php
/**
 * Additional notes for implementation:
 * 
 * 1. This component requires CSS styling to create the circular buttons
 *    as specified in SINGLE_PRODUCT_PLAN.txt
 * 
 * 2. JavaScript functionality needed for:
 *    - Handling size selection
 *    - Updating product variations
 *    - Managing stock status
 *    - Size guide modal (if enabled)
 * 
 * 3. The clothing size label transformation (XS, S, M, L, etc.)
 *    should be implemented via a PHP filter in functions.php
 *    or via JavaScript as mentioned in the plan
 * 
 * 4. Component integrates with WooCommerce variable products
 *    and works with the existing add-to-cart functionality
 * 
 * 5. Accessibility features include:
 *    - Proper ARIA labels and roles
 *    - Keyboard navigation support
 *    - Screen reader compatibility
 *    - Focus management
 */
?>