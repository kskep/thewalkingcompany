/**
 * Product Gallery Component - 2025 Standards
 * 
 * Handles Swiper gallery initialization, lightbox functionality,
 * and accessibility features for the single product page
 *
 * @package thewalkingtheme
 */

class EshopProductGallery {
    constructor(container) {
        this.container = container;
        this.productId = container.dataset.productId;
        this.mainSlider = null;
        this.thumbsSlider = null;
        this.lightbox = null;
        this.currentSlideIndex = 0;
        this.images = [];
        
        this.init();
    }

    /**
     * Initialize the gallery component
     */
    init() {
        // Check if Swiper is available
        if (typeof Swiper === 'undefined') {
            console.error('Swiper library is required for product gallery');
            return;
        }

        console.log('[EshopProductGallery] init start', {
            productId: this.productId,
            container: this.container,
            imageCount: this.container.querySelectorAll('.swiper-slide img').length
        });

        this.setupImages();
        this.initMainSlider();
        this.initThumbsSlider();
        this.initLightbox();
        this.bindEvents();
        this.handleKeyboardNavigation();
    }

    /**
     * Setup image data for gallery
     */
    setupImages() {
        const slides = this.container.querySelectorAll('.swiper-slide img');
        this.images = Array.from(slides).map((img, index) => ({
            src: img.src,
            largeSrc: img.dataset.large || img.src,
            alt: img.alt,
            title: img.title,
            index: index
        }));
    }

    /**
     * Initialize main product slider
     */
    initMainSlider() {
        const mainSliderEl = this.container.querySelector('#productMainSlider');
        if (!mainSliderEl) return;

        const nextBtn = this.container.querySelector('.product-main-slider .swiper-button-next');
        const prevBtn = this.container.querySelector('.product-main-slider .swiper-button-prev');

        console.log('[EshopProductGallery] initMainSlider', {
            productId: this.productId,
            mainSliderEl,
            nextBtn,
            prevBtn,
            imageCount: this.images.length
        });

        this.mainSlider = new Swiper(mainSliderEl, {
            loop: this.images.length > 1,
            spaceBetween: 0,
            slidesPerView: 1,
            grabCursor: true,
            keyboard: {
                enabled: true,
            },
            navigation: {
                nextEl: nextBtn || undefined,
                prevEl: prevBtn || undefined,
            },
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            on: {
                slideChange: (swiper) => {
                    console.log('[EshopProductGallery] slideChange', {
                        productId: this.productId,
                        activeIndex: swiper.activeIndex,
                        realIndex: swiper.realIndex,
                        wrapperWidth: swiper.wrapperEl ? swiper.wrapperEl.style.width : null,
                        slideWidths: swiper.slides ? Array.from(swiper.slides).map(slide => slide.style.width) : []
                    });
                    this.currentSlideIndex = swiper.realIndex || swiper.activeIndex;
                    this.updateProgress();
                    this.updateThumbnailsActive();
                },
                init: (swiper) => {
                    console.log('[EshopProductGallery] main slider initialized', {
                        productId: this.productId,
                        initialIndex: this.currentSlideIndex,
                        loop: this.images.length > 1,
                        wrapperWidth: swiper.wrapperEl ? swiper.wrapperEl.style.width : null
                    });
                    this.updateProgress();
                }
            }
        });

        console.log('[EshopProductGallery] main Swiper instance', {
            productId: this.productId,
            instanceExists: Boolean(this.mainSlider),
            slideCount: this.mainSlider ? this.mainSlider.slides.length : null
        });
    }

    /**
     * Initialize thumbnails slider
     */
    initThumbsSlider() {
        const thumbsSliderEl = this.container.querySelector('#productThumbsSlider');
        if (!thumbsSliderEl || this.images.length <= 1) return;

        console.log('[EshopProductGallery] initThumbsSlider', {
            productId: this.productId,
            thumbsSliderEl,
            imageCount: this.images.length
        });

        this.thumbsSlider = new Swiper(thumbsSliderEl, {
            spaceBetween: 12,
            slidesPerView: 'auto',
            watchSlidesProgress: true,
            grabCursor: true,
            breakpoints: {
                320: {
                    spaceBetween: 8,
                    slidesPerView: 4,
                },
                640: {
                    spaceBetween: 12,
                    slidesPerView: 5,
                },
                768: {
                    spaceBetween: 12,
                    slidesPerView: 6,
                },
                1024: {
                    spaceBetween: 16,
                    slidesPerView: 'auto',
                }
            }
        });

        // Connect thumbnails to main slider
        // Ensure initial thumbnail state matches main slider
        this.updateThumbnailsActive();
    }

    /**
     * Initialize lightbox functionality
     */
    initLightbox() {
        this.lightbox = this.container.querySelector('#galleryLightbox');
        if (!this.lightbox) return;

        const lightboxImage = this.lightbox.querySelector('.lightbox-image');
        const closeBtn = this.lightbox.querySelector('.lightbox-close');
        const backdrop = this.lightbox.querySelector('.lightbox-backdrop');
        const prevBtn = this.lightbox.querySelector('.lightbox-prev');
        const nextBtn = this.lightbox.querySelector('.lightbox-next');

        // Close lightbox events
        if (closeBtn) {
            closeBtn.addEventListener('click', () => this.closeLightbox());
        }
        if (backdrop) {
            backdrop.addEventListener('click', () => this.closeLightbox());
        }

        // Navigation events
        if (prevBtn) {
            prevBtn.addEventListener('click', () => this.lightboxPrev());
        }
        if (nextBtn) {
            nextBtn.addEventListener('click', () => this.lightboxNext());
        }

        // Keyboard events for lightbox
        document.addEventListener('keydown', (e) => {
            if (!this.lightbox.classList.contains('active')) return;

            switch (e.key) {
                case 'Escape':
                    this.closeLightbox();
                    break;
                case 'ArrowLeft':
                    this.lightboxPrev();
                    break;
                case 'ArrowRight':
                    this.lightboxNext();
                    break;
            }
        });
    }

    /**
     * Bind event listeners
     */
    bindEvents() {
        // Zoom trigger buttons
        const zoomTriggers = this.container.querySelectorAll('.zoom-trigger');
        zoomTriggers.forEach(trigger => {
            trigger.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const imageSrc = trigger.dataset.imageSrc;
                this.openLightbox(imageSrc);
            });
        });

        // Gallery image wrappers (click to zoom)
        const imageWrappers = this.container.querySelectorAll('.gallery-image-wrapper');
        imageWrappers.forEach((wrapper, index) => {
            wrapper.addEventListener('click', (e) => {
                const img = wrapper.querySelector('.gallery-main-image');
                if (img) {
                    this.openLightbox(img.dataset.large || img.src, index);
                }
            });
        });

        // Thumbnail navigation
        const thumbnailButtons = this.container.querySelectorAll('.thumbnail-button');
        thumbnailButtons.forEach((button, index) => {
            button.addEventListener('click', () => {
                if (this.mainSlider) {
                    this.mainSlider.slideTo(index);
                }
            });
        });

        // Touch events for mobile
        this.setupTouchEvents();
    }

    /**
     * Setup touch events for mobile interaction
     */
    setupTouchEvents() {
        if (!this.isMobile()) return;

        let touchStartX = 0;
        let touchStartY = 0;

        const mainSliderEl = this.container.querySelector('.product-main-slider');
        if (!mainSliderEl) return;

        mainSliderEl.addEventListener('touchstart', (e) => {
            touchStartX = e.touches[0].clientX;
            touchStartY = e.touches[0].clientY;
        });

        mainSliderEl.addEventListener('touchend', (e) => {
            const touchEndX = e.changedTouches[0].clientX;
            const touchEndY = e.changedTouches[0].clientY;
            
            const deltaX = touchEndX - touchStartX;
            const deltaY = touchEndY - touchStartY;

            // Vertical scroll should not trigger gallery navigation
            if (Math.abs(deltaY) > Math.abs(deltaX)) return;

            // Minimum swipe distance
            if (Math.abs(deltaX) < 50) return;

            if (deltaX > 0) {
                // Swipe right - previous slide
                this.mainSlider && this.mainSlider.slidePrev();
            } else {
                // Swipe left - next slide
                this.mainSlider && this.mainSlider.slideNext();
            }
        });
    }

    /**
     * Handle keyboard navigation
     */
    handleKeyboardNavigation() {
        document.addEventListener('keydown', (e) => {
            // Only handle when gallery is focused
            if (!this.container.contains(document.activeElement)) return;
            
            // Only handle when lightbox is not active
            if (this.lightbox && this.lightbox.classList.contains('active')) return;

            switch (e.key) {
                case 'ArrowLeft':
                    e.preventDefault();
                    this.mainSlider && this.mainSlider.slidePrev();
                    break;
                case 'ArrowRight':
                    e.preventDefault();
                    this.mainSlider && this.mainSlider.slideNext();
                    break;
                case 'Enter':
                case ' ':
                    if (document.activeElement.classList.contains('zoom-trigger') || 
                        document.activeElement.classList.contains('gallery-image-wrapper')) {
                        e.preventDefault();
                        const img = this.container.querySelector('.swiper-slide-active .gallery-main-image');
                        if (img) {
                            this.openLightbox(img.dataset.large || img.src);
                        }
                    }
                    break;
            }
        });
    }

    /**
     * Update progress indicator
     */
    updateProgress() {
        const currentEl = this.container.querySelector('.current-slide');
        const totalEl = this.container.querySelector('.total-slides');
        
        if (currentEl && totalEl) {
            currentEl.textContent = (this.currentSlideIndex + 1);
            totalEl.textContent = this.images.length;
        }
    }

    /**
     * Update active thumbnail
     */
    updateThumbnailsActive() {
        const thumbnails = this.container.querySelectorAll('.thumbnail-button');
        thumbnails.forEach((thumbnail, index) => {
            thumbnail.classList.toggle('active', index === this.currentSlideIndex);
        });

        if (this.thumbsSlider) {
            console.log('[EshopProductGallery] sync thumbs', {
                productId: this.productId,
                currentSlideIndex: this.currentSlideIndex,
                thumbsActiveIndex: this.thumbsSlider.activeIndex,
                thumbsSlides: this.thumbsSlider.slides ? Array.from(this.thumbsSlider.slides).map(slide => slide.style.width) : []
            });
            this.thumbsSlider.slideTo(this.currentSlideIndex);
        }
    }

    /**
     * Open lightbox with image
     */
    openLightbox(imageSrc, slideIndex = null) {
        if (!this.lightbox) return;

        // Set slide index if provided
        if (slideIndex !== null) {
            this.currentSlideIndex = slideIndex;
        }

        const lightboxImage = this.lightbox.querySelector('.lightbox-image');
        if (lightboxImage) {
            lightboxImage.src = imageSrc;
            lightboxImage.alt = this.images[this.currentSlideIndex]?.alt || '';
        }

        // Show lightbox
        this.lightbox.classList.add('active');
        this.lightbox.setAttribute('aria-hidden', 'false');
        
        // Focus close button for accessibility
        const closeBtn = this.lightbox.querySelector('.lightbox-close');
        if (closeBtn) {
            closeBtn.focus();
        }

        // Prevent body scroll
        document.body.style.overflow = 'hidden';

        // Fire custom event
        this.fireEvent('lightboxOpen', {
            imageSrc: imageSrc,
            slideIndex: this.currentSlideIndex
        });
    }

    /**
     * Close lightbox
     */
    closeLightbox() {
        if (!this.lightbox) return;

        this.lightbox.classList.remove('active');
        this.lightbox.setAttribute('aria-hidden', 'true');
        
        // Restore body scroll
        document.body.style.overflow = '';

        // Return focus to gallery
        const activeSlide = this.container.querySelector('.swiper-slide-active .zoom-trigger');
        if (activeSlide) {
            activeSlide.focus();
        }

        // Fire custom event
        this.fireEvent('lightboxClose');
    }

    /**
     * Navigate to previous image in lightbox
     */
    lightboxPrev() {
        if (this.images.length <= 1) return;

        this.currentSlideIndex = this.currentSlideIndex <= 0 
            ? this.images.length - 1 
            : this.currentSlideIndex - 1;

        this.updateLightboxImage();
    }

    /**
     * Navigate to next image in lightbox
     */
    lightboxNext() {
        if (this.images.length <= 1) return;

        this.currentSlideIndex = this.currentSlideIndex >= this.images.length - 1 
            ? 0 
            : this.currentSlideIndex + 1;

        this.updateLightboxImage();
    }

    /**
     * Update lightbox image
     */
    updateLightboxImage() {
        const lightboxImage = this.lightbox.querySelector('.lightbox-image');
        const currentImage = this.images[this.currentSlideIndex];

        if (lightboxImage && currentImage) {
            lightboxImage.src = currentImage.largeSrc;
            lightboxImage.alt = currentImage.alt;
        }

        // Sync main slider
        if (this.mainSlider) {
            this.mainSlider.slideTo(this.currentSlideIndex);
        }
    }

    /**
     * Check if device is mobile
     */
    isMobile() {
        return window.innerWidth <= 768;
    }

    /**
     * Fire custom event
     */
    fireEvent(eventName, detail = {}) {
        const event = new CustomEvent(`eshop:gallery:${eventName}`, {
            detail: {
                gallery: this,
                productId: this.productId,
                ...detail
            },
            bubbles: true
        });
        this.container.dispatchEvent(event);
    }

    /**
     * Destroy gallery instance
     */
    destroy() {
        if (this.mainSlider) {
            this.mainSlider.destroy(true, true);
        }
        if (this.thumbsSlider) {
            this.thumbsSlider.destroy(true, true);
        }

        // Remove event listeners
        // Note: Modern browsers automatically clean up event listeners when elements are removed
        
        this.fireEvent('destroyed');
    }

    /**
     * Refresh gallery (useful after dynamic content changes)
     */
    refresh() {
        this.setupImages();
        
        if (this.mainSlider) {
            this.mainSlider.update();
        }
        if (this.thumbsSlider) {
            this.thumbsSlider.update();
        }

        this.updateProgress();
        this.fireEvent('refreshed');
    }

    /**
     * Go to specific slide
     */
    goToSlide(index) {
        if (index < 0 || index >= this.images.length) return;

        if (this.mainSlider) {
            this.mainSlider.slideTo(index);
        }
    }

    /**
     * Get current slide index
     */
    getCurrentSlide() {
        return this.currentSlideIndex;
    }

    /**
     * Get total number of slides
     */
    getTotalSlides() {
        return this.images.length;
    }
}

function initProductGalleries() {
    const galleryContainers = document.querySelectorAll('.product-gallery-container');

    galleryContainers.forEach(container => {
        if (container.dataset.galleryInitialized === 'true') {
            return;
        }

        container.eshopGallery = new EshopProductGallery(container);
        container.dataset.galleryInitialized = 'true';
    });
}

function scheduleProductGalleryInit(delay = 300) {
    console.log('[EshopProductGallery] schedule init', { delay, readyState: document.readyState });
    window.setTimeout(() => {
        console.log('[EshopProductGallery] running initProductGalleries');
        initProductGalleries();
    }, delay);
}

if (document.readyState === 'complete') {
    scheduleProductGalleryInit();
} else {
    window.addEventListener('load', () => scheduleProductGalleryInit());
}

// Expose manual initializer for dynamic content (e.g., AJAX refreshes)
window.initializeEshopProductGalleries = scheduleProductGalleryInit;

/**
 * Export for module systems
 */
if (typeof module !== 'undefined' && module.exports) {
    module.exports = EshopProductGallery;
}

// Make available globally
window.EshopProductGallery = EshopProductGallery;