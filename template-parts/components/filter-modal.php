<?php
/**
 * Product Filter Modal Component
 * 
 * Renders the off-canvas filter drawer for product archives
 * 
 * @package E-Shop Theme
 */

if (!defined('ABSPATH')) { exit; }

// Debug: Add a comment to see if this file is being loaded
echo '<!-- Filter Modal Component Loaded -->';

// Only show on WooCommerce archive pages - but let's temporarily disable this check
// if (!class_exists('WooCommerce') || !(is_shop() || is_product_category() || is_product_tag())) {
//     return;
// }
?>
?>

<!-- Test Element (visible) -->
<div style="position: fixed; top: 10px; right: 10px; background: red; color: white; padding: 10px; z-index: 9999;">
    Filter Modal Loaded
</div>

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
            // Fallback to default filter components
            echo '<div class="filter-section mb-6">';
            echo '<h4 class="filter-title text-sm font-semibold text-gray-900 mb-3 pb-2 border-b border-gray-100">Test Filter</h4>';
            echo '<p>Filter components will appear here.</p>';
            echo '</div>';

            // Try to load components
            if (file_exists(get_template_directory() . '/template-parts/components/filters/price-filter.php')) {
                get_template_part('template-parts/components/filters/price-filter');
            }
            if (file_exists(get_template_directory() . '/template-parts/components/filters/category-filter.php')) {
                get_template_part('template-parts/components/filters/category-filter');
            }
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
