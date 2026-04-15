(function ($) {
  function buildMediaControl($input) {
    if ($input.data('eshopMediaReady')) {
      return;
    }

    var currentValue = ($input.val() || '').trim();
    var placeholder = (window.eshopWooSettingsMedia && window.eshopWooSettingsMedia.placeholder) || '';
    var selectLabel = (window.eshopWooSettingsMedia && window.eshopWooSettingsMedia.selectImage) || 'Select image';
    var removeLabel = (window.eshopWooSettingsMedia && window.eshopWooSettingsMedia.removeImage) || 'Remove image';
    var chooseTitle = (window.eshopWooSettingsMedia && window.eshopWooSettingsMedia.chooseImage) || 'Choose image';
    var useLabel = (window.eshopWooSettingsMedia && window.eshopWooSettingsMedia.useImage) || 'Use image';

    var $control = $('<div class="eshop-wc-media-control"></div>');
    var $buttons = $('<div class="eshop-wc-media-actions"></div>');
    var $preview = $('<img class="eshop-wc-media-preview" alt="" />');
    var $selectButton = $('<button type="button" class="button button-secondary"></button>').text(selectLabel);
    var $removeButton = $('<button type="button" class="button button-link-delete"></button>').text(removeLabel);

    function updatePreview(url) {
      var nextUrl = url || placeholder;
      if (nextUrl) {
        $preview.attr('src', nextUrl).show();
      } else {
        $preview.hide().attr('src', '');
      }

      $removeButton.prop('disabled', !url);
    }

    $buttons.append($selectButton, $removeButton);
    $input.wrap('<div class="eshop-wc-media-input-wrap"></div>');
    $input.after($control);
    $control.append($buttons, $preview);

    $selectButton.on('click', function () {
      var frame = wp.media({
        title: chooseTitle,
        button: { text: useLabel },
        multiple: false,
      });

      frame.on('select', function () {
        var attachment = frame.state().get('selection').first().toJSON();
        $input.val(attachment.url).trigger('change');
        updatePreview(attachment.url);
      });

      frame.open();
    });

    $removeButton.on('click', function () {
      $input.val('').trigger('change');
      updatePreview('');
    });

    updatePreview(currentValue);
    $input.data('eshopMediaReady', true);
  }

  $(function () {
    $('.eshop-wc-media-field').each(function () {
      buildMediaControl($(this));
    });
  });
})(jQuery);