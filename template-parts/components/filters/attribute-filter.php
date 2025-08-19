<?php
/**
 * Product Attribute Filter Component
 * 
 * @package E-Shop Theme
 */

if (!defined('ABSPATH')) { exit; }

// Get the attribute from args
$attribute = isset($args['attribute']) ? $args['attribute'] : null;

if (!$attribute) {
    return;
}

$taxonomy = 'pa_' . $attribute->attribute_name;
$attribute_label = $attribute->attribute_label;

// Get attribute terms
$terms = get_terms(array(
    'taxonomy' => $taxonomy,
    'hide_empty' => true,
));

if (empty($terms) || is_wp_error($terms)) {
    return;
}

// Get currently selected values
$selected_values = isset($_GET[$taxonomy]) ? (array) $_GET[$taxonomy] : array();
?>

<div class="filter-section mb-6 attribute-filter" data-attribute="<?php echo esc_attr($attribute->attribute_name); ?>">
    <h4 class="filter-title text-sm font-semibold text-gray-900 mb-3 pb-2 border-b border-gray-100">
        <?php echo esc_html($attribute_label); ?>
    </h4>
    
    <div class="attribute-terms space-y-2 max-h-48 overflow-y-auto">
        <?php foreach ($terms as $term) : 
            $is_checked = in_array($term->slug, $selected_values);
            $product_count = $term->count;
            
            // Get term meta for color/image if it's a color/image attribute
            $term_meta = get_term_meta($term->term_id);
            $color_value = isset($term_meta['color']) ? $term_meta['color'][0] : '';
            $image_id = isset($term_meta['image']) ? $term_meta['image'][0] : '';
        ?>
            <label class="flex items-center justify-between space-x-2 cursor-pointer hover:bg-gray-50 p-2 rounded group">
                <div class="flex items-center space-x-2">
                    <input 
                        type="checkbox" 
                        name="<?php echo esc_attr($taxonomy); ?>[]" 
                        value="<?php echo esc_attr($term->slug); ?>"
                        class="text-primary focus:ring-primary border-gray-300 rounded"
                        <?php checked($is_checked); ?>
                    >
                    
                    <?php if ($color_value) : ?>
                        <!-- Color swatch for color attributes -->
                        <span 
                            class="w-4 h-4 rounded-full border border-gray-300 inline-block" 
                            style="background-color: <?php echo esc_attr($color_value); ?>"
                            title="<?php echo esc_attr($term->name); ?>"
                        ></span>
                    <?php elseif ($image_id) : ?>
                        <!-- Image for image attributes -->
                        <?php 
                        $image_url = wp_get_attachment_image_url($image_id, 'thumbnail');
                        if ($image_url) :
                        ?>
                            <img 
                                src="<?php echo esc_url($image_url); ?>" 
                                alt="<?php echo esc_attr($term->name); ?>"
                                class="w-6 h-6 object-cover rounded border border-gray-300"
                            >
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <span class="text-sm text-gray-700 group-hover:text-gray-900">
                        <?php echo esc_html($term->name); ?>
                    </span>
                </div>
                <span class="text-xs text-gray-400 bg-gray-100 px-2 py-1 rounded-full">
                    <?php echo esc_html($product_count); ?>
                </span>
            </label>
        <?php endforeach; ?>
    </div>
</div>
