/**
 * Enhanced Single Product Page JavaScript - 2025
 * Handles all interactive elements on the single product page
 */

class SingleProductEnhanced {
    constructor() {
        this.init();
    }

    init() {
        this.handleQuantityControls();
        this.handleSizeSelection();
        this.handleColorVariants();
        this.handleAddToCart();
        this.handleWishlist();
        this.handleSocialSharing();
    }

    /**
     * Enhanced Quantity Controls
     */
    handleQuantityControls() {
        const quantityWrapper = document.querySelector('.cart .quantity');
        if (!quantityWrapper) return;

        // Add custom quantity buttons if they don't exist
        const quantityInput = quantityWrapper.querySelector('input[name="quantity"]');
        if (!quantityInput) return;

        // Check if custom buttons already exist
        if (!quantityWrapper.querySelector('.quantity-decrease')) {
            // Create decrease button
            const decreaseBtn = document.createElement('button');
            decreaseBtn.type = 'button';
            decreaseBtn.className = 'quantity-decrease px-3 py-2 hover:bg-gray-100 border-r border-gray-200';
            decreaseBtn.innerHTML = '−';
            decreaseBtn.setAttribute('aria-label', 'Decrease quantity');

            // Create increase button
            const increaseBtn = document.createElement('button');
            increaseBtn.type = 'button';
            increaseBtn.className = 'quantity-increase px-3 py-2 hover:bg-gray-100 border-l border-gray-200';
            increaseBtn.innerHTML = '+';
            increaseBtn.setAttribute('aria-label', 'Increase quantity');

            // Style the input
            quantityInput.className = 'w-16 text-center border-0 focus:outline-none';

            // Wrap with flex container
            const flexContainer = document.createElement('div');
            flexContainer.className = 'flex border-2 border-gray-200 no-radius';
            
            // Add elements in order
            flexContainer.appendChild(decreaseBtn);
            flexContainer.appendChild(quantityInput);
            flexContainer.appendChild(increaseBtn);

            // Replace the original quantity wrapper content
            quantityWrapper.innerHTML = '';
            quantityWrapper.appendChild(flexContainer);
        }

        // Add event listeners
        const decreaseBtn = quantityWrapper.querySelector('.quantity-decrease');
        const increaseBtn = quantityWrapper.querySelector('.quantity-increase');

        if (decreaseBtn) {
            decreaseBtn.addEventListener('click', () => {
                const current = parseInt(quantityInput.value) || 1;
                if (current > 1) {
                    quantityInput.value = current - 1;
                    quantityInput.dispatchEvent(new Event('change'));
                }
            });
        }

        if (increaseBtn) {
            increaseBtn.addEventListener('click', () => {
                const current = parseInt(quantityInput.value) || 1;
                const max = parseInt(quantityInput.getAttribute('max')) || 999;
                if (current < max) {
                    quantityInput.value = current + 1;
                    quantityInput.dispatchEvent(new Event('change'));
                }
            });
        }
    }

    /**
     * Enhanced Size Selection
     */
    handleSizeSelection() {
        const sizeOptions = document.querySelectorAll('.size-option-single');
        const sizeSelects = document.querySelectorAll('select[data-attribute_name*="size"]');

        sizeOptions.forEach(option => {
            option.addEventListener('click', (e) => {
                const target = e.currentTarget;
                const isInStock = target.getAttribute('data-in-stock') === 'true';
                
                if (!isInStock) return;

                // Remove selected class from all size options
                sizeOptions.forEach(opt => opt.classList.remove('selected'));
                
                // Add selected class to clicked option
                target.classList.add('selected');

                // Update hidden select
                const attributeName = target.getAttribute('data-attribute');
                const selectedValue = target.getAttribute('data-value');
                const hiddenSelect = document.querySelector(`select[data-attribute_name*="${attributeName}"]`);
                
                if (hiddenSelect) {
                    hiddenSelect.value = selectedValue;
                    hiddenSelect.dispatchEvent(new Event('change'));
                }

                // Update label
                const label = target.closest('.variation-wrapper').querySelector('.selected-value');
                if (label) {
                    label.textContent = `Size ${selectedValue}`;
                }
            });

            // Handle keyboard navigation
            option.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    option.click();
                }
            });
        });
    }

    /**
     * Enhanced Color Variants
     */
    handleColorVariants() {
        const colorOptions = document.querySelectorAll('.color-variant');

        colorOptions.forEach(option => {
            option.addEventListener('click', (e) => {
                const target = e.currentTarget;
                
                // Remove selected class from all color options
                colorOptions.forEach(opt => opt.classList.remove('selected'));
                
                // Add selected class to clicked option
                target.classList.add('selected');

                // Update selected color display
                const colorName = target.getAttribute('data-color-name');
                const selectedText = document.getElementById('selected-color');
                if (selectedText) selectedText.textContent = colorName;
            });
        });
    }

    /**
     * Enhanced Add to Cart Button
     */
    handleAddToCart() {
        const addToCartBtn = document.querySelector('.single_add_to_cart_button');
        if (!addToCartBtn) return;

        // Ensure magazine styling
        addToCartBtn.classList.add('no-radius', 'no-shadow');

        // Add enhanced click feedback
        addToCartBtn.addEventListener('click', (e) => {
            if (addToCartBtn.disabled) return;

            // Add loading state
            addToCartBtn.classList.add('loading');
            addToCartBtn.disabled = true;
            
            const originalText = addToCartBtn.textContent;
            addToCartBtn.textContent = 'Adding to Cart...';

            // Handle form submission normally, but provide visual feedback
            setTimeout(() => {
                if (!addToCartBtn.classList.contains('added')) {
                    addToCartBtn.textContent = 'Added to Cart!';
                    addToCartBtn.classList.remove('loading');
                    addToCartBtn.classList.add('added', 'bg-green-600');
                    addToCartBtn.classList.remove('from-pink-500', 'to-pink-600');

                    setTimeout(() => {
                        addToCartBtn.textContent = originalText;
                        addToCartBtn.classList.remove('added', 'bg-green-600');
                        addToCartBtn.classList.add('from-pink-500', 'to-pink-600');
                        addToCartBtn.disabled = false;
                    }, 2000);
                }
            }, 1000);
        });

        // Handle WooCommerce AJAX events
        document.body.addEventListener('added_to_cart', () => {
            addToCartBtn.classList.remove('loading');
            addToCartBtn.classList.add('added');
        });
    }

    /**
     * Enhanced Wishlist Functionality
     */
    handleWishlist() {
        const wishlistBtns = document.querySelectorAll('.add-to-wishlist, [data-action="wishlist"]');

        wishlistBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                
                const icon = btn.querySelector('svg path, i');
                const productId = btn.getAttribute('data-product-id') || btn.closest('.product').querySelector('[data-product-id]')?.getAttribute('data-product-id');
                
                // Toggle wishlist state
                const isInWishlist = btn.classList.contains('in-wishlist');
                
                if (isInWishlist) {
                    btn.classList.remove('in-wishlist', 'text-red-500');
                    btn.classList.add('text-gray-600');
                    this.showFeedback(btn, 'Removed from Wishlist');
                } else {
                    btn.classList.add('in-wishlist', 'text-red-500');
                    btn.classList.remove('text-gray-600');
                    this.showFeedback(btn, 'Added to Wishlist!');
                }

                // Here you would typically make an AJAX call to update the wishlist
                // this.updateWishlistAjax(productId, !isInWishlist);
            });
        });
    }

    /**
     * Enhanced Social Sharing
     */
    handleSocialSharing() {
        const copyLinkBtn = document.querySelector('.copy-link');
        
        if (copyLinkBtn) {
            copyLinkBtn.addEventListener('click', async () => {
                try {
                    await navigator.clipboard.writeText(window.location.href);
                    copyLinkBtn.innerHTML = '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
                    copyLinkBtn.classList.add('copied', 'bg-green-600');
                    
                    setTimeout(() => {
                        copyLinkBtn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>';
                        copyLinkBtn.classList.remove('copied', 'bg-green-600');
                    }, 2000);
                } catch (err) {
                    console.error('Failed to copy link:', err);
                    this.showFeedback(copyLinkBtn, 'Failed to copy link');
                }
            });
        }

        // Handle social share buttons
        const socialBtns = document.querySelectorAll('.social-share-btn[href]');
        socialBtns.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const url = btn.getAttribute('href');
                window.open(url, 'social-share', 'width=600,height=400,location=no,menubar=no,resizable=yes,scrollbars=yes,status=no,toolbar=no');
            });
        });
    }

    /**
     * Show feedback message
     */
    showFeedback(element, message) {
        const feedback = document.createElement('div');
        feedback.textContent = message;
        feedback.className = 'absolute -top-8 left-1/2 transform -translate-x-1/2 bg-black text-white px-2 py-1 text-xs rounded z-20 whitespace-nowrap';
        
        element.style.position = 'relative';
        element.appendChild(feedback);
        
        setTimeout(() => {
            feedback.remove();
        }, 2000);
    }

    /**
     * Update wishlist via AJAX (placeholder for actual implementation)
     */
    updateWishlistAjax(productId, addToWishlist) {
        // This would typically make an AJAX call to your wishlist handler
        const action = addToWishlist ? 'add_to_wishlist' : 'remove_from_wishlist';
        
        fetch(wc_add_to_cart_params.ajax_url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                action: action,
                product_id: productId,
                security: wc_add_to_cart_params.wc_ajax_nonce
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                console.log('Wishlist updated successfully');
            }
        })
        .catch(error => {
            console.error('Wishlist update failed:', error);
        });
    }
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    new SingleProductEnhanced();
});

// Also initialize on WooCommerce variation form found (for AJAX loaded content)
document.addEventListener('wc_variation_form', () => {
    new SingleProductEnhanced();
});