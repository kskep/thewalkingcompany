/**
 * Product Gallery Component JavaScript
 * 
 * Handles product gallery interactions, Swiper integration, and lightbox functionality
 * Following 2025 UX/UI standards with accessibility features
 *
 * @package thewalkingtheme
 */

(function() {
    'use strict';

    class ProductGallery {
        constructor(container) {
            this.container = container;
            this.mainSlider = null;
            this.thumbsSlider = null;
            this.currentIndex = 0;
            this.images = [];
            this.lightbox = null;
            this.isLightboxOpen = false;

            this.init();
        }

        init() {
            this.cacheElements();
            this.bindEvents();
            this.initializeSliders();
            this.setupKeyboardNavigation();
            this.setupAccessibility();
        }

        cacheElements() {
            this.mainSliderEl = this.container.querySelector('.product-main-slider');
            this.thumbsSliderEl = this.container.querySelector('.product-thumbs-slider');
            this.thumbnailButtons = this.container.querySelectorAll('.thumbnail-button');
            this.zoomTriggers = this.container.querySelectorAll('.zoom-trigger');
            this.lightbox = this.container.querySelector('.gallery-lightbox');
            this.lightboxImage = this.lightbox?.querySelector('.lightbox-image');
            this.progressCurrent = this.container.querySelector('.current-slide');
            this.progressTotal = this.container.querySelector('.total-slides');

            // Cache image data
            this.images = Array.from(this.container.querySelectorAll('.gallery-main-image')).map(img => ({
                src: img.src,
                large: img.dataset.large || img.src,
                alt: img.alt,
                title: img.title
            }));
        }

        bindEvents() {
            // Thumbnail clicks
            this.thumbnailButtons.forEach((button, index) => {
                button.addEventListener('click', () => this.goToSlide(index));
            });

            // Zoom triggers
            this.zoomTriggers.forEach((trigger, index) => {
                trigger.addEventListener('click', () => this.openLightbox(index));
            });

            // Lightbox events
            if (this.lightbox) {
                const backdrop = this.lightbox.querySelector('.lightbox-backdrop');
                const closeBtn = this.lightbox.querySelector('.lightbox-close');
                const prevBtn = this.lightbox.querySelector('.lightbox-prev');
                const nextBtn = this.lightbox.querySelector('.lightbox-next');

                backdrop?.addEventListener('click', () => this.closeLightbox());
                closeBtn?.addEventListener('click', () => this.closeLightbox());
                prevBtn?.addEventListener('click', () => this.lightboxPrev());
                nextBtn?.addEventListener('click', () => this.lightboxNext());
            }

            // Image double-click to zoom
            this.container.addEventListener('dblclick', (e) => {
                if (e.target.classList.contains('gallery-main-image')) {
                    this.openLightbox(this.currentIndex);
                }
            });
        }

        initializeSliders() {
            // Initialize thumbnails slider first
            if (this.thumbsSliderEl && typeof Swiper !== 'undefined') {
                this.thumbsSlider = new Swiper(this.thumbsSliderEl, {
                    spaceBetween: 12,
                    slidesPerView: 'auto',
                    freeMode: true,
                    watchSlidesProgress: true,
                    breakpoints: {
                        320: {
                            spaceBetween: 8,
                        },
                        768: {
                            spaceBetween: 12,
                        },
                        1024: {
                            spaceBetween: 12,
                            direction: window.innerWidth >= 1024 && this.container.classList.contains('gallery-desktop-layout') ? 'vertical' : 'horizontal',
                        }
                    }
                });
            }

            // Initialize main slider
            if (this.mainSliderEl && typeof Swiper !== 'undefined') {
                this.mainSlider = new Swiper(this.mainSliderEl, {
                    spaceBetween: 0,
                    effect: 'fade',
                    fadeEffect: {
                        crossFade: true
                    },
                    navigation: {
                        nextEl: '.swiper-button-next',
                        prevEl: '.swiper-button-prev',
                    },
                    thumbs: this.thumbsSlider ? {
                        swiper: this.thumbsSlider,
                        slideThumbActiveClass: 'active',
                        thumbsContainerClass: 'product-thumbs-slider'
                    } : null,
                    keyboard: {
                        enabled: true,
                        onlyInViewport: true,
                    },
                    a11y: {
                        enabled: true,
                        prevSlideMessage: 'Previous image',
                        nextSlideMessage: 'Next image',
                    },
                    on: {
                        slideChange: (swiper) => {
                            this.currentIndex = swiper.activeIndex;
                            this.updateProgress();
                            this.updateThumbnailsActive();
                            this.announceSlideChange();
                        }
                    }
                });

                // Initial setup
                this.updateProgress();
                this.updateThumbnailsActive();
            } else {
                // Fallback for when Swiper is not available
                this.setupFallbackNavigation();
            }
        }

        setupFallbackNavigation() {
            const images = this.container.querySelectorAll('.gallery-main-image');
            const navNext = this.container.querySelector('.swiper-button-next');
            const navPrev = this.container.querySelector('.swiper-button-prev');

            if (images.length <= 1) return;

            // Hide all images except first
            images.forEach((img, index) => {
                img.style.display = index === 0 ? 'block' : 'none';
            });

            navNext?.addEventListener('click', () => {
                if (this.currentIndex < images.length - 1) {
                    this.goToSlide(this.currentIndex + 1);
                }
            });

            navPrev?.addEventListener('click', () => {
                if (this.currentIndex > 0) {
                    this.goToSlide(this.currentIndex - 1);
                }
            });
        }

        goToSlide(index) {
            if (index < 0 || index >= this.images.length) return;

            this.currentIndex = index;

            if (this.mainSlider) {
                this.mainSlider.slideTo(index);
            } else {
                // Fallback
                const images = this.container.querySelectorAll('.gallery-main-image');
                images.forEach((img, i) => {
                    img.style.display = i === index ? 'block' : 'none';
                });
                this.updateProgress();
                this.updateThumbnailsActive();
            }
        }

        updateProgress() {
            if (this.progressCurrent) {
                this.progressCurrent.textContent = this.currentIndex + 1;
            }
            if (this.progressTotal) {
                this.progressTotal.textContent = this.images.length;
            }
        }

        updateThumbnailsActive() {
            this.thumbnailButtons.forEach((button, index) => {
                button.classList.toggle('active', index === this.currentIndex);
            });
        }

        openLightbox(index = this.currentIndex) {
            if (!this.lightbox || !this.images[index]) return;

            this.isLightboxOpen = true;
            this.currentIndex = index;

            // Set image
            if (this.lightboxImage) {
                this.lightboxImage.src = this.images[index].large;
                this.lightboxImage.alt = this.images[index].alt;
            }

            // Show lightbox
            this.lightbox.classList.add('active');
            this.lightbox.setAttribute('aria-hidden', 'false');

            // Trap focus
            this.trapFocus(this.lightbox);

            // Prevent body scroll
            document.body.style.overflow = 'hidden';

            // Announce to screen readers
            this.announceLightboxOpen();
        }

        closeLightbox() {
            if (!this.lightbox || !this.isLightboxOpen) return;

            this.isLightboxOpen = false;
            this.lightbox.classList.remove('active');
            this.lightbox.setAttribute('aria-hidden', 'true');

            // Restore body scroll
            document.body.style.overflow = '';

            // Return focus to trigger
            const trigger = this.zoomTriggers[this.currentIndex];
            if (trigger) {
                trigger.focus();
            }

            // Announce to screen readers
            this.announceLightboxClose();
        }

        lightboxPrev() {
            if (this.currentIndex > 0) {
                this.currentIndex--;
                this.updateLightboxImage();
            }
        }

        lightboxNext() {
            if (this.currentIndex < this.images.length - 1) {
                this.currentIndex++;
                this.updateLightboxImage();
            }
        }

        updateLightboxImage() {
            if (!this.lightboxImage || !this.images[this.currentIndex]) return;

            const image = this.images[this.currentIndex];
            this.lightboxImage.src = image.large;
            this.lightboxImage.alt = image.alt;

            // Update main slider if needed
            if (this.mainSlider) {
                this.mainSlider.slideTo(this.currentIndex);
            }

            // Announce change
            this.announceLightboxChange();
        }

        setupKeyboardNavigation() {
            document.addEventListener('keydown', (e) => {
                if (!this.isLightboxOpen) return;

                switch (e.key) {
                    case 'Escape':
                        e.preventDefault();
                        this.closeLightbox();
                        break;
                    case 'ArrowLeft':
                        e.preventDefault();
                        this.lightboxPrev();
                        break;
                    case 'ArrowRight':
                        e.preventDefault();
                        this.lightboxNext();
                        break;
                }
            });
        }

        setupAccessibility() {
            // Add ARIA labels to navigation buttons
            const prevBtn = this.container.querySelector('.swiper-button-prev');
            const nextBtn = this.container.querySelector('.swiper-button-next');

            if (prevBtn) {
                prevBtn.setAttribute('aria-label', 'Previous image');
                prevBtn.setAttribute('role', 'button');
            }

            if (nextBtn) {
                nextBtn.setAttribute('aria-label', 'Next image');
                nextBtn.setAttribute('role', 'button');
            }

            // Add live region for announcements
            if (!document.getElementById('gallery-announcer')) {
                const announcer = document.createElement('div');
                announcer.id = 'gallery-announcer';
                announcer.setAttribute('aria-live', 'polite');
                announcer.setAttribute('aria-atomic', 'true');
                announcer.style.position = 'absolute';
                announcer.style.left = '-10000px';
                announcer.style.width = '1px';
                announcer.style.height = '1px';
                announcer.style.overflow = 'hidden';
                document.body.appendChild(announcer);
            }
        }

        announceSlideChange() {
            const announcer = document.getElementById('gallery-announcer');
            if (announcer) {
                const current = this.currentIndex + 1;
                const total = this.images.length;
                const alt = this.images[this.currentIndex]?.alt || 'Product image';
                announcer.textContent = `${alt}, image ${current} of ${total}`;
            }
        }

        announceLightboxOpen() {
            const announcer = document.getElementById('gallery-announcer');
            if (announcer) {
                announcer.textContent = 'Image lightbox opened. Use arrow keys to navigate, Escape to close.';
            }
        }

        announceLightboxClose() {
            const announcer = document.getElementById('gallery-announcer');
            if (announcer) {
                announcer.textContent = 'Image lightbox closed.';
            }
        }

        announceLightboxChange() {
            const announcer = document.getElementById('gallery-announcer');
            if (announcer) {
                const current = this.currentIndex + 1;
                const total = this.images.length;
                const alt = this.images[this.currentIndex]?.alt || 'Product image';
                announcer.textContent = `${alt}, image ${current} of ${total}`;
            }
        }

        trapFocus(element) {
            const focusableElements = element.querySelectorAll(
                'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
            );

            const firstElement = focusableElements[0];
            const lastElement = focusableElements[focusableElements.length - 1];

            element.addEventListener('keydown', (e) => {
                if (e.key === 'Tab') {
                    if (e.shiftKey && document.activeElement === firstElement) {
                        e.preventDefault();
                        lastElement.focus();
                    } else if (!e.shiftKey && document.activeElement === lastElement) {
                        e.preventDefault();
                        firstElement.focus();
                    }
                }
            });

            // Focus first element
            if (firstElement) {
                firstElement.focus();
            }
        }

        // Public method to update gallery (e.g., when variant changes)
        updateGallery(newImages) {
            if (!Array.isArray(newImages)) return;

            this.images = newImages;
            this.currentIndex = 0;

            // Update sliders if they exist
            if (this.mainSlider) {
                this.mainSlider.destroy(true, true);
            }
            if (this.thumbsSlider) {
                this.thumbsSlider.destroy(true, true);
            }

            // Re-initialize
            setTimeout(() => {
                this.cacheElements();
                this.initializeSliders();
            }, 100);
        }

        // Cleanup method
        destroy() {
            if (this.mainSlider) {
                this.mainSlider.destroy(true, true);
            }
            if (this.thumbsSlider) {
                this.thumbsSlider.destroy(true, true);
            }

            // Remove event listeners
            this.thumbnailButtons.forEach(button => {
                button.removeEventListener('click', this.goToSlide);
            });

            document.body.style.overflow = '';
        }
    }

    // Auto-initialize galleries when DOM is ready
    function initProductGalleries() {
        const galleries = document.querySelectorAll('.product-gallery-container');
        
        galleries.forEach(gallery => {
            if (!gallery.productGalleryInstance) {
                gallery.productGalleryInstance = new ProductGallery(gallery);
            }
        });
    }

    // Initialize on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initProductGalleries);
    } else {
        initProductGalleries();
    }

    // Re-initialize on AJAX product updates (for variant switching)
    document.addEventListener('wc_variation_form_changed', initProductGalleries);
    
    // Make class available globally for manual initialization
    window.ProductGallery = ProductGallery;

})();