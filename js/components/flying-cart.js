/**
 * Flying Cart Component JavaScript
 * 
 * Handles all interactions for the floating cart widget
 * Features: toggle, animations, AJAX cart updates, item removal
 * 
 * @package E-Shop Theme
 */

(function ($) {
    'use strict';

    // Flying Cart Class
    class FlyingCart {
        constructor() {
            this.cart = $('#flying-cart');
            this.toggle = this.cart.find('.flying-cart__toggle');
            this.panel = this.cart.find('.flying-cart__panel');
            this.closeBtn = this.cart.find('.cart-close-btn');
            this.isExpanded = false;
            this.isLoading = false;

            this.init();
        }

        init() {
            this.bindEvents();
            this.setupAutoHide();
            this.updateCartDisplay();
        }

        bindEvents() {
            // Toggle cart panel
            this.toggle.on('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.toggleCart();
            });

            // Close cart panel
            this.closeBtn.on('click', (e) => {
                e.preventDefault();
                this.closeCart();
            });

            // Close cart when clicking outside
            $(document).on('click', (e) => {
                if (!this.cart.is(e.target) && this.cart.has(e.target).length === 0) {
                    this.closeCart();
                }
            });

            // Remove cart item
            this.cart.on('click', '.remove-cart-item', (e) => {
                e.preventDefault();
                const cartItemKey = $(e.currentTarget).data('cart-item-key');
                this.removeCartItem(cartItemKey);
            });

            // Quantity plus button
            this.cart.on('click', '.qty-plus', (e) => {
                e.preventDefault();
                const $btn = $(e.currentTarget);
                const cartItemKey = $btn.data('cart-item-key');
                const $qtyValue = $btn.siblings('.qty-value');
                const currentQty = parseInt($qtyValue.text()) || 1;
                this.updateCartQuantity(cartItemKey, currentQty + 1);
            });

            // Quantity minus button
            this.cart.on('click', '.qty-minus', (e) => {
                e.preventDefault();
                const $btn = $(e.currentTarget);
                const cartItemKey = $btn.data('cart-item-key');
                const $qtyValue = $btn.siblings('.qty-value');
                const currentQty = parseInt($qtyValue.text()) || 1;
                if (currentQty > 1) {
                    this.updateCartQuantity(cartItemKey, currentQty - 1);
                } else {
                    // If quantity would be 0, remove the item
                    this.removeCartItem(cartItemKey);
                }
            });

            // Listen for WooCommerce cart updates
            $(document.body).on('added_to_cart', (event, fragments, cart_hash, $button) => {
                this.onCartUpdated(fragments);
                this.showAddToCartAnimation($button);
            });

            $(document.body).on('removed_from_cart', (event, fragments, cart_hash) => {
                this.onCartUpdated(fragments);
            });

            $(document.body).on('wc_fragment_refresh', () => {
                this.refreshCart();
            });

            // Listen for cart quantity updates
            $(document.body).on('updated_cart_totals', () => {
                this.refreshCart();
            });

            // Keyboard accessibility
            this.toggle.on('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    this.toggleCart();
                }
            });

            // Escape key to close
            $(document).on('keydown', (e) => {
                if (e.key === 'Escape' && this.isExpanded) {
                    this.closeCart();
                }
            });
        }

        toggleCart() {
            if (this.isExpanded) {
                this.closeCart();
            } else {
                this.openCart();
            }
        }

        openCart() {
            if (this.isLoading) return;

            this.isExpanded = true;
            this.cart.addClass('expanded');
            this.toggle.attr('aria-expanded', 'true');

            // Focus management for accessibility
            setTimeout(() => {
                this.panel.find('.cart-close-btn').focus();
            }, 300);
        }

        closeCart() {
            this.isExpanded = false;
            this.cart.removeClass('expanded');
            this.toggle.attr('aria-expanded', 'false');
        }

        setupAutoHide() {
            let hideTimeout;

            // Auto-hide after inactivity
            this.cart.on('mouseenter', () => {
                clearTimeout(hideTimeout);
            });

            this.cart.on('mouseleave', () => {
                if (this.isExpanded) {
                    hideTimeout = setTimeout(() => {
                        this.closeCart();
                    }, 3000); // Hide after 3 seconds of inactivity
                }
            });
        }

        removeCartItem(cartItemKey) {
            if (this.isLoading) return;

            this.setLoading(true);

            const data = {
                action: 'remove_cart_item',
                cart_item_key: cartItemKey,
                nonce: eshop_ajax.nonce
            };

            $.ajax({
                url: eshop_ajax.ajax_url,
                type: 'POST',
                data: data,
                success: (response) => {
                    if (response.success) {
                        // Update cart fragments
                        if (response.data.fragments) {
                            this.updateFragments(response.data.fragments);
                        }

                        // Show success message
                        this.showNotification('Item removed from cart', 'success');

                        // Refresh the cart display
                        this.refreshCart();
                    } else {
                        this.showNotification('Error removing item', 'error');
                    }
                },
                error: () => {
                    this.showNotification('Error removing item', 'error');
                },
                complete: () => {
                    this.setLoading(false);
                }
            });
        }

        updateCartQuantity(cartItemKey, quantity) {
            if (this.isLoading) return;

            this.setLoading(true);

            const data = {
                action: 'update_cart_quantity',
                cart_item_key: cartItemKey,
                quantity: quantity,
                nonce: eshop_ajax.nonce
            };

            $.ajax({
                url: eshop_ajax.ajax_url,
                type: 'POST',
                data: data,
                success: (response) => {
                    if (response.success) {
                        // Update cart fragments
                        if (response.data.fragments) {
                            this.updateFragments(response.data.fragments);
                        }

                        // Trigger WooCommerce events
                        $(document.body).trigger('wc_fragment_refresh');
                        $(document.body).trigger('updated_cart_totals');

                        // Refresh the cart display
                        this.refreshCart();
                    } else {
                        this.showNotification('Error updating quantity', 'error');
                    }
                },
                error: () => {
                    this.showNotification('Error updating quantity', 'error');
                },
                complete: () => {
                    this.setLoading(false);
                }
            });
        }

        onCartUpdated(fragments) {
            this.updateFragments(fragments);
            this.updateCartDisplay();
            this.showCartUpdateAnimation();
        }

        updateFragments(fragments) {
            if (fragments) {
                $.each(fragments, function (key, value) {
                    $(key).replaceWith(value);
                });
            }
        }

        refreshCart() {
            if (this.isLoading) return;

            this.setLoading(true);

            $.ajax({
                url: eshop_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'get_flying_cart_content',
                    nonce: eshop_ajax.nonce
                },
                success: (response) => {
                    if (response.success) {
                        // Check if the response HTML is empty (cart is empty)
                        if (!response.data.html || response.data.html.trim() === '') {
                            // Cart is empty - hide/remove the flying cart
                            this.cart.fadeOut(300, () => {
                                this.cart.remove();
                            });
                            return;
                        }

                        this.cart.replaceWith(response.data.html);
                        this.cart = $('#flying-cart');

                        // If the flying cart no longer exists (was not rendered), stop
                        if (!this.cart.length) {
                            return;
                        }

                        this.toggle = this.cart.find('.flying-cart__toggle');
                        this.panel = this.cart.find('.flying-cart__panel');
                        this.closeBtn = this.cart.find('.cart-close-btn');
                        this.bindEvents();
                    }
                },
                complete: () => {
                    this.setLoading(false);
                }
            });
        }

        updateCartDisplay() {
            const cartCount = this.cart.find('.cart-count-badge').data('count') || 0;
            const isEmpty = cartCount === 0;

            this.cart.toggleClass('cart-empty', isEmpty);
            this.cart.toggleClass('cart-has-items', !isEmpty);

            // Update badge visibility
            const badge = this.cart.find('.cart-count-badge');
            if (cartCount > 0) {
                badge.removeClass('hidden').addClass('visible');
            } else {
                badge.removeClass('visible').addClass('hidden');
            }
        }

        showAddToCartAnimation($button) {
            // Add visual feedback to the add to cart button
            if ($button && $button.length) {
                $button.addClass('added-to-cart');
                setTimeout(() => {
                    $button.removeClass('added-to-cart');
                }, 2000);
            }

            // Animate the flying cart
            this.showCartUpdateAnimation();

            // Show notification
            this.showNotification('Item added to cart!', 'success');
        }

        showCartUpdateAnimation() {
            this.cart.addClass('cart-updated');

            // Pulse animation for the cart icon
            const icon = this.cart.find('.cart-icon');
            icon.addClass('animate-pulse');

            setTimeout(() => {
                this.cart.removeClass('cart-updated');
                icon.removeClass('animate-pulse');
            }, 600);
        }

        setLoading(loading) {
            this.isLoading = loading;
            this.cart.toggleClass('loading', loading);

            if (loading) {
                this.toggle.attr('aria-busy', 'true');
            } else {
                this.toggle.removeAttr('aria-busy');
            }
        }

        showNotification(message, type = 'info') {
            // Use existing notification system if available
            if (window.EShopTheme && window.EShopTheme.showNotification) {
                window.EShopTheme.showNotification(message, type);
                return;
            }

            // Fallback notification
            const notification = $(`
                <div class="flying-cart-notification ${type}">
                    ${message}
                </div>
            `);

            $('body').append(notification);

            setTimeout(() => {
                notification.addClass('show');
            }, 100);

            setTimeout(() => {
                notification.removeClass('show');
                setTimeout(() => {
                    notification.remove();
                }, 300);
            }, 3000);
        }

        // Public methods for external access
        open() {
            this.openCart();
        }

        close() {
            this.closeCart();
        }

        refresh() {
            this.refreshCart();
        }
    }

    // Initialize Flying Cart when DOM is ready
    $(document).ready(function () {
        // Only initialize if the flying cart element exists
        if ($('#flying-cart').length) {
            window.FlyingCartInstance = new FlyingCart();
        }
    });

    // Make FlyingCart available globally
    window.FlyingCart = FlyingCart;

})(jQuery);
