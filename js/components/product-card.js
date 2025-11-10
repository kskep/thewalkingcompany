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
            if (!sizeOverlay) {
                sizeOverlay = document.createElement('div');
                sizeOverlay.className = 'size-overlay';
                media.appendChild(sizeOverlay);
            }

            // Generate size chips based on product data
            this.generateSizeChips(sizeOverlay);

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

        /**
         * Generate size chips with stock status
         */
        generateSizeChips(container) {
            // This would typically fetch from product data
            // For now, using sample data structure
            const sizes = [
                { size: '36', stock: 5, status: 'available' },
                { size: '37', stock: 3, status: 'available' },
                { size: '38', stock: 1, status: 'low' },
                { size: '39', stock: 0, status: 'out' },
                { size: '40', stock: 2, status: 'available' }
            ];

            container.innerHTML = '';

            sizes.forEach(({ size, stock, status }) => {
                const chip = document.createElement('button');
                chip.className = `size-chip ${status === 'low' ? 'is-low' : ''} ${status === 'out' ? 'is-out' : ''}`;
                chip.textContent = size;
                chip.disabled = status === 'out';
                
                if (status === 'low') {
                    chip.setAttribute('data-tooltip', 'Only ' + stock + ' left');
                } else if (status === 'out') {
                    chip.setAttribute('data-tooltip', 'Out of stock');
                }

                chip.addEventListener('click', (e) => {
                    e.preventDefault();
                    if (status !== 'out') {
                        this.selectSize(size);
                    }
                });

                container.appendChild(chip);
            });
        }

        /**
         * Handle size selection
         */
        selectSize(size) {
            // Remove active state from all chips
            const chips = this.card.querySelectorAll('.size-chip');
            chips.forEach(chip => chip.classList.remove('active'));

            // Add active state to selected chip
            const selectedChip = Array.from(chips).find(chip => chip.textContent === size);
            if (selectedChip) {
                selectedChip.classList.add('active');
            }

            this.fireEvent('sizeSelected', { size, productId: this.productId });
        }

        /**
         * Setup wishlist functionality
         */
        setupWishlist() {
            if (!this.wishlistEnabled) return;

            const wishlistBtn = this.card.querySelector('.wishlist-button');
            if (!wishlistBtn) return;

            // Set initial state
            this.updateWishlistButtonState(wishlistBtn);

            wishlistBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                
                // Prevent multiple simultaneous clicks
                if (wishlistBtn.classList.contains('loading')) {
                    return;
                }
                
                this.toggleWishlist(wishlistBtn);
            });

            // Add keyboard support
            wishlistBtn.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    this.toggleWishlist(wishlistBtn);
                }
            });
        }

        /**
         * Toggle wishlist state
         */
        async toggleWishlist(button) {
            const productId = this.productId;

            try {
                // Show loading state
                button.classList.add('loading');
                button.setAttribute('aria-busy', 'true');
                button.style.pointerEvents = 'none';

                const response = await this.ajaxRequest({
                    action: 'add_to_wishlist',
                    product_id: productId,
                    nonce: window.eshop_ajax?.nonce || ''
                });

                if (response.success) {
                    const newIsInWishlist = response.data.is_in_wishlist;
                    
                    // Update button state based on server response
                    button.classList.remove('loading');
                    button.setAttribute('aria-busy', 'false');
                    button.style.pointerEvents = '';
                    
                    if (newIsInWishlist) {
                        button.classList.add('active', 'in-wishlist');
                    } else {
                        button.classList.remove('active', 'in-wishlist');
                    }

                    // Update SVG fill based on state
                    const svg = button.querySelector('svg');
                    if (svg) {
                        svg.setAttribute('fill', newIsInWishlist ? 'currentColor' : 'none');
                    }

                    // Update aria label from response
                    if (response.data.aria_label) {
                        button.setAttribute('aria-label', response.data.aria_label);
                    }

                    // Update header wishlist count
                    if (typeof response.data.count !== 'undefined') {
                        const countElement = document.querySelector('.wishlist-count');
                        if (countElement) {
                            if (response.data.count > 0) {
                                countElement.textContent = response.data.count_label || response.data.count;
                                countElement.classList.remove('hidden');
                            } else {
                                countElement.classList.add('hidden');
                            }
                        }
                    }

                    // Update header wishlist dropdown
                    if (response.data.dropdown_html) {
                        const wishlistItems = document.querySelector('.wishlist-items');
                        if (wishlistItems) {
                            wishlistItems.innerHTML = response.data.dropdown_html;
                        }
                    }

                    // Update "View All" button visibility
                    if (response.data.has_items !== undefined) {
                        const viewAllBtn = document.querySelector('.wishlist-view-all');
                        if (viewAllBtn) {
                            if (response.data.has_items) {
                                viewAllBtn.classList.remove('hidden');
                            } else {
                                viewAllBtn.classList.add('hidden');
                            }
                        }
                    }

                    // Show feedback
                    if (response.data.message) {
                        this.showFeedback(response.data.message);
                    }

                    // Sync all wishlist buttons for this product across the page
                    this.syncWishlistButtons(productId, response.data);

                    this.fireEvent('wishlistToggled', { 
                        productId, 
                        isInWishlist: response.data.is_in_wishlist,
                        count: response.data.count
                    });
                } else {
                    throw new Error(response.data?.message || 'Request failed');
                }
            } catch (error) {
                console.error('Wishlist toggle failed:', error);
                button.classList.remove('loading');
                button.setAttribute('aria-busy', 'false');
                button.style.pointerEvents = '';
                this.showFeedback('Error updating wishlist', 'error');
            }
        }

        /**
         * Sync all wishlist buttons for a product
         */
        syncWishlistButtons(productId, data) {
            const allButtons = document.querySelectorAll(`.add-to-wishlist[data-product-id="${productId}"]`);
            allButtons.forEach(btn => {
                // Skip the button that was just clicked (already updated)
                if (btn === this.card.querySelector('.wishlist-button')) return;

                // Update class state
                if (data.is_in_wishlist) {
                    btn.classList.add('active', 'in-wishlist');
                } else {
                    btn.classList.remove('active', 'in-wishlist');
                }

                // Update SVG fill
                const svg = btn.querySelector('svg');
                if (svg) {
                    svg.setAttribute('fill', data.is_in_wishlist ? 'currentColor' : 'none');
                }

                // Update aria label
                if (data.aria_label) {
                    btn.setAttribute('aria-label', data.aria_label);
                    btn.setAttribute('title', data.aria_label);
                }

                // Update button text if present
                const textEl = btn.querySelector('.wishlist-text');
                if (textEl && data.button_text) {
                    textEl.textContent = data.button_text;
                }
            });
        }

        /**
         * Update wishlist button state
         */
        updateWishlistButtonState(button) {
            // This would typically check if product is already in wishlist
            // For now, we'll use a data attribute or class
            const isInWishlist = button.classList.contains('active') || 
                               button.dataset.inWishlist === 'true';
            
            button.classList.toggle('active', isInWishlist);
            button.setAttribute('aria-label', isInWishlist ? 'Remove from wishlist' : 'Add to wishlist');
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
                // Don't trigger if clicking interactive elements
                if (e.target.closest('.wishlist-button, .size-chip, .media-dot, .add-to-cart')) {
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