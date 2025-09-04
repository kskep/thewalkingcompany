/**
 * Color Variants Selector JavaScript
 * 
 * Handles color variant selection and navigation on single product pages
 *
 * @package E-Shop Theme
 */

class EshopColorVariants {
    constructor(container) {
        this.container = container;
        this.currentProductId = container.dataset.productId;
        this.variants = [];
        this.isLoading = false;
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.loadVariants();
        this.setupKeyboardNavigation();
    }
    
    /**
     * Bind click and interaction events
     */
    bindEvents() {
        // Color variant click handlers
        this.container.addEventListener('click', (e) => {
            const variant = e.target.closest('.color-variant');
            if (variant && !variant.classList.contains('selected') && !variant.classList.contains('out-of-stock')) {
                this.selectVariant(variant);
            }
        });
        
        // Hover effects for better UX
        this.container.addEventListener('mouseenter', (e) => {
            const variant = e.target.closest('.color-variant');
            if (variant && !variant.classList.contains('out-of-stock')) {
                this.showVariantPreview(variant);
            }
        }, true);
        
        this.container.addEventListener('mouseleave', (e) => {
            const variant = e.target.closest('.color-variant');
            if (variant) {
                this.hideVariantPreview(variant);
            }
        }, true);
    }
    
    /**
     * Setup keyboard navigation for accessibility
     */
    setupKeyboardNavigation() {
        this.container.addEventListener('keydown', (e) => {
            const variant = e.target.closest('.color-variant');
            if (!variant) return;
            
            switch (e.key) {
                case 'Enter':
                case ' ':
                    e.preventDefault();
                    if (!variant.classList.contains('selected') && !variant.classList.contains('out-of-stock')) {
                        this.selectVariant(variant);
                    }
                    break;
                    
                case 'ArrowLeft':
                case 'ArrowRight':
                    e.preventDefault();
                    this.navigateVariants(variant, e.key === 'ArrowRight');
                    break;
            }
        });
    }
    
    /**
     * Navigate between variants using arrow keys
     */
    navigateVariants(currentVariant, forward = true) {
        const variants = Array.from(this.container.querySelectorAll('.color-variant'));
        const currentIndex = variants.indexOf(currentVariant);
        
        if (currentIndex === -1) return;
        
        const nextIndex = forward 
            ? (currentIndex + 1) % variants.length 
            : (currentIndex - 1 + variants.length) % variants.length;
            
        const nextVariant = variants[nextIndex];
        if (nextVariant) {
            nextVariant.focus();
        }
    }
    
    /**
     * Load variants data via AJAX
     */
    async loadVariants() {
        if (this.isLoading) return;
        
        this.isLoading = true;
        this.showLoadingState();
        
        try {
            const response = await this.fetchVariants();
            if (response.success) {
                this.variants = response.data.variants;
                this.updateVariantDisplay();
            } else {
                this.showError(response.data.message || 'Failed to load color variants');
            }
        } catch (error) {
            console.error('Error loading color variants:', error);
            this.showError('Network error loading color variants');
        } finally {
            this.isLoading = false;
            this.hideLoadingState();
        }
    }
    
    /**
     * Fetch variants from server
     */
    async fetchVariants() {
        const formData = new FormData();
        formData.append('action', 'get_color_variants');
        formData.append('product_id', this.currentProductId);
        formData.append('nonce', eshop_ajax.nonce);
        
        const response = await fetch(eshop_ajax.ajax_url, {
            method: 'POST',
            body: formData
        });
        
        return await response.json();
    }
    
    /**
     * Select a color variant
     */
    selectVariant(variantElement) {
        const productId = variantElement.dataset.productId;
        const url = variantElement.dataset.url;
        const colorName = variantElement.dataset.colorName;
        const inStock = variantElement.dataset.inStock === '1';
        
        if (!inStock) {
            this.showFeedback(`${colorName} is currently out of stock`, 'error');
            return;
        }
        
        // Show selection feedback
        this.showFeedback(`Switching to ${colorName}...`, 'loading');
        
        // Update URL and navigate
        if (url && url !== window.location.href) {
            // Use history API for smooth transition
            if (history.pushState) {
                history.pushState({productId: productId}, '', url);
                this.handleProductChange(productId, colorName);
            } else {
                // Fallback for older browsers
                window.location.href = url;
            }
        }
    }
    
    /**
     * Handle product change without full page reload
     */
    async handleProductChange(newProductId, colorName) {
        try {
            // Update current product context
            this.currentProductId = newProductId;
            this.container.dataset.productId = newProductId;
            
            // Show success feedback
            this.showFeedback(`Switched to ${colorName}`, 'success');
            
            // Reload the page to update all product data
            // In a more advanced implementation, this could update content via AJAX
            setTimeout(() => {
                window.location.reload();
            }, 500);
            
        } catch (error) {
            console.error('Error handling product change:', error);
            this.showFeedback('Error switching color variant', 'error');
        }
    }
    
    /**
     * Update variant display after loading
     */
    updateVariantDisplay() {
        // This method could update the display if variants are loaded dynamically
        // For now, variants are rendered server-side, so this is a placeholder
        this.showFeedback('Color variants loaded', 'success');
        
        // Hide feedback after a delay
        setTimeout(() => {
            this.hideFeedback();
        }, 2000);
    }
    
    /**
     * Show variant preview on hover
     */
    showVariantPreview(variantElement) {
        const colorName = variantElement.dataset.colorName;
        const price = variantElement.dataset.price;
        const inStock = variantElement.dataset.inStock === '1';
        
        // Update selected color info temporarily
        const selectedInfo = this.container.querySelector('.selected-color-info');
        if (selectedInfo) {
            const nameElement = selectedInfo.querySelector('.selected-color-name');
            const priceElement = selectedInfo.querySelector('.selected-color-price');
            
            if (nameElement) {
                nameElement.textContent = `Preview: ${colorName}`;
                nameElement.style.fontStyle = 'italic';
                nameElement.style.opacity = '0.8';
            }
            
            if (priceElement && price) {
                priceElement.innerHTML = price;
                priceElement.style.opacity = '0.8';
            }
        }
        
        // Show stock status if out of stock
        if (!inStock) {
            this.showFeedback(`${colorName} - Out of Stock`, 'warning');
        }
    }
    
    /**
     * Hide variant preview
     */
    hideVariantPreview(variantElement) {
        // Restore original selected color info
        const selectedInfo = this.container.querySelector('.selected-color-info');
        if (selectedInfo) {
            const nameElement = selectedInfo.querySelector('.selected-color-name');
            const priceElement = selectedInfo.querySelector('.selected-color-price');
            
            // Find current variant data to restore
            const currentVariant = this.container.querySelector('.color-variant.selected');
            if (currentVariant && nameElement) {
                const originalName = currentVariant.dataset.colorName;
                const originalPrice = currentVariant.dataset.price;
                
                nameElement.textContent = `Selected: ${originalName}`;
                nameElement.style.fontStyle = 'normal';
                nameElement.style.opacity = '1';
                
                if (priceElement && originalPrice) {
                    priceElement.innerHTML = originalPrice;
                    priceElement.style.opacity = '1';
                }
            }
        }
        
        this.hideFeedback();
    }
    
    /**
     * Show loading state
     */
    showLoadingState() {
        const loadingElement = this.container.querySelector('.color-variants-loading');
        const variantsContainer = this.container.querySelector('.color-variants-container');
        
        if (loadingElement && variantsContainer) {
            variantsContainer.style.display = 'none';
            loadingElement.style.display = 'flex';
        }
    }
    
    /**
     * Hide loading state
     */
    hideLoadingState() {
        const loadingElement = this.container.querySelector('.color-variants-loading');
        const variantsContainer = this.container.querySelector('.color-variants-container');
        
        if (loadingElement && variantsContainer) {
            loadingElement.style.display = 'none';
            variantsContainer.style.display = 'flex';
        }
    }
    
    /**
     * Show feedback message
     */
    showFeedback(message, type = 'info') {
        const feedbackElement = this.container.querySelector('.color-selection-feedback');
        const textElement = feedbackElement?.querySelector('.feedback-text');
        
        if (feedbackElement && textElement) {
            textElement.textContent = message;
            feedbackElement.className = `color-selection-feedback ${type}`;
            feedbackElement.style.display = 'block';
            
            // Add animation class
            feedbackElement.classList.add('selection-changed');
            
            // Remove animation class after animation completes
            setTimeout(() => {
                feedbackElement.classList.remove('selection-changed');
            }, 300);
        }
    }
    
    /**
     * Hide feedback message
     */
    hideFeedback() {
        const feedbackElement = this.container.querySelector('.color-selection-feedback');
        if (feedbackElement) {
            feedbackElement.style.display = 'none';
        }
    }
    
    /**
     * Show error message
     */
    showError(message) {
        this.showFeedback(message, 'error');
        console.error('Color variants error:', message);
    }
}

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    const colorVariantsContainers = document.querySelectorAll('.product-color-variants');
    colorVariantsContainers.forEach(container => {
        new EshopColorVariants(container);
    });
});

// Handle browser back/forward navigation
window.addEventListener('popstate', function(event) {
    if (event.state && event.state.productId) {
        // Reload page to show correct product
        window.location.reload();
    }
});

// Make class globally available
window.EshopColorVariants = EshopColorVariants;