/* Front Page Meta UI (slides + banner rows) */
(function($){
	function openMediaFrame(onSelect, config){
		config = config || {};
		const frame = wp.media({
			title: config.title || 'Select Image',
			button: { text: config.buttonText || 'Use image' },
			library: config.library || undefined,
			multiple: false
		});
		frame.on('select', function(){
			const attachment = frame.state().get('selection').first().toJSON();
			if (onSelect) onSelect(attachment);
		});
		frame.open();
	}

	function updateSlideThumb($row, url, mediaType){
		const defaultImage = window.ajaxurl.replace('admin-ajax.php','images/media/default.png');
		const isVideo = mediaType === 'video' && !!url;
		$row.find('.thumb img, .thumb video').remove();
		if (isVideo) {
			$row.find('.thumb').append('<video src="' + url + '" muted playsinline preload="metadata"></video>');
		} else {
			$row.find('.thumb').append('<img src="' + (url || defaultImage) + '" alt="" />');
		}
		$row.find('input.media-type').val(isVideo ? 'video' : 'image');
	}

	function bindRepeater($box){
		const $list = $box.find('.eshop-repeater-list');
		$list.sortable({ handle: '.handle', items: '> li' });

		$box.on('click', '.select-image', function(){
			const $row = $(this).closest('.eshop-repeater-item, tr');
			openMediaFrame(function(att){
				$row.find('input.img-url').val(att.url).trigger('change');
				updateSlideThumb($row, att.url, att.type === 'video' ? 'video' : 'image');
			}, {
				title: 'Select Image or Video',
				buttonText: 'Use media',
				library: { type: ['image', 'video'] }
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
						<input type="hidden" class="media-type" name="'+target+'['+idx+'][media_type]" value="image" />\
						<button type="button" class="button select-image">Select Image or Video</button>\
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

		// Default image placeholder
		const defaultImage = window.ajaxurl ? window.ajaxurl.replace('admin-ajax.php', 'images/media/default.png') : '';

		// Build row HTML dynamically
		function buildRowHtml(rowIndex) {
			const rowNumber = rowIndex + 1;
			return '<div class="eshop-banner-row" data-row-index="' + rowIndex + '">' +
				'<div class="eshop-banner-row-header">' +
					'<span class="dashicons dashicons-move row-handle"></span>' +
					'<span class="row-label">Row ' + rowNumber + ' <em class="banner-count">(0 banners)</em></span>' +
					'<div class="row-actions">' +
						'<button type="button" class="button button-small add-banner-to-row">' +
							'<span class="dashicons dashicons-plus-alt2"></span> Add Banner' +
						'</button>' +
						'<button type="button" class="button button-small button-link-delete remove-row">' +
							'<span class="dashicons dashicons-trash"></span>' +
						'</button>' +
						'<button type="button" class="button button-small toggle-row">' +
							'<span class="dashicons dashicons-arrow-down-alt2"></span>' +
						'</button>' +
					'</div>' +
				'</div>' +
				'<div class="eshop-banner-row-content">' +
					'<div class="eshop-banners-grid columns-0">' +
						'<p class="no-banners-message">Click "Add Banner" to add banners to this row.</p>' +
					'</div>' +
				'</div>' +
			'</div>';
		}

		// Build banner HTML dynamically
		function buildBannerHtml(rowIndex, bannerIndex) {
			return '<div class="eshop-banner-item" data-banner-index="' + bannerIndex + '">' +
				'<div class="banner-preview">' +
					'<img src="' + defaultImage + '" alt="" />' +
					'<button type="button" class="button-link remove-banner" title="Remove Banner">' +
						'<span class="dashicons dashicons-no-alt"></span>' +
					'</button>' +
				'</div>' +
				'<div class="banner-fields">' +
					'<input type="hidden" class="banner-image-url" name="eshop_banner_rows[' + rowIndex + '][banners][' + bannerIndex + '][image_url]" value="" />' +
					'<input type="hidden" class="banner-media-type" name="eshop_banner_rows[' + rowIndex + '][banners][' + bannerIndex + '][media_type]" value="image" />' +
					'<button type="button" class="button select-banner-image">Select Image or Video</button>' +
					'<input type="text" class="regular-text banner-alt" placeholder="Alt text" name="eshop_banner_rows[' + rowIndex + '][banners][' + bannerIndex + '][alt]" value="" />' +
					'<input type="text" class="regular-text banner-title" placeholder="Title (hover text)" name="eshop_banner_rows[' + rowIndex + '][banners][' + bannerIndex + '][title]" value="" />' +
					'<input type="url" class="regular-text banner-link" placeholder="Link URL" name="eshop_banner_rows[' + rowIndex + '][banners][' + bannerIndex + '][link]" value="" />' +
				'</div>' +
			'</div>';
		}

		function updateBannerPreview($banner, url, mediaType) {
			const safeUrl = url || defaultImage;
			const isVideo = mediaType === 'video' && !!url;
			const previewMarkup = isVideo
				? '<video src="' + safeUrl + '" muted playsinline preload="metadata"></video>'
				: '<img src="' + safeUrl + '" alt="" />';

			$banner.find('.banner-preview img, .banner-preview video').remove();
			$banner.find('.banner-preview').prepend(previewMarkup);
			$banner.find('.banner-media-type').val(isVideo ? 'video' : 'image');
		}

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
			const html = buildRowHtml(rowIndex);
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
			const html = buildBannerHtml(rowIndex, bannerIndex);
			
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
			const frame = wp.media({
				title: 'Select Image or Video',
				button: { text: 'Use media' },
				multiple: false,
				library: { type: ['image', 'video'] }
			});

			frame.on('select', function(){
				const att = frame.state().get('selection').first().toJSON();
				$banner.find('.banner-image-url').val(att.url);
				updateBannerPreview($banner, att.url, (att.type === 'video' ? 'video' : 'image'));
			});

			frame.open();
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
