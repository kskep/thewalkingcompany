<?php
/**
 * Category Filter Component (context-aware)
 *
 * - On shop/tag: lists categories available in current context
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

// Build list of categories to display
$available_categories = array();

if (is_product_category()) {
    // On a category archive: show direct children of current category
    $current = get_queried_object();
    if ($current && !is_wp_error($current)) {
        $children = get_terms(array(
            'taxonomy' => 'product_cat',
            'hide_empty' => true,
            'parent' => (int) $current->term_id,
            'orderby' => 'name',
            'order' => 'ASC',
        ));

        if (!empty($children) && !is_wp_error($children)) {
            foreach ($children as $term) {
                $available_categories[] = array(
                    'term_id' => (int) $term->term_id,
                    'name' => $term->name,
                    'slug' => $term->slug,
                    'count' => isset($term->count) ? (int) $term->count : 0,
                );
            }
        }
    }
} else {
    // On other archives: use context-aware categories helper
    $available_categories = function_exists('eshop_get_available_categories') ? eshop_get_available_categories() : array();
}

// If nothing to show, bail
if (empty($available_categories)) {
    return;
}
?>

<div class="filter-section mb-6">
    <h4 class="filter-title text-sm font-semibold text-gray-900 mb-3 pb-2 border-b border-gray-100">
        <?php esc_html_e('Categories', 'eshop-theme'); ?>
    </h4>

    <div class="category-filter space-y-2 max-h-48 overflow-y-auto">
        <?php foreach ($available_categories as $cat) :
            // $cat has term_id, name, slug, count
            $is_checked = in_array((int)$cat['term_id'], $selected_ids, true) || in_array($cat['slug'], $selected_slugs, true);
        ?>
            <label class="flex items-center justify-between space-x-2 cursor-pointer hover:bg-gray-50 p-2 rounded group">
                <div class="flex items-center space-x-2">
                    <input
                        type="checkbox"
                        name="product_cat[]"
                        value="<?php echo esc_attr((int)$cat['term_id']); ?>"
                        class="text-primary focus:ring-primary border-gray-300 rounded"
                        <?php checked($is_checked); ?>
                    >
                    <span class="text-sm text-gray-700 group-hover:text-gray-900">
                        <?php echo esc_html($cat['name']); ?>
                    </span>
                </div>
                <span class="text-xs text-gray-400 bg-gray-100 px-2 py-1 rounded-full">
                    <?php echo isset($cat['count']) ? esc_html((string)$cat['count']) : ''; ?>
                </span>
            </label>
        <?php endforeach; ?>
    </div>
</div>
