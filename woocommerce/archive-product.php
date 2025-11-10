<?php
defined( 'ABSPATH' ) || exit;

get_header( 'shop' );
?>
<div class="page-shell">
    <!-- Filter System - Now handled by product-archive-filters.php component -->
    <?php get_template_part('template-parts/components/product-archive-filters'); ?>

    <!-- Products wrapper - grid container applied by WooCommerce filters -->
    <div class="products-wrapper">
        <?php
        if ( woocommerce_product_loop() ) {
            woocommerce_product_loop_start();

            if ( wc_get_loop_prop( 'total' ) ) {
                while ( have_posts() ) {
                    the_post();
                    do_action( 'woocommerce_shop_loop' );
                    wc_get_template_part( 'content', 'product' );
                }
            }

            woocommerce_product_loop_end();
            do_action( 'woocommerce_after_shop_loop' );
        } else {
            do_action( 'woocommerce_no_products_found' );
        }
        ?>
    </div>
</div>
<?php
get_footer( 'shop' );