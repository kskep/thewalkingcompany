<?php
/**
 * The Template for displaying product archives
 *
 * @package E-Shop Theme
 */

defined('ABSPATH') || exit;

get_header('shop'); ?>

<div class="woocommerce-products-header mb-8">
    <?php if (apply_filters('woocommerce_show_page_title', true)) : ?>
        <h1 class="woocommerce-products-header__title page-title text-3xl font-bold text-dark mb-4"><?php woocommerce_page_title(); ?></h1>
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

<div class="shop-layout">
    
    <!-- Mobile Filter Toggle -->
    <div class="mobile-filter-toggle lg:hidden mb-6">
        <button class="filter-toggle-btn w-full bg-gray-100 text-dark py-3 px-4 flex items-center justify-center hover:bg-gray-200 transition-colors">
            <i class="fas fa-filter mr-2"></i>
            <?php _e('Filters', 'eshop-theme'); ?>
        </button>
    </div>

    <div class="shop-content-wrapper grid grid-cols-1 lg:grid-cols-4 gap-8">
        
        <!-- Sidebar Filters -->
        <aside class="shop-sidebar lg:col-span-1">
            <div class="filters-wrapper bg-white border border-gray-200 p-6 sticky top-4">
                
                <!-- Filter Header -->
                <div class="filter-header flex items-center justify-between mb-6 pb-4 border-b border-gray-200">
                    <h3 class="text-lg font-bold text-dark"><?php _e('Filters', 'eshop-theme'); ?></h3>
                    <button class="clear-filters text-sm text-gray-500 hover:text-primary transition-colors" style="display: none;">
                        <?php _e('Clear All', 'eshop-theme'); ?>
                    </button>
                </div>

                <!-- Active Filters -->
                <div class="active-filters mb-6" style="display: none;">
                    <h4 class="text-sm font-semibold text-dark mb-3"><?php _e('Active Filters', 'eshop-theme'); ?></h4>
                    <div class="active-filters-list space-y-2"></div>
                </div>

                <!-- Price Filter -->
                <div class="filter-section mb-6">
                    <h4 class="filter-title text-sm font-semibold text-dark mb-3 pb-2 border-b border-gray-100">
                        <?php _e('Price Range', 'eshop-theme'); ?>
                    </h4>
                    <div class="price-filter">
                        <div class="price-inputs flex space-x-2 mb-3">
                            <input type="number" id="min-price" class="w-full px-3 py-2 border border-gray-300 text-sm focus:outline-none focus:border-primary" placeholder="<?php _e('Min', 'eshop-theme'); ?>">
                            <span class="flex items-center text-gray-400">-</span>
                            <input type="number" id="max-price" class="w-full px-3 py-2 border border-gray-300 text-sm focus:outline-none focus:border-primary" placeholder="<?php _e('Max', 'eshop-theme'); ?>">
                        </div>
                        <button class="apply-price-filter w-full bg-primary text-white py-2 text-sm hover:bg-primary-dark transition-colors">
                            <?php _e('Apply', 'eshop-theme'); ?>
                        </button>
                    </div>
                </div>

                <!-- Category Filter -->
                <?php
                $product_categories = get_terms(array(
                    'taxonomy' => 'product_cat',
                    'hide_empty' => true,
                    'parent' => 0
                ));
                
                if (!empty($product_categories)) :
                ?>
                <div class="filter-section mb-6">
                    <h4 class="filter-title text-sm font-semibold text-dark mb-3 pb-2 border-b border-gray-100">
                        <?php _e('Categories', 'eshop-theme'); ?>
                    </h4>
                    <div class="category-filter space-y-2">
                        <?php foreach ($product_categories as $category) : ?>
                            <label class="flex items-center text-sm text-gray-700 hover:text-primary transition-colors cursor-pointer">
                                <input type="checkbox" name="product_cat[]" value="<?php echo $category->slug; ?>" class="mr-2 text-primary focus:ring-primary">
                                <span class="flex-1"><?php echo $category->name; ?></span>
                                <span class="text-xs text-gray-400">(<?php echo $category->count; ?>)</span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Attribute Filters -->
                <?php
                $attribute_taxonomies = wc_get_attribute_taxonomies();
                
                foreach ($attribute_taxonomies as $attribute) :
                    $taxonomy = wc_attribute_taxonomy_name($attribute->attribute_name);
                    $terms = get_terms(array(
                        'taxonomy' => $taxonomy,
                        'hide_empty' => true
                    ));
                    
                    if (!empty($terms)) :
                ?>
                <div class="filter-section mb-6">
                    <h4 class="filter-title text-sm font-semibold text-dark mb-3 pb-2 border-b border-gray-100">
                        <?php echo wc_attribute_label($attribute->attribute_name); ?>
                    </h4>
                    <div class="attribute-filter space-y-2" data-attribute="<?php echo $attribute->attribute_name; ?>">
                        <?php foreach ($terms as $term) : ?>
                            <label class="flex items-center text-sm text-gray-700 hover:text-primary transition-colors cursor-pointer">
                                <input type="checkbox" name="<?php echo $taxonomy; ?>[]" value="<?php echo $term->slug; ?>" class="mr-2 text-primary focus:ring-primary">
                                <span class="flex-1"><?php echo $term->name; ?></span>
                                <span class="text-xs text-gray-400">(<?php echo $term->count; ?>)</span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php 
                    endif;
                endforeach; 
                ?>

                <!-- Stock Status Filter -->
                <div class="filter-section mb-6">
                    <h4 class="filter-title text-sm font-semibold text-dark mb-3 pb-2 border-b border-gray-100">
                        <?php _e('Availability', 'eshop-theme'); ?>
                    </h4>
                    <div class="stock-filter space-y-2">
                        <label class="flex items-center text-sm text-gray-700 hover:text-primary transition-colors cursor-pointer">
                            <input type="checkbox" name="stock_status[]" value="instock" class="mr-2 text-primary focus:ring-primary">
                            <span class="flex-1"><?php _e('In Stock', 'eshop-theme'); ?></span>
                        </label>
                        <label class="flex items-center text-sm text-gray-700 hover:text-primary transition-colors cursor-pointer">
                            <input type="checkbox" name="stock_status[]" value="onbackorder" class="mr-2 text-primary focus:ring-primary">
                            <span class="flex-1"><?php _e('On Backorder', 'eshop-theme'); ?></span>
                        </label>
                    </div>
                </div>

                <!-- On Sale Filter -->
                <div class="filter-section mb-6">
                    <label class="flex items-center text-sm text-gray-700 hover:text-primary transition-colors cursor-pointer">
                        <input type="checkbox" name="on_sale" value="1" class="mr-2 text-primary focus:ring-primary">
                        <span class="flex-1 font-medium"><?php _e('On Sale', 'eshop-theme'); ?></span>
                    </label>
                </div>
            </div>
        </aside>

        <!-- Products Area -->
        <main class="shop-main lg:col-span-3">
            
            <!-- Toolbar -->
            <div class="shop-toolbar flex flex-col sm:flex-row items-start sm:items-center justify-between mb-6 pb-4 border-b border-gray-200">
                <div class="results-count mb-4 sm:mb-0">
                    <?php woocommerce_result_count(); ?>
                </div>
                <div class="shop-ordering">
                    <?php woocommerce_catalog_ordering(); ?>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="products-wrapper">
                <?php if (woocommerce_product_loop()) : ?>
                    
                    <?php woocommerce_product_loop_start(); ?>
                    
                    <div class="products-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5" id="products-grid">
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
                        <h3 class="text-2xl font-semibold text-dark mb-4"><?php _e('No products found', 'eshop-theme'); ?></h3>
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
        </main>
    </div>
</div>

<!-- Mobile Filter Modal -->
<div class="mobile-filter-modal fixed inset-0 bg-black bg-opacity-50 z-50 hidden lg:hidden">
    <div class="modal-content bg-white h-full w-full max-w-sm ml-auto overflow-y-auto">
        <div class="modal-header flex items-center justify-between p-4 border-b border-gray-200">
            <h3 class="text-lg font-bold text-dark"><?php _e('Filters', 'eshop-theme'); ?></h3>
            <button class="close-modal text-gray-400 hover:text-dark">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div class="modal-body p-4">
            <!-- Filters will be cloned here for mobile -->
        </div>
        <div class="modal-footer p-4 border-t border-gray-200">
            <div class="flex space-x-3">
                <button class="clear-filters flex-1 bg-gray-100 text-dark py-3 hover:bg-gray-200 transition-colors">
                    <?php _e('Clear All', 'eshop-theme'); ?>
                </button>
                <button class="apply-filters flex-1 bg-primary text-white py-3 hover:bg-primary-dark transition-colors">
                    <?php _e('Apply Filters', 'eshop-theme'); ?>
                </button>
            </div>
        </div>
    </div>
</div>

<?php
/**
 * Hook: woocommerce_after_main_content.
 *
 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
 */
do_action('woocommerce_after_main_content');

get_footer('shop');
?>