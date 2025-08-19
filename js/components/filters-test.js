/**
 * Simple Filter Test
 */

(function($) {
    'use strict';
    
    console.log('Filter test script loaded');

    window.EShopFilters = {
        init: function() {
            console.log('EShopFilters TEST initialized');
            this.bindEvents();
        },

        bindEvents: function() {
            console.log('Binding filter events TEST');
            
            // Simple event binding
            $('#open-filters').on('click', function(e) {
                e.preventDefault();
                console.log('Filter button clicked - TEST');
                $('#filter-backdrop').removeClass('hidden').addClass('show');
                $('#filter-drawer').addClass('open');
                $('body').addClass('overflow-hidden');
            });

            $('#close-filters, #filter-backdrop').on('click', function(e) {
                e.preventDefault();
                console.log('Closing filter drawer - TEST');
                $('#filter-backdrop').removeClass('show').addClass('hidden');
                $('#filter-drawer').removeClass('open');
                $('body').removeClass('overflow-hidden');
            });
        }
    };

})(jQuery);
