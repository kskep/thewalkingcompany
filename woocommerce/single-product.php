<?php
defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

// Robust product object initialization following WooCommerce best practices
$product = null;

// First, try to get the global product object if it exists
if ( isset($GLOBALS['product']) && is_a($GLOBALS['product'], 'WC_Product') ) {
    $product = $GLOBALS['product'];
} else {
    // If global is not valid, initialize it properly
    $product_id = get_the_ID();
    
    // Validate we have a proper product ID and post type
    if ( $product_id && is_numeric($product_id) && get_post_type($product_id) === 'product' ) {
        $product = wc_get_product($product_id);
        
        // Validate the product object was created successfully
        if ( is_a($product, 'WC_Product') ) {
            $GLOBALS['product'] = $product;
        } else {
            error_log('ERROR: wc_get_product() failed for ID ' . $product_id . '. Returned: ' . print_r($product, true));
            $product = null;
        }
    } else {
        error_log('ERROR: Invalid product context. ID: ' . $product_id . ', Post type: ' . get_post_type($product_id));
    }
} ?>

<div class="page-shell">
    <section class="hero">
        <article class="gallery-card">
            <div class="badge-stack">
                <?php
                // Display product badges (sale, featured, etc.)
                $badges = array();
                
                // Only proceed with badge logic if we have a valid product object
                if ( $product && is_a($product, 'WC_Product') ) {
                    // Check if product is on sale
                    if ( $product->is_on_sale() ) {
                        $badges[] = array( 'text' => 'Limited Drop', 'type' => 'sale' );
                    }
                    
                    // Check if product is featured
                    if ( $product->is_featured() ) {
                        $badges[] = array( 'text' => 'Editors\' Pick', 'type' => 'featured' );
                    }
                } else {
                    error_log('WARNING: Cannot display product badges - invalid product object');
                }
                
                // Add more badge logic as needed
                
                if ( !empty($badges) ) {
                    foreach ( $badges as $badge ) {
                        echo '<span class="badge"><span class="dot"></span>' . esc_html($badge['text']) . '</span>';
                    }
                }
                ?>
            </div>
            
            <?php
            // Use the simplified gallery component that matches the concept design
            get_template_part( 'template-parts/components/product-gallery-simple' );
            ?>

        </article>

        <aside class="details-panel">
            <?php if ( $product && is_a($product, 'WC_Product') ) : ?>
                <!-- Collection Tag -->
                <span class="collection-tag">
                    <?php
                    // Get the first product category as collection tag
                    $terms = get_the_terms( $product->get_id(), 'product_cat' );
                    if ( $terms && !is_wp_error( $terms ) ) {
                        echo esc_html( $terms[0]->name );
                    } else {
                        echo 'AW25 Capsule';
                    }
                    ?>
                </span>
                
                <!-- Product Header Row (Title + Price) -->
                <div class="product-header-row">
                    <!-- Product Title -->
                    <h1 class="product-title"><?php echo esc_html( $product->get_name() ); ?></h1>

                    <?php if ( $product->get_price_html() ) : ?>
                        <div class="price-row" data-base-price="<?php echo esc_attr( wp_kses_post( $product->get_price_html() ) ); ?>">
                            <span class="price-amount"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Options Block (Colors, Sizes, etc.) -->
                <div class="options-block">
                    <?php
                    /**
                     * Hook: woocommerce_before_add_to_cart_form.
                     *
                     * @hooked woocommerce_output_all_notices - 10
                     */
                    do_action( 'woocommerce_before_add_to_cart_form' );
                    ?>

                    <?php
                    /**
                     * Hook: woocommerce_single_product_summary.
                     *
                     * @hooked woocommerce_template_single_title - 5
                     * @hooked woocommerce_template_single_rating - 10
                     * @hooked woocommerce_template_single_price - 10
                     * @hooked woocommerce_template_single_excerpt - 20
                     * @hooked woocommerce_template_single_add_to_cart - 30
                     * @hooked woocommerce_template_single_meta - 40
                     * @hooked woocommerce_template_single_sharing - 50
                     * @hooked WC_Structured_Data::generate_product_data() - 60
                     */
                    do_action( 'woocommerce_single_product_summary' );
                    ?>

                    <?php
                    /**
                     * Hook: woocommerce_after_add_to_cart_form.
                     *
                     * @hooked woocommerce_template_single_sharing - 10
                     */
                    do_action( 'woocommerce_after_add_to_cart_form' );
                    ?>
                </div>

                <!-- CTA Row intentionally removed to prevent duplicate quantity/add-to-cart blocks.
                     Wishlist buttons are included inside the add-to-cart templates
                     (simple.php and variation-add-to-cart-button.php). -->

                <!-- Note Banner -->
                <div class="note-banner">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 8v4m0 4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z" />
                    </svg>
                    Ships within 48 hours from Athens with carbon-neutral delivery.
                </div>

                <!-- Detail Accordions -->
                <div class="detail-accordions">
                    <!-- Information Accordion (Moved Short Description) -->
                    <div class="detail-accordion">
                        <button class="detail-accordion-trigger" type="button" aria-expanded="true">
                            <span><?php _e('INFORMATION', 'eshop-theme'); ?></span>
                            <span class="icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M6 9l6 6 6-6"></path>
                                </svg>
                            </span>
                        </button>
                        <div class="detail-accordion-panel">
                            <?php if ( $product->get_short_description() ) : ?>
                                <div class="subtitle wc-short-description">
                                    <?php echo apply_filters( 'woocommerce_short_description', $product->get_short_description() ); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="detail-accordion">
                        <button class="detail-accordion-trigger" type="button" aria-expanded="false">
                            <span>Shipping & Returns</span>
                            <span class="icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M6 9l6 6 6-6"></path>
                                </svg>
                            </span>
                        </button>
                        <div class="detail-accordion-panel" hidden>
                            <p>Complimentary carbon-neutral delivery across Greece within 2 business days. EU orders arrive in 3–5 days.</p>
                            <p>We offer free exchanges within 30 days. Returns remain effortless—use the enclosed prepaid label or visit a Walking Company boutique.</p>
                        </div>
                    </div>

                    <div class="detail-accordion">
                        <button class="detail-accordion-trigger" type="button" aria-expanded="false">
                            <span>Payment Options</span>
                            <span class="icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M6 9l6 6 6-6"></path>
                                </svg>
                            </span>
                        </button>
                        <div class="detail-accordion-panel" hidden>
                            <p>Secure checkout supports Visa, Mastercard, Amex, and Apple Pay. Klarna instalments are available for purchases above €180.</p>
                            <p>Your card is only charged once the pair leaves our atelier.</p>
                        </div>
                    </div>
                </div>
            <?php else : ?>
                <div class="error-message">
                    <p><?php _e('Product information is currently unavailable. Please try again later.', 'eshop-theme'); ?></p>
                </div>
            <?php endif; ?>
        </aside>
    </section>

    <?php
        /**
         * Hook: woocommerce_after_single_product_summary.
         *
         * @hooked woocommerce_output_product_data_tabs - 10
         * @hooked woocommerce_upsell_display - 15
         * @hooked woocommerce_output_related_products - 20
         */
        do_action( 'woocommerce_after_single_product_summary' );
    ?>
</div>

<!-- Accordion JavaScript -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const triggers = document.querySelectorAll('.detail-accordion-trigger');

    triggers.forEach(function (trigger) {
        trigger.addEventListener('click', function () {
            const expanded = trigger.getAttribute('aria-expanded') === 'true';
            const panel = trigger.nextElementSibling;

            trigger.setAttribute('aria-expanded', String(!expanded));

            if (panel) {
                if (expanded) {
                    panel.setAttribute('hidden', '');
                } else {
                    panel.removeAttribute('hidden');
                }
            }
        });
    });
});
</script>

<?php get_footer( 'shop' );