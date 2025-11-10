/**
 * Product Archive Filters JavaScript
 * Handles filtering functionality matching the concept design
 */

(function() {
    'use strict';

    // DOM Elements
    const filterToggle = document.getElementById('filter-toggle');
    const filterModal = document.getElementById('filter-modal');
    const filterModalOverlay = document.getElementById('filter-modal-overlay');
    const filterModalClose = document.getElementById('filter-modal-close');
    const filterApply = document.getElementById('filter-apply');
    const filterClearAll = document.getElementById('filter-clear-all');
    const priceFilterApply = document.getElementById('price-filter-apply');
    const filterModalContent = filterModal?.querySelector('.filter-modal-content');

    // Filter state
    const filterState = {
        price: {
            min: '',
            max: ''
        },
        attributes: {},
        categories: [],
        sale: false,
        inStock: false
    };

    /**
     * Initialize filters
     */
    function initFilters() {
        if (!filterModal) return;

        // Parse current URL parameters
        parseURLParameters();
        
        // Event listeners
        setupEventListeners();
        
        // Update UI based on current filters
        updateFilterUI();
    }

    /**
     * Parse URL parameters and update filter state
     */
    function parseURLParameters() {
        const urlParams = new URLSearchParams(window.location.search);
        
        // Price parameters
        filterState.price.min = urlParams.get('min_price') || '';
        filterState.price.max = urlParams.get('max_price') || '';
        
        // Sale parameter
        filterState.sale = urlParams.get('on_sale') === '1';
        
        // Stock parameter
        filterState.inStock = urlParams.get('stock_status') === 'instock';
        
        // Attribute parameters
        const attributeTaxonomies = getAttributeTaxonomies();
        attributeTaxonomies.forEach(taxonomy => {
            const values = urlParams.getAll(taxonomy + '[]');
            filterState.attributes[taxonomy] = values;
        });
    }

    /**
     * Get all product attribute taxonomies
     */
    function getAttributeTaxonomies() {
        const taxonomies = [];
        const checkboxes = filterModalContent?.querySelectorAll('input[type="checkbox"][name^="pa_"]') || [];
        checkboxes.forEach(checkbox => {
            const taxonomy = checkbox.name;
            if (!taxonomies.includes(taxonomy)) {
                taxonomies.push(taxonomy);
            }
        });
        return taxonomies;
    }

    /**
     * Setup event listeners
     */
    function setupEventListeners() {
        // Modal controls
        if (filterToggle) {
            filterToggle.addEventListener('click', openFilterModal);
        }
        
        if (filterModalClose) {
            filterModalClose.addEventListener('click', closeFilterModal);
        }
        
        if (filterModalOverlay) {
            filterModalOverlay.addEventListener('click', closeFilterModal);
        }
        
        // Filter controls
        if (filterApply) {
            filterApply.addEventListener('click', applyFilters);
        }
        
        if (filterClearAll) {
            filterClearAll.addEventListener('click', clearAllFilters);
        }
        
        if (priceFilterApply) {
            priceFilterApply.addEventListener('click', applyPriceFilter);
        }
        
        // Checkbox/radio change handlers
        const filterOptions = filterModalContent?.querySelectorAll('input[type="checkbox"], input[type="radio"]');
        filterOptions?.forEach(option => {
            option.addEventListener('change', handleFilterChange);
        });
        
        // Price input handlers
        const minPriceInput = document.getElementById('filter-min-price');
        const maxPriceInput = document.getElementById('filter-max-price');
        
        if (minPriceInput) {
            minPriceInput.addEventListener('input', handlePriceInput);
        }
        
        if (maxPriceInput) {
            maxPriceInput.addEventListener('input', handlePriceInput);
        }
        
        // Close modal on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && filterModal?.classList.contains('active')) {
                closeFilterModal();
            }
        });
        
        // Active filters event delegation
        document.addEventListener('click', function(e) {
            // Remove individual filter chip
            if (e.target.classList.contains('filter-chip')) {
                e.preventDefault();
                const param = e.target.dataset.param;
                if (param) {
                    removeFilter(param);
                }
            }
            
            // Clear all filters button
            if (e.target.id === 'clear-all-filters') {
                e.preventDefault();
                clearAllFilters();
            }
        });
        
        // Handle browser back/forward
        window.addEventListener('popstate', function() {
            parseURLParameters();
            updateFilterUI();
        });
    }

    /**
     * Open filter modal
     */
    function openFilterModal() {
        if (filterModal && filterModalOverlay) {
            filterModal.classList.add('active');
            filterModalOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
            
            // Focus management
            filterModalClose?.focus();
        }
    }

    /**
     * Close filter modal
     */
    function closeFilterModal() {
        if (filterModal && filterModalOverlay) {
            filterModal.classList.remove('active');
            filterModalOverlay.classList.remove('active');
            document.body.style.overflow = '';
        }
    }

    /**
     * Handle filter change (checkbox/radio)
     */
    function handleFilterChange(e) {
        const input = e.target;
        const name = input.name;
        const value = input.value;
        const checked = input.checked;
        
        if (name === 'on_sale') {
            filterState.sale = checked;
        } else if (name === 'stock_status') {
            filterState.inStock = checked;
        } else if (name === 'category') {
            // Radio button for category
            filterState.categories = checked ? [value] : [];
        } else if (name.startsWith('pa_')) {
            // Product attribute
            if (!filterState.attributes[name]) {
                filterState.attributes[name] = [];
            }
            
            if (checked) {
                if (!filterState.attributes[name].includes(value)) {
                    filterState.attributes[name].push(value);
                }
            } else {
                filterState.attributes[name] = filterState.attributes[name].filter(v => v !== value);
                if (filterState.attributes[name].length === 0) {
                    delete filterState.attributes[name];
                }
            }
        }
    }

    /**
     * Handle price input change
     */
    function handlePriceInput() {
        const minPrice = document.getElementById('filter-min-price')?.value || '';
        const maxPrice = document.getElementById('filter-max-price')?.value || '';
        
        filterState.price.min = minPrice;
        filterState.price.max = maxPrice;
    }

    /**
     * Apply price filter only
     */
    function applyPriceFilter() {
        const currentURL = new URL(window.location);
        
        // Remove existing price parameters
        currentURL.searchParams.delete('min_price');
        currentURL.searchParams.delete('max_price');
        
        // Add new price parameters
        if (filterState.price.min) {
            currentURL.searchParams.set('min_price', filterState.price.min);
        }
        if (filterState.price.max) {
            currentURL.searchParams.set('max_price', filterState.price.max);
        }
        
        // Navigate to new URL
        window.location.href = currentURL.toString();
    }

    /**
     * Apply all filters
     */
    function applyFilters() {
        const currentURL = new URL(window.location);
        
        // Clear existing filter parameters
        const paramsToKeep = ['orderby', 'order']; // Keep sorting parameters
        
        // Remove all filter-related parameters
        for (const [key] of currentURL.searchParams.entries()) {
            if (!paramsToKeep.includes(key) && !key.startsWith('paged')) {
                currentURL.searchParams.delete(key);
            }
        }
        
        // Add price filters
        if (filterState.price.min) {
            currentURL.searchParams.set('min_price', filterState.price.min);
        }
        if (filterState.price.max) {
            currentURL.searchParams.set('max_price', filterState.price.max);
        }
        
        // Add sale filter
        if (filterState.sale) {
            currentURL.searchParams.set('on_sale', '1');
        }
        
        // Add stock filter
        if (filterState.inStock) {
            currentURL.searchParams.set('stock_status', 'instock');
        }
        
        // Add category filter
        if (filterState.categories.length > 0) {
            currentURL.searchParams.set('product_cat', filterState.categories[0]);
        }
        
        // Add attribute filters
        Object.entries(filterState.attributes).forEach(([taxonomy, values]) => {
            values.forEach(value => {
                currentURL.searchParams.append(taxonomy + '[]', value);
            });
        });
        
        // Navigate to new URL
        window.location.href = currentURL.toString();
    }

    /**
     * Remove individual filter
     */
    function removeFilter(param) {
        const currentURL = new URL(window.location);
        
        // Parse the parameter to determine what to remove
        if (param.startsWith('cat-')) {
            // Remove category filter
            const catId = param.replace('cat-', '');
            currentURL.searchParams.delete('product_cat');
        } else if (param === 'price') {
            // Remove price filters
            currentURL.searchParams.delete('min_price');
            currentURL.searchParams.delete('max_price');
        } else if (param === 'on_sale') {
            // Remove sale filter
            currentURL.searchParams.delete('on_sale');
        } else if (param.startsWith('pa_')) {
            // Remove attribute filter
            const [taxonomy, value] = param.split('-');
            // Remove specific attribute value (would need to handle array values)
            const values = currentURL.searchParams.getAll(taxonomy + '[]');
            const newValues = values.filter(v => v !== value);
            currentURL.searchParams.delete(taxonomy + '[]');
            newValues.forEach(v => {
                currentURL.searchParams.append(taxonomy + '[]', v);
            });
        }
        
        // Navigate to updated URL
        window.location.href = currentURL.toString();
    }

    /**
     * Clear all filters
     */
    function clearAllFilters() {
        const currentURL = new URL(window.location);
        
        // Get base URL without query parameters
        const baseURL = currentURL.origin + currentURL.pathname;
        
        // Navigate to clean URL
        window.location.href = baseURL;
    }

    /**
     * Update filter UI based on current state
     */
    function updateFilterUI() {
        // Update price inputs
        const minPriceInput = document.getElementById('filter-min-price');
        const maxPriceInput = document.getElementById('filter-max-price');
        
        if (minPriceInput) {
            minPriceInput.value = filterState.price.min;
        }
        
        if (maxPriceInput) {
            maxPriceInput.value = filterState.price.max;
        }
        
        // Update checkboxes
        const checkboxes = filterModalContent?.querySelectorAll('input[type="checkbox"]');
        checkboxes?.forEach(checkbox => {
            const name = checkbox.name;
            const value = checkbox.value;
            
            if (name === 'on_sale') {
                checkbox.checked = filterState.sale;
            } else if (name === 'stock_status') {
                checkbox.checked = filterState.inStock;
            } else if (filterState.attributes[name]) {
                checkbox.checked = filterState.attributes[name].includes(value);
            }
        });
        
        // Update radio buttons
        const radioButtons = filterModalContent?.querySelectorAll('input[type="radio"][name="category"]');
        radioButtons?.forEach(radio => {
            radio.checked = filterState.categories.includes(radio.value);
        });
    }

    /**
     * Show loading state
     */
    function showLoading() {
        if (filterModal) {
            filterModal.classList.add('loading');
        }
    }

    /**
     * Hide loading state
     */
    function hideLoading() {
        if (filterModal) {
            filterModal.classList.remove('loading');
        }
    }

    /**
     * Debounce function for performance
     */
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }

    // Auto-initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initFilters);
    } else {
        initFilters();
    }

    // Handle page load
    window.addEventListener('load', function() {
        hideLoading();
    });

    // Public API for external use
    window.ProductArchiveFilters = {
        open: openFilterModal,
        close: closeFilterModal,
        apply: applyFilters,
        clear: clearAllFilters,
        getState: () => ({...filterState})
    };

})();