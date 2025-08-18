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
        <button id="open-filters" class="filter-toggle-btn flex items-center gap-2 px-4 py-2 border border-gray-300 hover:border-primary transition-all duration-200" aria-label="Open Filters">
            <i class="fas fa-sliders-h text-sm" aria-hidden="true"></i>
            <span class="hidden sm:inline text-sm font-medium uppercase tracking-wide"><?php _e('Filters', 'eshop-theme'); ?></span>
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
// Off-Canvas Filter Drawer
?>
<!-- Filter Drawer Backdrop -->
<div id="filter-backdrop" class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden transition-opacity duration-300"></div>

<!-- Off-Canvas Filter Drawer -->
<div id="filter-drawer" class="fixed inset-y-0 right-0 w-full max-w-md bg-white z-50 transform translate-x-full transition-transform duration-300 ease-in-out" role="dialog" aria-modal="true" aria-labelledby="filter-drawer-title">

    <!-- Drawer Header -->
    <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 bg-white">
        <h2 id="filter-drawer-title" class="text-lg font-semibold text-gray-900 uppercase tracking-wide"><?php _e('Filters', 'eshop-theme'); ?></h2>
        <button id="close-filters" class="p-2 text-gray-400 hover:text-gray-600 transition-colors" aria-label="Close Filters">
            <i class="fas fa-times text-lg"></i>
        </button>
    </div>

    <!-- Drawer Content -->
    <div class="flex-1 overflow-y-auto px-6 py-4 h-[calc(100vh-140px)]">
        <?php
        // Dynamic widgets for filters
        if (is_active_sidebar('shop-filters')) {
            dynamic_sidebar('shop-filters');
        } else {
            // Fallback comprehensive filters if no widgets are present

            // Price Range Filter
            echo '<div class="filter-section mb-6">';
            echo '<h4 class="filter-title text-sm font-semibold text-gray-900 mb-3 pb-2 border-b border-gray-100">' . esc_html__('Price Range', 'eshop-theme') . '</h4>';
            echo '<div class="price-filter">';
            echo '<div class="price-inputs flex space-x-2 mb-3">';
            echo '<input type="number" id="min-price" class="w-full px-3 py-2 border border-gray-300 text-sm focus:outline-none focus:border-primary rounded" placeholder="' . esc_attr__('Min', 'eshop-theme') . '">';
            echo '<span class="flex items-center text-gray-400">-</span>';
            echo '<input type="number" id="max-price" class="w-full px-3 py-2 border border-gray-300 text-sm focus:outline-none focus:border-primary rounded" placeholder="' . esc_attr__('Max', 'eshop-theme') . '">';
            echo '</div>';
            echo '</div>';
            echo '</div>';

            // Categories Filter
            $categories = get_terms(array(
                'taxonomy' => 'product_cat',
                'hide_empty' => true,
                'parent' => 0, // Only top-level categories
            ));

            if (!empty($categories) && !is_wp_error($categories)) {
                echo '<div class="filter-section mb-6">';
                echo '<h4 class="filter-title text-sm font-semibold text-gray-900 mb-3 pb-2 border-b border-gray-100">' . esc_html__('Categories', 'eshop-theme') . '</h4>';
                echo '<div class="category-filter space-y-2">';

                foreach ($categories as $category) {
                    echo '<label class="flex items-center space-x-2 cursor-pointer hover:bg-gray-50 p-1 rounded">';
                    echo '<input type="checkbox" name="product_cat[]" value="' . esc_attr($category->term_id) . '" class="text-primary focus:ring-primary border-gray-300 rounded">';
                    echo '<span class="text-sm text-gray-700">' . esc_html($category->name) . '</span>';
                    echo '<span class="text-xs text-gray-400">(' . $category->count . ')</span>';
                    echo '</label>';
                }

                echo '</div>';
                echo '</div>';
            }

            // Stock Status Filter
            echo '<div class="filter-section mb-6">';
            echo '<h4 class="filter-title text-sm font-semibold text-gray-900 mb-3 pb-2 border-b border-gray-100">' . esc_html__('Availability', 'eshop-theme') . '</h4>';
            echo '<div class="stock-filter space-y-2">';

            echo '<label class="flex items-center space-x-2 cursor-pointer hover:bg-gray-50 p-1 rounded">';
            echo '<input type="checkbox" name="stock_status[]" value="instock" class="text-primary focus:ring-primary border-gray-300 rounded">';
            echo '<span class="text-sm text-gray-700">' . esc_html__('In Stock', 'eshop-theme') . '</span>';
            echo '</label>';

            echo '<label class="flex items-center space-x-2 cursor-pointer hover:bg-gray-50 p-1 rounded">';
            echo '<input type="checkbox" name="stock_status[]" value="outofstock" class="text-primary focus:ring-primary border-gray-300 rounded">';
            echo '<span class="text-sm text-gray-700">' . esc_html__('Out of Stock', 'eshop-theme') . '</span>';
            echo '</label>';

            echo '</div>';
            echo '</div>';

            // On Sale Filter
            echo '<div class="filter-section mb-6">';
            echo '<h4 class="filter-title text-sm font-semibold text-gray-900 mb-3 pb-2 border-b border-gray-100">' . esc_html__('Special Offers', 'eshop-theme') . '</h4>';
            echo '<div class="sale-filter space-y-2">';

            echo '<label class="flex items-center space-x-2 cursor-pointer hover:bg-gray-50 p-1 rounded">';
            echo '<input type="checkbox" name="on_sale" value="1" class="text-primary focus:ring-primary border-gray-300 rounded">';
            echo '<span class="text-sm text-gray-700">' . esc_html__('On Sale', 'eshop-theme') . '</span>';
            echo '</label>';

            echo '</div>';
            echo '</div>';

            // Product Attributes (Color, Size, etc.)
            $attributes = wc_get_attribute_taxonomies();

            if (!empty($attributes)) {
                foreach ($attributes as $attribute) {
                    $taxonomy = 'pa_' . $attribute->attribute_name;
                    $terms = get_terms(array(
                        'taxonomy' => $taxonomy,
                        'hide_empty' => true,
                    ));

                    if (!empty($terms) && !is_wp_error($terms)) {
                        echo '<div class="filter-section mb-6">';
                        echo '<h4 class="filter-title text-sm font-semibold text-gray-900 mb-3 pb-2 border-b border-gray-100">' . esc_html(wc_attribute_label($attribute->attribute_name)) . '</h4>';
                        echo '<div class="attribute-filter space-y-2" data-attribute="' . esc_attr($attribute->attribute_name) . '">';

                        foreach ($terms as $term) {
                            echo '<label class="flex items-center space-x-2 cursor-pointer hover:bg-gray-50 p-1 rounded">';
                            echo '<input type="checkbox" name="' . esc_attr($taxonomy) . '[]" value="' . esc_attr($term->slug) . '" class="text-primary focus:ring-primary border-gray-300 rounded">';
                            echo '<span class="text-sm text-gray-700">' . esc_html($term->name) . '</span>';
                            echo '<span class="text-xs text-gray-400">(' . $term->count . ')</span>';
                            echo '</label>';
                        }

                        echo '</div>';
                        echo '</div>';
                    }
                }
            }
        }

        }
        ?>
    </div>

    <!-- Sticky Action Bar -->
    <div class="sticky bottom-0 left-0 right-0 bg-white border-t border-gray-200 px-6 py-4 flex gap-3">
        <button id="clear-filters" class="flex-1 bg-gray-100 text-gray-700 py-3 px-4 font-semibold uppercase tracking-wide hover:bg-gray-200 transition-colors">
            <?php _e('Clear All', 'eshop-theme'); ?>
        </button>
        <button id="apply-filters" class="flex-1 bg-primary text-white py-3 px-4 font-semibold uppercase tracking-wide hover:bg-primary-dark transition-colors">
            <?php _e('Apply Filters', 'eshop-theme'); ?>
        </button>
    </div>
</div>

<?php

<?php
/**
 * Hook: woocommerce_after_main_content.
 *
 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
 */
do_action('woocommerce_after_main_content');

get_footer('shop');
?>