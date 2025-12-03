<?php
/**
 * Simplified Product Gallery Component for Single Product Page
 * 
 * Matches the concept design with main image and thumbnail strip
 *
 * @package E-Shop Theme
 */

defined('ABSPATH') || exit;

global $product;

// Ensure we have a usable product instance
if (!$product instanceof WC_Product) {
    if (isset($GLOBALS['product']) && $GLOBALS['product'] instanceof WC_Product) {
        $product = $GLOBALS['product'];
    } else {
        $product_id = get_the_ID();
        if ($product_id && get_post_type($product_id) === 'product') {
            $product = wc_get_product($product_id);
        }
    }
}

if (!$product instanceof WC_Product) {
    echo '<div class="product-gallery product-gallery--unavailable">';
    echo '<img src="' . esc_url(wc_placeholder_img_src('full')) . '" alt="' . esc_attr__('Product image placeholder', 'eshop-theme') . '">';
    echo '</div>';
    return;
}

// Get product gallery images
$attachment_ids = $product->get_gallery_image_ids();
$main_image_id = $product->get_image_id();
$main_image_url = wp_get_attachment_image_url($main_image_id, 'full');
$main_image_alt = get_post_meta($main_image_id, '_wp_attachment_image_alt', true);
$main_image_title = get_the_title($main_image_id);

// Build gallery images array (main image + gallery images)
$gallery_images = array();

// Add main image as first image
if ($main_image_id) {
    $gallery_images[] = array(
        'id' => $main_image_id,
        'url' => $main_image_url,
        'alt' => $main_image_alt ?: $product->get_name(),
        'title' => $main_image_title ?: $product->get_name()
    );
}

// Add gallery images
foreach ($attachment_ids as $index => $image_id) {
    if ($image_id && $image_id !== $main_image_id) {
        $gallery_images[] = array(
            'id' => $image_id,
            'url' => wp_get_attachment_image_url($image_id, 'full'),
            'alt' => get_post_meta($image_id, '_wp_attachment_image_alt', true) ?: $product->get_name(),
            'title' => get_the_title($image_id) ?: $product->get_name()
        );
    }
}

// If no images found, use placeholder
if (empty($gallery_images)) {
    $gallery_images[] = array(
        'id' => 0,
        'url' => wc_placeholder_img_src('full'),
        'alt' => __('Product placeholder', 'eshop-theme'),
        'title' => $product->get_name()
    );
}
?>

<div class="product-gallery-simple" data-product-id="<?php echo esc_attr($product->get_id()); ?>">
    <!-- Main Image -->
    <div class="main-image">
        <img src="<?php echo esc_url($gallery_images[0]['url']); ?>" 
             alt="<?php echo esc_attr($gallery_images[0]['alt']); ?>" 
             title="<?php echo esc_attr($gallery_images[0]['title']); ?>"
             class="product-gallery__main-image-img"
             loading="eager"
             decoding="async">
             
        <?php if (count($gallery_images) > 1) : ?>
            <div class="gallery-nav">
                <button class="gallery-prev" aria-label="<?php esc_attr_e('Previous image', 'eshop-theme'); ?>">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                </button>
                <button class="gallery-next" aria-label="<?php esc_attr_e('Next image', 'eshop-theme'); ?>">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                </button>
            </div>
        <?php endif; ?>
    </div>

    <!-- Thumbnail Strip -->
    <?php if (count($gallery_images) > 1) : ?>
        <div class="thumb-strip">
            <?php foreach ($gallery_images as $index => $image) : ?>
                <div class="thumb <?php echo $index === 0 ? 'active' : ''; ?>" 
                     data-index="<?php echo esc_attr($index); ?>"
                     data-src="<?php echo esc_url($image['url']); ?>">
                    <img src="<?php echo esc_url(wp_get_attachment_image_url($image['id'], 'medium')); ?>" 
                         alt="<?php echo esc_attr($image['alt']); ?>"
                         title="<?php echo esc_attr($image['title']); ?>"
                         loading="lazy"
                         decoding="async">
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const gallery = document.querySelector('.product-gallery-simple');
    if (!gallery) return;

    const mainImg = gallery.querySelector('.main-image img');
    const thumbs = gallery.querySelectorAll('.thumb');
    const prevBtn = gallery.querySelector('.gallery-prev');
    const nextBtn = gallery.querySelector('.gallery-next');
    
    let currentIndex = 0;
    const totalImages = thumbs.length;

    function updateGallery(index) {
        if (index < 0) index = totalImages - 1;
        if (index >= totalImages) index = 0;
        
        currentIndex = index;
        const targetThumb = thumbs[currentIndex];
        
        if (targetThumb) {
            const src = targetThumb.dataset.src;
            const alt = targetThumb.querySelector('img').alt;
            const title = targetThumb.querySelector('img').title;
            
            // Update main image
            mainImg.src = src;
            mainImg.alt = alt;
            mainImg.title = title;
            
            // Update active state
            thumbs.forEach(t => t.classList.remove('active'));
            targetThumb.classList.add('active');
            
            // Scroll thumbnail into view if needed
            targetThumb.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'nearest' });
        }
    }

    thumbs.forEach((thumb, index) => {
        thumb.addEventListener('click', function() {
            updateGallery(index);
        });
    });
    
    if (prevBtn) {
        prevBtn.addEventListener('click', function() {
            updateGallery(currentIndex - 1);
        });
    }
    
    if (nextBtn) {
        nextBtn.addEventListener('click', function() {
            updateGallery(currentIndex + 1);
        });
    }
});
</script>