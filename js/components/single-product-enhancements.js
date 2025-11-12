/**
 * Single Product Enhancements - Wishlist & Stock Updates
 * 
 * @package E-Shop Theme
 */

(function($) {
    'use strict';

    // Quantity controls: inject +/- buttons and enforce min/max
    function initializeQuantityControls($scope) {
        var $containers = ($scope && $scope.length ? $scope : $(document)).find('.cart .quantity');

        $containers.each(function() {
            var $q = $(this);
            if ($q.data('enhanced')) { return; }
            var $input = $q.find('input.qty');
            if ($input.length === 0) { return; }

            // Insert buttons
            var $minus = $('<button type="button" class="qty-btn qty-minus" aria-label="Decrease quantity">&minus;</button>');
            var $plus = $('<button type="button" class="qty-btn qty-plus" aria-label="Increase quantity">+</button>');

            // If not already injected, prepend/append
            if ($q.children('.qty-minus').length === 0) { $q.prepend($minus); }
            if ($q.children('.qty-plus').length === 0) { $q.append($plus); }

            function clamp(val, min, max, step) {
                var n = parseFloat(val);
                if (isNaN(n)) n = min || 1;
                if (!isNaN(min)) n = Math.max(n, min);
                if (!isNaN(max) && max > 0) n = Math.min(n, max);
                if (!isNaN(step) && step > 0) {
                    // Snap to nearest step
                    var base = (!isNaN(min) ? min : 0);
                    n = Math.round((n - base) / step) * step + base;
                }
                return n;
            }

            function readBounds() {
                return {
                    min: parseFloat($input.attr('min')) || 1,
                    max: parseFloat($input.attr('max')) || null,
                    step: parseFloat($input.attr('step')) || 1
                };
            }

            $minus.on('click', function() {
                var b = readBounds();
                var curr = parseFloat($input.val()) || b.min;
                var next = curr - b.step;
                next = clamp(next, b.min, b.max, b.step);
                $input.val(next).trigger('change');
            });

            $plus.on('click', function() {
                var b = readBounds();
                var curr = parseFloat($input.val()) || b.min;
                var next = curr + b.step;
                next = clamp(next, b.min, b.max, b.step);
                $input.val(next).trigger('change');
            });

            $input.on('change input blur', function() {
                var b = readBounds();
                var curr = parseFloat($input.val());
                var fixed = clamp(curr, b.min, b.max, b.step);
                if (fixed !== curr) {
                    $input.val(fixed);
                }
            });

            $q.data('enhanced', true);
        });
    }

    $(document).ready(function(){
        initializeQuantityControls($(document));
        // Ensure Woo variations script notices default selections
        $('.variations_form').trigger('check_variations');
    });

    // Re-init when variation form updates the add-to-cart area
    $(document.body).on('updated_wc_div found_variation show_variation', function(){
        initializeQuantityControls($(document));
    });

    // Update stock info display for variations
    $(document).on('found_variation', function(event, variation) {
        var $stockInfo = $('.stock-info-row');
        var $indicator = $stockInfo.find('.stock-indicator');
        var $text = $stockInfo.find('.stock-text');
        
        if (variation.is_in_stock) {
            $stockInfo.removeClass('out-of-stock').show();
            $indicator.css('background', '#10b981');
            
            if (variation.availability_html) {
                var stockText = $(variation.availability_html).text() || 'In Stock - Ready to Ship';
                $text.text(stockText);
            } else {
                $text.text('In Stock - Ready to Ship');
            }
        } else {
            $stockInfo.addClass('out-of-stock').show();
            $indicator.css('background', '#ef4444');
            $text.text('Out of Stock');
        }
    });

    // Reset stock info when variation is cleared
    $(document).on('reset_data', function() {
        $('.stock-info-row').hide();
    });

    // Handle wishlist button clicks with AJAX
    $(document).on('click', '.wishlist-action-btn', function(e) {
        e.preventDefault();
        
        var $btn = $(this);
        var productId = $btn.data('product-id');
        var nonce = $btn.data('nonce');
        
        // Prevent double clicks
        if ($btn.hasClass('loading')) {
            return;
        }
        
        $btn.addClass('loading');
        
        $.ajax({
            url: eshop_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'toggle_wishlist',
                product_id: productId,
                nonce: nonce
            },
            success: function(response) {
                if (response.success) {
                    // Toggle visual state
                    $btn.toggleClass('in-wishlist');
                    
                    // Update SVG fill
                    var $svg = $btn.find('svg');
                    if ($btn.hasClass('in-wishlist')) {
                        $svg.attr('fill', 'currentColor');
                        $btn.attr('aria-label', 'Remove from wishlist');
                    } else {
                        $svg.attr('fill', 'none');
                        $btn.attr('aria-label', 'Add to wishlist');
                    }
                    
                    // Show feedback message (if you have a feedback area)
                    if (response.data && response.data.message) {
                        // You can add a toast notification here if needed
                        console.log(response.data.message);
                    }
                    
                    // Trigger custom event for other scripts that might listen
                    $(document).trigger('wishlist_updated', [productId, $btn.hasClass('in-wishlist')]);
                }
            },
            error: function() {
                console.error('Wishlist update failed');
            },
            complete: function() {
                $btn.removeClass('loading');
            }
        });
    });

})(jQuery);
