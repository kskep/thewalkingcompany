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
        
        <!-- 2-Column Layout with 2025 Standards -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-16">
            
            <!-- Left Column - Modern Product Gallery -->
            <div class="product-images-section">
                <?php 
                // Include custom product gallery component
                get_template_part('template-parts/components/product-gallery');
                ?>
            </div>
            
            <!-- Right Column - Modern Product Information -->
            <div class="product-info-section">
                
                <!-- Product Header -->
                <div class="product-header mb-6">
                    <?php
                    // Product breadcrumb/category
                    $product_categories = wp_get_post_terms($product->get_id(), 'product_cat');
                    if (!empty($product_categories)) {
                        echo '<div class="product-breadcrumb mb-3">';
                        echo '<span class="text-sm font-medium text-gray-500 uppercase tracking-wide">';
                        echo esc_html($product_categories[0]->name);
                        echo '</span>';
                        echo '</div>';
                    }
                    
                    // Product title
                    woocommerce_template_single_title();
                    
                    // Product rating
                    woocommerce_template_single_rating();
                    ?>
                </div>
                
                <!-- Price Section -->
                <div class="product-price-section mb-8">
                    <?php woocommerce_template_single_price(); ?>
                    
                    <?php if ($product->is_on_sale()) : ?>
                    <div class="savings-info mt-2">
                        <?php
                        $regular_price = $product->get_regular_price();
                        $sale_price = $product->get_sale_price();
                        if ($regular_price && $sale_price) {
                            $savings = $regular_price - $sale_price;
                            $percentage = round(($savings / $regular_price) * 100);
                            echo '<span class="text-sm font-medium text-green-600">';
                            echo sprintf(__('Save %s (%d%%)', 'thewalkingtheme'), wc_price($savings), $percentage);
                            echo '</span>';
                        }
                        ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Product Description -->
                <?php if ($product->get_short_description()) : ?>
                <div class="product-description mb-8">
                    <?php woocommerce_template_single_excerpt(); ?>
                </div>
                <?php endif; ?>
                
                <!-- Add to Cart Section -->
                <div class="product-cart-section mb-8">
                    <?php woocommerce_template_single_add_to_cart(); ?>
                </div>
                
                <!-- Product Meta -->
                <div class="product-meta-section mb-8">
                    <?php woocommerce_template_single_meta(); ?>
                </div>
                
                <!-- Modern Product Actions -->
                <div class="product-actions-section">
                    
                    <!-- Wishlist & Quick Actions -->
                    <div class="quick-actions flex flex-wrap gap-3 mb-6 pb-6 border-b border-gray-200">
                        
                        <!-- Wishlist Button -->
                        <?php if (function_exists('eshop_wishlist_button')) : ?>
                            <?php 
                            $is_in_wishlist = function_exists('eshop_is_in_wishlist') ? eshop_is_in_wishlist($product->get_id()) : false;
                            $heart_class = $is_in_wishlist ? 'fas fa-heart text-pink-500' : 'far fa-heart text-gray-400';
                            $button_text = $is_in_wishlist ? __('Saved', 'thewalkingtheme') : __('Save', 'thewalkingtheme');
                            ?>
                            <button class="add-to-wishlist modern-action-btn" 
                                    data-product-id="<?php echo $product->get_id(); ?>"
                                    aria-label="<?php echo $is_in_wishlist ? __('Remove from Wishlist', 'thewalkingtheme') : __('Add to Wishlist', 'thewalkingtheme'); ?>">
                                <svg class="w-5 h-5" fill="<?php echo $is_in_wishlist ? 'currentColor' : 'none'; ?>" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                </svg>
                                <span><?php echo $button_text; ?></span>
                            </button>
                        <?php endif; ?>
                        
                        <!-- Ask Question Button -->
                        <button class="ask-question-btn modern-action-btn" data-toggle="ask-question">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span><?php _e('Ask a Question', 'thewalkingtheme'); ?></span>
                        </button>
                        
                    </div>
                    
                    <!-- Social Sharing -->
                    <div class="social-sharing">
                        <h4 class="text-sm font-semibold text-gray-900 mb-4"><?php _e('Share this product', 'thewalkingtheme'); ?></h4>
                        <div class="social-share-buttons">
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode(get_permalink()); ?>" 
                               target="_blank" 
                               class="social-share-btn facebook"
                               aria-label="<?php _e('Share on Facebook', 'thewalkingtheme'); ?>">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>
                                </svg>
                            </a>
                            <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode(get_permalink()); ?>&text=<?php echo urlencode(get_the_title()); ?>" 
                               target="_blank" 
                               class="social-share-btn twitter"
                               aria-label="<?php _e('Share on Twitter', 'thewalkingtheme'); ?>">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M23.953 4.57a10 10 0 01-2.825.775 4.958 4.958 0 002.163-2.723c-.951.555-2.005.959-3.127 1.184a4.92 4.92 0 00-8.384 4.482C7.69 8.095 4.067 6.13 1.64 3.162a4.822 4.822 0 00-.666 2.475c0 1.71.87 3.213 2.188 4.096a4.904 4.904 0 01-2.228-.616v.06a4.923 4.923 0 003.946 4.827 4.996 4.996 0 01-2.212.085 4.936 4.936 0 004.604 3.417 9.867 9.867 0 01-6.102 2.105c-.39 0-.779-.023-1.17-.067a13.995 13.995 0 007.557 2.209c9.053 0 13.998-7.496 13.998-13.985 0-.21 0-.42-.015-.63A9.935 9.935 0 0024 4.59z"/>
                                </svg>
                            </a>
                            <a href="https://pinterest.com/pin/create/button/?url=<?php echo urlencode(get_permalink()); ?>&media=<?php echo urlencode(wp_get_attachment_url($product->get_image_id())); ?>&description=<?php echo urlencode(get_the_title()); ?>" 
                               target="_blank" 
                               class="social-share-btn pinterest"
                               aria-label="<?php _e('Share on Pinterest', 'thewalkingtheme'); ?>">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.174-.105-.949-.199-2.403.041-3.439.219-.937 1.219-5.175 1.219-5.175s-.311-.623-.311-1.543c0-1.445.839-2.524 1.885-2.524.888 0 1.318.665 1.318 1.461 0 .891-.567 2.225-.859 3.46-.244 1.034.519 1.878 1.539 1.878 1.843 0 3.263-1.944 3.263-4.748 0-2.481-1.784-4.217-4.33-4.217-2.948 0-4.684 2.211-4.684 4.492 0 .891.343 1.845.771 2.365.085.104.098.195.072.301-.079.329-.254 1.029-.289 1.175-.045.191-.146.232-.336.14-1.294-.601-2.103-2.488-2.103-4.011 0-3.273 2.375-6.279 6.843-6.279 3.583 0 6.371 2.549 6.371 5.956 0 3.554-2.24 6.414-5.351 6.414-1.044 0-2.027-.544-2.363-1.263l-.644 2.455c-.233.899-.864 2.025-1.286 2.712.967.297 1.993.457 3.065.457 6.621 0 11.99-5.367 11.99-11.987C24.007 5.367 18.638.001 12.017.001z"/>
                                </svg>
                            </a>
                            <button class="social-share-btn copy-link"
                                    onclick="navigator.clipboard.writeText(window.location.href); this.innerHTML='<svg class=\'w-5 h-5\' fill=\'currentColor\' viewBox=\'0 0 24 24\'><path d=\'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z\'/></svg>'; setTimeout(() => location.reload(), 1500);"
                                    aria-label="<?php _e('Copy link', 'thewalkingtheme'); ?>">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                    
                </div>
                
            </div>
            
        </div>
        
    </div>
    
    <!-- Product Additional Information -->
    <div class="product-additional-info mt-16 pt-12 border-t border-gray-200">
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
