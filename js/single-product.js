/**
 * Single Product Page JavaScript
 * Enhanced functionality for magazine-style single product page
 *
 * @package thewalkingtheme
 */

(function($) {
    'use strict';

    // Product Actions Component
    window.ProductActions = function(container) {
        this.container = $(container);
        this.init();
    };

    ProductActions.prototype = {
        init: function() {
            this.bindEvents();
            this.checkStockStatus();
        },

        bindEvents: function() {
            var self = this;

            // Add to cart button
            this.container.find('.add-to-cart-btn').on('click', function(e) {
                e.preventDefault();
                self.addToCart($(this));
            });

            // Wishlist button
            this.container.find('.wishlist-btn').on('click', function(e) {
                e.preventDefault();
                self.toggleWishlist($(this));
            });

            // Stock status updates
            $(document.body).on('woocommerce_variation_has_changed', function() {
                self.checkStockStatus();
            });
        },

        addToCart: function(button) {
            var self = this;
            var productId = button.data('product-id');
            var quantity = 1;

            // Check if variation is selected for variable products
            var variationId = this.getSelectedVariation();
            if (this.container.closest('form').find('.variations select').length > 0) {
                if (!variationId) {
                    this.showFeedback('Παρακαλώ επιλέξτε νούμερο πριν προσθέσετε στο καλάθι.', 'error');
                    return;
                }
                productId = variationId;
            }

            // Show loading state
            this.setButtonLoading(button, true);

            // Prepare data
            var data = {
                action: 'woocommerce_add_to_cart',
                product_id: productId,
                quantity: quantity,
                nonce: woocommerce_params ? woocommerce_params.nonce : ''
            };

            // Add variation data if present
            var variationData = this.container.closest('form').serialize();
            if (variationData) {
                data = $.extend(data, this.parseQuery(variationData));
            }

            // Make AJAX request
            $.ajax({
                url: woocommerce_params.ajax_url,
                type: 'POST',
                data: data,
                success: function(response) {
                    self.handleAddToCartResponse(response, button);
                },
                error: function() {
                    self.showFeedback('Σφάλμα κατά την προσθήκη στο καλάθι.', 'error');
                },
                complete: function() {
                    self.setButtonLoading(button, false);
                }
            });
        },

        toggleWishlist: function(button) {
            var self = this;
            var productId = button.data('product-id');
            var nonce = button.data('nonce');

            $.ajax({
                url: wc_add_to_cart_params.ajax_url || woocommerce_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'add_to_wishlist',
                    product_id: productId,
                    nonce: nonce
                },
                success: function(response) {
                    if (response.success) {
                        var isInWishlist = response.data.is_in_wishlist;
                        self.updateWishlistButton(button, isInWishlist);
                        self.showFeedback(response.data.message, 'success');
                        self.updateWishlistCount();
                    } else {
                        self.showFeedback(response.data || 'Σφάλμα κατά την προσθήκη στα αγαπημένα.', 'error');
                    }
                },
                error: function() {
                    self.showFeedback('Σφάλμα κατά την προσθήκη στα αγαπημένα.', 'error');
                }
            });
        },

        getSelectedVariation: function() {
            var variationSelect = this.container.closest('form').find('.variations select');
            var variationId = '';

            variationSelect.each(function() {
                if ($(this).val() && $(this).val() !== '') {
                    variationId = $(this).val();
                }
            });

            return variationId;
        },

        setButtonLoading: function(button, loading) {
            if (loading) {
                button.addClass('btn-loading').prop('disabled', true);
            } else {
                button.removeClass('btn-loading').prop('disabled', false);
            }
        },

        handleAddToCartResponse: function(response, button) {
            if (response.error && response.product_url) {
                window.location = response.product_url;
                return;
            }

            // Update cart fragments
            $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, button]);
            this.showFeedback('Προστέθηκε στο καλάθι!', 'success');
            
            // Update cart count in header if exists
            this.updateCartCount();
        },

        updateWishlistButton: function(button, isInWishlist) {
            var textElement = button.find('.wishlist-text');
            var iconElement = button.find('.heart-icon');

            if (isInWishlist) {
                button.addClass('active');
                textElement.text('Αγαπημένα');
                iconElement.attr('fill', 'currentColor');
            } else {
                button.removeClass('active');
                textElement.text('Προσθήκη στα αγαπημένα');
                iconElement.attr('fill', 'none');
            }
        },

        updateWishlistCount: function() {
            // This would update wishlist count in header if needed
            $.ajax({
                url: woocommerce_params.ajax_url,
                type: 'GET',
                data: { action: 'get_wishlist_count' },
                success: function(response) {
                    $('.wishlist-count').text(response.data || 0);
                }
            });
        },

        updateCartCount: function() {
            // Trigger fragment update
            $(document.body).trigger('wc_fragment_refresh');
        },

        checkStockStatus: function() {
            var availabilityDiv = this.container.find('#product-availability');
            if (availabilityDiv.length === 0) return;

            var stockStatus = $('form.variations_form').find('.stock').text();
            var stockQty = $('form.variations_form').find('.stock').data('stock') || 0;

            if (stockStatus && stockStatus.indexOf('in stock') !== -1) {
                availabilityDiv.find('.stock-status').removeClass('out-of-stock').addClass('in-stock')
                    .find('span').text('Σε απόθεμα');
                this.container.find('.add-to-cart-btn').prop('disabled', false);
            } else {
                availabilityDiv.find('.stock-status').removeClass('in-stock').addClass('out-of-stock')
                    .find('span').text('Εξαντλήθηκε');
                this.container.find('.add-to-cart-btn').prop('disabled', true);
            }
        },

        showFeedback: function(message, type) {
            var feedback = this.container.find('#action-feedback');
            feedback.removeClass('success error').addClass(type).text(message);
            
            setTimeout(function() {
                feedback.removeClass('success error').hide();
            }, 5000);
        },

        parseQuery: function(query) {
            var params = {};
            query.replace(/([^&=]+)=?([^&]*)(?:&+|$)/g, function(match, key, value) {
                params[key] = decodeURIComponent(value);
            });
            return params;
        }
    };

    // Product Accordions Component
    window.ProductAccordions = function(container) {
        this.container = $(container);
        this.init();
    };

    ProductAccordions.prototype = {
        init: function() {
            this.bindEvents();
            this.handleHash();
        },

        bindEvents: function() {
            var self = this;

            this.container.find('.accordion-header').on('click', function(e) {
                e.preventDefault();
                self.toggleAccordion($(this));
            });

            // Handle keyboard navigation
            this.container.find('.accordion-header').on('keydown', function(e) {
                if (e.which === 13 || e.which === 32) { // Enter or Space
                    e.preventDefault();
                    self.toggleAccordion($(this));
                }
            });

            // Handle hash changes
            $(window).on('hashchange', function() {
                self.handleHash();
            });
        },

        toggleAccordion: function(header) {
            var isExpanded = header.attr('aria-expanded') === 'true';
            var panel = header.siblings('.accordion-panel');

            // Close all other accordions
            this.container.find('.accordion-header').attr('aria-expanded', 'false');
            this.container.find('.accordion-panel').removeClass('expanded').attr('aria-hidden', 'true');

            // Toggle current accordion
            if (!isExpanded) {
                header.attr('aria-expanded', 'true');
                panel.addClass('expanded').attr('aria-hidden', 'false');

                // Update hash for deep linking
                var accordionId = header.attr('id');
                if (accordionId) {
                    window.location.hash = accordionId;
                }
            }
        },

        handleHash: function() {
            var hash = window.location.hash.substring(1);
            if (hash) {
                var targetHeader = this.container.find('#' + hash);
                if (targetHeader.length) {
                    // Close all first
                    this.container.find('.accordion-header').attr('aria-expanded', 'false');
                    this.container.find('.accordion-panel').removeClass('expanded').attr('aria-hidden', 'true');

                    // Open target
                    targetHeader.attr('aria-expanded', 'true');
                    targetHeader.siblings('.accordion-panel').addClass('expanded').attr('aria-hidden', 'false');
                }
            }
        }
    };

    // Enhanced Product Gallery (extends existing functionality)
    window.EnhancedProductGallery = function() {
        this.init();
    };

    EnhancedProductGallery.prototype = {
        init: function() {
            this.setupLazyLoading();
            this.enhanceNavigation();
            this.setupSwipeGestures();
        },

        setupLazyLoading: function() {
            if ('IntersectionObserver' in window) {
                const imageObserver = new IntersectionObserver((entries, observer) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            if (img.dataset.src) {
                                img.src = img.dataset.src;
                                img.classList.remove('lazy');
                                imageObserver.unobserve(img);
                            }
                        }
                    });
                });

                document.querySelectorAll('img[data-src]').forEach(img => {
                    imageObserver.observe(img);
                });
            }
        },

        enhanceNavigation: function() {
            // Add keyboard navigation
            $(document).on('keydown', function(e) {
                if ($('.product-gallery-container').length === 0) return;

                if (e.which === 37) { // Left arrow
                    $('.swiper-button-prev').click();
                } else if (e.which === 39) { // Right arrow
                    $('.swiper-button-next').click();
                }
            });
        },

        setupSwipeGestures: function() {
            let startX = 0;
            let endX = 0;

            $('.product-main-slider').on('touchstart', function(e) {
                startX = e.originalEvent.touches[0].clientX;
            });

            $('.product-main-slider').on('touchend', function(e) {
                endX = e.originalEvent.changedTouches[0].clientX;
                const diff = startX - endX;

                if (Math.abs(diff) > 50) { // Minimum swipe distance
                    if (diff > 0) {
                        $('.swiper-button-next').click();
                    } else {
                        $('.swiper-button-prev').click();
                    }
                }
            });
        }
    };

    // Initialize components when document is ready
    $(document).ready(function() {
        // Initialize enhanced gallery
        if ($('.product-gallery-container').length > 0) {
            new EnhancedProductGallery();
        }

        // Initialize product accordions
        $('.product-accordions-container').each(function() {
            new ProductAccordions(this);
        });

        // Initialize product actions
        $('.product-actions-container').each(function() {
            new ProductActions(this);
        });

        // Handle AJAX cart updates
        $(document.body).on('added_to_cart', function(event, fragments, cart_hash, button) {
            // Update cart count
            $('.cart-count').text(fragments['.cart-count']);
            
            // Show success message
            const cartButton = $('.add-to-cart-btn');
            cartButton.removeClass('btn-loading').prop('disabled', false);
            
            // Trigger flying cart if exists
            if (typeof updateFlyingCart === 'function') {
                updateFlyingCart();
            }
        });

        // Handle variation changes for variable products
        $('form.variations_form').on('found_variation', function(event, variation) {
            const addToCartBtn = $('.add-to-cart-btn');
            
            // Update button state based on availability
            if (variation.is_in_stock && variation.is_in_stock !== false) {
                addToCartBtn.prop('disabled', false).removeClass('disabled');
            } else {
                addToCartBtn.prop('disabled', true).addClass('disabled');
            }

            // Update price display if needed
            if (variation.display_price) {
                $('.current-price').text(wc_price(variation.display_price));
            }
        });

        // Reset button when no variation is selected
        $('form.variations_form').on('reset_data', function() {
            $('.add-to-cart-btn').prop('disabled', true).addClass('disabled');
        });
    });

    // Helper function to format prices
    function wc_price(price) {
        if (typeof woocommerce_params !== 'undefined') {
            return accounting.formatMoney(price, {
                symbol: woocommerce_params.currency_format_symbol,
                decimal: woocommerce_params.currency_format_decimal_sep,
                thousand: woocommerce_params.currency_format_thousand_sep,
                precision: woocommerce_params.currency_format_num_decimals,
                format: woocommerceParams.currency_format
            });
        }
        return '€' + parseFloat(price).toFixed(2);
    }

    // Expose globals for inline scripts
    window.ProductActions = ProductActions;
    window.ProductAccordions = ProductAccordions;
    window.EnhancedProductGallery = EnhancedProductGallery;

})(jQuery);