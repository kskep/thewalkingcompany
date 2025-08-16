<?php
/**
 * Hero Slider (ACF: desktop_slider repeater)
 * Fields:
 * - desktop_slider (repeater)
 *   - desktop_image (image)
 * - mobile_slider (repeater)
 *   - mobile_image (image)
 *
 * If you only created one repeater named `desktop_slider` and a second named `mobile_slider`, this template will handle both
 * and conditionally render based on screen size via CSS. If you instead used subfields `desktop_image` and `mobile_image`
 * within a single repeater, it will also work by reading both if present.
 */

if (!defined('ABSPATH')) {
    exit;
}

// Ensure ACF functions exist
if (!function_exists('have_rows') || !function_exists('get_sub_field')) {
    return;
}

// Helper to fetch slides for a given repeater name and image subfield
function eshop_get_slider_images($repeater_name, $image_field) {
    $slides = array();
    if (have_rows($repeater_name)) {
        while (have_rows($repeater_name)) { the_row();
            $img = get_sub_field($image_field);
            if ($img && !empty($img['url'])) {
                $slides[] = array(
                    'url' => esc_url($img['url']),
                    'alt' => esc_attr($img['alt'] ?? ''),
                );
            }
        }
        // reset rows pointer for safety
        if (function_exists('reset_rows')) {
            reset_rows();
        }
    }
    return $slides;
}

// Attempt to support two shapes:
// A) Two separate repeaters: desktop_slider (desktop_image), mobile_slider (mobile_image)
// B) Single repeater: desktop_slider having both desktop_image and mobile_image subfields per row.

$desktop_slides = eshop_get_slider_images('desktop_slider', 'desktop_image');
$mobile_slides_via_own_repeater = eshop_get_slider_images('mobile_slider', 'mobile_image');

// If mobile repeater isn't present, try to map from desktop repeater's mobile_image subfield
$mobile_slides = $mobile_slides_via_own_repeater;
if (empty($mobile_slides)) {
    // Re-scan desktop_slider for mobile_image subfield
    $tmp = array();
    if (have_rows('desktop_slider')) {
        while (have_rows('desktop_slider')) { the_row();
            $mimg = get_sub_field('mobile_image');
            if ($mimg && !empty($mimg['url'])) {
                $tmp[] = array(
                    'url' => esc_url($mimg['url']),
                    'alt' => esc_attr($mimg['alt'] ?? ''),
                );
            }
        }
        if (function_exists('reset_rows')) {
            reset_rows();
        }
    }
    $mobile_slides = $tmp;
}

// Fallback: if mobile empty, use desktop for both
if (empty($mobile_slides)) {
    $mobile_slides = $desktop_slides;
}

if (empty($desktop_slides) && empty($mobile_slides)) {
    return; // Nothing to render
}
?>

<section class="hero-slider-wrapper relative">
    <!-- Desktop Slider -->
    <?php if (!empty($desktop_slides)) : ?>
    <div class="hero-slider-desktop swiper hidden md:block">
        <div class="swiper-wrapper">
            <?php foreach ($desktop_slides as $slide) : ?>
                <div class="swiper-slide">
                    <div class="hero-slide relative overflow-hidden">
                        <img class="w-full h-auto block" src="<?php echo $slide['url']; ?>" alt="<?php echo $slide['alt']; ?>" />
                        <!-- Optional overlay/content container -->
                        <div class="slide-overlay absolute inset-0 flex items-center justify-center pointer-events-none">
                            <div class="slide-caption text-white text-center opacity-0 translate-y-4">
                                <!-- Add optional headings/buttons via ACF later -->
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="swiper-pagination"></div>
        <div class="swiper-button-prev"></div>
        <div class="swiper-button-next"></div>
    </div>
    <?php endif; ?>

    <!-- Mobile Slider -->
    <?php if (!empty($mobile_slides)) : ?>
    <div class="hero-slider-mobile swiper md:hidden">
        <div class="swiper-wrapper">
            <?php foreach ($mobile_slides as $slide) : ?>
                <div class="swiper-slide">
                    <div class="hero-slide relative overflow-hidden">
                        <img class="w-full h-auto block" src="<?php echo $slide['url']; ?>" alt="<?php echo $slide['alt']; ?>" />
                        <div class="slide-overlay absolute inset-0 flex items-center justify-center pointer-events-none">
                            <div class="slide-caption text-white text-center opacity-0 translate-y-4"></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="swiper-pagination"></div>
    </div>
    <?php endif; ?>
</section>
