/**
 * Size Selection Component - 2025 Standards
 * 
 * Handles size attribute selection with circular button interface,
 * supports both shoe sizes (numeric) and clothing sizes (text abbreviations)
 *
 * @package thewalkingtheme
 */

class EshopSizeSelection {
    constructor(container) {
        this.container = container;
        this.productId = container.dataset.productId;
        this.attributeType = container.dataset.attributeType || 'auto';
        this.selectedSize = null;
        this.sizeOptions = [];
        this.variationForm = null;
        
        this.init();
    }

    /**
     * Initialize the size selection component
     */
    init() {
        this.setupSizeOptions();
        this.bindEvents();
        this.detectAttributeType();
        this.setupVariationIntegration();
        this.handleInitialSelection();
    }

    /**
     * Setup size options data
     */
    setupSizeOptions() {
        const sizeElements = this.container.querySelectorAll('.size-option');
        this.sizeOptions = Array.from(sizeElements).map(element => ({
            element: element,
            value: element.dataset.size,
            name: element.dataset.fullSize || element.textContent.trim(),
            variationId: element.dataset.variationId,
            inStock: element.dataset.inStock === '1',
            isSelected: element.classList.contains('selected')
        }));

        // Set initial selected size if any
        const selected = this.sizeOptions.find(option => option.isSelected);
        if (selected) {
            this.selectedSize = selected.value;
        }
    }

    /**
     * Detect attribute type (shoe vs clothing)
     */
    detectAttributeType() {
        if (this.attributeType !== 'auto') return;

        // Auto-detect based on size values
        const hasNumericSizes = this.sizeOptions.some(option => 
            /^\d+(\.\d+)?$/.test(option.value)
        );

        const hasClothingSizes = this.sizeOptions.some(option => 
            /^(XS|S|M|L|XL|XXL|Extra Small|Small|Medium|Large|Extra Large|Double Extra Large)$/i.test(option.name)
        );

        if (hasNumericSizes) {
            this.attributeType = 'shoe';
        } else if (hasClothingSizes) {
            this.attributeType = 'clothing';
        } else {
            this.attributeType = 'general';
        }

        // Update container attribute
        this.container.setAttribute('data-attribute-type', this.attributeType);
        
        // Apply specific styling
        this.applySizeTypeStyles();
    }

    /**
     * Apply size type specific styles
     */
    applySizeTypeStyles() {
        this.sizeOptions.forEach(option => {
            const element = option.element;
            
            // Remove existing type classes
            element.classList.remove('shoe-size', 'clothing-size', 'general-size');
            
            // Add appropriate class
            element.classList.add(`${this.attributeType}-size`);
            
            // Handle clothing size abbreviations
            if (this.attributeType === 'clothing') {
                const abbreviation = this.getClothingSizeAbbreviation(option.name);
                if (abbreviation && abbreviation !== option.name) {
                    element.textContent = abbreviation;
                    element.setAttribute('title', option.name);
                }
            }
        });
    }

    /**
     * Get clothing size abbreviation
     */
    getClothingSizeAbbreviation(sizeName) {
        const abbreviations = {
            'Extra Small': 'XS',
            'Small': 'S',
            'Medium': 'M',
            'Large': 'L',
            'Extra Large': 'XL',
            'Double Extra Large': 'XXL',
            'Triple Extra Large': 'XXXL'
        };

        return abbreviations[sizeName] || sizeName;
    }

    /**
     * Bind event listeners
     */
    bindEvents() {
        // Size option clicks
        this.sizeOptions.forEach(option => {
            option.element.addEventListener('click', (e) => {
                e.preventDefault();
                this.selectSize(option.value);
            });

            // Keyboard navigation
            option.element.addEventListener('keydown', (e) => {
                this.handleKeyboardNavigation(e, option);
            });
        });

        // Size guide link if present
        const sizeGuideLink = this.container.querySelector('.size-guide-link');
        if (sizeGuideLink) {
            sizeGuideLink.addEventListener('click', (e) => {
                e.preventDefault();
                this.showSizeGuide();
            });
        }

        // Size chart trigger if present
        const sizeChartTrigger = this.container.querySelector('.size-chart-trigger');
        if (sizeChartTrigger) {
            sizeChartTrigger.addEventListener('click', (e) => {
                e.preventDefault();
                this.showSizeChart();
            });
        }
    }

    /**
     * Handle keyboard navigation
     */
    handleKeyboardNavigation(e, currentOption) {
        const currentIndex = this.sizeOptions.findIndex(opt => opt === currentOption);
        let targetIndex = currentIndex;

        switch (e.key) {
            case 'ArrowRight':
            case 'ArrowDown':
                e.preventDefault();
                targetIndex = (currentIndex + 1) % this.sizeOptions.length;
                break;
            case 'ArrowLeft':
            case 'ArrowUp':
                e.preventDefault();
                targetIndex = currentIndex === 0 ? this.sizeOptions.length - 1 : currentIndex - 1;
                break;
            case 'Enter':
            case ' ':
                e.preventDefault();
                this.selectSize(currentOption.value);
                return;
        }

        // Focus next/previous option
        if (targetIndex !== currentIndex) {
            this.sizeOptions[targetIndex].element.focus();
        }
    }

    /**
     * Select a size option
     */
    selectSize(sizeValue) {
        const option = this.sizeOptions.find(opt => opt.value === sizeValue);
        
        if (!option || !option.inStock) {
            this.showError(__('This size is not available', 'thewalkingtheme'));
            return;
        }

        // Clear previous selection
        this.clearSelection();

        // Set new selection
        this.selectedSize = sizeValue;
        option.element.classList.add('selected', 'animate');
        option.isSelected = true;

        // Remove animation class after animation completes
        setTimeout(() => {
            option.element.classList.remove('animate');
        }, 300);

        // Update label
        this.updateSelectedLabel(option.name);

        // Update variation if integrated
        this.updateVariation(sizeValue);

        // Show success feedback
        this.showSuccess();

        // Fire custom event
        this.fireEvent('sizeSelected', {
            size: sizeValue,
            sizeName: option.name,
            variationId: option.variationId
        });

        // Hide any error messages
        this.hideError();
    }

    /**
     * Clear current selection
     */
    clearSelection() {
        this.sizeOptions.forEach(option => {
            option.element.classList.remove('selected');
            option.isSelected = false;
        });
        this.selectedSize = null;
    }

    /**
     * Update selected size label
     */
    updateSelectedLabel(sizeName) {
        const label = this.container.querySelector('.size-selection-label');
        const selectedSpan = label?.querySelector('.selected-size');
        
        if (selectedSpan) {
            selectedSpan.textContent = sizeName;
        }
    }

    /**
     * Setup WooCommerce variation integration
     */
    setupVariationIntegration() {
        // Find the closest variation form
        this.variationForm = this.container.closest('form.variations_form');
        
        if (!this.variationForm) {
            // Try to find it in the same product section
            const productSection = this.container.closest('.single-product-layout, #product-' + this.productId);
            if (productSection) {
                this.variationForm = productSection.querySelector('form.variations_form');
            }
        }

        if (this.variationForm) {
            // Listen for variation changes from WooCommerce
            jQuery(this.variationForm).on('woocommerce_variation_has_changed', () => {
                this.syncWithVariationForm();
            });
        }
    }

    /**
     * Update WooCommerce variation selection
     */
    updateVariation(sizeValue) {
        if (!this.variationForm) return;

        // Find the size attribute select
        const attributeSelects = this.variationForm.querySelectorAll('select[name^="attribute_"]');
        
        for (let select of attributeSelects) {
            const attributeName = select.name;
            // Check if this is a size attribute
            if (attributeName.includes('size') || attributeName.includes('Size')) {
                select.value = sizeValue;
                // Trigger change event
                const event = new Event('change', { bubbles: true });
                select.dispatchEvent(event);
                break;
            }
        }
    }

    /**
     * Sync with WooCommerce variation form
     */
    syncWithVariationForm() {
        if (!this.variationForm) return;

        const attributeSelects = this.variationForm.querySelectorAll('select[name^=\"attribute_\"]');
        
        for (let select of attributeSelects) {
            if (select.name.includes('size') || select.name.includes('Size')) {
                const selectedValue = select.value;
                if (selectedValue && selectedValue !== this.selectedSize) {
                    // Update our component to match variation form
                    this.selectSize(selectedValue);
                }
                break;
            }
        }
    }

    /**
     * Handle initial selection from URL or variation form
     */
    handleInitialSelection() {
        // Check if there's a pre-selected size in the variation form
        if (this.variationForm) {
            const attributeSelects = this.variationForm.querySelectorAll('select[name^=\"attribute_\"]');
            
            for (let select of attributeSelects) {
                if (select.name.includes('size') || select.name.includes('Size')) {
                    const selectedValue = select.value;
                    if (selectedValue) {
                        this.selectSize(selectedValue);
                        return;
                    }
                }
            }
        }

        // Check URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        const sizeFromUrl = urlParams.get('size') || urlParams.get('attribute_pa_size-selection') || urlParams.get('attribute_pa_size');
        
        if (sizeFromUrl) {
            this.selectSize(sizeFromUrl);
        }
    }

    /**
     * Show size guide modal/popup
     */
    showSizeGuide() {
        // This can be customized to show a modal, popup, or navigate to a size guide page
        const sizeGuideUrl = this.container.dataset.sizeGuideUrl;
        
        if (sizeGuideUrl) {
            window.open(sizeGuideUrl, 'sizeGuide', 'width=800,height=600,scrollbars=yes');
        } else {
            // Show size information if available
            const sizeInfo = this.container.querySelector('.size-info');
            if (sizeInfo) {
                sizeInfo.classList.toggle('show');
            } else {
                alert(__('Size guide information is not available for this product.', 'thewalkingtheme'));
            }
        }

        this.fireEvent('sizeGuideOpened');
    }

    /**
     * Show size chart
     */
    showSizeChart() {
        // Fire event for external size chart implementations
        this.fireEvent('sizeChartRequested');
        
        // Default behavior - could be overridden by theme customizations
        console.log('Size chart requested for product:', this.productId);
    }

    /**
     * Show error message
     */
    showError(message) {
        this.container.classList.add('error');
        const errorElement = this.container.querySelector('.size-selection-error');
        if (errorElement) {
            errorElement.textContent = message;
        }
        
        this.fireEvent('error', { message });
    }

    /**
     * Hide error message
     */
    hideError() {
        this.container.classList.remove('error');
        const errorElement = this.container.querySelector('.size-selection-error');
        if (errorElement) {
            errorElement.textContent = '';
        }
    }

    /**
     * Show success feedback
     */
    showSuccess() {
        this.container.classList.add('success');
        
        // Auto-hide success state
        setTimeout(() => {
            this.container.classList.remove('success');
        }, 2000);
    }

    /**
     * Fire custom event
     */
    fireEvent(eventName, detail = {}) {
        const event = new CustomEvent(`eshop:size:${eventName}`, {
            detail: {
                sizeSelection: this,
                productId: this.productId,
                selectedSize: this.selectedSize,
                ...detail
            },
            bubbles: true
        });
        this.container.dispatchEvent(event);
    }

    /**
     * Get selected size value
     */
    getSelectedSize() {
        return this.selectedSize;
    }

    /**
     * Get selected size option object
     */
    getSelectedOption() {
        return this.sizeOptions.find(option => option.isSelected);
    }

    /**
     * Get all available sizes
     */
    getAvailableSizes() {
        return this.sizeOptions.filter(option => option.inStock);
    }

    /**
     * Update stock status for sizes
     */
    updateStock(stockData) {
        this.sizeOptions.forEach(option => {
            const stockInfo = stockData[option.value];
            if (stockInfo !== undefined) {
                option.inStock = stockInfo;
                option.element.classList.toggle('out-of-stock', !stockInfo);

                // If currently selected size is now out of stock, clear selection
                if (!stockInfo && option.isSelected) {
                    this.clearSelection();
                }
            }
        });

        this.fireEvent('stockUpdated', { stockData });
    }

    /**
     * Refresh the component
     */
    refresh() {
        this.setupSizeOptions();
        this.detectAttributeType();
        this.fireEvent('refreshed');
    }

    /**
     * Destroy the component
     */
    destroy() {
        // Clean up event listeners
        // Modern browsers handle this automatically when elements are removed
        this.fireEvent('destroyed');
    }
}

/**
 * Utility function for translations (fallback)
 */
function __(text, domain) {
    // Simple fallback for translation function
    return window.wp && window.wp.i18n && window.wp.i18n.__
        ? window.wp.i18n.__(text, domain)
        : text;
}

/**
 * Auto-initialize size selection components
 */
document.addEventListener('DOMContentLoaded', function() {
    const sizeContainers = document.querySelectorAll('.size-selection-container');

    sizeContainers.forEach(container => {
        // Store instance on element for external access
        container.eshopSizeSelection = new EshopSizeSelection(container);
    });
});

/**
 * Export for module systems
 */
if (typeof module !== 'undefined' && module.exports) {
    module.exports = EshopSizeSelection;
}

// Make available globally
window.EshopSizeSelection = EshopSizeSelection;