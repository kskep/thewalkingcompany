/**
 * Product Gallery - Magazine Style
 * Simple gallery functionality based on single-product-page_mydemo.html
 *
 * @package E-Shop Theme
 */

(function($) {
    'use strict';

    function initMagazineGallery() {
        // Gallery state
        let currentImageIndex = 0;
        const thumbnails = document.querySelectorAll('.product-gallery__thumbnail');
        const mainImages = document.querySelectorAll('.product-gallery__main-image');
        const totalImages = mainImages.length;
        const currentCounter = document.querySelector('.product-gallery__counter-current');

        if (!mainImages.length) return;

        function updateGallery(index) {
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
        }

        // Thumbnail click handlers
        thumbnails.forEach(thumbnail => {
            thumbnail.addEventListener('click', function() {
                const index = parseInt(this.dataset.index, 10);
                updateGallery(index);
            });
        });

        // Gallery arrow functionality
        const nextBtn = document.querySelector('.product-gallery__nav--next');
        const prevBtn = document.querySelector('.product-gallery__nav--prev');

        if (nextBtn && prevBtn) {
            nextBtn.addEventListener('click', () => {
                const nextIndex = (currentImageIndex + 1) % totalImages;
                updateGallery(nextIndex);
            });

            prevBtn.addEventListener('click', () => {
                const prevIndex = (currentImageIndex - 1 + totalImages) % totalImages;
                updateGallery(prevIndex);
            });
        }

        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') {
                const prevIndex = (currentImageIndex - 1 + totalImages) % totalImages;
                updateGallery(prevIndex);
            } else if (e.key === 'ArrowRight') {
                const nextIndex = (currentImageIndex + 1) % totalImages;
                updateGallery(nextIndex);
            }
        });
    }

    // Initialize on DOM ready
    $(document).ready(function() {
        initMagazineGallery();
    });

    // Re-initialize when variations change
    $(document).on('found_variation', '.variations_form', function() {
        setTimeout(initMagazineGallery, 100);
    });

    $(document).on('reset_image', '.variations_form', function() {
        setTimeout(initMagazineGallery, 100);
    });

})(jQuery);