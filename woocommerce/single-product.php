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

<div class="product-main-container grid-boundary">
    <?php while ( have_posts() ) : ?>
        <?php the_post(); ?>
        
        <?php global $product; ?>
            
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
                        
                        <!-- Product Header: Title, Rating, Price -->
                        <div class="product-header">
                            <?php
                            // Title
                            woocommerce_template_single_title();
                            
                            // Rating (if enabled)
                            woocommerce_template_single_rating();
                            
                            // Price
                            woocommerce_template_single_price();
                            ?>
                        </div>
                        
                        <!-- Product Actions (Variations, Add to Cart + Wishlist) -->
                        <div class="product-actions">
                            <?php woocommerce_template_single_add_to_cart(); ?>
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
        
    <?php endwhile; // end of the loop. ?>
</div><!-- .product-main-container -->

<!-- Sticky Add to Cart (Mobile) -->
<?php get_template_part('template-parts/components/sticky-atc'); ?>

<?php get_footer( 'shop' );

/* Omit closing PHP tag at the end of PHP files */