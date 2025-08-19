(function($){
  'use strict';

  function initMegaMenu(){
    var megaMenuTimer;

    // Hover open/close with slight delay
    $('.main-navigation .menu-item-has-children').hover(
      function(){
        clearTimeout(megaMenuTimer);
        var $megaMenu = $(this).find('.mega-menu-container');
        $megaMenu.stop(true, true).fadeIn(200);
      },
      function(){
        var $megaMenu = $(this).find('.mega-menu-container');
        megaMenuTimer = setTimeout(function(){
          $megaMenu.stop(true, true).fadeOut(150);
        }, 120);
      }
    );

    // Keep visible when hovering container
    $('.mega-menu-container').hover(
      function(){ clearTimeout(megaMenuTimer); },
      function(){
        var $megaMenu = $(this);
        megaMenuTimer = setTimeout(function(){
          $megaMenu.stop(true, true).fadeOut(150);
        }, 120);
      }
    );
  }

  $(document).ready(function(){
    initMegaMenu();
  });

})(jQuery);

