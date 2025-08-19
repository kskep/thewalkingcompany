(function($){
  'use strict';

  function initMegaMenu(){
    var megaMenuTimer;

    // Correctly position mega menus to be full-width and aligned to the viewport
    function positionMegaMenus() {
      // Loop through each mega menu container
      $('.mega-menu-container').each(function() {
        var $container = $(this);
        // Find the parent menu item (the li)
        var $parentMenuItem = $container.closest('.menu-item-has-children');
        
        if ($parentMenuItem.length) {
          // Get the exact offset of the parent menu item from the left edge of the document
          var parentOffset = $parentMenuItem.offset().left;
          
          // Set the 'left' CSS property of the mega menu to the negative of the parent's offset.
          // This counteracts the parent's relative positioning, effectively aligning the 
          // 100vw-wide mega menu to the viewport's left edge.
          $container.css({
            'left': -parentOffset + 'px'
          });
        }
      });
    }

    // --- The rest of the JS (hover logic) remains the same ---
    // It's important that positionMegaMenus() is called before the menu is shown.
    // The existing code structure does this correctly.

    $('.main-navigation .menu-item-has-children').hover(
      function(){ // Function to run on mouse enter
        clearTimeout(megaMenuTimer);
        var $megaMenu = $(this).find('.mega-menu-container');

        // Position the mega menu correctly just before showing it
        positionMegaMenus();

        // Use stop() to prevent animation queue buildup, then fade in
        $megaMenu.stop(true, true).css({
          'opacity': '1',
          'visibility': 'visible'
        });
      },
      function(){ // Function to run on mouse leave
        var $megaMenu = $(this).find('.mega-menu-container');
        megaMenuTimer = setTimeout(function(){
          $megaMenu.stop(true, true).css({
            'opacity': '0',
            'visibility': 'hidden'
          });
        }, 120); // A small delay before hiding
      }
    );

    // Keep the menu visible when the mouse moves from the link onto the menu itself
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

    // Recalculate positions if the window is resized
    $(window).on('resize', positionMegaMenus);

    // Run once on page load to set initial positions
    positionMegaMenus();
  }

  $(document).ready(function(){
    initMegaMenu();
  });

})(jQuery);