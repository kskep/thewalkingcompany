(function($){
  'use strict';

  function initProductGallery($gallery){
    // Initialize thumbnail swiper
    var thumbsSwiper = new Swiper($gallery.find('.product-gallery__thumbnails')[0], {
      spaceBetween: 8,
      slidesPerView: 'auto',
      freeMode: true,
      watchSlidesProgress: true,
      observer: true,
      observeParents: true,
      breakpoints: {
        320: {
          slidesPerView: 4,
          spaceBetween: 6
        },
        480: {
          slidesPerView: 5,
          spaceBetween: 8
        },
        768: {
          slidesPerView: 6,
          spaceBetween: 8
        },
        1024: {
          slidesPerView: 7,
          spaceBetween: 8
        }
      }
    });

    // Initialize main gallery swiper
    var mainSwiper = new Swiper($gallery.find('.product-gallery__main-image-wrapper')[0], {
      spaceBetween: 10,
      slidesPerView: 1,
      observer: true,
      observeParents: true,
      navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
      },
      pagination: {
        el: '.product-gallery__pagination',
        type: 'fraction',
      },
      thumbs: {
        swiper: thumbsSwiper,
      },
      keyboard: {
        enabled: true,
      },
      loop: false,
      grabCursor: true,
      on: {
        init: function() {
          // Force update after initialization
          this.update();
        }
      }
    });

    // Force update on window resize
    $(window).on('resize', function() {
      mainSwiper.update();
      thumbsSwiper.update();
    });

    // Simple zoom overlay for desktop images
    var $overlay = $('.product-gallery__zoom-overlay');
    var $zoomImg = $overlay.find('.product-gallery__zoom-image');

    function closeZoom() {
      $overlay.removeClass('is-active').attr('aria-hidden', 'true');
      $zoomImg.attr({src: '', alt: ''});
    }

    $gallery.on('click', '.product-gallery__main-image-img', function(){
      if (window.innerWidth <= 768) return;
      var src = $(this).attr('src');
      var alt = $(this).attr('alt') || '';
      $zoomImg.attr({src: src, alt: alt});
      
      // Move overlay to body to avoid stacking context issues
      $('body').append($overlay);
      $overlay.addClass('is-active').attr('aria-hidden', 'false');
    });
    
    $overlay.on('click', function(e){
      // Close on overlay background click, but not on the image itself
      if ($(e.target).is('.product-gallery__zoom-image')) return;
      // Close on close button click
      if ($(e.target).is('.product-gallery__zoom-close') || $(e.target).closest('.product-gallery__zoom-close').length) {
        closeZoom();
        return;
      }
      // Close on overlay background click
      if ($(e.target).is('.product-gallery__zoom-overlay')) {
        closeZoom();
      }
    });

    // ESC key to close zoom
    $(document).on('keydown', function(e){
      if (e.key === 'Escape' && $overlay.hasClass('is-active')) {
        closeZoom();
      }
    });
  }

  $(function(){
    $('.product-gallery').each(function(){ initProductGallery($(this)); });
  });

})(jQuery);
