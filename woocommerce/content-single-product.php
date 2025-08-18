<?php
/**
 * The template for displaying product content in the single-product.php template
 *
 * @package E-Shop Theme
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

<div id="product-<?php the_ID(); ?>" <?php wc_product_class('single-product-layout', $product); ?>>
    
    <div class="container mx-auto px-4 py-8">
        
        <!-- 2-Column Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12">
            
            <!-- Left Column - Product Images -->
            <div class="product-images-section">
                
                <!-- Main Product Gallery -->
                <div class="product-gallery">
                    <?php
                    /**
                     * Hook: woocommerce_before_single_product_summary.
                     *
                     * @hooked woocommerce_show_product_sale_flash - 10
                     * @hooked woocommerce_show_product_images - 20
                     */
                    do_action('woocommerce_before_single_product_summary');
                    ?>
                </div>
                
            </div>
            
            <!-- Right Column - Product Information -->
            <div class="product-info-section">
                
                <div class="product-summary">
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
                    do_action('woocommerce_single_product_summary');
                    ?>
                </div>
                
                <!-- Additional Product Actions -->
                <div class="product-actions mt-8 pt-6 border-t border-gray-200">
                    
                    <!-- Wishlist Button -->
                    <?php if (function_exists('eshop_wishlist_button')) : ?>
                        <div class="wishlist-action mb-4">
                            <?php 
                            $is_in_wishlist = function_exists('eshop_is_in_wishlist') ? eshop_is_in_wishlist($product->get_id()) : false;
                            $heart_class = $is_in_wishlist ? 'fas fa-heart text-red-500' : 'far fa-heart text-gray-400';
                            $button_text = $is_in_wishlist ? __('Remove from Wishlist', 'eshop-theme') : __('Add to Wishlist', 'eshop-theme');
                            ?>
                            <button class="add-to-wishlist flex items-center space-x-2 px-4 py-2 border border-gray-300 hover:border-gray-400 transition-colors rounded-lg" 
                                    data-product-id="<?php echo $product->get_id(); ?>">
                                <i class="<?php echo $heart_class; ?>"></i>
                                <span><?php echo $button_text; ?></span>
                            </button>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Social Sharing -->
                    <div class="social-sharing">
                        <h4 class="text-sm font-semibold text-gray-900 mb-3"><?php _e('Share this product', 'eshop-theme'); ?></h4>
                        <div class="flex space-x-3">
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(get_permalink()); ?>" 
                               target="_blank" 
                               class="social-share-btn facebook">
                                <i class="fab fa-facebook-f"></i>
                            </a>
                            <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(get_permalink()); ?>&text=<?php echo urlencode(get_the_title()); ?>" 
                               target="_blank" 
                               class="social-share-btn twitter">
                                <i class="fab fa-twitter"></i>
                            </a>
                            <a href="https://pinterest.com/pin/create/button/?url=<?php echo urlencode(get_permalink()); ?>&media=<?php echo urlencode(wp_get_attachment_url($product->get_image_id())); ?>&description=<?php echo urlencode(get_the_title()); ?>" 
                               target="_blank" 
                               class="social-share-btn pinterest">
                                <i class="fab fa-pinterest-p"></i>
                            </a>
                            <a href="mailto:?subject=<?php echo urlencode(get_the_title()); ?>&body=<?php echo urlencode(get_permalink()); ?>" 
                               class="social-share-btn email">
                                <i class="fas fa-envelope"></i>
                            </a>
                        </div>
                    </div>
                    
                </div>
                
            </div>
            
        </div>
        
    </div>
    
    <!-- Product Tabs / Additional Information -->
    <div class="product-tabs-section mt-12 pt-8 border-t border-gray-200">
        <div class="container mx-auto px-4">
            <?php
            /**
             * Hook: woocommerce_after_single_product_summary.
             *
             * @hooked woocommerce_output_product_data_tabs - 10
             * @hooked woocommerce_upsell_display - 15
             * @hooked woocommerce_output_related_products - 20
             */
            do_action('woocommerce_after_single_product_summary');
            ?>
        </div>
    </div>

</div>

<?php do_action('woocommerce_after_single_product'); ?>
