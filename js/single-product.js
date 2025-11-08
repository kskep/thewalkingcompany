/*
(function($){
  $(function(){
    var $sticky = $('.sticky-atc');
    if (!$sticky.length) return;

    var $selection = $sticky.find('.sticky-atc__selection');
    var $price = $sticky.find('.sticky-atc__price');

    function titleCase(s){ if(!s) return ''; return s.charAt(0).toUpperCase()+s.slice(1); }

    function readSelectedAttributes(){
      var parts = [];
      // Try Woo selects
      $('select[name^="attribute_"]').each(function(){
        var val = $(this).val();
        if(val){ parts.push(titleCase(val)); }
      });
      // Fallback to custom selected buttons/spans
      if (parts.length === 0){
        var size = $('.size-selection__button.is-selected').text().trim();
        var color = $('.attribute-option-single.selected').text().trim();
        if(size) parts.push(size);
        if(color) parts.push(color);
      }
      if (parts.length === 0){
        return 'Select options';
      }
      return parts.join(' / ');
    }

    function updateSticky(){
      $selection.text(readSelectedAttributes());
      // Update price from main product price area if present
      var priceHtml = $('.product-header .price').html();
      if (priceHtml){ $price.html(priceHtml); }
    }

    updateSticky();
    $(document).on('change click', 'select[name^="attribute_"], .attribute-option-single, .size-selection__button', function(){
      setTimeout(updateSticky, 50);
    });

    // Forward sticky ATC to the main add to cart
    $sticky.on('click', '.sticky-atc__button', function(){
      var $btn = $('.single_add_to_cart_button:visible').first();
      if ($btn.length) { $btn.trigger('click'); }
    });
  });
})(jQuery);
*/