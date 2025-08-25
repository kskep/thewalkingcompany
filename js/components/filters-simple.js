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

            // Size filters
            var selectedSizes = [];
            $('input[name="pa_size[]"]:checked').each(function() {
                selectedSizes.push($(this).val());
            });
            if (selectedSizes.length > 0) {
                url.searchParams.set('pa_size', selectedSizes.join(','));
                filters.pa_size = selectedSizes;
            } else {
                url.searchParams.delete('pa_size');
            }

            // Color filters
            var selectedColors = [];
            $('input[name="pa_color[]"]:checked').each(function() {
                selectedColors.push($(this).val());
            });
            if (selectedColors.length > 0) {
                url.searchParams.set('pa_color', selectedColors.join(','));
                filters.pa_color = selectedColors;
            } else {
                url.searchParams.delete('pa_color');
            }

            // Material filters
            var selectedMaterials = [];
            $('input[name="pa_material[]"]:checked').each(function() {
                selectedMaterials.push($(this).val());
            });
            if (selectedMaterials.length > 0) {
                url.searchParams.set('pa_material', selectedMaterials.join(','));
                filters.pa_material = selectedMaterials;
            } else {
                url.searchParams.delete('pa_material');
            }

            // Brand filters
            var selectedBrands = [];
            $('input[name="pa_brand[]"]:checked').each(function() {
                selectedBrands.push($(this).val());
            });
            if (selectedBrands.length > 0) {
                url.searchParams.set('pa_brand', selectedBrands.join(','));
                filters.pa_brand = selectedBrands;
            } else {
                url.searchParams.delete('pa_brand');
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
            $('input[name="product_cat[]"], input[name="on_sale"], input[name="pa_size[]"], input[name="pa_color[]"], input[name="pa_material[]"], input[name="pa_brand[]"]').prop('checked', false);

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
            url.searchParams.delete('pa_size');
            url.searchParams.delete('pa_color');
            url.searchParams.delete('pa_material');
            url.searchParams.delete('pa_brand');

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

            // Size filters
            var sizeAttr = url.searchParams.get('pa_size');
            if (sizeAttr) {
                var sizes = sizeAttr.split(',');
                sizes.forEach(function(size) {
                    $('input[name="pa_size[]"][value="' + size + '"]').prop('checked', true);
                });
            }

            // Color filters
            var colorAttr = url.searchParams.get('pa_color');
            if (colorAttr) {
                var colors = colorAttr.split(',');
                colors.forEach(function(color) {
                    $('input[name="pa_color[]"][value="' + color + '"]').prop('checked', true);
                });
            }

            // Material filters
            var materialAttr = url.searchParams.get('pa_material');
            if (materialAttr) {
                var materials = materialAttr.split(',');
                materials.forEach(function(material) {
                    $('input[name="pa_material[]"][value="' + material + '"]').prop('checked', true);
                });
            }

            // Brand filters
            var brandAttr = url.searchParams.get('pa_brand');
            if (brandAttr) {
                var brands = brandAttr.split(',');
                brands.forEach(function(brand) {
                    $('input[name="pa_brand[]"][value="' + brand + '"]').prop('checked', true);
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
