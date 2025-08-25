<?php
/**
 * Product Filter Modal Component
 * 
 * Renders the off-canvas filter drawer for product archives
 * 
 * @package E-Shop Theme
 */

if (!defined('ABSPATH')) { exit; }

// Debug: Check WooCommerce and page conditions
echo '<!-- Filter Modal Debug: WooCommerce class exists: ' . (class_exists('WooCommerce') ? 'YES' : 'NO') . ' -->';
echo '<!-- Filter Modal Debug: is_shop(): ' . (is_shop() ? 'YES' : 'NO') . ' -->';
echo '<!-- Filter Modal Debug: is_product_category(): ' . (is_product_category() ? 'YES' : 'NO') . ' -->';
echo '<!-- Filter Modal Debug: is_product_tag(): ' . (is_product_tag() ? 'YES' : 'NO') . ' -->';

// Only show on WooCommerce archive pages
if (!class_exists('WooCommerce') || !(is_shop() || is_product_category() || is_product_tag())) {
    echo '<!-- Filter Modal: Conditions not met, not rendering modal -->';
    return;
}

echo '<!-- Filter Modal: Conditions met, rendering modal -->';
?>

<!-- Filter Backdrop -->
<div id="filter-backdrop" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden transition-opacity duration-300"></div>

<!-- Off-Canvas Filter Drawer -->
<div id="filter-drawer" class="fixed inset-y-0 right-0 w-full max-w-md bg-white z-50 transform translate-x-full transition-transform duration-300 ease-in-out" role="dialog" aria-modal="true" aria-labelledby="filter-drawer-title">

    <!-- Drawer Header -->
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 bg-white">
        <h2 id="filter-drawer-title" class="text-lg font-semibold text-gray-900 uppercase tracking-wide">
            <?php _e('Filters', 'eshop-theme'); ?>
        </h2>
        <button id="close-filters" class="p-2 text-gray-400 hover:text-gray-600 transition-colors" aria-label="<?php esc_attr_e('Close Filters', 'eshop-theme'); ?>">
            <i class="fas fa-times text-lg"></i>
        </button>
    </div>

    <!-- Drawer Content -->
    <div class="flex-1 overflow-y-auto px-6 py-4 h-[calc(100vh-140px)]">
        <?php
        // Use dynamic widgets if available
        if (is_active_sidebar('shop-filters')) {
            dynamic_sidebar('shop-filters');
        } else {
            // Load real filter components

            // Price Filter
            if (file_exists(get_template_directory() . '/template-parts/components/filters/price-filter.php')) {
                get_template_part('template-parts/components/filters/price-filter');
            } else {
                // Inline price filter
                echo '<div class="filter-section mb-6">';
                echo '<h4 class="filter-title text-sm font-semibold text-gray-900 mb-3 pb-2 border-b border-gray-100">' . esc_html__('Price Range', 'eshop-theme') . '</h4>';
                echo '<div class="price-filter">';
                echo '<div class="price-inputs flex space-x-2 mb-3">';
                echo '<input type="number" id="min-price" class="w-full px-3 py-2 border border-gray-300 text-sm focus:outline-none focus:border-primary rounded" placeholder="' . esc_attr__('Min', 'eshop-theme') . '">';
                echo '<span class="flex items-center text-gray-400">-</span>';
                echo '<input type="number" id="max-price" class="w-full px-3 py-2 border border-gray-300 text-sm focus:outline-none focus:border-primary rounded" placeholder="' . esc_attr__('Max', 'eshop-theme') . '">';
                echo '</div>';
                echo '<button class="apply-price-filter w-full mt-3 px-4 py-2 bg-primary text-white text-sm font-medium uppercase tracking-wide hover:bg-primary-dark transition-colors">' . esc_html__('Apply Price Filter', 'eshop-theme') . '</button>';
                echo '</div>';
                echo '</div>';
            }

            // Product Categories Filter
            $product_categories = get_terms(array(
                'taxonomy' => 'product_cat',
                'hide_empty' => true,
                'parent' => 0, // Only top-level categories
                'number' => 10
            ));

            if (!empty($product_categories) && !is_wp_error($product_categories)) {
                echo '<div class="filter-section mb-6">';
                echo '<h4 class="filter-title text-sm font-semibold text-gray-900 mb-3 pb-2 border-b border-gray-100">' . esc_html__('Categories', 'eshop-theme') . '</h4>';
                echo '<div class="category-filter space-y-2 max-h-48 overflow-y-auto">';

                foreach ($product_categories as $category) {
                    $is_selected = isset($_GET['product_cat']) && in_array($category->slug, (array)$_GET['product_cat']);
                    echo '<label class="flex items-center justify-between space-x-2 cursor-pointer hover:bg-gray-50 p-2 rounded group">';
                    echo '<div class="flex items-center space-x-2">';
                    echo '<input type="checkbox" name="product_cat[]" value="' . esc_attr($category->slug) . '" class="text-primary focus:ring-primary border-gray-300 rounded"' . checked($is_selected, true, false) . '>';
                    echo '<span class="text-sm text-gray-700 group-hover:text-gray-900">' . esc_html($category->name) . '</span>';
                    echo '</div>';
                    echo '<span class="text-xs text-gray-400 bg-gray-100 px-2 py-1 rounded-full">' . esc_html($category->count) . '</span>';
                    echo '</label>';
                }

                echo '</div>';
                echo '</div>';
            }

            // On Sale Filter
            $on_sale_selected = isset($_GET['on_sale']) && $_GET['on_sale'] === '1';
            $sale_count = count(wc_get_product_ids_on_sale());

            if ($sale_count > 0) {
                echo '<div class="filter-section mb-6">';
                echo '<h4 class="filter-title text-sm font-semibold text-gray-900 mb-3 pb-2 border-b border-gray-100">' . esc_html__('Special Offers', 'eshop-theme') . '</h4>';
                echo '<div class="sale-filter space-y-2">';
                echo '<label class="flex items-center justify-between space-x-2 cursor-pointer hover:bg-gray-50 p-2 rounded group">';
                echo '<div class="flex items-center space-x-2">';
                echo '<input type="checkbox" name="on_sale" value="1" class="text-primary focus:ring-primary border-gray-300 rounded"' . checked($on_sale_selected, true, false) . '>';
                echo '<span class="text-sm text-gray-700 group-hover:text-gray-900">' . esc_html__('On Sale', 'eshop-theme') . '</span>';
                echo '<i class="fas fa-tag text-red-500 text-xs" title="' . esc_attr__('Sale Items', 'eshop-theme') . '"></i>';
                echo '</div>';
                echo '<span class="text-xs text-gray-400 bg-red-100 text-red-600 px-2 py-1 rounded-full font-medium">' . esc_html($sale_count) . '</span>';
                echo '</label>';
                echo '</div>';
                echo '</div>';
            }

            // Stock Status Filter
            $stock_options = array(
                'instock' => __('In Stock', 'eshop-theme'),
                'outofstock' => __('Out of Stock', 'eshop-theme'),
                'onbackorder' => __('On Backorder', 'eshop-theme')
            );
            $selected_stock = isset($_GET['stock_status']) ? (array)$_GET['stock_status'] : array();

            echo '<div class="filter-section mb-6">';
            echo '<h4 class="filter-title text-sm font-semibold text-gray-900 mb-3 pb-2 border-b border-gray-100">' . esc_html__('Availability', 'eshop-theme') . '</h4>';
            echo '<div class="stock-filter space-y-2">';

            foreach ($stock_options as $value => $label) {
                $is_checked = in_array($value, $selected_stock);
                echo '<label class="flex items-center justify-between space-x-2 cursor-pointer hover:bg-gray-50 p-2 rounded group">';
                echo '<div class="flex items-center space-x-2">';
                echo '<input type="checkbox" name="stock_status[]" value="' . esc_attr($value) . '" class="text-primary focus:ring-primary border-gray-300 rounded"' . checked($is_checked, true, false) . '>';
                echo '<span class="text-sm text-gray-700 group-hover:text-gray-900">' . esc_html($label) . '</span>';

                if ($value === 'instock') {
                    echo '<i class="fas fa-check-circle text-green-500 text-xs" title="' . esc_attr__('Available', 'eshop-theme') . '"></i>';
                } elseif ($value === 'outofstock') {
                    echo '<i class="fas fa-times-circle text-red-500 text-xs" title="' . esc_attr__('Not Available', 'eshop-theme') . '"></i>';
                } elseif ($value === 'onbackorder') {
                    echo '<i class="fas fa-clock text-yellow-500 text-xs" title="' . esc_attr__('Available Soon', 'eshop-theme') . '"></i>';
                }

                echo '</div>';
                echo '</label>';
            }

            echo '</div>';
            echo '</div>';
        }
        ?>
    </div>

    <!-- Drawer Footer -->
    <div class="flex items-center justify-between px-6 py-4 border-t border-gray-200 bg-gray-50">
        <button id="clear-filters" class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 transition-colors">
            <i class="fas fa-times mr-2"></i>
            <?php _e('Clear All', 'eshop-theme'); ?>
        </button>
        <button id="apply-filters" class="px-6 py-2 bg-primary text-white text-sm font-medium uppercase tracking-wide hover:bg-primary-dark transition-colors">
            <?php _e('Apply Filters', 'eshop-theme'); ?>
        </button>
    </div>
</div>

<!-- Active Filters Bar -->
<div class="active-filters-bar bg-gradient-to-r from-gray-50 to-gray-100 border border-gray-200 p-4 mb-6" style="display: none;">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-sm font-semibold text-gray-700 uppercase tracking-wide">
                <?php _e('Active Filters:', 'eshop-theme'); ?>
            </span>
            <div class="active-filters-list flex flex-wrap gap-2"></div>
        </div>
        <button class="clear-all-filters text-sm text-red-600 hover:text-red-800 font-semibold uppercase tracking-wide transition-colors">
            <i class="fas fa-times mr-1"></i>
            <?php _e('Clear All', 'eshop-theme'); ?>
        </button>
    </div>
</div>

<!-- Loading Overlay -->
<div class="products-loading hidden absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center z-10">
    <div class="flex items-center space-x-2">
        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-primary"></div>
        <span class="text-sm text-gray-600"><?php _e('Loading...', 'eshop-theme'); ?></span>
    </div>
</div>
