<?php
/**
 * Single Product Template - Magazine Style
 *
 * Magazine-style single product with editorial layout.
 * Features custom gallery navigation, enhanced typography, and modern design.
 *
 * @package E-Shop Theme
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' ); ?>

<!-- Main Product Container -->
<div id="product-<?php the_ID(); ?>" class="product">
    <div class="magazine-container">

        <!-- Magazine Header -->
        <div class="magazine-header">
            <h1 class="magazine-title"><?php echo esc_html(get_bloginfo('name')); ?></h1>
            <p class="magazine-subtitle"><?php echo esc_html(get_bloginfo('description')); ?></p>
        </div>

        <!-- Editorial Layout -->
        <div class="editorial-layout">

            <!-- Left Column: Product Gallery -->
            <div class="editorial-content">
                <?php
                /**
                 * Custom Product Gallery Component
                 * Replaces default WooCommerce gallery with our custom implementation
                 */
                get_template_part('template-parts/components/product-gallery');
                ?>
            </div>

            <!-- Right Column: Product Details -->
            <div class="product-details">

                <!-- Breadcrumbs -->
                <?php get_template_part('template-parts/components/breadcrumbs'); ?>

                <!-- Product Header -->
                <div class="product-header">
                    <?php
                    // Title
                    woocommerce_template_single_title();

                    // Rating (if enabled)
                    woocommerce_template_single_rating();

                    // Price
                    woocommerce_template_single_price();

                    // Stock Status
                    global $product;
                    if ($product->is_in_stock()) {
                        echo '<div class="stock in-stock">';
                        echo '<i class="fas fa-check-circle"></i>';
                        echo '<span>' . esc_html__('In Stock - Ships Today', 'eshop-theme') . '</span>';
                        echo '</div>';
                    }
                    ?>
                </div>

                <!-- Product Actions -->
                <div class="product-actions">
                    <?php woocommerce_template_single_add_to_cart(); ?>
                </div>

                <!-- Trust Badges -->
                <?php get_template_part('template-parts/components/trust-badges'); ?>

                <!-- Product Accordions -->
                <?php get_template_part('template-parts/components/product-accordions'); ?>

                <!-- Product Meta Information -->
                <div class="product-meta">
                    <?php woocommerce_template_single_meta(); ?>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        <?php
        /**
         * Related Products from Same Category and Parent Category
         * Using the twc-card component from product archive
         */
        eshop_output_related_products_from_categories();
        ?>
    </div>
</div>

<!-- Sticky Add to Cart (Mobile) -->
<?php // get_template_part('template-parts/components/sticky-atc'); ?>

<?php get_footer( 'shop' );

/* Omit closing PHP tag at the end of PHP files */