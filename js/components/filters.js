/**
 * Product Filters Component
 * Handles all filter functionality for product archives
 */

(function($) {
    'use strict';

    // Ensure jQuery is available
    if (typeof $ === 'undefined') {
        console.error('jQuery is not available for EShopFilters');
        return;
    }

    window.EShopFilters = {

        // Initialize the filter system
        init: function() {
            console.log('EShopFilters initialized');
            console.log('Current page URL:', window.location.href);
            console.log('Filter elements check:');
            console.log('- Filter button (#open-filters):', $('#open-filters').length);
            console.log('- Filter drawer (#filter-drawer):', $('#filter-drawer').length);
            console.log('- Filter backdrop (#filter-backdrop):', $('#filter-backdrop').length);

            this.bindEvents();
            this.initPriceSlider();
        },

        // Bind all filter-related events
        bindEvents: function() {
            console.log('Binding filter events');
            console.log('Filter button exists:', $('#open-filters').length > 0);
            console.log('Filter drawer exists:', $('#filter-drawer').length > 0);

            // Off-Canvas Filter Drawer
            $('#open-filters').on('click', this.openDrawer);
            $('#close-filters, #filter-backdrop').on('click', this.closeDrawer);
            
            // Apply and clear filters
            $('#apply-filters').on('click', this.applyFiltersAndClose);
            $('#clear-filters').on('click', this.clearAllFilters);
            
            // Filter change handlers
            $(document).on('change', '#filter-drawer input[type="checkbox"]', function() {
                // Auto-apply filters on change (optional)
                EShopFilters.applyFilters();
            });

            $(document).on('click', '.apply-price-filter', this.applyFilters);
            $(document).on('change', '.woocommerce-ordering select', this.applyFilters);

            // Quick filter buttons
            $(document).on('click', '.quick-filter-btn:not(.more-filters-btn)', this.handleQuickFilter);

            // Pagination (both custom and Woo default)
            $(document).on('click', '.pagination a, .woocommerce-pagination .page-numbers a', this.handlePagination);

            // Remove individual filters
            $(document).on('click', '.remove-filter', this.removeFilter);

            // Clear all filters
            $(document).on('click', '.clear-filters, .clear-all-filters', this.clearAllFilters);

            // Keyboard accessibility - Escape to close drawer
            $(document).on('keydown', this.handleKeyboard);
        },

        // Open filter drawer
        openDrawer: function(e) {
            if (e) e.preventDefault();
            console.log('Opening filter drawer');
            console.log('Backdrop element:', $('#filter-backdrop').length);
            console.log('Drawer element:', $('#filter-drawer').length);

            $('#filter-backdrop').removeClass('hidden').addClass('show');
            $('#filter-drawer').addClass('open');
            $('body').addClass('overflow-hidden');

            // Initialize price slider when drawer opens
            setTimeout(function() {
                EShopFilters.initPriceSlider();
            }, 20);
        },

        // Close filter drawer
        closeDrawer: function(e) {
            if (e) e.preventDefault();
            console.log('Closing filter drawer');
            $('#filter-backdrop').removeClass('show').addClass('hidden');
            $('#filter-drawer').removeClass('open');
            $('body').removeClass('overflow-hidden');
        },

        // Apply filters and close drawer
        applyFiltersAndClose: function() {
            EShopFilters.closeDrawer();
            EShopFilters.applyFilters();
        },

        // Handle keyboard events
        handleKeyboard: function(e) {
            if (e.key === 'Escape' && $('#filter-drawer').hasClass('open')) {
                EShopFilters.closeDrawer();
            }
        },

        // Handle quick filter buttons
        handleQuickFilter: function() {
            var $btn = $(this);
            var filterType = $btn.data('filter');

            // Toggle active state
            $btn.toggleClass('active');

            if (filterType === 'price') {
                var minPrice = $btn.data('min');
                var maxPrice = $btn.data('max');

                if ($btn.hasClass('active')) {
                    // Remove other active price filters
                    $('.quick-filter-btn[data-filter="price"]').not($btn).removeClass('active');

                    // Set price inputs in modal
                    $('#min-price').val(minPrice || '');
                    $('#max-price').val(maxPrice || '');
                } else {
                    // Clear price inputs
                    $('#min-price, #max-price').val('');
                }
            } else if (filterType === 'on_sale') {
                var checkbox = $('input[name="on_sale"]');
                checkbox.prop('checked', $btn.hasClass('active'));
            } else if (filterType === 'stock_status') {
                var value = $btn.data('value');
                var checkbox = $('input[name="stock_status[]"][value="' + value + '"]');
                checkbox.prop('checked', $btn.hasClass('active'));
            }

            // Apply filters immediately
            EShopFilters.applyFilters();
        },

        // Handle pagination clicks
        handlePagination: function(e) {
            e.preventDefault();
            var url = $(this).attr('href');
            var page = EShopFilters.getParameterByName('paged', url);
            if (!page) {
                // Try to extract /page/2/ from pretty permalinks
                var match = url && url.match(/\/(?:page)\/(\d+)\/?/i);
                page = match && match[1] ? parseInt(match[1], 10) : 1;
            } else {
                page = parseInt(page, 10) || 1;
            }
            EShopFilters.applyFilters(page);
        },

        // Initialize price range slider
        initPriceSlider: function() {
            var sliderEl = document.getElementById('price-slider');
            if (!sliderEl || sliderEl.__inited || typeof noUiSlider === 'undefined') return;
            
            var minInput = document.getElementById('min-price');
            var maxInput = document.getElementById('max-price');
            if (!minInput || !maxInput) return;
            
            var maxPrice = parseFloat(sliderEl.getAttribute('data-max')) || 1000;
            var minStart = parseFloat(minInput.value) || 0;
            var maxStart = parseFloat(maxInput.value) || maxPrice;
            var start = [minStart, maxStart];
            
            noUiSlider.create(sliderEl, {
                start: start,
                connect: true,
                range: { min: 0, max: maxPrice },
                step: 1,
            });
            
            sliderEl.__inited = true;
            
            sliderEl.noUiSlider.on('update', function(values) {
                var v0 = Math.round(values[0]);
                var v1 = Math.round(values[1]);
                minInput.value = v0;
                maxInput.value = v1;
            });
        },

        // Apply filters with AJAX
        applyFilters: function(page = 1) {
            var filters = EShopFilters.collectFilters();
            var orderby = $('.woocommerce-ordering select').val() || 'menu_order';

            // Show loading
            $('.products-wrapper').addClass('relative');
            $('.products-loading').removeClass('hidden');

            $.ajax({
                url: eshop_ajax.ajax_url,
                type: 'POST',
                data: $.param(filterData),
                success: function(response) {
                    if (response.success) {
                        $('.products-wrapper').html(response.data.products);
                        $('.woocommerce-result-count').html(response.data.result_count);
                        // Replace pagination that sits below products
                        if (typeof response.data.pagination !== 'undefined') {
                            // If there's already a pagination nav, replace it; else append after wrapper
                            var $existing = $('.woocommerce-pagination');
                            if ($existing.length) {
                                $existing.replaceWith(response.data.pagination);
                            } else {
                                // Append after products wrapper to mirror template structure
                                $('.products-wrapper').after(response.data.pagination);
                            }
                        }
                        
                        // Re-init product sliders on new DOM
                        if (typeof initializeProductCardSliders === 'function') {
                            initializeProductCardSliders();
                        }

                        // Update active filters display
                        EShopFilters.updateActiveFilters(filters);

                        // Scroll to products
                        $('html, body').animate({
                            scrollTop: $('.shop-toolbar').offset().top - 100
                        }, 300);
                    }
                },
                complete: function() {
                    $('.products-loading').addClass('hidden');
                    $('.products-wrapper').removeClass('relative');
                }
            });
        },

        // Collect all active filters
        collectFilters: function() {
            var filters = {};

            // Price filters
            var minPrice = $('#min-price').val();
            var maxPrice = $('#max-price').val();
            if (minPrice) filters.min_price = minPrice;
            if (maxPrice) filters.max_price = maxPrice;

            // Category filters
            var categories = [];
            $('input[name="product_cat[]"]:checked').each(function() {
                categories.push($(this).val());
            });
            if (categories.length) filters.product_cat = categories;

            // Attribute filters
            $('.attribute-filter').each(function() {
                var attribute = $(this).data('attribute');
                var taxonomy = 'pa_' + attribute;
                var values = [];

                $(this).find('input:checked').each(function() {
                    values.push($(this).val());
                });

                if (values.length) {
                    filters[taxonomy] = values;
                }
            });

            // Stock status
            var stockStatus = [];
            $('input[name="stock_status[]"]:checked').each(function() {
                stockStatus.push($(this).val());
            });
            if (stockStatus.length) filters.stock_status = stockStatus;

            // On sale
            if ($('input[name="on_sale"]:checked').length) {
                filters.on_sale = 1;
            }

            return filters;
        },

        // Update active filters display
        updateActiveFilters: function(filters) {
            var $activeFilters = $('.active-filters');
            var $activeFiltersList = $('.active-filters-list');
            var $activeFiltersBar = $('.active-filters-bar');
            var $clearButton = $('.clear-filters');

            $activeFiltersList.empty();

            var hasFilters = false;

            // Price filter
            if (filters.min_price || filters.max_price) {
                hasFilters = true;
                var priceText = 'Price: ';
                if (filters.min_price && filters.max_price) {
                    priceText += '$' + filters.min_price + ' - $' + filters.max_price;
                } else if (filters.min_price) {
                    priceText += 'From $' + filters.min_price;
                } else {
                    priceText += 'Up to $' + filters.max_price;
                }

                $activeFiltersList.append(
                    '<div class="active-filter flex items-center justify-between bg-gray-100 px-3 py-1 text-sm">' +
                    '<span>' + priceText + '</span>' +
                    '<button class="remove-filter ml-2 text-gray-400 hover:text-red-500" data-filter="price">' +
                    '<i class="fas fa-times text-xs"></i>' +
                    '</button>' +
                    '</div>'
                );
            }

            // Category filters
            if (filters.product_cat) {
                hasFilters = true;
                filters.product_cat.forEach(function(cat) {
                    var catName = $('input[value="' + cat + '"]').siblings('span').first().text();
                    $activeFiltersList.append(
                        '<div class="active-filter flex items-center justify-between bg-gray-100 px-3 py-1 text-sm">' +
                        '<span>' + catName + '</span>' +
                        '<button class="remove-filter ml-2 text-gray-400 hover:text-red-500" data-filter="product_cat" data-value="' + cat + '">' +
                        '<i class="fas fa-times text-xs"></i>' +
                        '</button>' +
                        '</div>'
                    );
                });
            }

            // Show/hide active filters section
            if (hasFilters) {
                $activeFilters.show();
                $activeFiltersBar.show();
                $clearButton.show();
            } else {
                $activeFilters.hide();
                $activeFiltersBar.hide();
                $clearButton.hide();
            }

            // Update quick filter button states
            EShopFilters.updateQuickFilterStates(filters);
        },

        // Update quick filter button states
        updateQuickFilterStates: function(filters) {
            // Reset all quick filter buttons
            $('.quick-filter-btn').removeClass('active');

            // Update price filter buttons
            if (filters.min_price || filters.max_price) {
                var minPrice = parseInt(filters.min_price) || 0;
                var maxPrice = parseInt(filters.max_price) || 999999;

                $('.quick-filter-btn[data-filter="price"]').each(function() {
                    var btnMin = parseInt($(this).data('min')) || 0;
                    var btnMax = parseInt($(this).data('max')) || 999999;

                    if (btnMin === minPrice && btnMax === maxPrice) {
                        $(this).addClass('active');
                    }
                });
            }

            // Update on sale filter
            if (filters.on_sale) {
                $('.quick-filter-btn[data-filter="on_sale"]').addClass('active');
            }

            // Update stock status filter
            if (filters.stock_status && filters.stock_status.includes('instock')) {
                $('.quick-filter-btn[data-filter="stock_status"][data-value="instock"]').addClass('active');
            }
        },

        // Clear all filters
        clearAllFilters: function() {
            $('#filter-drawer input[type="checkbox"]').prop('checked', false);
            $('#min-price, #max-price').val('');
            $('.active-filters').hide();
            $('.active-filters-bar').hide();
            $('.clear-filters').hide();
            $('.quick-filter-btn').removeClass('active');
            EShopFilters.applyFilters();
        },

        // Remove individual filter
        removeFilter: function() {
            var filterType = $(this).data('filter');
            var filterValue = $(this).data('value');

            if (filterType === 'price') {
                $('#min-price, #max-price').val('');
            } else if (filterType === 'product_cat') {
                $('input[name="product_cat[]"][value="' + filterValue + '"]').prop('checked', false);
            }

            EShopFilters.applyFilters();
        },

        // Helper function to get URL parameter
        getParameterByName: function(name, url) {
            if (!url) url = window.location.href;
            name = name.replace(/[\[\]]/g, '\\$&');
            var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)');
            var results = regex.exec(url);
            if (!results) return null;
            if (!results[2]) return '';
            return decodeURIComponent(results[2].replace(/\+/g, ' '));
        }
    };

    // WordPress-compatible initialization
    $(document).ready(function() {
        console.log('EShopFilters: Document ready, checking for shop layout...');

        // Check if we're on a shop page
        if ($('.shop-layout').length > 0 || $('body').hasClass('woocommerce-shop') || $('body').hasClass('woocommerce-archive')) {
            console.log('EShopFilters: Shop page detected, initializing...');

            // Small delay to ensure all elements are loaded
            setTimeout(function() {
                if (typeof window.EShopFilters !== 'undefined') {
                    window.EShopFilters.init();
                } else {
                    console.error('EShopFilters: Object not available');
                }
            }, 100);
        } else {
            console.log('EShopFilters: Not a shop page, skipping initialization');
        }
    });

})(jQuery);
