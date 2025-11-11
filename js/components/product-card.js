/**
 * Product Card Interactive Features - Concept Design Implementation
 * 
 * Handles all interactive elements for product cards including:
 * - Media dot navigation
 * - Size overlay system
 * - Wishlist functionality  
 * - Color variant selection
 * - Enhanced interactions
 */

(function($) {
    'use strict';

    class ProductCard {
        constructor(card) {
            this.card = card;
            this.productId = card.dataset.productId;
            this.isVariableProduct = card.dataset.variable === 'true';
            this.wishlistEnabled = card.dataset.wishlist === 'true';
            
            this.init();
        }

        init() {
            this.setupMediaDots();
            this.setupSizeOverlay();
            this.setupWishlist();
            this.setupColorVariants();
            this.setupBadgeSystem();
            this.bindEvents();
        }

        /**
         * Setup media dot navigation based on product gallery
         */
        setupMediaDots() {
            const media = this.card.querySelector('.product-card__media');
            const images = media?.querySelectorAll('img');
            
            if (!images || images.length <= 1) return;

            let dotRow = media.querySelector('.media-dot-row');
            if (!dotRow) {
                dotRow = document.createElement('div');
                dotRow.className = 'media-dot-row';
                media.appendChild(dotRow);
            }

            // Clear existing dots
            dotRow.innerHTML = '';

            // Create dots for each image
            images.forEach((img, index) => {
                const dot = document.createElement('span');
                dot.className = `media-dot ${index === 0 ? 'is-active' : ''}`;
                dot.setAttribute('data-index', index);
                
                if (index === 0) {
                    dot.setAttribute('aria-label', 'Active image');
                } else {
                    dot.setAttribute('aria-label', `Image ${index + 1}`);
                    dot.setAttribute('role', 'button');
                    dot.setAttribute('tabindex', '0');
                    dot.setAttribute('aria-label', `View image ${index + 1}`);
                }

                dotRow.appendChild(dot);
            });

            // Add click handlers for dots
            dotRow.addEventListener('click', (e) => {
                if (e.target.classList.contains('media-dot')) {
                    this.switchImage(parseInt(e.target.dataset.index));
                }
            });

            // Add keyboard navigation for dots
            dotRow.addEventListener('keydown', (e) => {
                if (e.target.classList.contains('media-dot')) {
                    const currentIndex = parseInt(e.target.dataset.index);
                    let newIndex = currentIndex;

                    switch (e.key) {
                        case 'ArrowLeft':
                            e.preventDefault();
                            newIndex = currentIndex > 0 ? currentIndex - 1 : images.length - 1;
                            break;
                        case 'ArrowRight':
                            e.preventDefault();
                            newIndex = currentIndex < images.length - 1 ? currentIndex + 1 : 0;
                            break;
                    }

                    if (newIndex !== currentIndex) {
                        this.switchImage(newIndex);
                        const dots = dotRow.querySelectorAll('.media-dot');
                        dots[newIndex]?.focus();
                    }
                }
            });
        }

        /**
         * Switch between product images
         */
        switchImage(index) {
            const media = this.card.querySelector('.product-card__media');
            const images = media?.querySelectorAll('img');
            const dots = media?.querySelectorAll('.media-dot');

            if (!images || !dots || index >= images.length) return;

            // Update image visibility
            images.forEach((img, i) => {
                img.style.opacity = i === index ? '1' : '0.7';
            });

            // Update dot states
            dots.forEach((dot, i) => {
                dot.classList.toggle('is-active', i === index);
                dot.setAttribute('aria-label', i === index ? 'Active image' : `View image ${i + 1}`);
            });

            this.fireEvent('imageChanged', { index });
        }

        /**
         * Setup size overlay system
         */
        setupSizeOverlay() {
            if (!this.isVariableProduct) return;

            const media = this.card.querySelector('.product-card__media');
            if (!media) return;

            let sizeOverlay = media.querySelector('.size-overlay');
            // Respect server-rendered overlay if present; do not generate placeholder sizes
            // If no overlay exists, skip creating dummy content as this UI is informational only
            if (!sizeOverlay) {
                return;
            }

            // Setup touch interaction for mobile
            let touchStartY = 0;
            let touchStartTime = 0;

            media.addEventListener('touchstart', (e) => {
                touchStartY = e.touches[0].clientY;
                touchStartTime = Date.now();
            });

            media.addEventListener('touchend', (e) => {
                const touchEndY = e.changedTouches[0].clientY;
                const touchEndTime = Date.now();
                const touchDiff = touchStartY - touchEndY;
                const timeDiff = touchEndTime - touchStartTime;

                // Only trigger if it's a swipe up (not a tap) and quick enough
                if (touchDiff > 50 && timeDiff < 500) {
                    this.card.classList.toggle('show-sizes');
                }
            });
        }

        // No size selection functionality is needed for the informational overlay

        /**
         * Setup wishlist functionality - now uses existing theme.js system
         */
        setupWishlist() {
            if (!this.wishlistEnabled) return;

            const wishlistBtn = this.card.querySelector('.wishlist-button');
            if (!wishlistBtn) return;

            // Set initial state based on existing PHP-rendered state
            this.updateWishlistButtonState(wishlistBtn);

            // Add keyboard support for accessibility
            wishlistBtn.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    // Let the existing theme.js system handle the click
                    wishlistBtn.click();
                }
            });
        }

        /**
         * Wishlist functionality is now handled by theme.js
         * This function just keeps the initial state consistent
         */
        updateWishlistButtonState(button) {
            // Button state is already set correctly by PHP template
            // No additional JavaScript initialization needed
            return;
        }

        /**
         * Setup color variants
         */
        setupColorVariants() {
            const colorRow = this.card.querySelector('.color-row');
            if (!colorRow) return;

            const colorDots = colorRow.querySelector('.color-dots');
            if (!colorDots) return;

            colorDots.addEventListener('click', (e) => {
                if (e.target.classList.contains('color-dot')) {
                    const color = e.target.dataset.color;
                    if (color) {
                        this.selectColor(color);
                    }
                }
            });
        }

        /**
         * Handle color selection
         */
        selectColor(color) {
            // Remove active state from all color dots
            const dots = this.card.querySelectorAll('.color-dot');
            dots.forEach(dot => dot.classList.remove('active'));

            // Add active state to selected dot
            const selectedDot = Array.from(dots).find(dot => dot.dataset.color === color);
            if (selectedDot) {
                selectedDot.classList.add('active');
            }

            this.fireEvent('colorSelected', { color, productId: this.productId });
        }

        /**
         * Setup enhanced badge system
         */
        setupBadgeSystem() {
            const badgeStack = this.card.querySelector('.badge-stack');
            if (!badgeStack) return;

            // Add dynamic badges based on product data
            this.addDynamicBadges(badgeStack);
        }

        /**
         * Add dynamic badges (new, sale, etc.)
         * DISABLED - Badges are now rendered server-side in product-card.php to avoid duplicates
         */
        addDynamicBadges(badgeStack) {
            // Badges are now handled by PHP template to prevent duplication
            // All badge logic is in template-parts/components/product-card.php
            return;
        }

        /**
         * Check if product is new (added within last 30 days)
         */
        isNewProduct() {
            const product = this.card;
            const dateAdded = new Date(product.dataset.dateAdded || Date.now());
            const thirtyDaysAgo = new Date(Date.now() - 30 * 24 * 60 * 60 * 1000);
            return dateAdded > thirtyDaysAgo;
        }

        /**
         * Get sale information
         */
        getSaleInfo() {
            // This would typically come from product data
            const salePrice = this.card.dataset.salePrice;
            const regularPrice = this.card.dataset.regularPrice;
            
            if (salePrice && regularPrice) {
                const regular = parseFloat(regularPrice);
                const sale = parseFloat(salePrice);
                const percentage = Math.round(((regular - sale) / regular) * 100);
                return { percentage, regular, sale };
            }
            
            return { percentage: null };
        }

        /**
         * Setup event bindings
         */
        bindEvents() {
            // Add to cart button
            const addToCartBtn = this.card.querySelector('.add-to-cart');
            if (addToCartBtn) {
                addToCartBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.handleAddToCart(addToCartBtn);
                });
            }

            // Card click to go to product page
            this.card.addEventListener('click', (e) => {
                // Don't trigger if clicking interactive elements (including wishlist button)
                if (e.target.closest('.size-chip, .media-dot, .add-to-cart, .add-to-wishlist, .wishlist-button')) {
                    return;
                }
                
                const productLink = this.card.querySelector('.product-card__title a');
                if (productLink) {
                    productLink.click();
                }
            });
        }

        /**
         * Handle add to cart
         */
        async handleAddToCart(button) {
            try {
                button.classList.add('loading');
                button.setAttribute('aria-busy', 'true');

                // Get selected variation if variable product
                const selectedSize = this.card.querySelector('.size-chip.active')?.textContent;
                
                const addToCartData = {
                    action: 'add_to_cart',
                    product_id: this.productId,
                    quantity: 1
                };

                if (selectedSize) {
                    // This would need to be adapted based on your WooCommerce setup
                    addToCartData.variation_id = this.getVariationId(selectedSize);
                }

                const response = await this.ajaxRequest({
                    ...addToCartData,
                    nonce: window.eshop_ajax?.nonce || ''
                });

                if (response.success) {
                    button.classList.remove('loading');
                    button.setAttribute('aria-busy', 'false');
                    button.classList.add('added');
                    
                    this.showFeedback('Added to cart!');
                    this.fireEvent('addedToCart', { productId: this.productId });
                    
                    setTimeout(() => {
                        button.classList.remove('added');
                    }, 2000);
                } else {
                    throw new Error(response.data || 'Add to cart failed');
                }
            } catch (error) {
                console.error('Add to cart failed:', error);
                button.classList.remove('loading');
                button.setAttribute('aria-busy', 'false');
                this.showFeedback('Error adding to cart', 'error');
            }
        }

        /**
         * Get variation ID for selected attributes
         */
        getVariationId(size) {
            // This would implement your variation logic
            // For now, return a placeholder
            return this.productId + '-var-' + size;
        }

        /**
         * Make AJAX request
         */
        ajaxRequest(data) {
            return new Promise((resolve, reject) => {
                $.ajax({
                    url: window.eshop_ajax?.ajax_url || '/wp-admin/admin-ajax.php',
                    type: 'POST',
                    data: data,
                    success: resolve,
                    error: reject
                });
            });
        }

        /**
         * Show feedback message
         */
        showFeedback(message, type = 'success') {
            const feedback = document.createElement('div');
            feedback.className = `product-card-feedback ${type}`;
            feedback.textContent = message;
            
            this.card.appendChild(feedback);
            
            setTimeout(() => {
                feedback.classList.add('show');
            }, 100);
            
            setTimeout(() => {
                feedback.classList.remove('show');
                setTimeout(() => {
                    feedback.remove();
                }, 300);
            }, 3000);
        }

        /**
         * Fire custom event
         */
        fireEvent(eventName, detail = {}) {
            const event = new CustomEvent(`product-card:${eventName}`, {
                detail: {
                    card: this.card,
                    productId: this.productId,
                    ...detail
                }
            });
            this.card.dispatchEvent(event);
        }
    }

    // Initialize all product cards
    function initProductCards() {
        const cards = document.querySelectorAll('.product-card');
        cards.forEach(card => {
            if (!card.productCard) {
                card.productCard = new ProductCard(card);
            }
        });
    }

    // Initialize when DOM is ready
    $(document).ready(function() {
        initProductCards();
        
        // Re-initialize when new content is loaded (e.g., via AJAX)
        $(document.body).on('product-cards:refresh', initProductCards);
    });

    // Export for external use
    window.ProductCard = ProductCard;
    window.initProductCards = initProductCards;

})(jQuery);