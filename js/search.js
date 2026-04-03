/**
 * Search Modal & Live AJAX Search
 */
(function ($) {
    'use strict';

    var $modal = $('#search-modal');
    var $overlay = $modal.find('.search-modal__overlay');
    var $input = $modal.find('.search-modal__input');
    var $results = $modal.find('.search-modal__results');
    var $close = $modal.find('.search-modal__close');
    var debounceTimer = null;
    var selectedIndex = -1;
    var ajaxRequest = null;

    // --- Open / Close ---

    function openModal() {
        $modal.removeClass('hidden');
        $('body').css('overflow', 'hidden');
        $input.val('');
        $results.html('<div class="search-modal__empty"><p class="search-modal__hint">Eisagete keimeno gia anazitisi proionton...</p></div>');
            selectedIndex = -1;
            return;
        }

        if (ajaxRequest) {
            ajaxRequest.abort();
        }

        $results.html('<div class="search-modal__loading"><i class="fas fa-spinner fa-spin"></i> Anazitisi...</div>');

        ajaxRequest = $.ajax({
            url: eshop_ajax.ajax_url,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'eshop_live_search',
                keyword: keyword,
                nonce: eshop_ajax.nonce
            },
            success: function (response) {
                ajaxRequest = null;
                if (response.success && response.data) {
                    renderResults(response.data, keyword);
                } else {
                    $results.html('<div class="search-modal__no-results">Den vrethikan proionta.</div>');
                }
            },
            error: function (xhr) {
                ajaxRequest = null;
                if (xhr.statusText !== 'abort') {
                    $results.html('<div class="search-modal__no-results">Kati pige strava. Parakaloume dokimaste ksana.</div>');
                }
            }
        });
    }

    function renderResults(data, keyword) {
        var products = data.products || [];
        var totalCount = data.total_count || 0;
        selectedIndex = -1;

        if (products.length === 0) {
            $results.html(
                '<div class="search-modal__no-results">' +
                    '<p>Den vrethikan proionta gia "<strong>' + escapeHtml(keyword) + '</strong>"</p>' +
                '</div>' +
                '<a href="' + escapeHtml(eshop_ajax.home_url || '/') + '?s=' + encodeURIComponent(keyword) + '" class="search-modal__view-all">' +
                    'Deite oles tis eggrafes' +
                '</a>'
            );
            return;
        }

        var html = '';

        if (totalCount > products.length) {
            html += '<div class="search-modal__count">Emfanizontai ' + products.length + ' apo ' + totalCount + ' apotelesmata</div>';
        }

        for (var i = 0; i < products.length; i++) {
            var p = products[i];
            html +=
                '<a href="' + escapeHtml(p.url) + '" class="search-modal__result-item" data-index="' + i + '">' +
                    '<img src="' + escapeHtml(p.image) + '" alt="" class="search-modal__result-image" loading="lazy" />' +
                    '<div class="search-modal__result-info">' +
                        '<p class="search-modal__result-title">' + escapeHtml(p.title) + '</p>' +
                        (p.sku ? '<p class="search-modal__result-sku">SKU: ' + escapeHtml(p.sku) + '</p>' : '') +
                    '</div>' +
                    '<div class="search-modal__result-price">' + p.price + '</div>' +
                '</a>';
        }

        html +=
            '<a href="' + escapeHtml(eshop_ajax.home_url || '/') + '?s=' + encodeURIComponent(keyword) + '" class="search-modal__view-all">' +
                'Deite ola ta ' + totalCount + ' apotelesmata' +
            '</a>';

        $results.html(html);
    }

    function escapeHtml(str) {
        if (!str) return '';
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    }

    // --- Input Debounce ---

    $input.on('input', function () {
        var keyword = $(this).val().trim();
        if (debounceTimer) {
            clearTimeout(debounceTimer);
        }
        debounceTimer = setTimeout(function () {
            performSearch(keyword);
        }, 300);
    });

    // --- Keyboard Navigation ---

    $input.on('keydown', function (e) {
        var $items = $results.find('.search-modal__result-item');
        if ($items.length === 0) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            selectedIndex = Math.min(selectedIndex + 1, $items.length - 1);
            updateSelection($items);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            selectedIndex = Math.max(selectedIndex - 1, 0);
            updateSelection($items);
        } else if (e.key === 'Enter' && selectedIndex >= 0) {
            e.preventDefault();
            var href = $items.eq(selectedIndex).attr('href');
            if (href) {
                window.location.href = href;
            }
        }
    });

    function updateSelection($items) {
        $items.removeClass('selected');
        if (selectedIndex >= 0 && selectedIndex < $items.length) {
            $items.eq(selectedIndex).addClass('selected');
            // Scroll into view
            var $selected = $items.eq(selectedIndex);
            var container = $results[0];
            if ($selected.position().top + $selected.outerHeight() > container.clientHeight) {
                container.scrollTop += $selected.outerHeight();
            } else if ($selected.position().top < 0) {
                container.scrollTop += $selected.position().top;
            }
        }
    }

    // --- Prevent form submission when live results are showing ---
    $modal.find('.search-modal__form').on('submit', function (e) {
        var val = $input.val().trim();
        if (!val) {
            e.preventDefault();
        }
    });

})(jQuery);
