<?php
/**
 * Front: Flexible Banner Rows Section
 *
 * Displays flexible rows of banners (1-4 per row)
 * Each row automatically adjusts columns based on number of banners
 * 
 * Uses eshop_get_banner_rows() from inc/front-fields.php
 *
 * @package E-Shop Theme
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get banner rows from theme settings
$banner_rows = function_exists('eshop_get_banner_rows') ? eshop_get_banner_rows() : array();

// If no banners are found, show a placeholder message for admin users
if (empty($banner_rows)) {
    if (current_user_can('manage_options')) {
        echo '<div class="container mx-auto px-4 py-8">';
        echo '<div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded">';
        echo '<strong>' . esc_html__('Admin Notice:', 'eshop-theme') . '</strong> ' . esc_html__('Front page banner rows are not configured yet.', 'eshop-theme') . '<br>';
        echo esc_html__('Edit the Front Page and add Banner Rows.', 'eshop-theme');
        echo '</div>';
        echo '</div>';
    }
    return;
}
?>

<section class="banner-rows-section">
    <?php foreach ($banner_rows as $row_index => $row) :
        $banners = $row['banners'] ?? array();
        $column_count = count($banners);
        
        if ($column_count === 0) continue;
        
        // Determine column class based on number of banners
        $column_class = 'banner-cols-' . $column_count;
    ?>
    <div class="banner-row <?php echo esc_attr($column_class); ?>" data-row="<?php echo esc_attr($row_index + 1); ?>">
        <?php foreach ($banners as $banner_index => $banner) :
            $image_url = $banner['image_url'] ?? '';
            $media_type = $banner['media_type'] ?? (function_exists('eshop_is_video_url') && eshop_is_video_url($image_url) ? 'video' : 'image');
            $alt = $banner['alt'] ?? '';
            $title = $banner['title'] ?? '';
            $link = $banner['link'] ?? '';
            
            if (empty($image_url)) continue;
        ?>
        <div class="banner-item">
            <a href="<?php echo esc_url($link ?: '#'); ?>" class="banner-link" <?php echo empty($link) ? '' : ''; ?>>
                <div class="banner-image-wrapper">
                    <?php if ($media_type === 'video') : ?>
                    <video
                        class="banner-image banner-video"
                        autoplay
                        muted
                        loop
                        playsinline
                        preload="metadata"
                    >
                        <source src="<?php echo esc_url($image_url); ?>" type="<?php echo esc_attr(wp_check_filetype($image_url)['type'] ?: 'video/mp4'); ?>">
                        <?php esc_html_e('Your browser does not support the video tag.', 'eshop-theme'); ?>
                    </video>
                    <?php else : ?>
                    <img
                        src="<?php echo esc_url($image_url); ?>"
                        alt="<?php echo esc_attr($alt ?: $title); ?>"
                        class="banner-image"
                        loading="<?php echo $row_index === 0 ? 'eager' : 'lazy'; ?>"
                    />
                    <?php endif; ?>
                    
                    <?php if (!empty($title)) : ?>
                    <!-- Hidden title for SEO -->
                    <h3 class="sr-only"><?php echo esc_html($title); ?></h3>
                    <?php endif; ?>
                </div>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
</section>
