(function($){
  'use strict';

  function initMegaMenu(){
    var megaMenuTimer;

    // Hover open/close with slight delay - show correct mega menu based on data attribute
    $('.main-navigation .menu-item-has-children').hover(
      function(){
        clearTimeout(megaMenuTimer);
        var menuSlug = $(this).data('mega-menu');
        if (menuSlug) {
          // Hide all mega menus first
          $('.mega-menu-container').removeClass('show');
          // Show the correct one
          $('.mega-menu-container[data-mega-menu="' + menuSlug + '"]').addClass('show');
        }
      },
      function(){
        var menuSlug = $(this).data('mega-menu');
        if (menuSlug) {
          var $megaMenu = $('.mega-menu-container[data-mega-menu="' + menuSlug + '"]');
          megaMenuTimer = setTimeout(function(){
            $megaMenu.removeClass('show');
          }, 120);
        }
      }
    );

    // Keep visible when hovering container
    $('.mega-menu-container').hover(
      function(){
        clearTimeout(megaMenuTimer);
        $(this).addClass('show');
      },
      function(){
        var $megaMenu = $(this);
        megaMenuTimer = setTimeout(function(){
          $megaMenu.removeClass('show');
        }, 120);
      }
    );
  }

  $(document).ready(function(){
    initMegaMenu();
  });

})(jQuery);

