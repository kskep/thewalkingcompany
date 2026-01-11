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
            this.initPriceSlider();
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

            // Size grid click handlers (for hidden checkboxes) - use event delegation
            $(document).on('click.simple', '.size-option', function(e) {
                e.preventDefault();
                console.log('SimpleFilters: Size option clicked');

                var $this = $(this);
                var $checkbox = $this.find('input[type="checkbox"]');
                var $label = $this.find('.size-label');

                console.log('SimpleFilters: Found checkbox:', $checkbox.length);
                console.log('SimpleFilters: Found label:', $label.length);
                console.log('SimpleFilters: Current checked state:', $checkbox.prop('checked'));

                // Toggle the checkbox
                var newState = !$checkbox.prop('checked');
                $checkbox.prop('checked', newState);

                // Update visual state
                if (newState) {
                    $label.removeClass('bg-white text-gray-700 border-gray-300')
                          .addClass('bg-primary text-white border-primary');
                    console.log('SimpleFilters: Applied selected styles');
                } else {
                    $label.removeClass('bg-primary text-white border-primary')
                          .addClass('bg-white text-gray-700 border-gray-300');
                    console.log('SimpleFilters: Applied unselected styles');
                }

                console.log('SimpleFilters: Size checkbox toggled:', $checkbox.val(), 'to', newState);
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

            // Product attribute filters (pa_*)
            var attributeNames = [];
            $('input[type="checkbox"][name^="pa_"]').each(function() {
                var rawName = $(this).attr('name') || '';
                var baseName = rawName.replace(/\[\]$/, '');
                if (baseName && attributeNames.indexOf(baseName) === -1) {
                    attributeNames.push(baseName);
                }
            });

            attributeNames.forEach(function(attribute) {
                var selectedValues = [];
                $('input[name="' + attribute + '[]"]:checked').each(function() {
                    selectedValues.push($(this).val());
                });

                if (selectedValues.length > 0) {
                    url.searchParams.set(attribute, selectedValues.join(','));
                    filters[attribute] = selectedValues;
                } else {
                    url.searchParams.delete(attribute);
                }
            });

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
            $('input[name="product_cat[]"], input[name="on_sale"]').prop('checked', false);
            $('input[type="checkbox"][name^="pa_"]').prop('checked', false);

            // Reset visual states for size grids
            $('.size-label').removeClass('bg-primary text-white border-primary')
                           .addClass('bg-white text-gray-700 border-gray-300');

            // Reset price slider
            var sliderEl = document.getElementById('price-slider');
            if (sliderEl && sliderEl.noUiSlider) {
                var minRange = parseInt(sliderEl.dataset.min) || 0;
                var maxRange = parseInt(sliderEl.dataset.max) || 1000;
                sliderEl.noUiSlider.set([minRange, maxRange]);
            }

            // Redirect to clean URL
            var url = new URL(window.location.href);
            url.searchParams.delete('min_price');
            url.searchParams.delete('max_price');
            url.searchParams.delete('product_cat');
            url.searchParams.delete('on_sale');
            // Remove all attribute params (pa_*)
            Array.from(url.searchParams.keys()).forEach(function(key) {
                if (key.indexOf('pa_') === 0) {
                    url.searchParams.delete(key);
                }
            });

            console.log('SimpleFilters: Redirecting to clean URL:', url.toString());
            window.location.href = url.toString();
        },

        // Close drawer function
        closeDrawer: function() {
            $('#filter-backdrop').removeClass('show').addClass('hidden');
            $('#filter-drawer').removeClass('open');
            $('body').removeClass('overflow-hidden');
        },

        // Initialize price slider
        initPriceSlider: function() {
            var sliderEl = document.getElementById('price-slider');
            if (!sliderEl || typeof noUiSlider === 'undefined') {
                console.log('SimpleFilters: Price slider element or noUiSlider not found');
                return;
            }

            // Prevent double initialization
            if (sliderEl.noUiSlider) {
                console.log('SimpleFilters: Price slider already initialized');
                return;
            }

            var minRange = parseInt(sliderEl.dataset.min) || 0;
            var maxRange = parseInt(sliderEl.dataset.max) || 1000;
            var currentMin = parseInt(sliderEl.dataset.currentMin) || minRange;
            var currentMax = parseInt(sliderEl.dataset.currentMax) || maxRange;

            console.log('SimpleFilters: Initializing price slider', {
                min: minRange,
                max: maxRange,
                currentMin: currentMin,
                currentMax: currentMax
            });

            noUiSlider.create(sliderEl, {
                start: [currentMin, currentMax],
                connect: true,
                range: {
                    'min': minRange,
                    'max': maxRange
                },
                step: 1,
                format: {
                    to: function(value) {
                        return Math.round(value);
                    },
                    from: function(value) {
                        return Number(value);
                    }
                }
            });

            // Update price display and hidden inputs when slider changes
            sliderEl.noUiSlider.on('update', function(values) {
                var minVal = Math.round(values[0]);
                var maxVal = Math.round(values[1]);

                // Update price display
                $('.price-min').text('$' + minVal);
                $('.price-max').text('$' + maxVal);

                // Update hidden inputs
                $('#min-price').val(minVal);
                $('#max-price').val(maxVal);
            });

            console.log('SimpleFilters: Price slider initialized successfully');
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

            // Product attribute filters (pa_*)
            var attributeNames = [];
            $('input[type="checkbox"][name^="pa_"]').each(function() {
                var rawName = $(this).attr('name') || '';
                var baseName = rawName.replace(/\[\]$/, '');
                if (baseName && attributeNames.indexOf(baseName) === -1) {
                    attributeNames.push(baseName);
                }
            });

            attributeNames.forEach(function(attribute) {
                var attrValue = url.searchParams.get(attribute);
                if (attrValue) {
                    var values = attrValue.split(',');
                    values.forEach(function(value) {
                        var $checkbox = $('input[name="' + attribute + '[]"][value="' + value + '"]');
                        $checkbox.prop('checked', true);

                        // Update visual state for size grids
                        var $label = $checkbox.siblings('.size-label');
                        if ($label.length > 0) {
                            $label.removeClass('bg-white text-gray-700 border-gray-300')
                                  .addClass('bg-primary text-white border-primary');
                        }
                    });
                }
            });

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
