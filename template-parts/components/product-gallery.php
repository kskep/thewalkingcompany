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

<div class="product-gallery" data-product-id="<?php echo esc_attr($product->get_id()); ?>">
    
    <!-- Sale Badge -->
    <?php if ($product->is_on_sale()) : ?>
        <span class="onsale" aria-label="<?php esc_attr_e('On sale', 'eshop-theme'); ?>">
            <?php echo esc_html__('Sale', 'eshop-theme'); ?>
        </span>
    <?php endif; ?>
    
    <!-- Main Image Container -->
    <div class="product-gallery__main">
        <div class="product-gallery__main-image-wrapper">
            <?php foreach ($gallery_images as $index => $image) : ?>
                <div class="product-gallery__main-image <?php echo $index === 0 ? 'is-active' : ''; ?>" 
                     data-index="<?php echo esc_attr($index); ?>"
                     role="tabpanel"
                     id="gallery-image-<?php echo esc_attr($index); ?>"
                     aria-hidden="<?php echo $index === 0 ? 'false' : 'true'; ?>">
                    
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
                             height="<?php echo esc_attr(wp_get_attachment_metadata($image['id'])['height'] ?? '800'); ?>"
                             style="max-width: 100%; height: auto;">
                    <?php endif; ?>
                    
                    <!-- Loading State -->
                    <div class="product-gallery__loading" aria-hidden="true">
                        <div class="product-gallery__loading-spinner"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Zoom Overlay (Desktop) -->
        <div class="product-gallery__zoom-overlay" aria-hidden="true">
            <img src="" alt="" class="product-gallery__zoom-image">
            <button class="product-gallery__zoom-close" aria-label="<?php esc_attr_e('Close zoom', 'eshop-theme'); ?>">
                <i class="fas fa-times" aria-hidden="true"></i>
            </button>
        </div>
    </div>
    
    <!-- Thumbnail Gallery -->
    <?php if (count($gallery_images) > 1) : ?>
        <div class="product-gallery__thumbnails">
            <div class="product-gallery__thumbnails-wrapper">
                <?php foreach ($gallery_images as $index => $image) : ?>
                    <button class="product-gallery__thumbnail <?php echo $index === 0 ? 'is-active' : ''; ?>"
                            data-index="<?php echo esc_attr($index); ?>"
                            aria-label="<?php echo esc_attr(sprintf(__('View image %d of %d: %s', 'eshop-theme'), $index + 1, count($gallery_images), $image['title'])); ?>"
                            aria-controls="gallery-image-<?php echo esc_attr($index); ?>"
                            aria-selected="<?php echo $index === 0 ? 'true' : 'false'; ?>"
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
                    </button>
                <?php endforeach; ?>
            </div>
            
            <!-- Thumbnail Navigation (Mobile) -->
            <?php if (count($gallery_images) > 4) : ?>
                <button class="product-gallery__thumbnail-nav product-gallery__thumbnail-nav--prev" 
                        aria-label="<?php esc_attr_e('Previous thumbnails', 'eshop-theme'); ?>">
                    <i class="fas fa-chevron-left" aria-hidden="true"></i>
                </button>
                <button class="product-gallery__thumbnail-nav product-gallery__thumbnail-nav--next"
                        aria-label="<?php esc_attr_e('Next thumbnails', 'eshop-theme'); ?>">
                    <i class="fas fa-chevron-right" aria-hidden="true"></i>
                </button>
            <?php endif; ?>
        </div>
    <?php endif; ?>
    
    <!-- Gallery Navigation (Mobile Swipe) -->
    <div class="product-gallery__mobile-nav">
        <button class="product-gallery__mobile-nav-btn product-gallery__mobile-nav--prev"
                aria-label="<?php esc_attr_e('Previous image', 'eshop-theme'); ?>">
            <i class="fas fa-chevron-left" aria-hidden="true"></i>
        </button>
        <button class="product-gallery__mobile-nav-btn product-gallery__mobile-nav--next"
                aria-label="<?php esc_attr_e('Next image', 'eshop-theme'); ?>">
            <i class="fas fa-chevron-right" aria-hidden="true"></i>
        </button>
    </div>
    
    <!-- Image Counter -->
    <?php if (count($gallery_images) > 1) : ?>
        <div class="product-gallery__counter" aria-live="polite">
            <span class="product-gallery__counter-current">1</span>
            <span class="product-gallery__counter-separator">/</span>
            <span class="product-gallery__counter-total"><?php echo count($gallery_images); ?></span>
        </div>
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