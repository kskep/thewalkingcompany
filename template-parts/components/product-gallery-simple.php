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

    thumbs.forEach(thumb => {
        thumb.addEventListener('click', function() {
            const src = this.dataset.src;
            const alt = this.querySelector('img').alt;
            const title = this.querySelector('img').title;
            
            // Update main image
            mainImg.src = src;
            mainImg.alt = alt;
            mainImg.title = title;
            
            // Update active state
            thumbs.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
        });
    });
});
</script>