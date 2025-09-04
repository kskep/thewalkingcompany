<?php
/**
 * Product Gallery Component
 * 
 * Modern product gallery with Swiper slider and thumbnail navigation
 * Follows 2025 UX/UI standards with accessibility features
 *
 * @package thewalkingtheme
 */

defined('ABSPATH') || exit;

global $product;

if (!$product) {
    return;
}

$attachment_ids = $product->get_gallery_image_ids();
$main_image_id = $product->get_image_id();

// Combine main image with gallery images
$all_image_ids = array_merge([$main_image_id], $attachment_ids);
$all_image_ids = array_filter($all_image_ids); // Remove empty values

if (empty($all_image_ids)) {
    return;
}

?>

<div class="product-gallery-container" data-product-id="<?php echo esc_attr($product->get_id()); ?>">
    
    <!-- Main Gallery Slider -->
    <div class="product-main-gallery">
        <div class="swiper product-main-slider" id="productMainSlider">
            <div class="swiper-wrapper">
                <?php foreach ($all_image_ids as $image_id) : 
                    $image_src = wp_get_attachment_image_src($image_id, 'woocommerce_single');
                    $image_full = wp_get_attachment_image_src($image_id, 'full');
                    $image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
                    $image_title = get_the_title($image_id);
                ?>
                    <div class="swiper-slide">
                        <div class="gallery-image-wrapper">
                            <img 
                                src="<?php echo esc_url($image_src[0]); ?>" 
                                alt="<?php echo esc_attr($image_alt); ?>"
                                title="<?php echo esc_attr($image_title); ?>"
                                data-large="<?php echo esc_url($image_full[0]); ?>"
                                class="gallery-main-image"
                                loading="lazy"
                            />
                            
                            <!-- Zoom Button -->
                            <button 
                                class="zoom-trigger" 
                                aria-label="<?php esc_attr_e('Zoom image', 'thewalkingtheme'); ?>"
                                data-image-src="<?php echo esc_url($image_full[0]); ?>"
                            >
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M21 21L16.514 16.506M19 10.5C19 15.194 15.194 19 10.5 19S2 15.194 2 10.5 5.806 2 10.5 2 19 5.806 19 10.5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    <path d="M13.5 10.5H7.5M10.5 7.5V13.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Navigation Arrows -->
            <div class="gallery-navigation">
                <button class="swiper-button-prev gallery-nav-btn" aria-label="<?php esc_attr_e('Previous image', 'thewalkingtheme'); ?>">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                <button class="swiper-button-next gallery-nav-btn" aria-label="<?php esc_attr_e('Next image', 'thewalkingtheme'); ?>">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
            
            <!-- Progress Indicator -->
            <div class="gallery-progress">
                <span class="current-slide">1</span>
                <span class="separator">/</span>
                <span class="total-slides"><?php echo count($all_image_ids); ?></span>
            </div>
        </div>
    </div>
    
    <!-- Thumbnail Navigation -->
    <?php if (count($all_image_ids) > 1) : ?>
    <div class="product-thumbnails-gallery">
        <div class="swiper product-thumbs-slider" id="productThumbsSlider">
            <div class="swiper-wrapper">
                <?php foreach ($all_image_ids as $index => $image_id) : 
                    $thumb_src = wp_get_attachment_image_src($image_id, 'woocommerce_gallery_thumbnail');
                    $image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
                ?>
                    <div class="swiper-slide">
                        <button 
                            class="thumbnail-button <?php echo $index === 0 ? 'active' : ''; ?>"
                            data-slide-index="<?php echo $index; ?>"
                            aria-label="<?php echo esc_attr(sprintf(__('View image %d', 'thewalkingtheme'), $index + 1)); ?>"
                        >
                            <img 
                                src="<?php echo esc_url($thumb_src[0]); ?>" 
                                alt="<?php echo esc_attr($image_alt); ?>"
                                class="thumbnail-image"
                                loading="lazy"
                            />
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Lightbox Overlay -->
    <div class="gallery-lightbox" id="galleryLightbox" aria-hidden="true">
        <div class="lightbox-backdrop"></div>
        <div class="lightbox-content">
            <button class="lightbox-close" aria-label="<?php esc_attr_e('Close lightbox', 'thewalkingtheme'); ?>">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </button>
            <div class="lightbox-image-container">
                <img class="lightbox-image" src="" alt="" />
            </div>
            <div class="lightbox-navigation">
                <button class="lightbox-prev" aria-label="<?php esc_attr_e('Previous image', 'thewalkingtheme'); ?>">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
                <button class="lightbox-next" aria-label="<?php esc_attr_e('Next image', 'thewalkingtheme'); ?>">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>
    
</div>

<!-- Product Badge -->
<?php if ($product->is_on_sale()) : ?>
<div class="product-badge sale-badge">
    <?php
    $percentage = 0;
    if ($product->get_regular_price() && $product->get_sale_price()) {
        $percentage = round((($product->get_regular_price() - $product->get_sale_price()) / $product->get_regular_price()) * 100);
    }
    ?>
    <span class="badge-text">
        <?php if ($percentage > 0) : ?>
            <?php echo sprintf(__('-%d%%', 'thewalkingtheme'), $percentage); ?>
        <?php else : ?>
            <?php _e('Sale', 'thewalkingtheme'); ?>
        <?php endif; ?>
    </span>
</div>
<?php endif; ?>