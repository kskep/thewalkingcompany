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
                
                $('#filter-backdrop').removeClass('hidden').addClass('show');
                $('#filter-drawer').addClass('open');
                $('body').addClass('overflow-hidden');
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
            
            console.log('SimpleFilters: Events bound successfully');
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
