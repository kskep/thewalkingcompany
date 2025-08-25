/**
 * Simple Filter Component - WordPress Compatible
 * Minimal implementation to ensure functionality works
 */

(function($) {
    'use strict';
    
    console.log('Simple filter script loaded');
    
    // Ensure we have jQuery
    if (typeof $ === 'undefined') {
        console.error('jQuery not available');
        return;
    }
    
    // Simple filter object
    window.SimpleFilters = {
        init: function() {
            console.log('SimpleFilters: Initializing...');
            this.bindEvents();
            this.populateFiltersFromURL();
        },
        
        bindEvents: function() {
            console.log('SimpleFilters: Binding events...');
            
            // Remove any existing handlers to prevent duplicates
            $('#open-filters').off('click.simple');
            $('#close-filters, #filter-backdrop').off('click.simple');
            
            // Open filter
            $('#open-filters').on('click.simple', function(e) {
                e.preventDefault();
                console.log('SimpleFilters: Opening drawer');

                var $backdrop = $('#filter-backdrop');
                var $drawer = $('#filter-drawer');

                console.log('SimpleFilters: Backdrop found:', $backdrop.length);
                console.log('SimpleFilters: Drawer found:', $drawer.length);

                if ($backdrop.length && $drawer.length) {
                    $backdrop.removeClass('hidden').addClass('show');
                    $drawer.addClass('open');
                    $('body').addClass('overflow-hidden');
                    console.log('SimpleFilters: Filter drawer opened successfully');
                } else {
                    console.error('SimpleFilters: Filter elements not found!');
                }
            });
            
            // Close filter
            $('#close-filters, #filter-backdrop').on('click.simple', function(e) {
                e.preventDefault();
                console.log('SimpleFilters: Closing drawer');
                
                $('#filter-backdrop').removeClass('show').addClass('hidden');
                $('#filter-drawer').removeClass('open');
                $('body').removeClass('overflow-hidden');
            });
            
            // Escape key
            $(document).on('keydown.simple', function(e) {
                if (e.key === 'Escape' && $('#filter-drawer').hasClass('open')) {
                    $('#filter-backdrop').removeClass('show').addClass('hidden');
                    $('#filter-drawer').removeClass('open');
                    $('body').removeClass('overflow-hidden');
                }
            });

            // Apply filters button
            $('#apply-filters').on('click.simple', function(e) {
                e.preventDefault();
                console.log('SimpleFilters: Applying filters');
                window.SimpleFilters.applyFilters();
            });

            // Clear filters button
            $('#clear-filters').on('click.simple', function(e) {
                e.preventDefault();
                console.log('SimpleFilters: Clearing filters');
                window.SimpleFilters.clearFilters();
            });

            // Price filter apply button
            $('.apply-price-filter').on('click.simple', function(e) {
                e.preventDefault();
                console.log('SimpleFilters: Applying price filter');
                window.SimpleFilters.applyFilters();
            });

            console.log('SimpleFilters: Events bound successfully');
        },

        // Apply filters function
        applyFilters: function() {
            console.log('SimpleFilters: Collecting filter data...');

            var filters = {};
            var url = new URL(window.location.href);

            // Price filters
            var minPrice = $('#min-price').val();
            var maxPrice = $('#max-price').val();
            if (minPrice) {
                url.searchParams.set('min_price', minPrice);
                filters.min_price = minPrice;
            } else {
                url.searchParams.delete('min_price');
            }
            if (maxPrice) {
                url.searchParams.set('max_price', maxPrice);
                filters.max_price = maxPrice;
            } else {
                url.searchParams.delete('max_price');
            }

            // Category filters
            var selectedCategories = [];
            $('input[name="product_cat[]"]:checked').each(function() {
                selectedCategories.push($(this).val());
            });
            if (selectedCategories.length > 0) {
                url.searchParams.set('product_cat', selectedCategories.join(','));
                filters.product_cat = selectedCategories;
            } else {
                url.searchParams.delete('product_cat');
            }

            // On sale filter
            if ($('input[name="on_sale"]:checked').length > 0) {
                url.searchParams.set('on_sale', '1');
                filters.on_sale = '1';
            } else {
                url.searchParams.delete('on_sale');
            }

            // Stock status filters
            var selectedStock = [];
            $('input[name="stock_status[]"]:checked').each(function() {
                selectedStock.push($(this).val());
            });
            if (selectedStock.length > 0) {
                url.searchParams.set('stock_status', selectedStock.join(','));
                filters.stock_status = selectedStock;
            } else {
                url.searchParams.delete('stock_status');
            }

            console.log('SimpleFilters: Collected filters:', filters);

            // Close the drawer
            this.closeDrawer();

            // Redirect to filtered URL
            console.log('SimpleFilters: Redirecting to:', url.toString());
            window.location.href = url.toString();
        },

        // Clear all filters
        clearFilters: function() {
            console.log('SimpleFilters: Clearing all filters');

            // Clear form inputs
            $('#min-price, #max-price').val('');
            $('input[name="product_cat[]"], input[name="on_sale"], input[name="stock_status[]"]').prop('checked', false);

            // Redirect to clean URL
            var url = new URL(window.location.href);
            url.searchParams.delete('min_price');
            url.searchParams.delete('max_price');
            url.searchParams.delete('product_cat');
            url.searchParams.delete('on_sale');
            url.searchParams.delete('stock_status');

            console.log('SimpleFilters: Redirecting to clean URL:', url.toString());
            window.location.href = url.toString();
        },

        // Close drawer function
        closeDrawer: function() {
            $('#filter-backdrop').removeClass('show').addClass('hidden');
            $('#filter-drawer').removeClass('open');
            $('body').removeClass('overflow-hidden');
        },

        // Populate filters from URL parameters
        populateFiltersFromURL: function() {
            console.log('SimpleFilters: Populating filters from URL...');

            var url = new URL(window.location.href);

            // Price filters
            var minPrice = url.searchParams.get('min_price');
            var maxPrice = url.searchParams.get('max_price');
            if (minPrice) {
                $('#min-price').val(minPrice);
            }
            if (maxPrice) {
                $('#max-price').val(maxPrice);
            }

            // Category filters
            var productCat = url.searchParams.get('product_cat');
            if (productCat) {
                var categories = productCat.split(',');
                categories.forEach(function(cat) {
                    $('input[name="product_cat[]"][value="' + cat + '"]').prop('checked', true);
                });
            }

            // On sale filter
            var onSale = url.searchParams.get('on_sale');
            if (onSale === '1') {
                $('input[name="on_sale"]').prop('checked', true);
            }

            // Stock status filters
            var stockStatus = url.searchParams.get('stock_status');
            if (stockStatus) {
                var statuses = stockStatus.split(',');
                statuses.forEach(function(status) {
                    $('input[name="stock_status[]"][value="' + status + '"]').prop('checked', true);
                });
            }

            console.log('SimpleFilters: Filters populated from URL');
        }
    };
    
    // Initialize when document is ready
    $(document).ready(function() {
        console.log('SimpleFilters: Document ready');
        
        // Check if we're on the right page
        if ($('.shop-layout').length > 0 || $('body').hasClass('woocommerce')) {
            console.log('SimpleFilters: Shop page detected');
            
            // Wait a bit for elements to be ready
            setTimeout(function() {
                console.log('SimpleFilters: Checking elements...');
                console.log('- Filter button:', $('#open-filters').length);
                console.log('- Filter drawer:', $('#filter-drawer').length);
                console.log('- Filter backdrop:', $('#filter-backdrop').length);
                
                if ($('#open-filters').length > 0) {
                    window.SimpleFilters.init();
                } else {
                    console.warn('SimpleFilters: Filter button not found');
                }
            }, 200);
        } else {
            console.log('SimpleFilters: Not a shop page');
        }
    });
    
})(jQuery);
