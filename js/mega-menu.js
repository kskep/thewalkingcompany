(function($){
  'use strict';

  function initMegaMenu(){
    var megaMenuTimer;

    // Position mega menus correctly on page load
    function positionMegaMenus() {
      $('.mega-menu-container').each(function() {
        var $container = $(this);
        var $nav = $('.main-navigation');
        var navOffset = $nav.offset();
        var navWidth = $nav.outerWidth();

        // Calculate position to center relative to viewport
        var leftOffset = ($(window).width() - $container.outerWidth()) / 2;

        $container.css({
          'left': '50%',
          'transform': 'translateX(-50%)',
          'margin-left': '0'
        });
      });
    }

    // Hover open/close with slight delay
    $('.main-navigation .menu-item-has-children').hover(
      function(){
        clearTimeout(megaMenuTimer);
        var $megaMenu = $(this).find('.mega-menu-container');

        // Position the mega menu
        positionMegaMenus();

        $megaMenu.stop(true, true).css({
          'opacity': '1',
          'visibility': 'visible'
        });
      },
      function(){
        var $megaMenu = $(this).find('.mega-menu-container');
        megaMenuTimer = setTimeout(function(){
          $megaMenu.stop(true, true).css({
            'opacity': '0',
            'visibility': 'hidden'
          });
        }, 120);
      }
    );

    // Keep visible when hovering container
    $('.mega-menu-container').hover(
      function(){
        clearTimeout(megaMenuTimer);
        $(this).css({
          'opacity': '1',
          'visibility': 'visible'
        });
      },
      function(){
        var $megaMenu = $(this);
        megaMenuTimer = setTimeout(function(){
          $megaMenu.css({
            'opacity': '0',
            'visibility': 'hidden'
          });
        }, 120);
      }
    );

    // Position on window resize
    $(window).on('resize', positionMegaMenus);

    // Initial positioning
    positionMegaMenus();
  }

  $(document).ready(function(){
    initMegaMenu();
  });

})(jQuery);

