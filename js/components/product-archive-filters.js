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

        filterState.price.min = urlParams.get('min_price') || '';
        filterState.price.max = urlParams.get('max_price') || '';
        filterState.sale = urlParams.get('on_sale') === '1';
        filterState.inStock = urlParams.get('stock_status') === 'instock';

        const attributeTaxonomies = getAttributeTaxonomies();
        attributeTaxonomies.forEach(taxonomy => {
            let collected = [];
            // Support array-style (taxonomy[]) and comma-delimited (taxonomy="a,b") formats
            const arrayValues = urlParams.getAll(taxonomy + '[]');
            if (arrayValues && arrayValues.length) {
                collected = arrayValues.map(v => v.trim()).filter(Boolean);
            } else if (urlParams.has(taxonomy)) {
                collected = urlParams.get(taxonomy).split(',').map(v => v.trim()).filter(Boolean);
            }
            if (collected.length) {
                filterState.attributes[taxonomy] = collected;
            }
        });

        // Category (single slug): support comma-delimited list (take first)
        if (urlParams.has('product_cat')) {
            const rawCat = urlParams.get('product_cat');
            filterState.categories = rawCat ? [rawCat.split(',')[0]] : [];
        }
    }

    /**
     * Get all product attribute taxonomies
     */
    function getAttributeTaxonomies() {
        const taxonomies = [];
        const checkboxes = filterModalContent?.querySelectorAll('input[type="checkbox"][name^="pa_"]') || [];
        checkboxes.forEach(cb => {
            const raw = cb.name; // e.g. pa_color[]
            const base = raw.replace(/\[\]$/, '');
            if (!taxonomies.includes(base)) taxonomies.push(base);
        });
        return taxonomies;
    }

    /**
     * Setup event listeners
     */
    function setupEventListeners() {
        // Modal controls
        if (filterToggle) filterToggle.addEventListener('click', openFilterModal);
        const filterToggleDesktop = document.getElementById('filter-toggle-desktop');
        if (filterToggleDesktop) filterToggleDesktop.addEventListener('click', openFilterModal);
        
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
            // Remove individual filter chip (use closest to handle inner spans)
            const chip = e.target.closest('.filter-chip');
            if (chip) {
                e.preventDefault();
                const param = chip.dataset.param;
                if (param) removeFilter(param);
                return;
            }

            // Clear all filters button (handle clicks on inner elements)
            const clearBtn = e.target.closest('#clear-all-filters');
            if (clearBtn) {
                e.preventDefault();
                clearAllFilters();
            }
        });

        // Direct listener as well if the button exists
        const clearAll = document.getElementById('clear-all-filters');
        if (clearAll) clearAll.addEventListener('click', function(e){ e.preventDefault(); clearAllFilters(); });
        
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

        // Preserve non-filter params (orderby, order) and clear others
        const preserve = ['orderby', 'order'];
        [...currentURL.searchParams.keys()].forEach(key => {
            if (!preserve.includes(key)) {
                currentURL.searchParams.delete(key);
            }
        });

        if (filterState.price.min) currentURL.searchParams.set('min_price', filterState.price.min);
        if (filterState.price.max) currentURL.searchParams.set('max_price', filterState.price.max);
        if (filterState.sale) currentURL.searchParams.set('on_sale', '1');
        if (filterState.inStock) currentURL.searchParams.set('stock_status', 'instock');
        if (filterState.categories.length) currentURL.searchParams.set('product_cat', filterState.categories[0]);

        // Attribute filters: write as comma-delimited list (Woo parsing expects this format)
        Object.entries(filterState.attributes).forEach(([taxonomy, values]) => {
            if (values.length) {
                currentURL.searchParams.set(taxonomy, values.join(','));
            }
        });

        window.location.href = currentURL.toString();
    }

    /**
     * Remove individual filter
     */
    function removeFilter(param) {
        const currentURL = new URL(window.location);

        if (param === 'price') {
            currentURL.searchParams.delete('min_price');
            currentURL.searchParams.delete('max_price');
        } else if (param === 'on_sale') {
            currentURL.searchParams.delete('on_sale');
        } else if (param.startsWith('cat-')) {
            currentURL.searchParams.delete('product_cat');
        } else if (param.startsWith('pa_')) {
            const [taxonomy, value] = param.split('-');
            if (currentURL.searchParams.has(taxonomy)) {
                const raw = currentURL.searchParams.get(taxonomy);
                let list = raw.split(',').map(v => v.trim()).filter(Boolean);
                list = list.filter(v => v !== value);
                currentURL.searchParams.delete(taxonomy);
                if (list.length) {
                    currentURL.searchParams.set(taxonomy, list.join(','));
                }
            } else {
                // Fallback array-style removal
                const all = currentURL.searchParams.getAll(taxonomy + '[]');
                const remaining = all.filter(v => v !== value);
                currentURL.searchParams.delete(taxonomy + '[]');
                if (remaining.length) {
                    remaining.forEach(v => currentURL.searchParams.append(taxonomy + '[]', v));
                }
            }
        } else if (param === 'stock_status') {
            currentURL.searchParams.delete('stock_status');
        }

        window.location.href = currentURL.toString();
    }

    /**
     * Clear all filters
     */
    function clearAllFilters() {
        // If we're in a taxonomy archive (subcategory etc.), redirect to main shop page.
        const shopPage = window.eshop_ajax && window.eshop_ajax.shop_url ? window.eshop_ajax.shop_url : null;
        const body = document.body;
        const inTaxArchive = body.classList.contains('tax-product_cat') || body.classList.contains('tax-product_tag');

        if (inTaxArchive && shopPage) {
            window.location.href = shopPage;
            return;
        }

        const currentURL = new URL(window.location.href);
        // Remove known filter params
        ['min_price','max_price','on_sale','stock_status','product_cat'].forEach(p=>currentURL.searchParams.delete(p));
        // Remove all attribute params (pa_*) including array-style
        [...currentURL.searchParams.keys()].forEach(key => {
            if (key.startsWith('pa_')) currentURL.searchParams.delete(key);
        });
        // Also remove array-style taxonomy params
        // Build a clean URL without query
        const baseURL = currentURL.origin + currentURL.pathname;
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
        const checkboxes = filterModalContent?.querySelectorAll('input[type="checkbox"][name^="pa_"], input[type="checkbox"][name="on_sale"], input[type="checkbox"][name="stock_status"]');
        checkboxes?.forEach(checkbox => {
            const rawName = checkbox.name;
            const baseName = rawName.replace(/\[\]$/, '');
            const value = checkbox.value;
            if (rawName === 'on_sale') {
                checkbox.checked = filterState.sale;
            } else if (rawName === 'stock_status') {
                checkbox.checked = filterState.inStock;
            } else if (filterState.attributes[baseName]) {
                checkbox.checked = filterState.attributes[baseName].includes(value);
            } else {
                checkbox.checked = false;
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