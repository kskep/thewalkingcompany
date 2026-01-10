/* Front Page Meta UI (slides + banner rows) */
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

	/* Banner Rows functionality */
	function bindBannerRows(){
		const $container = $('#eshop-banner-rows-container');
		if (!$container.length) return;

		// Make rows sortable
		$container.sortable({
			handle: '.row-handle',
			items: '> .eshop-banner-row',
			placeholder: 'eshop-row-placeholder',
			update: function() {
				renumberAllRows();
			}
		});

		// Make banners within each row sortable
		$container.find('.eshop-banners-grid').each(function(){
			$(this).sortable({
				items: '> .eshop-banner-item',
				placeholder: 'eshop-banner-placeholder',
				update: function() {
					renumberAllRows();
					updateGridColumns($(this));
				}
			});
		});

		// Add new row
		$('#eshop-add-banner-row').on('click', function(){
			const rowIndex = $container.find('.eshop-banner-row').length;
			const rowNumber = rowIndex + 1;
			const template = $('#tmpl-eshop-banner-row').html();
			const html = template
				.replace(/\{\{data\.rowIndex\}\}/g, rowIndex)
				.replace(/\{\{data\.rowNumber\}\}/g, rowNumber);
			$container.append(html);
			
			// Make the new row's grid sortable
			const $newRow = $container.find('.eshop-banner-row').last();
			$newRow.find('.eshop-banners-grid').sortable({
				items: '> .eshop-banner-item',
				placeholder: 'eshop-banner-placeholder',
				update: function() {
					renumberAllRows();
					updateGridColumns($(this));
				}
			});
		});

		// Add banner to row
		$container.on('click', '.add-banner-to-row', function(){
			const $row = $(this).closest('.eshop-banner-row');
			const $grid = $row.find('.eshop-banners-grid');
			const bannerCount = $grid.find('.eshop-banner-item').length;
			
			if (bannerCount >= 4) {
				alert('Maximum 4 banners per row.');
				return;
			}

			$grid.find('.no-banners-message').remove();
			
			const rowIndex = $row.data('row-index');
			const bannerIndex = bannerCount;
			const template = $('#tmpl-eshop-banner-item').html();
			const html = template
				.replace(/\{\{data\.rowIndex\}\}/g, rowIndex)
				.replace(/\{\{data\.bannerIndex\}\}/g, bannerIndex);
			
			$grid.append(html);
			updateGridColumns($grid);
			updateRowBannerCount($row);
			
			// Update button state
			if (bannerCount + 1 >= 4) {
				$(this).prop('disabled', true);
			}
		});

		// Remove banner
		$container.on('click', '.remove-banner', function(){
			const $banner = $(this).closest('.eshop-banner-item');
			const $row = $banner.closest('.eshop-banner-row');
			const $grid = $banner.closest('.eshop-banners-grid');
			
			$banner.remove();
			renumberAllRows();
			updateGridColumns($grid);
			updateRowBannerCount($row);
			
			// Re-enable add button
			$row.find('.add-banner-to-row').prop('disabled', false);
		});

		// Remove row
		$container.on('click', '.remove-row', function(){
			if (confirm('Remove this entire row?')) {
				$(this).closest('.eshop-banner-row').remove();
				renumberAllRows();
			}
		});

		// Toggle row collapse
		$container.on('click', '.toggle-row', function(){
			const $row = $(this).closest('.eshop-banner-row');
			const $content = $row.find('.eshop-banner-row-content');
			const $icon = $(this).find('.dashicons');
			
			$content.slideToggle(200);
			$icon.toggleClass('dashicons-arrow-down-alt2 dashicons-arrow-up-alt2');
		});

		// Select banner image
		$container.on('click', '.select-banner-image', function(){
			const $banner = $(this).closest('.eshop-banner-item');
			openMediaFrame(function(att){
				$banner.find('.banner-image-url').val(att.url);
				$banner.find('.banner-preview img').attr('src', att.url);
			});
		});

		function renumberAllRows(){
			$container.find('.eshop-banner-row').each(function(rowIdx){
				$(this).attr('data-row-index', rowIdx);
				$(this).find('.row-label').contents().first().replaceWith('Row ' + (rowIdx + 1) + ' ');
				
				$(this).find('.eshop-banner-item').each(function(bannerIdx){
					$(this).attr('data-banner-index', bannerIdx);
					$(this).find('input').each(function(){
						this.name = this.name.replace(
							/eshop_banner_rows\[\d+\]\[banners\]\[\d+\]/,
							'eshop_banner_rows[' + rowIdx + '][banners][' + bannerIdx + ']'
						);
					});
				});
			});
		}

		function updateGridColumns($grid){
			const count = $grid.find('.eshop-banner-item').length;
			$grid.removeClass('columns-0 columns-1 columns-2 columns-3 columns-4');
			$grid.addClass('columns-' + count);
		}

		function updateRowBannerCount($row){
			const count = $row.find('.eshop-banner-item').length;
			const label = count === 1 ? 'banner' : 'banners';
			$row.find('.banner-count').text('(' + count + ' ' + label + ')');
		}
	}

	$(document).ready(function(){
		$('.eshop-meta.hero').each(function(){ bindRepeater($(this)); });
		$('.eshop-meta.tiles').each(function(){ bindRepeater($(this)); });
		bindBannerRows();
	});
})(jQuery);
