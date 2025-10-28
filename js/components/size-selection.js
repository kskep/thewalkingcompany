(function($){
  'use strict';

  // Handle custom size selector buttons from template-parts/components/size-selection.php
  function handleSizeSelectorClick(e){
    e.preventDefault();
    var $btn = $(this);
    if ($btn.is(':disabled') || $btn.hasClass('out-of-stock')) return;

    var sizeName = $btn.data('size');
    var attribute = $btn.data('attribute'); // e.g., pa_select-size
    var $wrapper = $btn.closest('.variation-wrapper');

    // Visual state
    $btn.siblings('.size-selector-button').removeClass('selected').attr('aria-checked', 'false');
    $btn.addClass('selected').attr('aria-checked', 'true');

    // Update WooCommerce hidden select within this wrapper
    var $select = $wrapper.find('select[name="attribute_'+attribute+'"]').first();
    if ($select.length){
      // Find option whose text matches the size name (term name). Use text compare fallback when value is slug.
      var matchedVal = '';
      $select.find('option').each(function(){
        var $opt = $(this);
        if ($.trim($opt.text()).toLowerCase() === String(sizeName).toLowerCase()){
          matchedVal = $opt.val();
          return false;
        }
      });
      if (matchedVal){
        $select.val(matchedVal).trigger('change');
      }
    }

    // Update label in this wrapper if present
    var $label = $wrapper.find('.selected-value');
    if ($label.length){ $label.text(sizeName); }

    // Trigger Woo variations check
    $wrapper.closest('.variations_form').trigger('check_variations');
  }

  $(function(){
    $(document).on('click', '.size-selector-button', handleSizeSelectorClick);
  });

})(jQuery);
