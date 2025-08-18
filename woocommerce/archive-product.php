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
    <div class="shop-toolbar flex items-center justify-between mb-6 pb-4 border-b border-gray-200">
        <!-- Left: Filter icon/button opens modal with categories, attributes, price -->
        <button class="eshop-modal-open flex items-center gap-2 px-4 py-2 border border-gray-300 hover:border-gray-400 transition-colors" data-target="#filters-modal" aria-label="Open Filters">
            <i class="fas fa-sliders-h text-sm" aria-hidden="true"></i>
            <span class="hidden sm:inline"><?php _e('Filters', 'eshop-theme'); ?></span>
        </button>

        <!-- Right: Sorting -->
        <div class="shop-ordering">
            <?php woocommerce_catalog_ordering(); ?>
        </div>
    </div>

    <!-- Active Filters Bar -->
    <div class="active-filters-bar bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6" style="display: none;">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-2">
                <span class="text-sm font-medium text-gray-700"><?php _e('Active Filters:', 'eshop-theme'); ?></span>
                <div class="active-filters-list flex flex-wrap gap-2"></div>
            </div>
            <button class="clear-all-filters text-sm text-red-600 hover:text-red-800 font-medium transition-colors">
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