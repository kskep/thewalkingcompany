<?php
/**
 * Single Product Template - Magazine Style (Redesigned)
 *
 * This template has been completely redesigned to match the magazine-style demo.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 1.6.4
 */

defined('ABSPATH') || exit;

get_header('shop');

do_action('woocommerce_before_main_content');

while (have_posts()) :
    the_post();

    global $product;

    do_action('woocommerce_before_single_product');

    if (post_password_required()) {
        echo get_the_password_form();
        continue;
    }

    ?>
    <div id="product-<?php the_ID(); ?>" <?php wc_product_class('mag-single-product', $product); ?>>
        <div class="mag-shell">
            <?php get_template_part('template-parts/components/breadcrumbs'); ?>

            <div class="mag-shell__grid">
                <div class="mag-shell__media">
                    <?php
                    /**
                     * Hook: woocommerce_before_single_product_summary.
                     */
                    do_action('woocommerce_before_single_product_summary');
                    ?>
                </div>

                <aside class="mag-shell__summary" aria-label="<?php esc_attr_e('Product details', 'eshop-theme'); ?>">
                    <?php
                    /**
                     * Hook: woocommerce_single_product_summary.
                     */
                    do_action('woocommerce_single_product_summary');
                    ?>
                </aside>
            </div>
        </div>
    </div>

    <?php
    /**
     * Hook: woocommerce_after_single_product_summary.
     *
     * @hooked woocommerce_output_product_data_tabs - 10
     * @hooked woocommerce_upsell_display - 15
     * @hooked eshop_output_related_products_from_categories - 20
     */
    do_action('woocommerce_after_single_product_summary');

    do_action('woocommerce_after_single_product');

endwhile; // End of the loop.

do_action('woocommerce_after_main_content');

get_footer('shop');