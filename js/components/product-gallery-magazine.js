/**
 * Product Gallery - Magazine Style
 * Simple gallery functionality based on single-product-page_mydemo.html
 *
 * @package E-Shop Theme
 */

(function($) {
    'use strict';

    function initMagazineGallery() {
        // Find all gallery containers on the page
        const galleryContainers = document.querySelectorAll('.product-gallery');

        galleryContainers.forEach(function(galleryContainer) {
            // Gallery state for each container
            let currentImageIndex = 0;
            const thumbnails = galleryContainer.querySelectorAll('.product-gallery__thumbnail');
            const mainImages = galleryContainer.querySelectorAll('.product-gallery__main-image');
            const totalImages = mainImages.length;
            const currentCounter = galleryContainer.querySelector('.product-gallery__counter-current');

            if (!mainImages.length) return;

            function updateGallery(index) {
                if (index < 0 || index >= totalImages) return;

                currentImageIndex = index;

                // Update thumbnails
                thumbnails.forEach((t, i) => {
                    t.classList.toggle('is-active', i === index);
                    t.setAttribute('aria-selected', i === index ? 'true' : 'false');
                });

                // Update main images
                mainImages.forEach((img, i) => {
                    img.classList.toggle('is-active', i === index);
                    img.setAttribute('aria-hidden', i === index ? 'false' : 'true');
                });

                // Update counter
                if (currentCounter) {
                    currentCounter.textContent = index + 1;
                }

                // Trigger custom event for other scripts
                $(galleryContainer).trigger('gallery:change', [index]);
            }

            // Thumbnail click handlers
            thumbnails.forEach(thumbnail => {
                thumbnail.addEventListener('click', function() {
                    const index = parseInt(this.dataset.index, 10);
                    if (!isNaN(index)) {
                        updateGallery(index);
                    }
                });
            });

            // Gallery arrow functionality
            const nextBtn = galleryContainer.querySelector('.product-gallery__nav--next');
            const prevBtn = galleryContainer.querySelector('.product-gallery__nav--prev');

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

            // Touch/swipe support for mobile
            let touchStartX = 0;
            let touchEndX = 0;

            galleryContainer.addEventListener('touchstart', function(e) {
                touchStartX = e.changedTouches[0].screenX;
            }, { passive: true });

            galleryContainer.addEventListener('touchend', function(e) {
                touchEndX = e.changedTouches[0].screenX;
                handleSwipe();
            }, { passive: true });

            function handleSwipe() {
                const swipeThreshold = 50;
                const diff = touchStartX - touchEndX;

                if (Math.abs(diff) > swipeThreshold) {
                    if (diff > 0) {
                        // Swipe left - next image
                        const nextIndex = (currentImageIndex + 1) % totalImages;
                        updateGallery(nextIndex);
                    } else {
                        // Swipe right - previous image
                        const prevIndex = (currentImageIndex - 1 + totalImages) % totalImages;
                        updateGallery(prevIndex);
                    }
                }
            }

            // Keyboard navigation
            galleryContainer.addEventListener('keydown', function(e) {
                if (e.key === 'ArrowLeft') {
                    e.preventDefault();
                    const prevIndex = (currentImageIndex - 1 + totalImages) % totalImages;
                    updateGallery(prevIndex);
                } else if (e.key === 'ArrowRight') {
                    e.preventDefault();
                    const nextIndex = (currentImageIndex + 1) % totalImages;
                    updateGallery(nextIndex);
                }
            });

            // Auto-initialize first image
            updateGallery(0);
        });
    }

    // Initialize on DOM ready
    $(document).ready(function() {
        // Small delay to ensure all elements are loaded
        setTimeout(initMagazineGallery, 100);
    });

    // Re-initialize when variations change
    $(document).on('found_variation', '.variations_form', function() {
        setTimeout(initMagazineGallery, 200);
    });

    $(document).on('reset_image', '.variations_form', function() {
        setTimeout(initMagazineGallery, 200);
    });

    // Handle AJAX loaded content
    $(document).ajaxComplete(function() {
        setTimeout(initMagazineGallery, 100);
    });

})(jQuery);