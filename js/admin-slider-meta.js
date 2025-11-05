/* Front Page Meta UI (slides + tiles) */
(function($){
	function openMediaFrame(onSelect){
		const frame = wp.media({
			title: 'Select Image',
			button: { text: 'Use image' },
			multiple: false
		});
		frame.on('select', function(){
			const attachment = frame.state().get('selection').first().toJSON();
			if (onSelect) onSelect(attachment);
		});
		frame.open();
	}

	function bindRepeater($box){
		const $list = $box.find('.eshop-repeater-list');
		$list.sortable({ handle: '.handle', items: '> li' });

		$box.on('click', '.select-image', function(){
			const $row = $(this).closest('.eshop-repeater-item, tr');
			openMediaFrame(function(att){
				$row.find('input.img-url').val(att.url).trigger('change');
				$row.find('img').attr('src', att.url);
			});
		});

		$box.on('click', '.link-remove', function(){
			const $row = $(this).closest('.eshop-repeater-item');
			$row.remove();
			renumber($list);
		});

		$box.on('click', '.eshop-add-item', function(){
			const target = $(this).data('target');
			const idx = $list.children().length;
			const $li = $(
				'<li class="eshop-repeater-item">\
					<div class="thumb"><img src="'+window.ajaxurl.replace('admin-ajax.php','images/media/default.png')+'" alt="" /></div>\
					<div class="fields">\
						<input type="hidden" class="img-url" name="'+target+'['+idx+'][url]" value="" />\
						<button type="button" class="button select-image">Select Image</button>\
						<input type="text" class="regular-text alt" placeholder="Alt text (optional)" name="'+target+'['+idx+'][alt]" value="" />\
						<button type="button" class="button link-remove">&times;</button>\
					</div>\
					<span class="dashicons dashicons-move handle"></span>\
				</li>'
			);
			$list.append($li);
		});

		function renumber($list){
			$list.children().each(function(i){
				$(this).find('input').each(function(){
					this.name = this.name.replace(/\[(\d+)\]/, '['+i+']');
				});
			});
		}
	}

	$(document).ready(function(){
		$('.eshop-meta.hero').each(function(){ bindRepeater($(this)); });
		$('.eshop-meta.tiles').each(function(){ bindRepeater($(this)); });
	});
})(jQuery);
