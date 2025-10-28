<?php
/**
 * Single Product Template
 *
 * This template is a wrapper for single product pages.
 * It implements a modern CSS Grid layout with images on the left and details on the right.
 *
 * @package E-Shop Theme
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' ); ?>

<div class="container mx-auto px-4 py-8">
    <?php while ( have_posts() ) : ?>
        <?php the_post(); ?>
        
        <div id="product-<?php the_ID(); ?>" <?php wc_product_class( '', $product ); ?>>
            
            <!-- Main Product Container with CSS Grid Layout -->
            <div class="product-main-container">
                
                <!-- Left Column: Product Image Gallery -->
                <div class="product-gallery-column">
                    <?php
                    /**
                     * Custom Product Gallery Component
                     * Replaces default WooCommerce gallery with our custom implementation
                     */
                    get_template_part('template-parts/components/product-gallery');
                    ?>
                </div>
                
                <!-- Right Column: Product Details & Actions -->
                <div class="product-details-column">
                    <div class="product-details-wrapper">
                        <?php // Breadcrumbs ?>
                        <?php get_template_part('template-parts/components/breadcrumbs'); ?>
                        
                        <!-- Product Title & Code -->
                        <div class="product-header">
                            <?php
                            /**
                             * Hook: woocommerce_single_product_summary.
                             *
                             * @hooked woocommerce_template_single_title - 5
                             * @hooked woocommerce_template_single_rating - 10
                             * @hooked woocommerce_template_single_price - 15
                             * @hooked woocommerce_template_single_excerpt - 20
                             * @hooked woocommerce_template_single_add_to_cart - 30
                             * @hooked woocommerce_template_single_meta - 40
                             * @hooked woocommerce_template_single_sharing - 50
                             */
                            do_action( 'woocommerce_single_product_summary' );
                            ?>
                        </div>
                        
                        <!-- Product Actions (Variations, Add to Cart + Wishlist) -->
                        <div class="product-actions">
                            <?php
                            /**
                             * Custom hook for product variations and add to cart
                             * This will render the variable.php or simple.php templates
                             */
                            woocommerce_template_single_add_to_cart();
                            ?>
                        </div>
                        
                        <!-- Trust Badges -->
                        <?php get_template_part('template-parts/components/trust-badges'); ?>
                        
                        <!-- Product Accordions (Details, Materials, Shipping, Help) -->
                        <?php get_template_part('template-parts/components/product-accordions'); ?>
                        
                        <!-- Product Meta Information -->
                        <div class="product-meta">
                            <?php woocommerce_template_single_meta(); ?>
                        </div>
                        
                    </div>
                </div>
                
            </div><!-- .product-main-container -->
            
            <!-- Product Additional Information Tabs -->
            <div class="product-tabs-container mt-16">
                <?php
                /**
                 * Hook: woocommerce_after_single_product_summary.
                 *
                 * @hooked woocommerce_output_product_data_tabs - 10
                 * @hooked woocommerce_upsell_display - 15
                 * @hooked woocommerce_output_related_products - 20 (replaced with enhanced version)
                 */
                do_action( 'woocommerce_after_single_product_summary' );
                ?>
            </div>
            
        </div><!-- #product-<?php the_ID(); ?> -->
        
    <?php endwhile; // end of the loop. ?>
</div>

<!-- Sticky Add to Cart (Mobile) -->
<?php get_template_part('template-parts/components/sticky-atc'); ?>

<?php get_footer( 'shop' );

/* Omit closing PHP tag at the end of PHP files */