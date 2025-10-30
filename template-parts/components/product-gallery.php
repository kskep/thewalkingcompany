<?php
/**
 * Product Gallery Component
 *
 * Modern product gallery with main image and thumbnail gallery.
 * Features zoom functionality, responsive design, and WooCommerce integration.
 * Follows magazine aesthetic with sharp edges and minimal shadows.
 *
 * @package E-Shop Theme
 */

defined('ABSPATH') || exit;

global $product;

// Get product gallery images
$attachment_ids = $product->get_gallery_image_ids();
$main_image_id = $product->get_image_id();
$main_image_url = wp_get_attachment_image_url($main_image_id, 'full');
$main_image_alt = get_post_meta($main_image_id, '_wp_attachment_image_alt', true);
$main_image_title = get_the_title($main_image_id);

// Add main image to gallery array
$gallery_images = array();
if ($main_image_id) {
    $gallery_images[] = array(
        'id' => $main_image_id,
        'url' => $main_image_url,
        'alt' => $main_image_alt ?: $product->get_name(),
        'title' => $main_image_title,
        'is_video' => false
    );
}

// Add gallery images
foreach ($attachment_ids as $attachment_id) {
    $gallery_images[] = array(
        'id' => $attachment_id,
        'url' => wp_get_attachment_image_url($attachment_id, 'full'),
        'alt' => get_post_meta($attachment_id, '_wp_attachment_image_alt', true) ?: get_the_title($attachment_id),
        'title' => get_the_title($attachment_id),
        'is_video' => wp_attachment_is('video', $attachment_id)
    );
}

// If no images, use placeholder
if (empty($gallery_images)) {
    $gallery_images[] = array(
        'id' => 0,
        'url' => wc_placeholder_img_src('full'),
        'alt' => __('Product placeholder', 'eshop-theme'),
        'title' => $product->get_name(),
        'is_video' => false
    );
}
?>

<div class="product-gallery<?php echo count($gallery_images) > 1 ? ' has-thumbnails' : ''; ?>" data-product-id="<?php echo esc_attr($product->get_id()); ?>">
    
    <!-- Sale Badge -->
    <?php if ($product->is_on_sale()) : ?>
        <span class="onsale" aria-label="<?php esc_attr_e('On sale', 'eshop-theme'); ?>">
            <?php echo esc_html__('Sale', 'eshop-theme'); ?>
        </span>
    <?php endif; ?>
    
    <!-- Main Image Container with Swiper -->
    <div class="product-gallery__main">
        <div class="product-gallery__main-image-wrapper swiper">
            <div class="swiper-wrapper">
                <?php foreach ($gallery_images as $index => $image) : ?>
                    <div class="product-gallery__main-image swiper-slide" 
                         data-index="<?php echo esc_attr($index); ?>"
                         role="tabpanel"
                         id="gallery-image-<?php echo esc_attr($index); ?>">
                        
                        <?php if ($image['is_video']) : ?>
                            <video class="product-gallery__video"
                                   controls
                                   preload="metadata"
                                   aria-label="<?php echo esc_attr($image['alt']); ?>">
                                <source src="<?php echo esc_url($image['url']); ?>" type="video/mp4">
                                <?php esc_html_e('Your browser does not support the video tag.', 'eshop-theme'); ?>
                            </video>
                        <?php else : ?>
                            <img src="<?php echo esc_url($image['url']); ?>"
                                 alt="<?php echo esc_attr($image['alt']); ?>"
                                 title="<?php echo esc_attr($image['title']); ?>"
                                 class="product-gallery__main-image-img"
                                 loading="eager"
                                 decoding="async"
                                 width="<?php echo esc_attr(wp_get_attachment_metadata($image['id'])['width'] ?? '800'); ?>"
                                 height="<?php echo esc_attr(wp_get_attachment_metadata($image['id'])['height'] ?? '800'); ?>">
                        <?php endif; ?>
                        
                        <!-- Loading State -->
                        <div class="product-gallery__loading" aria-hidden="true">
                            <div class="product-gallery__loading-spinner"></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Swiper Navigation -->
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
        </div>
        
    </div>
    
    <!-- Zoom Overlay (Desktop) - Moved outside gallery container -->
    <div class="product-gallery__zoom-overlay" aria-hidden="true">
        <img src="" alt="" class="product-gallery__zoom-image">
        <button class="product-gallery__zoom-close" aria-label="<?php esc_attr_e('Close zoom', 'eshop-theme'); ?>">
            <i class="fas fa-times" aria-hidden="true"></i>
        </button>
    </div>
    
    <!-- Thumbnail Gallery with Swiper -->
    <?php if (count($gallery_images) > 1) : ?>
        <div class="product-gallery__thumbnails swiper">
            <div class="product-gallery__thumbnails-wrapper swiper-wrapper">
                <?php foreach ($gallery_images as $index => $image) : ?>
                    <div class="product-gallery__thumbnail swiper-slide"
                            data-index="<?php echo esc_attr($index); ?>"
                            aria-label="<?php echo esc_attr(sprintf(__('View image %d of %d: %s', 'eshop-theme'), $index + 1, count($gallery_images), $image['title'])); ?>"
                            aria-controls="gallery-image-<?php echo esc_attr($index); ?>"
                            role="tab">
                        
                        <?php if ($image['is_video']) : ?>
                            <div class="product-gallery__thumbnail-video">
                                <img src="<?php echo esc_url($image['url']); ?>"
                                     alt="<?php echo esc_attr($image['alt']); ?>"
                                     loading="lazy"
                                     decoding="async">
                                <div class="product-gallery__video-play-icon">
                                    <i class="fas fa-play" aria-hidden="true"></i>
                                </div>
                            </div>
                        <?php else : ?>
                            <img src="<?php echo esc_url(wp_get_attachment_image_url($image['id'], 'thumbnail')); ?>"
                                 alt="<?php echo esc_attr($image['alt']); ?>"
                                 loading="lazy"
                                 decoding="async">
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- Image Counter (Pagination) -->
    <?php if (count($gallery_images) > 1) : ?>
        <div class="product-gallery__pagination swiper-pagination"></div>
    <?php endif; ?>
</div>

<!-- Product Gallery Data for JavaScript -->
<script type="application/json" class="product-gallery-data">
<?php
$gallery_data = array();
foreach ($gallery_images as $image) {
    $gallery_data[] = array(
        'url' => $image['url'],
        'alt' => $image['alt'],
        'title' => $image['title'],
        'is_video' => $image['is_video']
    );
}
echo json_encode($gallery_data);
?>
</script>