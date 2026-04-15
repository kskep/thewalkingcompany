<?php
/**
 * Component: Hero Slider
 * Usage:
 * get_template_part('template-parts/components/hero-slider', null, [
 *   'desktop_repeater' => 'desktop_slider',
 *   'desktop_field'    => 'desktop_image',
 *   'mobile_repeater'  => 'mobile_slider',
 *   'mobile_field'     => 'mobile_image',
 *   'show_nav'         => true,
 *   'wrapper_classes'  => ''
 * ]);
 *
 * Supports either:
 * - Two repeaters (desktop_slider/mobile_slider) with image fields per slide
 * - A single desktop_slider repeater that also includes a mobile_image subfield
 */

if (!defined('ABSPATH')) {
    exit;
}

// Extract args with defaults
$args = isset($args) && is_array($args) ? $args : array();
$post_id          = $args['post_id'] ?? get_the_ID();
$desktop_repeater = $args['desktop_repeater'] ?? 'desktop_slider';
$desktop_field    = $args['desktop_field'] ?? 'desktop_image';
$mobile_repeater  = $args['mobile_repeater'] ?? 'mobile_slider';
$mobile_field     = $args['mobile_field'] ?? 'mobile_image';
$show_nav         = isset($args['show_nav']) ? (bool) $args['show_nav'] : true;
$wrapper_classes  = isset($args['wrapper_classes']) ? trim($args['wrapper_classes']) : '';

// Get slides from theme settings (no ACF required)
if (!function_exists('eshop_get_hero_slides')) { return; }

$desktop_slides = eshop_get_hero_slides('desktop');
$mobile_slides  = eshop_get_hero_slides('mobile');

// Fallback: if mobile is empty, reuse desktop
if (empty($mobile_slides)) { $mobile_slides = $desktop_slides; }

if (empty($desktop_slides) && empty($mobile_slides)) { return; }
?>

<section class="hero-slider-wrapper js-hero-slider <?php echo esc_attr($wrapper_classes); ?>">
    <?php if (!empty($desktop_slides)) : ?>
        <div class="hero-slider-desktop swiper">
            <div class="swiper-wrapper">
                <?php foreach ($desktop_slides as $slide) : ?>
                    <div class="swiper-slide">
                        <?php if (!empty($slide['link'])) : ?><a href="<?php echo esc_url($slide['link']); ?>" class="hero-slide-link block"><?php endif; ?>
                        <div class="hero-slide relative overflow-hidden">
                            <?php if (($slide['media_type'] ?? 'image') === 'video') : ?>
                            <video class="w-full h-auto block hero-media" autoplay muted loop playsinline preload="metadata">
                                <source src="<?php echo esc_url($slide['url']); ?>" type="<?php echo esc_attr(wp_check_filetype($slide['url'])['type'] ?: 'video/mp4'); ?>">
                                <?php esc_html_e('Your browser does not support the video tag.', 'eshop-theme'); ?>
                            </video>
                            <?php else : ?>
                            <img class="w-full h-auto block" src="<?php echo $slide['url']; ?>" alt="<?php echo $slide['alt']; ?>" />
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($slide['link'])) : ?></a><?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="swiper-pagination"></div>
            <?php if ($show_nav) : ?>
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($mobile_slides)) : ?>
        <div class="hero-slider-mobile swiper">
            <div class="swiper-wrapper">
                <?php foreach ($mobile_slides as $slide) : ?>
                    <div class="swiper-slide">
                        <?php if (!empty($slide['link'])) : ?><a href="<?php echo esc_url($slide['link']); ?>" class="hero-slide-link block"><?php endif; ?>
                        <div class="hero-slide relative overflow-hidden">
                            <?php if (($slide['media_type'] ?? 'image') === 'video') : ?>
                            <video class="w-full h-auto block hero-media" autoplay muted loop playsinline preload="metadata">
                                <source src="<?php echo esc_url($slide['url']); ?>" type="<?php echo esc_attr(wp_check_filetype($slide['url'])['type'] ?: 'video/mp4'); ?>">
                                <?php esc_html_e('Your browser does not support the video tag.', 'eshop-theme'); ?>
                            </video>
                            <?php else : ?>
                            <img class="w-full h-auto block" src="<?php echo $slide['url']; ?>" alt="<?php echo $slide['alt']; ?>" />
                            <?php endif; ?>
                        </div>
                        <?php if (!empty($slide['link'])) : ?></a><?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
            <div class="swiper-pagination"></div>
        </div>
    <?php endif; ?>
</section>
