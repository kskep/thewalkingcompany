<?php
/**
 * Category Filter Component (context-aware)
 *
 * - On shop/tag: lists top-level categories with their children available in current context
 * - On a category archive: lists direct child categories so users can filter into subcategories
 *
 * @package E-Shop Theme
 */

if (!defined('ABSPATH')) { exit; }

// Parse selected categories from URL (IDs or slugs)
$selected_raw = isset($_GET['product_cat']) ? sanitize_text_field(wp_unslash($_GET['product_cat'])) : '';
$selected_tokens = $selected_raw !== '' ? array_filter(array_map('trim', explode(',', $selected_raw))) : array();
$selected_ids = array_map('intval', array_filter($selected_tokens, 'is_numeric'));
$selected_slugs = array_values(array_filter($selected_tokens, function($v){ return !is_numeric($v); }));

// Build hierarchical list of categories to display
$available_categories = array();
$current_category_id = 0;

if (is_product_category()) {
    // On a category archive: show direct children of current category
    $current = get_queried_object();
    if ($current && !is_wp_error($current)) {
        $current_category_id = (int) $current->term_id;
        $children = get_terms(array(
            'taxonomy' => 'product_cat',
            'hide_empty' => false, // Show all children even if temporarily empty due to other filters
            'parent' => $current_category_id,
            'orderby' => 'name',
            'order' => 'ASC',
        ));

        if (!empty($children) && !is_wp_error($children)) {
            foreach ($children as $term) {
                // Get grandchildren for this child
                $grandchildren = get_terms(array(
                    'taxonomy' => 'product_cat',
                    'hide_empty' => false,
                    'parent' => (int) $term->term_id,
                    'orderby' => 'name',
                    'order' => 'ASC',
                ));
                
                $child_data = array(
                    'term_id' => (int) $term->term_id,
                    'name' => $term->name,
                    'slug' => $term->slug,
                    'count' => isset($term->count) ? (int) $term->count : 0,
                    'children' => array(),
                    'level' => 1,
                );
                
                // Add grandchildren if they exist
                if (!empty($grandchildren) && !is_wp_error($grandchildren)) {
                    foreach ($grandchildren as $grandchild) {
                        $child_data['children'][] = array(
                            'term_id' => (int) $grandchild->term_id,
                            'name' => $grandchild->name,
                            'slug' => $grandchild->slug,
                            'count' => isset($grandchild->count) ? (int) $grandchild->count : 0,
                            'level' => 2,
                        );
                    }
                }
                
                $available_categories[] = $child_data;
            }
        }
    }
} else {
    // On other archives (shop page, tags, etc): get top-level categories and their children
    $top_level_categories = get_terms(array(
        'taxonomy' => 'product_cat',
        'hide_empty' => true,
        'parent' => 0,
        'orderby' => 'name',
        'order' => 'ASC',
    ));
    
    // Debug: Check what categories are found
    echo '<!-- Category Debug: Found ' . (is_array($top_level_categories) ? count($top_level_categories) : 0) . ' top-level categories -->';
    if (is_wp_error($top_level_categories)) {
        echo '<!-- Category Error: ' . esc_html($top_level_categories->get_error_message()) . ' -->';
    }
    
    if (!empty($top_level_categories) && !is_wp_error($top_level_categories)) {
        foreach ($top_level_categories as $category) {
            // Get children for this top-level category
            $children = get_terms(array(
                'taxonomy' => 'product_cat',
                'hide_empty' => true,
                'parent' => (int) $category->term_id,
                'orderby' => 'name',
                'order' => 'ASC',
            ));
            
            $category_data = array(
                'term_id' => (int) $category->term_id,
                'name' => $category->name,
                'slug' => $category->slug,
                'count' => isset($category->count) ? (int) $category->count : 0,
                'children' => array(),
                'level' => 0,
            );
            
            // Add children if they exist
            if (!empty($children) && !is_wp_error($children)) {
                foreach ($children as $child) {
                    // Get grandchildren for this child
                    $grandchildren = get_terms(array(
                        'taxonomy' => 'product_cat',
                        'hide_empty' => true,
                        'parent' => (int) $child->term_id,
                        'orderby' => 'name',
                        'order' => 'ASC',
                    ));
                    
                    $child_data = array(
                        'term_id' => (int) $child->term_id,
                        'name' => $child->name,
                        'slug' => $child->slug,
                        'count' => isset($child->count) ? (int) $child->count : 0,
                        'children' => array(),
                        'level' => 1,
                    );
                    
                    // Add grandchildren if they exist
                    if (!empty($grandchildren) && !is_wp_error($grandchildren)) {
                        foreach ($grandchildren as $grandchild) {
                            $child_data['children'][] = array(
                                'term_id' => (int) $grandchild->term_id,
                                'name' => $grandchild->name,
                                'slug' => $grandchild->slug,
                                'count' => isset($grandchild->count) ? (int) $grandchild->count : 0,
                                'level' => 2,
                            );
                        }
                    }
                    
                    $category_data['children'][] = $child_data;
                }
            }
            
            $available_categories[] = $category_data;
        }
    }
}

// If nothing to show, bail
if (empty($available_categories)) {
    // Show a message for debugging
    echo '<div class="filter-section mb-6"><p class="text-xs text-gray-400 italic px-4"><!-- No categories found with products. Please assign products to categories in WooCommerce. --></p></div>';
    return;
}

echo '<!-- Category Debug: Rendering ' . count($available_categories) . ' categories -->';
?>

<div class="filter-section mb-6">
    <h4 class="filter-title text-sm font-semibold text-gray-900 mb-3 pb-2 border-b border-gray-100">
        <?php 
        if (is_product_category() && $current_category_id > 0) {
            $current_cat = get_term($current_category_id, 'product_cat');
            if ($current_cat && !is_wp_error($current_cat)) {
                printf(
                    /* translators: %s: current category name */
                    esc_html__('Filter %s', 'eshop-theme'),
                    '<span class="font-normal text-gray-600">' . esc_html($current_cat->name) . '</span>'
                );
            } else {
                esc_html_e('Categories', 'eshop-theme');
            }
        } else {
            esc_html_e('Categories', 'eshop-theme');
        }
        ?>
    </h4>

    <div class="category-filter space-y-1">
        <?php
        // Recursive function to render category hierarchy
        function render_category_hierarchy($categories, $selected_ids, $selected_slugs, $level = 0) {
            foreach ($categories as $cat) {
                $is_checked = in_array((int)$cat['term_id'], $selected_ids, true) || in_array($cat['slug'], $selected_slugs, true);
                $indent_class = $level > 0 ? 'ml-' . ($level * 4) : '';
                $text_size = $level === 0 ? 'text-sm' : 'text-xs';
                $font_weight = $level === 0 ? 'font-medium' : 'font-normal';
                
                // Add icon for parent categories with children
                $has_children = !empty($cat['children']);
                $icon = $has_children ? '<i class="fas fa-chevron-right text-xs text-gray-400 mr-1"></i>' : '<span class="inline-block w-3 mr-1"></span>';
                
                ?>
                <label class="flex items-center justify-between space-x-2 cursor-pointer hover:bg-gray-50 p-2 rounded group <?php echo esc_attr($indent_class); ?>">
                    <div class="flex items-center space-x-2">
                        <input
                            type="checkbox"
                            name="product_cat[]"
                            value="<?php echo esc_attr((int)$cat['term_id']); ?>"
                            class="text-primary focus:ring-primary border-gray-300 rounded"
                            <?php checked($is_checked); ?>
                        >
                        <?php echo $icon; ?>
                        <span class="<?php echo esc_attr($text_size . ' ' . $font_weight); ?> text-gray-700 group-hover:text-gray-900">
                            <?php echo esc_html($cat['name']); ?>
                        </span>
                    </div>
                    <span class="text-xs text-gray-400 bg-gray-100 px-2 py-1 rounded-full">
                        <?php echo isset($cat['count']) ? esc_html((string)$cat['count']) : ''; ?>
                    </span>
                </label>
                <?php
                
                // Render children if they exist
                if ($has_children) {
                    render_category_hierarchy($cat['children'], $selected_ids, $selected_slugs, $level + 1);
                }
            }
        }
        
        // Render the hierarchy
        render_category_hierarchy($available_categories, $selected_ids, $selected_slugs);
        ?>
    </div>
</div>
