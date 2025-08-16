<?php
/**
 * The template for displaying product content within loops
 *
 * @package E-Shop Theme
 */

defined('ABSPATH') || exit;

global $product;

// Ensure visibility.
if (empty($product) || !$product->is_visible()) {
    return;
}
?>

<div <?php wc_product_class('product-card bg-white border border-gray-200 overflow-hidden hover:shadow-lg transition-all duration-200 group', $product); ?>>
    
    <!-- Product Image -->
    <div class="product-image relative overflow-hidden">
        <a href="<?php the_permalink(); ?>" class="block">
            <?php
            /**
             * Hook: woocommerce_before_shop_loop_item_title.
             *
             * @hooked woocommerce_show_product_loop_sale_flash - 10
             * @hooked woocommerce_template_loop_product_thumbnail - 10
             */
            do_action('woocommerce_before_shop_loop_item_title');
            ?>
        </a>
        
        <!-- Product Actions Overlay -->
        <div class="product-actions absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-20 transition-all duration-200 flex items-center justify-center opacity-0 group-hover:opacity-100">
            <div class="actions-buttons flex space-x-2">
                
                <!-- Quick View -->
                <button class="quick-view-btn bg-white text-dark p-2 hover:bg-primary hover:text-white transition-colors" data-product-id="<?php echo $product->get_id(); ?>" title="<?php _e('Quick View', 'eshop-theme'); ?>">
                    <i class="fas fa-eye"></i>
                </button>
                
                <!-- Wishlist -->
                <?php if (function_exists('eshop_wishlist_button')) : ?>
                    <div class="wishlist-action bg-white hover:bg-primary hover:text-white transition-colors">
                        <?php eshop_wishlist_button($product->get_id()); ?>
                    </div>
                <?php endif; ?>
                
                <!-- Add to Cart -->
                <?php if ($product->is_purchasable() && $product->is_in_stock()) : ?>
                    <button class="add-to-cart-btn bg-white text-dark p-2 hover:bg-primary hover:text-white transition-colors" data-product-id="<?php echo $product->get_id(); ?>" title="<?php _e('Add to Cart', 'eshop-theme'); ?>">
                        <i class="fas fa-shopping-cart"></i>
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sale Badge -->
        <?php if ($product->is_on_sale()) : ?>
            <div class="sale-badge absolute top-2 left-2 bg-red-500 text-white text-xs px-2 py-1 font-semibold">
                <?php
                if ($product->get_type() === 'variable') {
                    echo __('Sale', 'eshop-theme');
                } else {
                    $regular_price = $product->get_regular_price();
                    $sale_price = $product->get_sale_price();
                    if ($regular_price && $sale_price) {
                        $percentage = round((($regular_price - $sale_price) / $regular_price) * 100);
                        echo '-' . $percentage . '%';
                    } else {
                        echo __('Sale', 'eshop-theme');
                    }
                }
                ?>
            </div>
        <?php endif; ?>

        <!-- Stock Status -->
        <?php if (!$product->is_in_stock()) : ?>
            <div class="stock-badge absolute top-2 right-2 bg-gray-500 text-white text-xs px-2 py-1 font-semibold">
                <?php _e('Out of Stock', 'eshop-theme'); ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Product Info -->
    <div class="product-info p-4">
        
        <!-- Product Categories -->
        <?php
        $categories = get_the_terms($product->get_id(), 'product_cat');
        if ($categories && !is_wp_error($categories)) :
            $category = array_shift($categories);
        ?>
            <div class="product-category mb-2">
                <a href="<?php echo get_term_link($category); ?>" class="text-xs text-gray-500 hover:text-primary transition-colors uppercase tracking-wide">
                    <?php echo $category->name; ?>
                </a>
            </div>
        <?php endif; ?>

        <!-- Product Title -->
        <h3 class="product-title mb-2">
            <a href="<?php the_permalink(); ?>" class="text-base font-semibold text-dark hover:text-primary transition-colors line-clamp-2">
                <?php the_title(); ?>
            </a>
        </h3>

        <!-- Product Rating -->
        <?php if (wc_review_ratings_enabled()) : ?>
            <div class="product-rating mb-2">
                <?php
                $rating_count = $product->get_rating_count();
                $review_count = $product->get_review_count();
                $average = $product->get_average_rating();

                if ($rating_count > 0) :
                ?>
                    <div class="flex items-center space-x-1">
                        <div class="stars flex text-yellow-400">
                            <?php
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $average) {
                                    echo '<i class="fas fa-star text-xs"></i>';
                                } elseif ($i - 0.5 <= $average) {
                                    echo '<i class="fas fa-star-half-alt text-xs"></i>';
                                } else {
                                    echo '<i class="far fa-star text-xs"></i>';
                                }
                            }
                            ?>
                        </div>
                        <span class="text-xs text-gray-500">(<?php echo $review_count; ?>)</span>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Product Price -->
        <div class="product-price mb-3">
            <?php
            /**
             * Hook: woocommerce_after_shop_loop_item_title.
             *
             * @hooked woocommerce_template_loop_price - 10
             */
            do_action('woocommerce_after_shop_loop_item_title');
            ?>
        </div>

        <!-- Product Attributes Preview -->
        <?php
        $attributes = $product->get_attributes();
        if (!empty($attributes)) :
            $displayed_attributes = array_slice($attributes, 0, 2); // Show first 2 attributes
        ?>
            <div class="product-attributes mb-3">
                <?php foreach ($displayed_attributes as $attribute_name => $attribute) : ?>
                    <?php if ($attribute->get_visible()) : ?>
                        <div class="attribute text-xs text-gray-600 mb-1">
                            <span class="attribute-label font-medium"><?php echo wc_attribute_label($attribute_name); ?>:</span>
                            <span class="attribute-value">
                                <?php
                                if ($attribute->is_taxonomy()) {
                                    $terms = wp_get_post_terms($product->get_id(), $attribute_name, array('fields' => 'names'));
                                    echo implode(', ', array_slice($terms, 0, 3));
                                } else {
                                    $values = explode('|', $attribute->get_options()[0]);
                                    echo implode(', ', array_slice($values, 0, 3));
                                }
                                ?>
                            </span>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <!-- Add to Cart Button -->
        <div class="product-actions">
            <?php
            /**
             * Hook: woocommerce_after_shop_loop_item.
             *
             * @hooked woocommerce_template_loop_add_to_cart - 10
             */
            do_action('woocommerce_after_shop_loop_item');
            ?>
        </div>
    </div>
</div>