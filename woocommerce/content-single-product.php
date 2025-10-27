<?php
/**
 * The template for displaying product content in the single-product.php template
 *
 * @package thewalkingtheme
 */

defined('ABSPATH') || exit;

global $product;

/**
 * Hook: woocommerce_before_single_product.
 *
 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
 * @hooked woocommerce_breadcrumb - 20
 */
do_action('woocommerce_before_single_product');

if (post_password_required()) {
    echo get_the_password_form(); // WPCS: XSS ok.
    return;
}
?>

<div id="product-<?php the_ID(); ?>" <?php wc_product_class('single-product-main', $product); ?>>
    
    <!-- Product Main Container - Two Column Layout -->
    <div class="single-product-container">
        <div class="container">
            <div class="single-product-grid">
                
                <!-- Left Column: Product Gallery -->
                <div class="product-gallery-column">
                    <?php
                    /**
                     * Hook: woocommerce_before_single_product_summary.
                     *
                     * @hooked woocommerce_show_product_sale_flash - 10
                     * @hooked woocommerce_show_product_images - 20
                     */
                    do_action('woocommerce_before_single_product_summary');
                    
                    // Use our enhanced product gallery component
                    get_template_part('template-parts/components/product-gallery');
                    ?>
                </div>
                
                <!-- Right Column: Product Details -->
                <div class="product-details-column">
                    
                    <!-- Product Summary Container -->
                    <div class="product-summary-container">
                        
                        <!-- Product Title & SKU -->
                        <?php get_template_part('template-parts/components/product-title'); ?>
                        
                        <!-- Product Pricing -->
                        <?php get_template_part('template-parts/components/product-pricing'); ?>
                        
                        <!-- Product Variations (if variable product) -->
                        <?php
                        if ($product->is_type('variable')) :
                            /**
                             * Hook: woocommerce_single_product_summary.
                             * 
                             * @hooked woocommerce_template_single_title - 5 (disabled - we use our component)
                             * @hooked woocommerce_template_single_rating - 10 (disabled)
                             * @hooked woocommerce_template_single_price - 10 (disabled - we use our component)
                             * @hooked woocommerce_template_single_excerpt - 20
                             * @hooked woocommerce_template_single_add_to_cart - 30
                             * @hooked woocommerce_template_single_meta - 40
                             */
                            
                            // Color Variants Section
                            $color_variants = function_exists('eshop_get_product_color_group_variants') 
                                ? eshop_get_product_color_group_variants($product->get_id()) 
                                : array();
                            
                            if (!empty($color_variants) && count($color_variants) > 1) :
                        ?>
                                <div class="product-variants-section">
                                    <h3 class="variants-heading"><?php _e('ΕΠΙΛΟΓΗ ΧΡΩΜΑΤΟΣ', 'thewalkingtheme'); ?></h3>
                                    <?php
                                    get_template_part('template-parts/components/color-variants', '', array(
                                        'variants' => $color_variants,
                                        'current_id' => $product->get_id()
                                    ));
                                    ?>
                                </div>
                        <?php 
                            endif;
                            
                            // Size Selection Section
                            get_template_part('template-parts/components/size-selection');
                            
                        endif;
                        ?>
                        
                        <!-- Product Actions (Add to Cart & Wishlist) -->
                        <?php get_template_part('template-parts/components/product-actions'); ?>
                        
                        <!-- Product Description (Short) -->
                        <?php if ($product->get_short_description()) : ?>
                            <div class="product-short-description">
                                <h4 class="description-title"><?php _e('Περιγραφή', 'thewalkingtheme'); ?></h4>
                                <div class="description-content">
                                    <?php echo wp_kses_post($product->get_short_description()); ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                    </div>
                    
                    <!-- Product Information Accordions -->
                    <?php get_template_part('template-parts/components/product-accordions'); ?>
                    
                </div>
                
            </div>
        </div>
    </div>
    
    <!-- Product Tabs Section (if needed for detailed descriptions) -->
    <?php
    /**
     * Hook: woocommerce_after_single_product_summary.
     *
     * @hooked woocommerce_output_product_data_tabs - 10 (disabled - we use accordions)
     * @hooked woocommerce_output_related_products - 20 (handled separately)
     */
    do_action('woocommerce_after_single_product_summary');
    ?>
    
    <!-- Product Related Section -->
    <?php
    // This is handled by our enhanced related products function in woocommerce-functions.php
    ?>
    
</div>

<?php
/**
 * Hook: woocommerce_after_single_product.
 */
do_action('woocommerce_after_single_product');
?>
