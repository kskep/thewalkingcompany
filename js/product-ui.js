(function($){
  'use strict';

  // Size Variant Selection (Product Archive)
  $(document).on('click', '.size-option', function(e) {
    e.preventDefault();

    var $sizeOption = $(this);
    var isInStock = $sizeOption.data('in-stock') === true || $sizeOption.data('in-stock') === 'true';

    if (!isInStock) return false;

    $sizeOption.siblings('.size-option').removeClass('active');
    $sizeOption.addClass('active');

    var selectedSize = $sizeOption.data('size');
    var variationId = $sizeOption.data('variation-id');
    var $card = $sizeOption.closest('.product-card');
    $card.data('selected-size', selectedSize);
    $card.data('selected-variation-id', variationId);
  });

  // Custom Variation Selection (Single Product Page)
  $(document).on('click', '.size-option-single, .attribute-option-single', function(e) {
    e.preventDefault();

    var $option = $(this);
    var isInStock = $option.data('in-stock') !== 'false';
    var value = $option.data('value');

    if (!isInStock) return false;

    $option.siblings().removeClass('selected');
    $option.addClass('selected');

    var $wrapper = $option.closest('.variation-wrapper');
    var $select = $wrapper.find('select');
    $select.val(value).trigger('change');

    var $label = $wrapper.find('.selected-value');
    $label.text(value);

    $('.variations_form').trigger('check_variations');
  });

})(jQuery);

