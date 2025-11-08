/**
 * Color Variants Selector JavaScript
 * 
 * Handles color variant selection and navigation on single product pages
 *
 * @package E-Shop Theme
 */

/**
 * Enhanced Color Variants Component - 2025 Standards
 * 
 * Handles color variant selection with grouped SKU functionality,
 * smooth transitions, and accessibility features
 *
 * @package thewalkingtheme
 */

class EshopColorVariants {
    constructor(container) {
        this.container = container;
        this.productId = container.dataset.productId;
        this.variants = [];
        this.selectedVariant = null;
        this.isLoading = false;
        
        this.init();
    }

    /**
     * Initialize the color variants component
     */
    init() {
        this.setupVariants();
        this.bindEvents();
        this.handleInitialSelection();
        this.setupKeyboardNavigation();
    }

    /**
     * Setup variant data from DOM elements
     */
    setupVariants() {
        const variantElements = this.container.querySelectorAll('.color-variant');
        
        this.variants = Array.from(variantElements).map(element => ({
            element: element,
            id: element.dataset.productId,
            url: element.dataset.url,
            colorName: element.dataset.colorName,
            price: element.dataset.price,
            inStock: element.dataset.inStock === '1',
            isCurrent: element.classList.contains('selected')
        }));
        
        // Find currently selected variant
        this.selectedVariant = this.variants.find(variant => variant.isCurrent);
    }

    /**
     * Bind event listeners
     */
    bindEvents() {
        this.variants.forEach(variant => {
            // Click events
            variant.element.addEventListener('click', (e) => {
                e.preventDefault();
                this.selectVariant(variant);
            });

            // Touch events for mobile
            variant.element.addEventListener('touchstart', (e) => {
                if (!variant.inStock) {
                    e.preventDefault();
                }
            });

            // Mouse events for hover feedback
            variant.element.addEventListener('mouseenter', () => {
                this.showVariantPreview(variant);
            });

            variant.element.addEventListener('mouseleave', () => {
                this.hideVariantPreview();
            });
        });

        // Handle browser back/forward navigation
        window.addEventListener('popstate', () => {
            this.handleUrlChange();
        });
    }

    /**
     * Setup keyboard navigation
     */
    setupKeyboardNavigation() {
        this.variants.forEach((variant, index) => {
            variant.element.setAttribute('tabindex', '0');
            variant.element.setAttribute('role', 'button');
            
            variant.element.addEventListener('keydown', (e) => {
                this.handleKeyboardNavigation(e, index);
            });
        });
    }

    /**
     * Handle keyboard navigation
     */
    handleKeyboardNavigation(e, currentIndex) {
        let targetIndex = currentIndex;
        
        switch (e.key) {
            case 'ArrowRight':
            case 'ArrowDown':
                e.preventDefault();
                targetIndex = (currentIndex + 1) % this.variants.length;
                break;
            case 'ArrowLeft':
            case 'ArrowUp':
                e.preventDefault();
                targetIndex = currentIndex === 0 ? this.variants.length - 1 : currentIndex - 1;
                break;
            case 'Enter':
            case ' ':
                e.preventDefault();
                this.selectVariant(this.variants[currentIndex]);
                return;
            case 'Home':
                e.preventDefault();
                targetIndex = 0;
                break;
            case 'End':
                e.preventDefault();
                targetIndex = this.variants.length - 1;
                break;
        }
        
        if (targetIndex !== currentIndex) {
            this.variants[targetIndex].element.focus();
        }
    }

    /**
     * Select a color variant
     */
    selectVariant(variant) {
        if (!variant.inStock) {
            this.showError(__('This color is out of stock', 'thewalkingtheme'));
            return;
        }

        if (!variant.url || variant.url === window.location.href) {
            return;
        }

        window.location.href = variant.url;
    }

    /**
     * Update visual selection
     */
    updateSelection(newVariant) {
        // Clear previous selection
        this.variants.forEach(variant => {
            variant.element.classList.remove('selected');
            variant.isCurrent = false;
        });
        
        // Set new selection
        newVariant.element.classList.add('selected');
        newVariant.isCurrent = true;
        this.selectedVariant = newVariant;
        
        // Update selected color info
        this.updateSelectedColorInfo(newVariant);
        
        // Show selection feedback
        this.showSelectionFeedback(newVariant.colorName);
        
        // Fire selection event
        this.fireEvent('variantSelected', { variant: newVariant });
    }

    /**
     * Navigate to variant URL
     */
    navigateToVariant(variant) {
        if (variant.url && variant.url !== window.location.href) {
            // Use history API for smooth navigation
            if (this.shouldUseHistoryAPI()) {
                history.pushState(null, null, variant.url);
                this.loadVariantContent(variant);
            } else {
                // Fallback to direct navigation
                window.location.href = variant.url;
            }
        } else {
            this.setLoading(false);
        }
    }

    /**
     * Check if we should use History API for navigation
     */
    shouldUseHistoryAPI() {
        // Use History API if the theme supports AJAX loading
        return typeof window.eshopAjaxEnabled !== 'undefined' && window.eshopAjaxEnabled;
    }

    /**
     * Load variant content via AJAX
     */
    loadVariantContent(variant) {
        fetch(variant.url, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.text();
        })
        .then(html => {
            this.updatePageContent(html, variant);
            this.setLoading(false);
        })
        .catch(error => {
            console.error('Error loading variant:', error);
            // Fallback to direct navigation
            window.location.href = variant.url;
        });
    }

    /**
     * Update page content with new variant data
     */
    updatePageContent(html, variant) {
        const parser = new DOMParser();
        const newDoc = parser.parseFromString(html, 'text/html');
        
        // Update product gallery
        const newGallery = newDoc.querySelector('.product-gallery-container');
        const currentGallery = document.querySelector('.product-gallery-container');
        if (newGallery && currentGallery) {
            currentGallery.innerHTML = newGallery.innerHTML;
            // Reinitialize gallery if needed
            if (typeof EshopProductGallery !== 'undefined') {
                currentGallery.eshopGallery = new EshopProductGallery(currentGallery);
            }
        }
        
        // Update price
        const newPrice = newDoc.querySelector('.price');
        const currentPrice = document.querySelector('.price');
        if (newPrice && currentPrice) {
            currentPrice.innerHTML = newPrice.innerHTML;
        }
        
        // Update product title if it differs
        const newTitle = newDoc.querySelector('.product_title, .entry-title');
        const currentTitle = document.querySelector('.product_title, .entry-title');
        if (newTitle && currentTitle && newTitle.textContent !== currentTitle.textContent) {
            currentTitle.textContent = newTitle.textContent;
            document.title = newTitle.textContent;
        }
        
        // Update size selection if present
        const newSizeSelection = newDoc.querySelector('.size-selection-container');
        const currentSizeSelection = document.querySelector('.size-selection-container');
        if (newSizeSelection && currentSizeSelection) {
            currentSizeSelection.innerHTML = newSizeSelection.innerHTML;
            // Reinitialize size selection
            if (typeof EshopSizeSelection !== 'undefined') {
                currentSizeSelection.eshopSizeSelection = new EshopSizeSelection(currentSizeSelection);
            }
        }
        
        this.fireEvent('contentUpdated', { variant, html });
    }

    /**
     * Handle URL changes (back/forward navigation)
     */
    handleUrlChange() {
        const currentUrl = window.location.href;
        const matchingVariant = this.variants.find(variant => variant.url === currentUrl);
        
        if (matchingVariant && !matchingVariant.isCurrent) {
            this.updateSelection(matchingVariant);
        }
    }

    /**
     * Handle initial selection on page load
     */
    handleInitialSelection() {
        if (this.selectedVariant) {
            this.updateSelectedColorInfo(this.selectedVariant);
        }
    }

    /**
     * Show variant preview on hover
     */
    showVariantPreview(variant) {
        if (variant.isCurrent || !variant.inStock) {
            return;
        }
        
        // You could implement image preloading or price preview here
        this.fireEvent('variantPreview', { variant });
    }

    /**
     * Hide variant preview
     */
    hideVariantPreview() {
        this.fireEvent('variantPreviewHide');
    }

    /**
     * Update selected color information display
     */
    updateSelectedColorInfo(variant) {
        const selectedColorInfo = this.container.querySelector('.selected-color-info');
        if (!selectedColorInfo) return;
        
        const nameElement = selectedColorInfo.querySelector('.selected-color-name');
        const priceElement = selectedColorInfo.querySelector('.selected-color-price');
        
        if (nameElement) {
            nameElement.textContent = sprintf(__('Selected: %s', 'thewalkingtheme'), variant.colorName);
        }
        
        if (priceElement && variant.price) {
            priceElement.innerHTML = variant.price;
        }
    }

    /**
     * Show selection feedback
     */
    showSelectionFeedback(colorName) {
        const feedback = this.container.querySelector('.color-selection-feedback');
        if (!feedback) return;
        
        const feedbackText = feedback.querySelector('.feedback-text');
        if (feedbackText) {
            feedbackText.textContent = sprintf(__('Selected %s', 'thewalkingtheme'), colorName);
        }
        
        feedback.style.display = 'block';
        feedback.classList.add('selection-changed');
        
        // Hide feedback after delay
        setTimeout(() => {
            feedback.style.display = 'none';
            feedback.classList.remove('selection-changed');
        }, 3000);
    }

    /**
     * Show error message
     */
    showError(message) {
        const feedback = this.container.querySelector('.color-selection-feedback');
        if (!feedback) {
            alert(message);
            return;
        }
        
        const feedbackText = feedback.querySelector('.feedback-text');
        if (feedbackText) {
            feedbackText.textContent = message;
        }
        
        feedback.style.display = 'block';
        feedback.classList.add('error');
        
        setTimeout(() => {
            feedback.style.display = 'none';
            feedback.classList.remove('error');
        }, 5000);
        
        this.fireEvent('error', { message });
    }

    /**
     * Set loading state
     */
    setLoading(loading) {
        this.isLoading = loading;
        
        this.variants.forEach(variant => {
            variant.element.classList.toggle('loading', loading);
        });
        
        // Show/hide loading skeleton
        const loadingSkeleton = this.container.querySelector('.color-variants-loading');
        const variantsContainer = this.container.querySelector('.color-variants-container');
        
        if (loadingSkeleton && variantsContainer) {
            loadingSkeleton.style.display = loading ? 'flex' : 'none';
            variantsContainer.style.display = loading ? 'none' : 'flex';
        }
        
        this.fireEvent(loading ? 'loadingStart' : 'loadingEnd');
    }

    /**
     * Fire custom event
     */
    fireEvent(eventName, detail = {}) {
        const event = new CustomEvent(`eshop:colorVariants:${eventName}`, {
            detail: {
                colorVariants: this,
                productId: this.productId,
                selectedVariant: this.selectedVariant,
                ...detail
            },
            bubbles: true
        });
        this.container.dispatchEvent(event);
    }

    /**
     * Get selected variant
     */
    getSelectedVariant() {
        return this.selectedVariant;
    }

    /**
     * Get all variants
     */
    getVariants() {
        return this.variants;
    }

    /**
     * Refresh component
     */
    refresh() {
        this.setupVariants();
        this.fireEvent('refreshed');
    }

    /**
     * Destroy component
     */
    destroy() {
        // Remove event listeners
        this.variants.forEach(variant => {
            variant.element.removeAttribute('tabindex');
            variant.element.removeAttribute('role');
        });
        
        this.fireEvent('destroyed');
    }
}

/**
 * Utility functions
 */
function __(text, domain) {
    return window.wp && window.wp.i18n && window.wp.i18n.__ 
        ? window.wp.i18n.__(text, domain) 
        : text;
}

function sprintf(format, ...args) {
    return format.replace(/%s/g, () => args.shift() || '');
}

/**
 * Auto-initialize color variants
 */
document.addEventListener('DOMContentLoaded', function() {
    const colorVariantContainers = document.querySelectorAll('.product-color-variants');
    
    colorVariantContainers.forEach(container => {
        container.eshopColorVariants = new EshopColorVariants(container);
    });
});

/**
 * Export for module systems
 */
if (typeof module !== 'undefined' && module.exports) {
    module.exports = EshopColorVariants;
}

// Make available globally
window.EshopColorVariants = EshopColorVariants;
