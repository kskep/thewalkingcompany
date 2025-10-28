(function($){
  'use strict';

  function initProductGallery($gallery){
    var $mainSlides = $gallery.find('.product-gallery__main-image');
    var $thumbs = $gallery.find('.product-gallery__thumbnail');
    var $counterCurrent = $gallery.find('.product-gallery__counter-current');
    var total = $mainSlides.length;

    function setActive(index){
      index = Math.max(0, Math.min(index, total - 1));
      $mainSlides.removeClass('is-active').attr('aria-hidden', 'true');
      $mainSlides.filter('[data-index="'+index+'"]').addClass('is-active').attr('aria-hidden', 'false');

      $thumbs.removeClass('is-active').attr('aria-selected', 'false');
      $thumbs.filter('[data-index="'+index+'"]').addClass('is-active').attr('aria-selected', 'true');

      if ($counterCurrent.length){ $counterCurrent.text(index + 1); }
    }

    // Thumbnail click
    $thumbs.on('click', function(e){
      e.preventDefault();
      var index = parseInt($(this).data('index'), 10) || 0;
      setActive(index);
    });

    // Mobile prev/next
    $gallery.find('.product-gallery__mobile-nav--prev').on('click', function(){
      var idx = $mainSlides.index($mainSlides.filter('.is-active')) - 1;
      setActive(idx);
    });
    $gallery.find('.product-gallery__mobile-nav--next').on('click', function(){
      var idx = $mainSlides.index($mainSlides.filter('.is-active')) + 1;
      setActive(idx);
    });

    // Simple zoom overlay for desktop images
    var $overlay = $gallery.find('.product-gallery__zoom-overlay');
    var $zoomImg = $overlay.find('.product-gallery__zoom-image');

    $gallery.on('click', '.product-gallery__main-image-img', function(){
      if (window.innerWidth <= 768) return;
      var src = $(this).attr('src');
      var alt = $(this).attr('alt') || '';
      $zoomImg.attr({src: src, alt: alt});
      $overlay.addClass('is-active').attr('aria-hidden', 'false');
    });
    $overlay.on('click', '.product-gallery__zoom-close, .product-gallery__zoom-overlay', function(){
      $overlay.removeClass('is-active').attr('aria-hidden', 'true');
      $zoomImg.attr({src: '', alt: ''});
    });

    // Initialize counter
    if ($counterCurrent.length){ $counterCurrent.text(1); }
  }

  $(function(){
    $('.product-gallery').each(function(){ initProductGallery($(this)); });
  });

})(jQuery);
