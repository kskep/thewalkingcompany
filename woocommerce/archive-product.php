<?php
defined( 'ABSPATH' ) || exit;

get_header( 'shop' );
?>
<div class="page-shell">
    <section class="toolbar">
        <div class="toolbar__top">
            <div class="breadcrumb">
                <?php woocommerce_breadcrumb(); ?>
            </div>
            <div class="result-meta">
                <?php woocommerce_result_count(); ?>
                <label>
                    <?php woocommerce_catalog_ordering(); ?>
                </label>
            </div>
        </div>

        <div class="active-filters">
            <!-- Active filters can be dynamically populated here -->
        </div>
    </section>

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
<?php
get_footer( 'shop' );