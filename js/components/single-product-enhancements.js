/**
 * Single Product Enhancements - Wishlist & Stock Updates
 * 
 * @package E-Shop Theme
 */

(function($) {
    'use strict';

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
