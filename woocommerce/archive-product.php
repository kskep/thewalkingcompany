<?php
/**
 * Product Archive – Magazine Layout
 */
defined('ABSPATH') || exit;

get_header('shop'); ?>

<div class="shop-layout demo-container">
    <div class="shop-inner">

        <?php get_template_part('template-parts/components/filters'); ?>

        <div class="products-wrapper">
            <?php if (woocommerce_product_loop()) : ?>

                <?php woocommerce_product_loop_start(); ?>

                <?php while (have_posts()) : the_post();
                    do_action('woocommerce_shop_loop');
                    wc_get_template_part('content', 'product');
                endwhile; ?>

                <?php woocommerce_product_loop_end(); ?>

                <?php do_action('woocommerce_after_shop_loop'); ?>

            <?php else : ?>
                <div class="no-products-found text-center py-12">
                    <div class="mb-6">
                        <i class="fas fa-search text-6xl text-gray-300"></i>
                    </div>
                    <h3 class="text-2xl font-semibold text-gray-900 mb-4"><?php _e('No products found', 'eshop-theme'); ?></h3>
                    <p class="text-gray-600 mb-6"><?php _e('Try adjusting your filters or search terms', 'eshop-theme'); ?></p>
                    <button class="clear-filters bg-primary text-white px-6 py-3 hover:bg-primary-dark transition-colors"><?php _e('Clear Filters', 'eshop-theme'); ?></button>
                </div>
            <?php endif; ?>
        </div>

        <div class="products-loading hidden">
            <div class="loading-overlay absolute inset-0 bg-white bg-opacity-75 flex items-center justify-center z-10">
                <div class="loading-spinner">
                    <i class="fas fa-spinner fa-spin text-2xl text-primary"></i>
                    <p class="mt-2 text-sm text-gray-600"><?php _e('Loading products...', 'eshop-theme'); ?></p>
                </div>
            </div>
        </div>

    </div>
</div>

<?php do_action('woocommerce_after_main_content'); ?>
<?php get_footer('shop'); ?>