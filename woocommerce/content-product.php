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

<div <?php wc_product_class('product-card bg-white overflow-hidden hover:shadow-lg transition-all duration-300 group relative', $product); ?>>
    
    <!-- Product Image / Slider -->
    <div class="product-image relative overflow-hidden bg-gray-50">
        <?php
        $main_image_id   = $product->get_image_id();
        $gallery_ids     = $product->get_gallery_image_ids();
        $has_any_image   = $main_image_id || !empty($gallery_ids);
        ?>
        <div class="product-slider swiper relative">
            <div class="swiper-wrapper">
                <?php if ($has_any_image) : ?>
                    <?php if ($main_image_id) : ?>
                        <div class="swiper-slide">
                            <a href="<?php the_permalink(); ?>" class="block aspect-square">
                                <?php echo wp_get_attachment_image($main_image_id, 'woocommerce_thumbnail', false, array(
                                    'class' => 'w-full h-full object-cover transition-transform duration-300 group-hover:scale-105',
                                    'alt' => $product->get_name()
                                )); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($gallery_ids)) : foreach ($gallery_ids as $gid) : ?>
                        <div class="swiper-slide">
                            <a href="<?php the_permalink(); ?>" class="block aspect-square">
                                <?php echo wp_get_attachment_image($gid, 'woocommerce_thumbnail', false, array(
                                    'class' => 'w-full h-full object-cover transition-transform duration-300 group-hover:scale-105',
                                    'alt' => $product->get_name()
                                )); ?>
                            </a>
                        </div>
                    <?php endforeach; endif; ?>
                <?php else : ?>
                    <div class="swiper-slide">
                        <a href="<?php the_permalink(); ?>" class="block aspect-square">
                            <div class="w-full h-full flex items-center justify-center bg-gray-100">
                                <i class="fas fa-image text-gray-300 text-4xl"></i>
                            </div>
                        </a>
                    </div>
                <?php endif; ?>
            </div>
            <div class="swiper-pagination"></div>
        </div>
        
        <!-- Wishlist Button -->
        <?php if (function_exists('eshop_wishlist_button')) : ?>
            <div class="absolute top-3 right-3 z-10">
                <?php 
                $is_in_wishlist = function_exists('eshop_is_in_wishlist') ? eshop_is_in_wishlist($product->get_id()) : false;
                $heart_class = $is_in_wishlist ? 'fas fa-heart text-red-500' : 'far fa-heart text-gray-400';
                ?>
                <button class="add-to-wishlist w-8 h-8 bg-white rounded-full flex items-center justify-center shadow-sm hover:shadow-md transition-all duration-200 hover:scale-110" 
                        data-product-id="<?php echo $product->get_id(); ?>" 
                        title="<?php _e('Add to Wishlist', 'eshop-theme'); ?>">
                    <i class="<?php echo $heart_class; ?> text-sm"></i>
                </button>
            </div>
        <?php endif; ?>

        <!-- Sale Badge -->
        <?php if ($product->is_on_sale()) : ?>
            <div class="absolute top-3 left-3 bg-red-500 text-white text-xs px-2 py-1 font-semibold rounded">
                <?php _e('SALE', 'eshop-theme'); ?>
            </div>
        <?php endif; ?>

        <!-- Stock Status -->
        <?php if (!$product->is_in_stock()) : ?>
            <div class="absolute inset-0 bg-white bg-opacity-80 flex items-center justify-center">
                <span class="bg-gray-800 text-white px-3 py-1 text-sm font-medium rounded">
                    <?php _e('Out of Stock', 'eshop-theme'); ?>
                </span>
            </div>
        <?php endif; ?>
    </div>

    <!-- Color Variants (if available) -->
    <?php
    $color_variants = function_exists('eshop_get_product_color_variants') ? eshop_get_product_color_variants($product, 4) : array();
    if (!empty($color_variants)) :
    ?>
        <div class="color-variants flex justify-center space-x-1 py-3">
            <?php foreach ($color_variants as $color) : ?>
                <span class="w-3 h-3 rounded-full border border-gray-200 hover:scale-110 transition-transform duration-200" 
                      style="background-color: <?php echo esc_attr($color['hex']); ?>"
                      title="<?php echo esc_attr($color['name']); ?>"></span>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Product Info -->
    <div class="product-info p-4">
        
        <!-- Product Title -->
        <h3 class="product-title mb-2">
            <a href="<?php the_permalink(); ?>" class="text-sm font-medium text-gray-900 hover:text-gray-700 transition-colors line-clamp-2 leading-tight">
                <?php the_title(); ?>
            </a>
        </h3>

        <!-- Product Price -->
        <div class="product-price">
            <?php
            $price_html = $product->get_price_html();
            if ($price_html) {
                echo '<div class="text-lg font-semibold text-gray-900">' . $price_html . '</div>';
            }
            ?>
        </div>
    </div>
</div>