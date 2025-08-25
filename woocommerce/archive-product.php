<?php
/**
 * The Template for displaying product archives
 *
 * @package E-Shop Theme
 */

defined('ABSPATH') || exit;

get_header('shop'); ?>

<div class="mx-auto px-4 py-8 shop-layout">

    <!-- Shop Toolbar: Filter (left) + Sorting (right); page title removed in favor of breadcrumbs -->
    <div class="shop-toolbar flex items-center justify-between mb-6 pb-4">
        <!-- Left: Filter button opens off-canvas drawer -->
        <button id="open-filters" class="filter-toggle-btn-flat flex items-center gap-2 px-4 py-2 bg-transparent hover:bg-gray-100 transition-all duration-200" aria-label="Open Filters">
            <i class="fas fa-sliders-h text-sm" aria-hidden="true"></i>
            <span class="text-sm font-medium uppercase tracking-wide"><?php _e('Filters', 'eshop-theme'); ?></span>
        </button>

        <!-- Right: Sorting -->
        <div class="shop-ordering">
            <?php woocommerce_catalog_ordering(); ?>
        </div>
    </div>

    <!-- Active Filters Bar -->
    <div class="active-filters-bar bg-gradient-to-r from-gray-50 to-gray-100 border border-gray-200 p-4 mb-6" style="display: none;">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-sm font-semibold text-gray-700 uppercase tracking-wide"><?php _e('Active Filters:', 'eshop-theme'); ?></span>
                <div class="active-filters-list flex flex-wrap gap-2"></div>
            </div>
            <button class="clear-all-filters text-sm text-red-600 hover:text-red-800 font-semibold uppercase tracking-wide transition-colors">
                <i class="fas fa-times mr-1"></i>
                <?php _e('Clear All', 'eshop-theme'); ?>
            </button>
        </div>
    </div>


    <!-- Products Grid -->
    <div class="products-wrapper">
        <?php if (woocommerce_product_loop()) : ?>

            <?php woocommerce_product_loop_start(); ?>

            <?php
            // Start the product loop
            while (have_posts()) {
                the_post();

                /**
                 * Hook: woocommerce_shop_loop.
                 */
                do_action('woocommerce_shop_loop');

                // Output the product content
                wc_get_template_part('content', 'product');
            }
            ?>

            <?php woocommerce_product_loop_end(); ?>
            
            <?php
            /**
             * Hook: woocommerce_after_shop_loop.
             *
             * @hooked woocommerce_pagination - 10
             */
            do_action('woocommerce_after_shop_loop');
            ?>
            
        <?php else : ?>
            
            <div class="no-products-found text-center py-12">
                <div class="mb-6">
                    <i class="fas fa-search text-6xl text-gray-300"></i>
                </div>
                <h3 class="text-2xl font-semibold text-gray-900 mb-4"><?php _e('No products found', 'eshop-theme'); ?></h3>
                <p class="text-gray-600 mb-6"><?php _e('Try adjusting your filters or search terms', 'eshop-theme'); ?></p>
                <button class="clear-filters bg-primary text-white px-6 py-3 hover:bg-primary-dark transition-colors">
                    <?php _e('Clear Filters', 'eshop-theme'); ?>
                </button>
            </div>
            
        <?php endif; ?>
    </div>

    <!-- Loading Overlay -->
    <div class="products-loading hidden">
        <div class="loading-overlay absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center z-10">
            <div class="loading-spinner">
                <i class="fas fa-spinner fa-spin text-2xl text-primary"></i>
                <p class="mt-2 text-sm text-gray-600"><?php _e('Loading products...', 'eshop-theme'); ?></p>
            </div>
        </div>
    </div>
</div>

<?php
// Debug: Check if we're including the filter modal
echo '<!-- Archive Page Debug: About to include filter modal -->';
echo '<!-- Archive Page Debug: Template directory: ' . get_template_directory() . ' -->';
$filter_modal_path = get_template_directory() . '/template-parts/components/filter-modal.php';
echo '<!-- Archive Page Debug: Filter modal file exists: ' . (file_exists($filter_modal_path) ? 'YES' : 'NO') . ' -->';

// Include the filter modal component
get_template_part('template-parts/components/filter-modal');

echo '<!-- Archive Page Debug: Filter modal inclusion complete -->';

// Fallback: Add filter modal HTML directly if not included above
?>
<!-- Fallback Filter Modal HTML -->
<div id="filter-backdrop" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden transition-opacity duration-300" style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); z-index: 40; opacity: 0; visibility: hidden; transition: all 0.3s ease;"></div>

<div id="filter-drawer" class="fixed inset-y-0 right-0 w-full max-w-md bg-white z-50 transform translate-x-full transition-transform duration-300 ease-in-out" role="dialog" aria-modal="true" aria-labelledby="filter-drawer-title" style="position: fixed; top: 0; right: 0; bottom: 0; width: 100%; max-width: 400px; background: white; z-index: 50; transform: translateX(100%); transition: transform 0.3s ease; box-shadow: -4px 0 20px rgba(0,0,0,0.1);">
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
        <div class="filter-section mb-6">
            <h4 class="filter-title text-sm font-semibold text-gray-900 mb-3 pb-2 border-b border-gray-100">
                <?php esc_html_e('Test Filters', 'eshop-theme'); ?>
            </h4>
            <p class="text-sm text-gray-600 mb-4">Filter functionality is working! This is a test filter modal.</p>

            <div class="space-y-3">
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" class="text-primary focus:ring-primary border-gray-300 rounded">
                    <span class="text-sm text-gray-700">Test Filter Option 1</span>
                </label>
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" class="text-primary focus:ring-primary border-gray-300 rounded">
                    <span class="text-sm text-gray-700">Test Filter Option 2</span>
                </label>
                <label class="flex items-center space-x-2 cursor-pointer">
                    <input type="checkbox" class="text-primary focus:ring-primary border-gray-300 rounded">
                    <span class="text-sm text-gray-700">Test Filter Option 3</span>
                </label>
            </div>
        </div>
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

<!-- Inline CSS for filter modal states -->
<style>
#filter-backdrop.show {
    opacity: 1 !important;
    visibility: visible !important;
}

#filter-drawer.open {
    transform: translateX(0) !important;
}

body.overflow-hidden {
    overflow: hidden !important;
}

.hidden {
    display: none !important;
}
</style>
<?php

/**
/**
 * Hook: woocommerce_after_main_content.
 *
 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
 */
do_action('woocommerce_after_main_content');

get_footer('shop');
?>