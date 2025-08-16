(function($){
    function openMedia(frameTitle, cb){
        var frame = wp.media({
            title: frameTitle,
            multiple: false,
            library: { type: 'image' }
        });
        frame.on('select', function(){
            var attachment = frame.state().get('selection').first().toJSON();
            cb(attachment);
        });
        frame.open();
    }

    function bindRow($row){
        $row.on('click', '.eshop-select-image', function(){
            var $btn = $(this);
            var target = $btn.data('target');
            openMedia('Select Image', function(att){
                $row.find('input[name$="['+target+']"]').val(att.id);
                var src = att.sizes && att.sizes.medium ? att.sizes.medium.url : att.url;
                $btn.closest('.eshop-image-picker').find('.eshop-image-preview').html('<img src="'+src+'" style="max-width:160px;height:auto;display:block;"/>');
            });
        });
        $row.on('click', '.eshop-remove-image', function(){
            var target = $(this).data('target');
            $row.find('input[name$="['+target+']"]').val('');
            $(this).closest('.eshop-image-picker').find('.eshop-image-preview').empty();
        });
        $row.on('click', '.eshop-remove-slide', function(){
            $row.remove();
        });
    }

    $(document).ready(function(){
        var $box = $('#eshop-hero-slider-metabox');
        if(!$box.length) return;

        // Bind existing rows
        $box.find('.eshop-slide-row').each(function(){ bindRow($(this)); });

        $('#eshop-add-slide').on('click', function(){
            var $list = $box.find('.eshop-slides');
            var index = $list.find('.eshop-slide-row').length;
            var tpl = $('#eshop-slide-template').html().replace(/__INDEX__/g, index);
            var $row = $(tpl);
            $list.append($row);
            bindRow($row);
        });
    });
})(jQuery);
