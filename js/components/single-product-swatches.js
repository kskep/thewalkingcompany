/**
 * Consolidated Single Product Variations Handler
 * 
 * Handles all variable product functionality:
 * - Color swatches, size tiles, and other attribute selections
 * - Stock status updates and validation
 * - Add to cart functionality
 * - Variation price updates
 * - Form validation and WooCommerce integration
 * - Quantity controls
 * - Wishlist functionality
 * 
 * Consolidates functionality from:
 * - single-product-swatches.js
 * - single-product-enhancements.js
 * - size-selection.js
 * - Inline JavaScript in variable.php
 */

(function() {
    'use strict';

    class SingleProductVariations {
        constructor() {
            this.init();
        }

        init() {
            this.bindAttributeEvents();
            this.bindVariationFormEvents();
            this.bindQuantityEvents();
            this.bindWishlistEvents();

            // Delay initial sync to allow Woo scripts to hydrate the variation form.
            window.requestAnimationFrame(() => {
                this.initializeFromForms();
                window.setTimeout(() => {
                    this.initializeFromForms();
                }, 60);
            });
        }

        bindAttributeEvents() {
            // Unified click handler for all attribute types
            document.addEventListener('click', (event) => {
                const target = event.target.closest('.swatch, .size-tile, .attribute-option-single');
                if (!target || target.classList.contains('disabled')) {
                    return;
                }

                this.selectAttribute(target);
                this.updateWooCommerceVariation(target);
            });
        }

        bindVariationFormEvents() {
            if (!window.jQuery) {
                return;
            }

            const $ = window.jQuery;

            $(document).on('woocommerce_update_variation_values', '.variations_form', (event) => {
                const form = event.currentTarget;
                this.refreshAttributeStates(form);
            });

            $(document).on('found_variation', '.variations_form', (event, variation) => {
                const form = event.currentTarget;
                this.syncSelectedAttributes(form);
                this.updateStockInfo(variation);
                this.updatePrice(variation);
                this.initializeQuantityControls($(form));
            });

            $(document).on('reset_data', '.variations_form', (event) => {
                const form = event.currentTarget;
                this.resetAttributes(form);
                this.resetStockInfo();
                this.resetPrice();
            });

            $(document).on('updated_wc_div found_variation show_variation', () => {
                this.initializeQuantityControls($(document));
            });
        }

        bindQuantityEvents() {
            if (!window.jQuery) {
                return;
            }

            const $ = window.jQuery;
            // Initialize quantity controls
            this.initializeQuantityControls($(document));

            // Re-initialize when variation form updates
            $(document.body).on('updated_wc_div found_variation show_variation', () => {
                this.initializeQuantityControls($(document));
            });
        }

        bindWishlistEvents() {
            if (!window.jQuery) {
                return;
            }

            const $ = window.jQuery;

            $(document).on('click', '.wishlist-action-btn', (e) => {
                e.preventDefault();
                
                const $btn = $(e.currentTarget);
                const productId = $btn.data('product-id');
                const nonce = $btn.data('nonce');
                
                // Prevent double clicks
                if ($btn.hasClass('loading')) {
                    return;
                }
                
                $btn.addClass('loading');
                
                $.ajax({
                    url: (window.eshop_ajax && window.eshop_ajax.ajax_url) ? window.eshop_ajax.ajax_url : '/wp-admin/admin-ajax.php',
                    type: 'POST',
                    data: {
                        action: 'toggle_wishlist',
                        product_id: productId,
                        nonce: nonce
                    },
                    success: (response) => {
                        if (response.success) {
                            // Toggle visual state
                            $btn.toggleClass('in-wishlist');
                            
                            // Update SVG fill
                            const $svg = $btn.find('svg');
                            if ($btn.hasClass('in-wishlist')) {
                                $svg.attr('fill', 'currentColor');
                                $btn.attr('aria-label', 'Remove from wishlist');
                            } else {
                                $svg.attr('fill', 'none');
                                $btn.attr('aria-label', 'Add to wishlist');
                            }
                            
                            // Show feedback message (if you have a feedback area)
                            if (response.data && response.data.message) {
                                // You can add a toast notification here if needed
                                console.log(response.data.message);
                            }
                            
                            // Trigger custom event for other scripts that might listen
                            $(document).trigger('wishlist_updated', [productId, $btn.hasClass('in-wishlist')]);
                        }
                    },
                    error: () => {
                        console.error('Wishlist update failed');
                    },
                    complete: () => {
                        $btn.removeClass('loading');
                    }
                });
            });
        }

        initializeFromForms() {
            document.querySelectorAll('.variations_form, .eshop-variations-form').forEach((form) => {
                this.refreshAttributeStates(form);
            });
        }

        selectAttribute(element) {
            // Determine the selector and container based on element type
            let selector, container;
            
            if (element.classList.contains('swatch')) {
                selector = '.swatch';
                container = element.closest('.color-palette');
            } else if (element.classList.contains('size-tile')) {
                selector = '.size-tile';
                container = element.closest('.size-grid');
            } else if (element.classList.contains('attribute-option-single')) {
                selector = '.attribute-option-single';
                container = element.closest('.attribute-options-single');
            }

            if (!container) {
                // Fallback to variation wrapper
                container = element.closest('.variation-wrapper');
            }

            if (!container) {
                return;
            }

            // Remove selected state from siblings
            container.querySelectorAll(selector).forEach((node) => {
                node.classList.remove('selected');
                node.setAttribute('aria-pressed', 'false');
            });

            // Add selected state to clicked element
            element.classList.add('selected');
            element.setAttribute('aria-pressed', 'true');

            // Update label if present
            const label = container.closest('.variation-wrapper')?.querySelector('.selected-value');
            if (label) {
                let labelText = element.textContent.trim();
                
                // For swatches, use the swatch-name if available
                if (element.classList.contains('swatch')) {
                    const swatchName = element.querySelector('.swatch-name');
                    if (swatchName) {
                        labelText = swatchName.textContent.trim();
                    }
                }
                
                label.textContent = labelText;
            }
        }

        updateWooCommerceVariation(element) {
            const attribute = element.dataset.attribute;
            const value = element.dataset.value;

            if (!attribute) {
                return;
            }

            const select = document.querySelector(`select[name="${attribute}"]`);
            if (!select) {
                return;
            }

            const previousValue = select.value;
            if (previousValue !== value) {
                select.value = value;
            }

            // Trigger change event
            if (window.jQuery) {
                window.jQuery(select).trigger('change');
            } else {
                const changeEvent = new Event('change', { bubbles: true });
                select.dispatchEvent(changeEvent);
            }

            // Trigger Woo variations check
            if (select.form) {
                if (window.jQuery) {
                    window.jQuery(select.form).trigger('check_variations');
                }
                this.syncSelectedAttributes(select.form);
            }
        }

        refreshAttributeStates(form) {
            if (!form) {
                return;
            }

            const selects = form.querySelectorAll('select[name^="attribute_"]');
            selects.forEach((select) => {
                const attribute = select.name;
                const options = Array.from(select.options).filter((option) => option.value !== '');
                const otherSelected = Array.from(selects).some((other) => other !== select && other.value !== '');
                const currentHasSelection = select.value !== '';

                options.forEach((option) => {
                    const value = option.value;
                    const elements = this.queryAttributeElements(attribute, value);

                    if (!elements.length) {
                        return;
                    }

                    const fallbackAvailable = elements.some((element) => element.dataset.inStock === 'true');
                    const wooDisabled = option.disabled || option.classList.contains('disabled');
                    const useWooConstraint = otherSelected || currentHasSelection;
                    const shouldDisable = useWooConstraint ? wooDisabled : !fallbackAvailable;

                    elements.forEach((element) => {
                        // Only disable if explicitly marked as out of stock, not based on default logic
                        const isExplicitlyOutOfStock = element.dataset.inStock === 'false' || element.disabled;
                        element.classList.toggle('disabled', isExplicitlyOutOfStock);
                        element.setAttribute('aria-disabled', isExplicitlyOutOfStock ? 'true' : 'false');

                        if (shouldDisable && element.classList.contains('selected')) {
                            element.classList.remove('selected');
                            element.setAttribute('aria-pressed', 'false');
                        }
                    });
                });
            });

            this.syncSelectedAttributes(form);
        }

        syncSelectedAttributes(form) {
            if (!form) {
                return;
            }

            const selects = form.querySelectorAll('select[name^="attribute_"]');
            selects.forEach((select) => {
                const attribute = select.name;
                const currentValue = select.value;
                const options = this.queryAttributeElements(attribute);

                options.forEach((element) => {
                    const matches = element.dataset.value === currentValue && currentValue !== '';
                    element.classList.toggle('selected', matches);
                    element.setAttribute('aria-pressed', matches ? 'true' : 'false');

                    if (!matches && !currentValue && element.dataset.default === 'true' && element.dataset.inStock === 'true' && !element.classList.contains('disabled')) {
                        element.classList.add('selected');
                        element.setAttribute('aria-pressed', 'true');
                    }
                });
            });
        }

        resetAttributes(form) {
            if (!form) {
                return;
            }

            const selects = form.querySelectorAll('select[name^="attribute_"]');
            selects.forEach((select) => {
                const attribute = select.name;
                const options = this.queryAttributeElements(attribute);

                options.forEach((element) => {
                    element.classList.remove('selected');
                    element.setAttribute('aria-pressed', 'false');

                    if (element.dataset.default === 'true' && element.dataset.inStock === 'true' && !element.classList.contains('disabled')) {
                        element.classList.add('selected');
                        element.setAttribute('aria-pressed', 'true');
                    }
                });
            });
        }

        queryAttributeElements(attribute, value) {
            const baseSelector = `[data-attribute="${attribute}"]`;

            if (typeof value === 'undefined') {
                return Array.from(document.querySelectorAll(baseSelector));
            }

            let escapedValue = value;
            if (window.CSS && typeof window.CSS.escape === 'function') {
                escapedValue = window.CSS.escape(value);
            }

            return Array.from(document.querySelectorAll(`${baseSelector}[data-value="${escapedValue}"]`));
        }

        updateStockInfo(variation) {
            if (!window.jQuery) {
                return;
            }

            const $ = window.jQuery;
            const $stockInfo = $('.stock-info-row');
            
            if ($stockInfo.length === 0) {
                return;
            }

            const $indicator = $stockInfo.find('.stock-indicator');
            const $text = $stockInfo.find('.stock-text');
            
            if (variation.is_in_stock) {
                $stockInfo.removeClass('out-of-stock').show();
                $indicator.css('background', '#10b981');
                
                if (variation.availability_html) {
                    const stockText = $(variation.availability_html).text() || 'In Stock - Ready to Ship';
                    $text.text(stockText);
                } else {
                    $text.text('In Stock - Ready to Ship');
                }
            } else {
                $stockInfo.addClass('out-of-stock').show();
                $indicator.css('background', '#ef4444');
                $text.text('Out of Stock');
            }
        }

        updatePrice(variation) {
            const priceRow = document.querySelector('.price-row');
            if (!priceRow) {
                return;
            }

            const priceTarget = priceRow.querySelector('.price-amount');
            if (!priceTarget) {
                return;
            }

            const basePrice = priceRow.dataset.basePrice || priceTarget.innerHTML;

            if (variation && variation.price_html) {
                priceTarget.innerHTML = variation.price_html;
            } else {
                priceTarget.innerHTML = basePrice;
            }
        }

        resetPrice() {
            const priceRow = document.querySelector('.price-row');
            if (!priceRow) {
                return;
            }

            const priceTarget = priceRow.querySelector('.price-amount');
            if (!priceTarget) {
                return;
            }

            const basePrice = priceRow.dataset.basePrice;
            if (basePrice) {
                priceTarget.innerHTML = basePrice;
            }
        }

        resetStockInfo() {
            if (!window.jQuery) {
                return;
            }

            const $stockInfo = window.jQuery('.stock-info-row');
            if ($stockInfo.length > 0) {
                $stockInfo.hide();
            }
        }

        initializeQuantityControls($scope) {
            if (!window.jQuery) {
                return;
            }

            const $ = window.jQuery;
            const $containers = ($scope && $scope.length ? $scope : $(document)).find('.cart .quantity');

            $containers.each(function() {
                const $q = $(this);
                if ($q.data('enhanced')) { return; }
                const $input = $q.find('input.qty');
                if ($input.length === 0) { return; }

                // Insert buttons
                const $minus = $('<button type="button" class="qty-btn qty-minus" aria-label="Decrease quantity">&minus;</button>');
                const $plus = $('<button type="button" class="qty-btn qty-plus" aria-label="Increase quantity">+</button>');

                // If not already injected, prepend/append
                if ($q.children('.qty-minus').length === 0) { $q.prepend($minus); }
                if ($q.children('.qty-plus').length === 0) { $q.append($plus); }

                function clamp(val, min, max, step) {
                    let n = parseFloat(val);
                    if (isNaN(n)) n = min || 1;
                    if (!isNaN(min)) n = Math.max(n, min);
                    if (!isNaN(max) && max > 0) n = Math.min(n, max);
                    if (!isNaN(step) && step > 0) {
                        // Snap to nearest step
                        const base = (!isNaN(min) ? min : 0);
                        n = Math.round((n - base) / step) * step + base;
                    }
                    return n;
                }

                function readBounds() {
                    return {
                        min: parseFloat($input.attr('min')) || 1,
                        max: parseFloat($input.attr('max')) || null,
                        step: parseFloat($input.attr('step')) || 1
                    };
                }

                $minus.on('click', function() {
                    const b = readBounds();
                    const curr = parseFloat($input.val()) || b.min;
                    let next = curr - b.step;
                    next = clamp(next, b.min, b.max, b.step);
                    $input.val(next).trigger('change');
                });

                $plus.on('click', function() {
                    const b = readBounds();
                    const curr = parseFloat($input.val()) || b.min;
                    let next = curr + b.step;
                    next = clamp(next, b.min, b.max, b.step);
                    $input.val(next).trigger('change');
                });

                $input.on('change input blur', function() {
                    const b = readBounds();
                    const curr = parseFloat($input.val());
                    const fixed = clamp(curr, b.min, b.max, b.step);
                    if (fixed !== curr) {
                        $input.val(fixed);
                    }
                });

                $q.data('enhanced', true);
            });
        }
    }

    // Initialize the class when DOM is ready
    function initializeVariationsHandler() {
        new SingleProductVariations();
        
        // Ensure Woo variations script notices default selections
        if (window.jQuery) {
            window.jQuery('.variations_form, .eshop-variations-form').trigger('check_variations');
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeVariationsHandler);
    } else {
        initializeVariationsHandler();
    }

})();