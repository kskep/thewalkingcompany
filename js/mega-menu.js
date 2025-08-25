(function($){
  'use strict';

  function initMegaMenu(){
    // State management
    var activeMenu = null;
    var hideTimer = null;
    var showTimer = null;
    var mouseTracker = {
      x: 0,
      y: 0,
      lastX: 0,
      lastY: 0
    };

    // Configuration
    var config = {
      showDelay: 100,      // Delay before showing menu
      hideDelay: 150,      // Delay before hiding menu
      intentDelay: 300,    // Delay for mouse intent detection
      transitionSpeed: 200 // CSS transition speed
    };

    // Correctly position mega menus to be full-width and aligned to the viewport
    function positionMegaMenus() {
      $('.mega-menu-container').each(function() {
        var $container = $(this);
        var $parentMenuItem = $container.closest('.menu-item-has-children');

        if ($parentMenuItem.length) {
          var parentOffset = $parentMenuItem.offset().left;
          $container.css({
            'left': -parentOffset + 'px'
          });
        }
      });
    }

    // Clear all timers
    function clearAllTimers() {
      if (hideTimer) {
        clearTimeout(hideTimer);
        hideTimer = null;
      }
      if (showTimer) {
        clearTimeout(showTimer);
        showTimer = null;
      }
    }

    // Hide all mega menus
    function hideAllMenus(immediate = false) {
      $('.mega-menu-container').stop(true, true).css({
        'opacity': '0',
        'visibility': 'hidden'
      });

      // Remove active class from all menu items
      $('.main-navigation .menu-item-has-children').removeClass('menu-active');

      activeMenu = null;
      clearAllTimers();
    }

    // Show specific mega menu
    function showMenu($menuItem) {
      if (activeMenu && activeMenu[0] === $menuItem[0]) {
        return; // Already active
      }

      // Hide any currently active menu first
      hideAllMenus(true);

      var $megaMenu = $menuItem.find('.mega-menu-container');
      if ($megaMenu.length === 0) {
        return; // No mega menu for this item
      }

      // Set as active
      activeMenu = $menuItem;
      $menuItem.addClass('menu-active');

      // Position and show
      positionMegaMenus();
      $megaMenu.stop(true, true).css({
        'opacity': '1',
        'visibility': 'visible'
      });
    }

    // Hide specific mega menu
    function hideMenu($menuItem, delay = config.hideDelay) {
      if (!$menuItem || (activeMenu && activeMenu[0] !== $menuItem[0])) {
        return; // Not the active menu
      }

      clearAllTimers();

      hideTimer = setTimeout(function() {
        if (activeMenu && activeMenu[0] === $menuItem[0]) {
          hideAllMenus();
        }
      }, delay);
    }

    // Check if mouse is moving towards the mega menu
    function isMovingTowardsMenu($menuItem, currentX, currentY) {
      var $megaMenu = $menuItem.find('.mega-menu-container');
      if ($megaMenu.length === 0) return false;

      var menuOffset = $megaMenu.offset();
      var menuTop = menuOffset.top;
      var menuBottom = menuTop + $megaMenu.outerHeight();

      // Check if mouse is moving downward towards the menu area
      var movingDown = currentY > mouseTracker.lastY;
      var inMenuXRange = currentX >= menuOffset.left && currentX <= (menuOffset.left + $megaMenu.outerWidth());

      return movingDown && inMenuXRange && currentY < menuBottom;
    }

    // Track mouse movement for intent detection
    function updateMouseTracker(e) {
      mouseTracker.lastX = mouseTracker.x;
      mouseTracker.lastY = mouseTracker.y;
      mouseTracker.x = e.pageX;
      mouseTracker.y = e.pageY;
    }

    // Main menu item hover handlers
    $('.main-navigation .menu-item-has-children').on('mouseenter', function(e) {
      var $menuItem = $(this);
      updateMouseTracker(e);

      clearAllTimers();

      // Check if this item has a mega menu
      if ($menuItem.find('.mega-menu-container').length === 0) {
        return;
      }

      // If there's already an active menu, show immediately
      if (activeMenu) {
        showMenu($menuItem);
      } else {
        // First menu activation - small delay for intent
        showTimer = setTimeout(function() {
          showMenu($menuItem);
        }, config.showDelay);
      }
    });

    $('.main-navigation .menu-item-has-children').on('mouseleave', function(e) {
      var $menuItem = $(this);
      updateMouseTracker(e);

      // Check if mouse is moving towards the mega menu
      if (isMovingTowardsMenu($menuItem, e.pageX, e.pageY)) {
        // Give more time if moving towards menu
        hideMenu($menuItem, config.intentDelay);
      } else {
        hideMenu($menuItem);
      }
    });

    // Mega menu container hover handlers
    $('.mega-menu-container').on('mouseenter', function() {
      clearAllTimers();
      // Keep menu visible when hovering over it
    });

    $('.mega-menu-container').on('mouseleave', function() {
      var $menuItem = $(this).closest('.menu-item-has-children');
      hideMenu($menuItem);
    });

    // Global mouse tracking
    $(document).on('mousemove', function(e) {
      updateMouseTracker(e);
    });

    // Hide menus when clicking outside
    $(document).on('click', function(e) {
      if (!$(e.target).closest('.main-navigation').length) {
        hideAllMenus(true);
      }
    });

    // Handle window resize
    $(window).on('resize', function() {
      positionMegaMenus();
    });

    // Handle escape key
    $(document).on('keydown', function(e) {
      if (e.keyCode === 27) { // Escape key
        hideAllMenus(true);
      }
    });

    // Initialize positions
    positionMegaMenus();
  }

  $(document).ready(function(){
    initMegaMenu();
  });

})(jQuery);