<?php
/**
 * The Template for displaying product archives
 *
 * @package E-Shop Theme
 */

defined('ABSPATH') || exit;

get_header('shop'); ?>

<div class="container mx-auto px-4 py-8 shop-layout">
    
    <!-- Page Header -->
    <div class="woocommerce-products-header mb-8">
        <?php if (apply_filters('woocommerce_show_page_title', true)) : ?>
            <h1 class="woocommerce-products-header__title page-title text-3xl font-bold text-gray-900 mb-4"><?php woocommerce_page_title(); ?></h1>
        <?php endif; ?>

        <?php
        /**
         * Hook: woocommerce_archive_description.
         *
         * @hooked woocommerce_taxonomy_archive_description - 10
         * @hooked woocommerce_product_archive_description - 10
         */
        do_action('woocommerce_archive_description');
        ?>
    </div>

    <!-- Shop Toolbar -->
    <div class="shop-toolbar flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 pb-4 border-b border-gray-200">
        <div class="flex items-center space-x-4 mb-4 sm:mb-0">
            <!-- Filter Toggle Button -->
            <button class="eshop-modal-open flex items-center space-x-2 px-4 py-2 border border-gray-300 hover:border-gray-400 transition-colors" data-target="#filters-modal">
                <i class="fas fa-sliders-h text-sm"></i>
                <span><?php _e('Filters', 'eshop-theme'); ?></span>
            </button>
            
            <!-- Results Count -->
            <div class="results-count text-gray-600">
                <?php woocommerce_result_count(); ?>
            </div>
        </div>
        
        <!-- Sort Dropdown -->
        <div class="shop-ordering">
            <?php woocommerce_catalog_ordering(); ?>
        </div>
    </div>

    <!-- Products Grid -->
    <div class="products-wrapper">
        <?php if (woocommerce_product_loop()) : ?>
            
            <?php woocommerce_product_loop_start(); ?>
            
            <div class="products-grid grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6" id="products-grid">
                <?php
                if (wc_get_loop_prop('is_shortcode')) {
                    $columns = absint(wc_get_loop_prop('columns'));
                }
                
                while (have_posts()) {
                    the_post();
                    
                    /**
                     * Hook: woocommerce_shop_loop.
                     */
                    do_action('woocommerce_shop_loop');
                    
                    wc_get_template_part('content', 'product');
                }
                ?>
            </div>
            
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
// Filters Modal: reusable component with widget area contents
get_template_part('template-parts/components/modal', null, array(
    'id' => 'filters-modal',
    'title' => __('Filters', 'eshop-theme'),
    'size' => 'lg',
    'content_cb' => function () {
        // Active Filters summary container
        echo '<div class="active-filters mb-6" style="display:none;">';
        echo '<h4 class="text-sm font-semibold text-gray-900 mb-3">' . esc_html__('Active Filters', 'eshop-theme') . '</h4>';
        echo '<div class="active-filters-list space-y-2"></div>';
        echo '</div>';

        // Dynamic widgets for filters
        if (is_active_sidebar('shop-filters')) {
            dynamic_sidebar('shop-filters');
        } else {
            // Fallback basic filters if no widgets are present
            echo '<div class="filter-section mb-6">';
            echo '<h4 class="filter-title text-sm font-semibold text-gray-900 mb-3 pb-2 border-b border-gray-100">' . esc_html__('Price Range', 'eshop-theme') . '</h4>';
            echo '<div class="price-filter">';
            echo '<div class="price-inputs flex space-x-2 mb-3">';
            echo '<input type="number" id="min-price" class="w-full px-3 py-2 border border-gray-300 text-sm focus:outline-none focus:border-primary rounded" placeholder="' . esc_attr__('Min', 'eshop-theme') . '">';
            echo '<span class="flex items-center text-gray-400">-</span>';
            echo '<input type="number" id="max-price" class="w-full px-3 py-2 border border-gray-300 text-sm focus:outline-none focus:border-primary rounded" placeholder="' . esc_attr__('Max', 'eshop-theme') . '">';
            echo '</div>';
            echo '<button class="apply-price-filter w-full bg-primary text-white py-2 text-sm hover:bg-primary-dark transition-colors rounded">' . esc_html__('Apply', 'eshop-theme') . '</button>';
            echo '</div>';
            echo '</div>';
        }

        // Footer actions inside modal content so buttons are visible
        echo '<div class="pt-2 mt-4 border-t border-gray-200">';
        echo '<div class="flex space-x-3">';
        echo '<button class="clear-filters flex-1 bg-gray-100 text-gray-700 py-3 hover:bg-gray-200 transition-colors rounded">' . esc_html__('Clear All', 'eshop-theme') . '</button>';
        echo '<button class="apply-filters flex-1 bg-primary text-white py-3 hover:bg-primary-dark transition-colors rounded">' . esc_html__('Apply Filters', 'eshop-theme') . '</button>';
        echo '</div>';
        echo '</div>';
    },
));
?>

<?php
/**
 * Hook: woocommerce_after_main_content.
 *
 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
 */
do_action('woocommerce_after_main_content');

get_footer('shop');
?>