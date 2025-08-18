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

// Hide out of stock products completely
if (!$product->is_in_stock()) {
    return;
}
?>

<div <?php wc_product_class('product-card group relative', $product); ?>>
    
    <!-- Product Image / Slider -->
    <div class="product-image relative overflow-hidden aspect-square">
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
                                <?php echo wp_get_attachment_image($main_image_id, 'product-thumbnail-hq', false, array(
                                    'class' => 'w-full h-full object-cover transition-transform duration-300 group-hover:scale-105',
                                    'alt' => $product->get_name(),
                                    'loading' => 'lazy'
                                )); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($gallery_ids)) : foreach ($gallery_ids as $gid) : ?>
                        <div class="swiper-slide">
                            <a href="<?php the_permalink(); ?>" class="block aspect-square">
                                <?php echo wp_get_attachment_image($gid, 'product-thumbnail-hq', false, array(
                                    'class' => 'w-full h-full object-cover transition-transform duration-300 group-hover:scale-105',
                                    'alt' => $product->get_name(),
                                    'loading' => 'lazy'
                                )); ?>
                            </a>
                        </div>
                    <?php endforeach; endif; ?>
                <?php else : ?>
                    <div class="swiper-slide">
                        <a href="<?php the_permalink(); ?>" class="block aspect-square">
                            <div class="w-full h-full flex items-center justify-center" style="background-color: var(--bg-warm);">
                                <i class="fas fa-image text-gray-300 text-4xl"></i>
                            </div>
                        </a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Navigation arrows (only show if multiple images) -->
            <?php if (($main_image_id ? 1 : 0) + count($gallery_ids) > 1) : ?>
                <div class="swiper-button-prev product-slider-prev">
                    <i class="fas fa-chevron-left"></i>
                </div>
                <div class="swiper-button-next product-slider-next">
                    <i class="fas fa-chevron-right"></i>
                </div>
            <?php endif; ?>

            <!-- Pagination dots (only show if multiple images) -->
            <?php if (($main_image_id ? 1 : 0) + count($gallery_ids) > 1) : ?>
                <div class="swiper-pagination product-slider-pagination"></div>
            <?php endif; ?>
        </div>
        
        <!-- Wishlist Button -->
        <?php if (function_exists('eshop_wishlist_button')) : ?>
            <div class="absolute top-4 right-4 z-10">
                <?php
                $is_in_wishlist = function_exists('eshop_is_in_wishlist') ? eshop_is_in_wishlist($product->get_id()) : false;
                $heart_class = $is_in_wishlist ? 'material-icons' : 'material-icons';
                $heart_icon = $is_in_wishlist ? 'favorite' : 'favorite_border';
                ?>
                <button class="add-to-wishlist text-gray-600 hover:text-red-500 transition-colors"
                        data-product-id="<?php echo $product->get_id(); ?>"
                        title="<?php _e('Add to Wishlist', 'eshop-theme'); ?>">
                    <span class="<?php echo $heart_class; ?> text-2xl"><?php echo $heart_icon; ?></span>
                </button>
            </div>
        <?php endif; ?>

        <!-- Product Badges -->
        <div class="absolute top-4 left-4 flex flex-col gap-y-2 z-10">
            <?php
            // Sale Badge
            if ($product->is_on_sale()) {
                echo '<span class="badge badge-sale">Sale</span>';
            }

            // Low Stock Badge (if stock is low but not out)
            $stock_quantity = $product->get_stock_quantity();
            if ($product->is_in_stock() && $stock_quantity !== null && $stock_quantity <= 5 && $stock_quantity > 0) {
                echo '<span class="badge badge-low-stock">Low Stock</span>';
            }

            // New Badge (products created in last 30 days)
            $created_date = get_the_date('U', $product->get_id());
            $thirty_days_ago = strtotime('-30 days');
            if ($created_date > $thirty_days_ago) {
                echo '<span class="badge badge-new">New</span>';
            }
            ?>
        </div>

        <!-- Stock Status Overlay (for out of stock products) -->
        <?php if (!$product->is_in_stock()) : ?>
            <div class="absolute inset-0 bg-white bg-opacity-80 flex items-center justify-center z-20">
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

    <!-- Size Variants (if available) -->
    <?php
    $size_variants = function_exists('eshop_get_product_size_variants') ? eshop_get_product_size_variants($product, 8) : array();
    if (!empty($size_variants)) :
    ?>
        <div class="size-variants flex justify-center flex-wrap gap-2 px-3 py-3">
            <?php foreach ($size_variants as $size) : ?>
                <span class="size-option w-8 h-8 rounded-full border border-gray-300 flex items-center justify-center text-xs font-medium transition-all duration-200 hover:border-gray-400 <?php echo !$size['in_stock'] ? 'opacity-50 cursor-not-allowed bg-gray-100 text-gray-400' : 'bg-white text-gray-700 hover:bg-gray-50'; ?>"
                      title="<?php echo esc_attr($size['name'] . (!$size['in_stock'] ? ' - Out of Stock' : '')); ?>"
                      data-size="<?php echo esc_attr($size['slug']); ?>"
                      data-variation-id="<?php echo esc_attr($size['variation_id']); ?>"
                      data-in-stock="<?php echo $size['in_stock'] ? 'true' : 'false'; ?>">
                    <?php echo esc_html($size['name']); ?>
                </span>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Product Info -->
    <div class="product-info">

        <!-- Product Title -->
        <h3 class="product-title">
            <a href="<?php the_permalink(); ?>">
                <?php the_title(); ?>
            </a>
        </h3>

        <!-- Product Price -->
        <div class="product-price">
            <?php
            $price_html = $product->get_price_html();
            if ($price_html) {
                echo '<p class="price">' . $price_html . '</p>';
            }
            ?>
        </div>
    </div>
</div>