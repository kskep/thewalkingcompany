<?php
/**
 * Single Product Template - Redesigned & Unified
 *
 * A clean, modern, and semantic template for the single product page.
 *
 * @package E-Shop Theme
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' ); ?>

<div id="product-<?php the_ID(); ?>" <?php wc_product_class( 'single-product-layout', $product ); ?>>
    <div class="grid-boundary">
        <main class="product-main-container">

            <!-- Left Column: Product Gallery -->
            <div class="product-gallery-column">
                <?php
                /**
                 * Hook: woocommerce_before_single_product_summary.
                 *
                 * @hooked woocommerce_show_product_sale_flash - 10 (Removed by theme)
                 * @hooked eshop_custom_single_product_badges - 10 (Custom)
                 * @hooked woocommerce_show_product_images - 20 (Removed by theme)
                 * @hooked eshop_show_custom_product_gallery - 20 (Custom)
                 */
                do_action( 'woocommerce_before_single_product_summary' );
                ?>
            </div>

            <!-- Right Column: Product Details & Actions -->
            <div class="product-details-column">
                <div class="product-details-wrapper">

                    <!-- Breadcrumbs -->
                    <div class="product-breadcrumb">
                        <?php woocommerce_breadcrumb(); ?>
                    </div>

                    <!-- Header: Title, Price, Rating -->
                    <header class="product-header">
                        <?php
                        /**
                         * Hook: woocommerce_single_product_summary.
                         *
                         * @hooked woocommerce_template_single_title - 5
                         * @hooked woocommerce_template_single_rating - 10 (Removed by theme)
                         * @hooked woocommerce_template_single_price - 10
                         * @hooked woocommerce_template_single_excerpt - 20
                         * @hooked woocommerce_template_single_add_to_cart - 30 (Moved)
                         * @hooked woocommerce_template_single_meta - 40
                         * @hooked woocommerce_template_single_sharing - 50
                         */
                        woocommerce_template_single_title();
                        woocommerce_template_single_price();
                        ?>
                    </header>

                    <!-- Short Description -->
                    <div class="product-description">
                        <?php woocommerce_template_single_excerpt(); ?>
                    </div>
                    
                    <!-- Variations & Add to Cart Form -->
                    <div class="product-cart-section">
                        <?php woocommerce_template_single_add_to_cart(); ?>
                    </div>
                    
                    <!-- Product Meta -->
                    <div class="product-meta-section">
                        <?php woocommerce_template_single_meta(); ?>
                    </div>
                    
                    <!-- Trust Badges -->
                    <?php get_template_part('template-parts/components/trust-badges'); ?>

                </div>
            </div>
        </main>
    </div>

    <!-- Tabs & Related Products Section -->
    <div class="product-additional-info">
        <div class="grid-boundary">
            <?php
            /**
             * Hook: woocommerce_after_single_product_summary.
             *
             * @hooked woocommerce_output_product_data_tabs - 10
             * @hooked woocommerce_upsell_display - 15
             * @hooked eshop_output_related_products_from_categories - 20 (Custom)
             */
            do_action( 'woocommerce_after_single_product_summary' );
            ?>
        </div>
    </div>
</div>

<?php get_footer( 'shop' ); ?>