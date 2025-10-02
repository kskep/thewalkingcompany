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

<div id="product-<?php the_ID(); ?>" <?php wc_product_class('single-product-layout bg-white p-6', $product); ?>>
    
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
                    echo '<h1 class="text-3xl font-bold mb-4 text-gray-900">' . get_the_title() . '</h1>';
                    
                    // Product rating
                    if (wc_review_ratings_enabled()) {
                        $rating_count = $product->get_rating_count();
                        $review_count = $product->get_review_count();
                        $average      = $product->get_average_rating();
                        
                        if ($rating_count > 0) {
                            echo '<div class="flex items-center gap-2 mb-4">';
                            echo '<div class="text-yellow-400">';
                            
                            // Display stars
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $average) {
                                    echo '★';
                                } else {
                                    echo '☆';
                                }
                            }
                            
                            echo '</div>';
                            echo '<span class="text-sm text-gray-500">' . sprintf(_n('%s review', '%s reviews', $review_count, 'thewalkingtheme'), $review_count) . '</span>';
                            echo '</div>';
                        }
                    }
                    ?>
                </div>
                
                <!-- Price Section -->
                <div class="product-price-section mb-6">
                    <div class="flex items-baseline gap-4 mb-2">
                        <?php
                        $price_html = $product->get_price_html();
                        if ($product->is_on_sale()) {
                            $regular_price = $product->get_regular_price();
                            $sale_price = $product->get_sale_price();
                            echo '<span class="text-3xl font-bold text-pink-600">' . wc_price($sale_price) . '</span>';
                            echo '<span class="text-xl text-gray-400 line-through">' . wc_price($regular_price) . '</span>';
                        } else {
                            echo '<span class="text-3xl font-bold text-pink-600">' . $price_html . '</span>';
                        }
                        ?>
                    </div>
                    
                    <?php if ($product->is_on_sale()) : ?>
                    <div class="savings-info">
                        <?php
                        $regular_price = $product->get_regular_price();
                        $sale_price = $product->get_sale_price();
                        if ($regular_price && $sale_price) {
                            $savings = $regular_price - $sale_price;
                            $percentage = round(($savings / $regular_price) * 100);
                            echo '<div class="inline-block bg-green-100 text-green-800 px-3 py-1 text-sm font-medium no-radius">';
                            echo sprintf(__('Save %s (%d%%)', 'thewalkingtheme'), wc_price($savings), $percentage);
                            echo '</div>';
                        }
                        ?>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Product Description -->
                <?php if ($product->get_short_description()) : ?>
                <div class="product-description mb-6">
                    <p class="text-gray-600"><?php echo wp_kses_post($product->get_short_description()); ?></p>
                </div>
                <?php endif; ?>
                
                <!-- Color Variants Section -->
                <?php if (function_exists('eshop_product_has_color_variants') && eshop_product_has_color_variants($product->get_id())) : ?>
                <div class="color-variants-section mb-6">
                    <?php get_template_part('template-parts/components/color-variants', null, array(
                        'current_id' => $product->get_id()
                    )); ?>
                </div>
                <?php endif; ?>
                
                <!-- Size Selection Section -->
                <?php if ($product->is_type('variable') && function_exists('eshop_get_product_size_variants')) : ?>
                    <?php $size_variants = eshop_get_product_size_variants($product, 12); ?>
                    <?php if (!empty($size_variants)) : ?>
                    <div class="size-selection-section mb-6">
                        <?php get_template_part('template-parts/components/size-selection'); ?>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
                
                <!-- Add to Cart Section -->
                <div class="product-cart-section mb-6">
                    <?php woocommerce_template_single_add_to_cart(); ?>
                </div>
                
                <!-- Modern Product Actions -->
                <div class="product-actions-section">
                    
                    <!-- Wishlist & Quick Actions -->
                    <div class="quick-actions flex flex-wrap gap-3 mb-6">
                        
                        <!-- Wishlist Button -->
                        <?php if (function_exists('eshop_wishlist_button_enhanced')) : ?>
                            <button class="flex items-center gap-2 px-4 py-2 border-2 border-gray-200 hover:border-pink-600 hover:text-pink-600 no-radius modern-action-btn">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                                </svg>
                                <span><?php _e('Save to Wishlist', 'thewalkingtheme'); ?></span>
                            </button>
                        <?php endif; ?>
                        
                        <!-- Ask Question Button -->
                        <button class="ask-question-btn modern-action-btn flex items-center gap-2 px-4 py-2 border-2 border-gray-200 hover:border-pink-600 hover:text-pink-600 no-radius" data-toggle="ask-question">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span><?php _e('Ask a Question', 'thewalkingtheme'); ?></span>
                        </button>
                        
                    </div>
                    
                    <!-- Product Meta -->
                    <div class="product-meta-section mb-6">
                        <?php woocommerce_template_single_meta(); ?>
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
