<?php
/**
 * Single Product Template - Magazine Style (Redesigned)
 *
 * This template has been completely redesigned to match the magazine-style demo.
 *
 * @package E-Shop Theme
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' ); ?>

<div id="product-<?php the_ID(); ?>" <?php wc_product_class( '', $product ); ?>>
    <div class="magazine-container">

        <!-- Magazine Header -->
        <header class="magazine-header">
            <h2 class="magazine-title"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></h2>
            <p class="magazine-subtitle"><?php echo esc_html( get_bloginfo( 'description' ) ); ?></p>
        </header>

        <!-- Editorial Layout -->
        <div class="editorial-layout">

            <!-- Left Column: Product Gallery -->
            <div class="editorial-content">
                <?php
                /**
                 * Hook: woocommerce_before_single_product_summary.
                 * This hook now renders our custom product gallery.
                 * Default sale flash and images are removed in functions.php.
                 */
                do_action( 'woocommerce_before_single_product_summary' );
                ?>
            </div>

            <!-- Right Column: Product Details -->
            <div class="product-details">

                <!-- Product Header: Title, Rating, Price, Stock -->
                <section class="product-header">
                    <?php
                    woocommerce_template_single_title();
                    woocommerce_template_single_rating();
                    woocommerce_template_single_price();

                    // Custom Stock Status
                    if ( $product->is_in_stock() ) {
                        echo '<div class="stock in-stock"><i class="fas fa-check-circle"></i><span>' . esc_html__( 'In Stock - Ships Today', 'eshop-theme' ) . '</span></div>';
                    } else {
                        echo '<div class="stock out-of-stock"><i class="fas fa-times-circle"></i><span>' . esc_html__( 'Out of Stock', 'eshop-theme' ) . '</span></div>';
                    }
                    ?>
                </section>

                <!-- Product Actions: Variations, Add to Cart, Wishlist -->
                <section class="product-actions">
                    <?php
                    /**
                     * Hook: woocommerce_single_product_summary.
                     * We use this hook area primarily for the add_to_cart template.
                     * Other default hooks like excerpt, meta, etc., are called separately.
                     */
                    woocommerce_template_single_add_to_cart();
                    ?>
                </section>

                <!-- Trust Badges -->
                <?php get_template_part( 'template-parts/components/trust-badges' ); ?>

                <!-- Product Accordions -->
                <?php get_template_part( 'template-parts/components/product-accordions' ); ?>

                <!-- Product Meta Information -->
                <section class="product-meta">
                    <?php woocommerce_template_single_meta(); ?>
                </section>
            </div>
        </div>

    </div>
</div>

<?php
/**
 * Hook: woocommerce_after_single_product_summary.
 *
 * @hooked woocommerce_output_product_data_tabs - 10 (This is now handled by our accordions)
 * @hooked woocommerce_upsell_display - 15
 * @hooked eshop_output_related_products_from_categories - 20 (This will now correctly display related products)
 */
do_action( 'woocommerce_after_single_product_summary' );
?>

<?php get_footer( 'shop' ); ?>