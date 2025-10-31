/**
 * Reworked Product Card JavaScript
 * Handles interactions for the enhanced product card component
 */

(function($) {
    'use strict';

    window.TWCProductCard = {
        // Initialize all product cards on the page
        init: function() {
            this.bindEvents();
            this.initializeCards();
        },

        // Bind event handlers
        bindEvents: function() {
            const self = this;

            // Add to cart buttons
            $(document).on('click', '.twc-card-reworked__add-to-cart', function(e) {
                e.preventDefault();
                e.stopPropagation();
                self.handleAddToCart($(this));
            });

            // Wishlist buttons
            $(document).on('click', '.twc-card-reworked__wishlist', function(e) {
                e.preventDefault();
                e.stopPropagation();
                self.handleWishlist($(this));
            });

            // Quick view buttons
            $(document).on('click', '.twc-card-reworked__quick-action[data-action="quick-view"]', function(e) {
                e.preventDefault();
                e.stopPropagation();
                self.handleQuickView($(this));
            });

            // Compare buttons
            $(document).on('click', '.twc-card-reworked__quick-action[data-action="compare"]', function(e) {
                e.preventDefault();
                e.stopPropagation();
                self.handleCompare($(this));
            });

            // Size selection
            $(document).on('click', '.twc-card-reworked__size-option', function(e) {
                e.preventDefault();
                e.stopPropagation();
                self.handleSizeSelection($(this));
            });

            // Image gallery navigation (for cards with multiple images)
            $(document).on('click', '.twc-card-reworked__dot', function(e) {
                e.preventDefault();
                e.stopPropagation();
                self.handleImageNavigation($(this));
            });

            // Keyboard navigation
            $(document).on('keydown', '.twc-card-reworked', function(e) {
                self.handleKeyboardNavigation(e, $(this));
            });
        },

        // Initialize individual cards
        initializeCards: function() {
            $('.twc-card-reworked').each(function() {
                const $card = $(this);
                
                // Add ARIA labels for better accessibility
                $card.find('.twc-card-reworked__add-to-cart').attr('aria-label', 'Add to cart');
                $card.find('.twc-card-reworked__wishlist').attr('aria-label', 'Add to wishlist');
                $card.find('.twc-card-reworked__quick-action[data-action="quick-view"]').attr('aria-label', 'Quick view');
                $card.find('.twc-card-reworked__quick-action[data-action="compare"]').attr('aria-label', 'Compare product');

                // Initialize image gallery if multiple images exist
                self.initializeImageGallery($card);
            });
        },

        // Handle add to cart functionality
        handleAddToCart: function($button) {
            const $card = $button.closest('.twc-card-reworked');
            const productId = $card.data('product-id');
            const $selectedSize = $card.find('.twc-card-reworked__size-option.is-selected');
            
            // Add loading state
            $card.addClass('is-loading');
            $button.prop('disabled', true);

            // Get selected variation or use simple product ID
            let addToCartData = {
                action: 'woocommerce_add_to_cart',
                product_id: productId
            };

            if ($selectedSize.length > 0) {
                addToCartData.variation_id = $selectedSize.data('variation-id');
                addToCartData.quantity = 1;
            }

            // Perform AJAX add to cart
            $.ajax({
                url: wc_add_to_cart_params.wc_ajax_url.toString().replace('%%endpoint%%', 'add_to_cart'),
                type: 'POST',
                data: addToCartData,
                success: function(response) {
                    if (response.error) {
                        self.showNotification(response.error, 'error');
                    } else {
                        self.showNotification('Product added to cart!', 'success');
                        $button.addClass('is-added').text('Added!');
                        
                        // Update cart fragments if available
                        if (typeof wc_cart_fragments_params !== 'undefined') {
                            $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $button]);
                        }

                        // Reset button after delay
                        setTimeout(function() {
                            $button.removeClass('is-added').text('Add to Cart');
                        }, 2000);
                    }
                },
                error: function() {
                    self.showNotification('Error adding product to cart', 'error');
                },
                complete: function() {
                    $card.removeClass('is-loading');
                    $button.prop('disabled', false);
                }
            });
        },

        // Handle wishlist functionality
        handleWishlist: function($button) {
            const $card = $button.closest('.twc-card-reworked');
            const productId = $card.data('product-id');
            const isActive = $button.hasClass('is-active');

            // Toggle active state immediately for better UX
            $button.toggleClass('is-active');
            
            // Perform AJAX wishlist toggle
            $.ajax({
                url: twc_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'twc_toggle_wishlist',
                    product_id: productId,
                    nonce: twc_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        const message = isActive ? 'Removed from wishlist' : 'Added to wishlist';
                        self.showNotification(message, 'success');
                    } else {
                        // Revert state on error
                        $button.toggleClass('is-active');
                        self.showNotification(response.data.message || 'Error updating wishlist', 'error');
                    }
                },
                error: function() {
                    // Revert state on error
                    $button.toggleClass('is-active');
                    self.showNotification('Error updating wishlist', 'error');
                }
            });
        },

        // Handle quick view functionality
        handleQuickView: function($button) {
            const $card = $button.closest('.twc-card-reworked');
            const productId = $card.data('product-id');

            // Trigger quick view modal
            $(document.body).trigger('twc_quick_view', [productId]);
        },

        // Handle compare functionality
        handleCompare: function($button) {
            const $card = $button.closest('.twc-card-reworked');
            const productId = $card.data('product-id');

            // Trigger compare functionality
            $(document.body).trigger('twc_add_to_compare', [productId]);
        },

        // Handle size selection
        handleSizeSelection: function($sizeOption) {
            const $card = $sizeOption.closest('.twc-card-reworked');
            
            // Remove active state from siblings
            $card.find('.twc-card-reworked__size-option').removeClass('is-selected');
            
            // Add active state to clicked option
            $sizeOption.addClass('is-selected');

            // Update add to cart button with variation info
            const variationId = $sizeOption.data('variation-id');
            const price = $sizeOption.data('price');
            
            if (variationId) {
                $card.find('.twc-card-reworked__add-to-cart').data('variation-id', variationId);
                
                if (price) {
                    $card.find('.twc-card-reworked__price-current').text(price);
                }
            }

            // Trigger custom event for other components
            $(document.body).trigger('twc_size_selected', [variationId, $card]);
        },

        // Handle image gallery navigation
        handleImageNavigation: function($dot) {
            const $card = $dot.closest('.twc-card-reworked');
            const slideIndex = $dot.data('slide');
            
            // Update active dot
            $card.find('.twc-card-reworked__dot').removeClass('is-active');
            $dot.addClass('is-active');
            
            // Update image (if using swiper or similar)
            const $swiper = $card.find('.swiper');
            if ($swiper.length > 0 && $swiper[0].swiper) {
                $swiper[0].swiper.slideTo(slideIndex);
            }
        },

        // Initialize image gallery for cards with multiple images
        initializeImageGallery: function($card) {
            const $images = $card.find('.twc-card-reworked__image img');
            
            if ($images.length > 1) {
                // Create dots if they don't exist
                if ($card.find('.twc-card-reworked__dots').length === 0) {
                    const $dotsContainer = $('<div class="twc-card-reworked__dots"></div>');
                    
                    $images.each(function(index) {
                        const $dot = $('<span class="twc-card-reworked__dot"></span>');
                        if (index === 0) $dot.addClass('is-active');
                        $dot.attr('data-slide', index);
                        $dotsContainer.append($dot);
                    });
                    
                    $card.find('.twc-card-reworked__image').append($dotsContainer);
                }

                // Initialize Swiper if available
                if (typeof Swiper !== 'undefined') {
                    const $swiperContainer = $card.find('.swiper');
                    if ($swiperContainer.length > 0) {
                        new Swiper($swiperContainer[0], {
                            loop: true,
                            autoplay: false,
                            pagination: {
                                el: $card.find('.twc-card-reworked__dots')[0],
                                clickable: true,
                                bulletClass: 'twc-card-reworked__dot',
                                bulletActiveClass: 'is-active'
                            },
                            on: {
                                slideChange: function(swiper) {
                                    $card.find('.twc-card-reworked__dot').removeClass('is-active');
                                    $card.find('.twc-card-reworked__dot').eq(swiper.activeIndex).addClass('is-active');
                                }
                            }
                        });
                    }
                }
            }
        },

        // Handle keyboard navigation
        handleKeyboardNavigation: function(e, $card) {
            switch(e.keyCode) {
                case 13: // Enter
                // Simulate click on product link
                    $card.find('.twc-card-reworked__title a')[0].click();
                    break;
                case 32: // Space
                    e.preventDefault();
                    // Toggle wishlist
                    $card.find('.twc-card-reworked__wishlist').click();
                    break;
                case 37: // Left arrow
                    // Navigate to previous image
                    const $prevDot = $card.find('.twc-card-reworked__dot.is-active').prev();
                    if ($prevDot.length > 0) $prevDot.click();
                    break;
                case 39: // Right arrow
                    // Navigate to next image
                    const $nextDot = $card.find('.twc-card-reworked__dot.is-active').next();
                    if ($nextDot.length > 0) $nextDot.click();
                    break;
            }
        },

        // Show notification message
        showNotification: function(message, type = 'success') {
            // Create notification element
            const $notification = $(`
                <div class="twc-notification twc-notification--${type}">
                    <span class="twc-notification__message">${message}</span>
                    <button class="twc-notification__close" aria-label="Close notification">
                        <span class="material-icons">close</span>
                    </button>
                </div>
            `);

            // Add to page
            $('body').append($notification);

            // Show with animation
            setTimeout(function() {
                $notification.addClass('is-visible');
            }, 100);

            // Auto hide after 3 seconds
            setTimeout(function() {
                $notification.removeClass('is-visible');
                setTimeout(function() {
                    $notification.remove();
                }, 300);
            }, 3000);

            // Handle close button
            $notification.find('.twc-notification__close').on('click', function() {
                $notification.removeClass('is-visible');
                setTimeout(function() {
                    $notification.remove();
                }, 300);
            });
        },

        // Update card data (for AJAX updates)
        updateCard: function(productId, data) {
            const $card = $(`.twc-card-reworked[data-product-id="${productId}"]`);
            
            if ($card.length > 0) {
                // Update price
                if (data.price) {
                    $card.find('.twc-card-reworked__price-current').text(data.price);
                }

                // Update badges
                if (data.badges) {
                    const $badgesContainer = $card.find('.twc-card-reworked__badges');
                    $badgesContainer.empty();
                    
                    data.badges.forEach(function(badge) {
                        const $badge = $(`<span class="twc-card-reworked__badge twc-card-reworked__badge--${badge.type}">${badge.text}</span>`);
                        $badgesContainer.append($badge);
                    });
                }

                // Update sizes
                if (data.sizes) {
                    const $sizesContainer = $card.find('.twc-card-reworked__size-options');
                    $sizesContainer.empty();
                    
                    data.sizes.forEach(function(size) {
                        const $sizeOption = $(`
                            <button class="twc-card-reworked__size-option" 
                                    data-variation-id="${size.variation_id}" 
                                    data-price="${size.price}"
                                    ${size.low_stock ? 'is-low-stock' : ''}>
                                ${size.label}
                            </button>
                        `);
                        $sizesContainer.append($sizeOption);
                    });
                }

                // Update wishlist state
                if (data.is_in_wishlist !== undefined) {
                    $card.find('.twc-card-reworked__wishlist')
                        .toggleClass('is-active', data.is_in_wishlist);
                }
            }
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        if ($('.twc-card-reworked').length > 0) {
            TWCProductCard.init();
        }
    });

    // Re-initialize after AJAX loads
    $(document.body).on('twc_products_loaded', function() {
        if ($('.twc-card-reworked').length > 0) {
            TWCProductCard.init();
        }
    });

})(jQuery);