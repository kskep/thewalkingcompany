<?php
/**
 * Category Filter Component
 * 
 * @package E-Shop Theme
 */

if (!defined('ABSPATH')) { exit; }

// Get product categories
$categories = get_terms(array(
    'taxonomy' => 'product_cat',
    'hide_empty' => true,
    'parent' => 0, // Only top-level categories
));

if (empty($categories) || is_wp_error($categories)) {
    return;
}

// Get currently selected categories from URL (supports comma-separated IDs or slugs)
$selected_raw = isset($_GET['product_cat']) ? sanitize_text_field(wp_unslash($_GET['product_cat'])) : '';
$selected_tokens = $selected_raw !== '' ? array_filter(array_map('trim', explode(',', $selected_raw))) : array();
$selected_ids = array_map('intval', array_filter($selected_tokens, 'is_numeric'));
$selected_slugs = array_values(array_filter($selected_tokens, function($v){ return !is_numeric($v); }));
?>

<div class="filter-section mb-6">
    <h4 class="filter-title text-sm font-semibold text-gray-900 mb-3 pb-2 border-b border-gray-100">
        <?php esc_html_e('Categories', 'eshop-theme'); ?>
    </h4>
    
    <div class="category-filter space-y-2 max-h-48 overflow-y-auto">
        <?php foreach ($categories as $category) : 
            $is_checked = in_array($category->term_id, $selected_ids, true) || in_array($category->slug, $selected_slugs, true);
            $product_count = $category->count;
            
            // Get subcategories if any
            $subcategories = get_terms(array(
                'taxonomy' => 'product_cat',
                'hide_empty' => true,
                'parent' => $category->term_id,
            ));
        ?>
            <div class="category-item">
                <label class="flex items-center justify-between space-x-2 cursor-pointer hover:bg-gray-50 p-2 rounded group">
                    <div class="flex items-center space-x-2">
                        <input 
                            type="checkbox" 
                            name="product_cat[]" 
                            value="<?php echo esc_attr($category->term_id); ?>"
                            class="text-primary focus:ring-primary border-gray-300 rounded"
                            <?php checked($is_checked); ?>
                        >
                        <span class="text-sm text-gray-700 group-hover:text-gray-900">
                            <?php echo esc_html($category->name); ?>
                        </span>
                    </div>
                    <span class="text-xs text-gray-400 bg-gray-100 px-2 py-1 rounded-full">
                        <?php echo esc_html($product_count); ?>
                    </span>
                </label>

                <?php if (!empty($subcategories)) : ?>
                    <div class="subcategories ml-6 mt-1 space-y-1">
                        <?php foreach ($subcategories as $subcategory) : 
                            $sub_is_checked = in_array($subcategory->term_id, $selected_ids, true) || in_array($subcategory->slug, $selected_slugs, true);
                            $sub_product_count = $subcategory->count;
                        ?>
                            <label class="flex items-center justify-between space-x-2 cursor-pointer hover:bg-gray-50 p-1 rounded text-sm group">
                                <div class="flex items-center space-x-2">
                                    <input 
                                        type="checkbox" 
                                        name="product_cat[]" 
                                        value="<?php echo esc_attr($subcategory->term_id); ?>"
                                        class="text-primary focus:ring-primary border-gray-300 rounded"
                                        <?php checked($sub_is_checked); ?>
                                    >
                                    <span class="text-xs text-gray-600 group-hover:text-gray-800">
                                        <?php echo esc_html($subcategory->name); ?>
                                    </span>
                                </div>
                                <span class="text-xs text-gray-400 bg-gray-100 px-1.5 py-0.5 rounded-full">
                                    <?php echo esc_html($sub_product_count); ?>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
