/**
 * Product Gallery - Magazine Style Enhancement
 * Enhances the default WooCommerce gallery with magazine styling and custom navigation
 *
 * @package E-Shop Theme
 */

(function($) {
    'use strict';

    function initMagazineGalleryEnhancements() {
        // Find all gallery containers on the page
        const galleryContainers = document.querySelectorAll('.product-gallery');

        galleryContainers.forEach(function(galleryContainer) {
            // Get Swiper instance if available
            const swiperContainer = galleryContainer.querySelector('.product-gallery__main-image-wrapper');
            let mainSwiper = null;

            if (swiperContainer && swiperContainer.swiper) {
                mainSwiper = swiperContainer.swiper;
            }

            const totalImages = galleryContainer.querySelectorAll('.product-gallery__main-image').length;
            const currentCounter = galleryContainer.querySelector('.product-gallery__counter-current');

            if (!totalImages) return;

            // Function to update counter
            function updateCounter(activeIndex) {
                if (currentCounter) {
                    currentCounter.textContent = activeIndex + 1;
                }
            }

            // Custom magazine navigation arrows
            const nextBtn = galleryContainer.querySelector('.product-gallery__nav--next');
            const prevBtn = galleryContainer.querySelector('.product-gallery__nav--prev');

            if (mainSwiper) {
                // If Swiper is initialized, hook into its events
                mainSwiper.on('slideChange', function() {
                    updateCounter(mainSwiper.activeIndex);
                });

                // Hook custom arrows to Swiper
                if (nextBtn) {
                    nextBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        mainSwiper.slideNext();
                    });
                }

                if (prevBtn) {
                    prevBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        mainSwiper.slidePrev();
                    });
                }

                // Initialize counter
                updateCounter(mainSwiper.activeIndex);

            } else {
                // Fallback: Use manual navigation if Swiper is not available
                let currentImageIndex = 0;
                const mainImages = galleryContainer.querySelectorAll('.product-gallery__main-image');

                function updateGallery(index) {
                    if (index < 0 || index >= totalImages) return;

                    currentImageIndex = index;

                    // Update main images
                    mainImages.forEach((img, i) => {
                        img.classList.toggle('swiper-slide-active', i === index);
                        img.setAttribute('aria-hidden', i === index ? 'false' : 'true');
                    });

                    // Update counter
                    updateCounter(index);
                }

                if (nextBtn) {
                    nextBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        const nextIndex = (currentImageIndex + 1) % totalImages;
                        updateGallery(nextIndex);
                    });
                }

                if (prevBtn) {
                    prevBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        const prevIndex = (currentImageIndex - 1 + totalImages) % totalImages;
                        updateGallery(prevIndex);
                    });
                }

                // Initialize first image
                updateGallery(0);
            }

            // Ensure lightbox trigger is visible and styled
            const lightboxTrigger = galleryContainer.querySelector('.woocommerce-product-gallery__trigger');
            if (lightboxTrigger) {
                lightboxTrigger.innerHTML = '<i class="fas fa-expand"></i>';
                lightboxTrigger.setAttribute('title', 'View image gallery');
            }

            // Ensure click on main image opens lightbox
            const mainImages = galleryContainer.querySelectorAll('.product-gallery__main-image img');
            mainImages.forEach(function(img) {
                img.style.cursor = 'zoom-in';
                img.addEventListener('click', function() {
                    if (lightboxTrigger) {
                        lightboxTrigger.click();
                    }
                });
            });
        });
    }

    // Initialize after Swiper has been initialized
    $(document).ready(function() {
        // Wait for Swiper to initialize
        setTimeout(initMagazineGalleryEnhancements, 500);
    });

    // Re-initialize when variations change
    $(document).on('found_variation', '.variations_form', function() {
        setTimeout(initMagazineGalleryEnhancements, 600);
    });

    $(document).on('reset_image', '.variations_form', function() {
        setTimeout(initMagazineGalleryEnhancements, 600);
    });

    // Handle AJAX loaded content
    $(document).ajaxComplete(function() {
        setTimeout(initMagazineGalleryEnhancements, 500);
    });

})(jQuery);