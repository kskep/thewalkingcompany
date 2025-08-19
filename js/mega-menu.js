(function($){
  'use strict';

  function initMegaMenu(){
    var megaMenuTimer;

    // Hover open/close with slight delay - use CSS transitions instead of fadeIn/fadeOut
    $('.main-navigation .menu-item-has-children').hover(
      function(){
        clearTimeout(megaMenuTimer);
        var $megaMenu = $(this).find('.mega-menu-container');
        $megaMenu.addClass('show');
      },
      function(){
        var $megaMenu = $(this).find('.mega-menu-container');
        megaMenuTimer = setTimeout(function(){
          $megaMenu.removeClass('show');
        }, 120);
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

