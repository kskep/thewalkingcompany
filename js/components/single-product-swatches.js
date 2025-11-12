/**
 * Single Product Swatches Handler
 * 
 * Handles color and size swatch interactions for the concept design
 */

(function() {
    'use strict';

    class SingleProductSwatches {
        constructor() {
            this.init();
        }

        init() {
            this.bindSwatchEvents();
            this.bindSizeEvents();
            this.bindVariationFormEvents();

            // Delay initial sync to allow Woo scripts to hydrate the variation form.
            window.requestAnimationFrame(() => {
                this.initializeFromForms();
                window.setTimeout(() => {
                    this.initializeFromForms();
                }, 60);
            });
        }

        bindSwatchEvents() {
            document.addEventListener('click', (event) => {
                const target = event.target.closest('.swatch');
                if (!target || target.classList.contains('disabled')) {
                    return;
                }

                this.selectInGroup(target, '.swatch');
                this.updateWooCommerceVariation(target);
            });
        }

        bindSizeEvents() {
            document.addEventListener('click', (event) => {
                const target = event.target.closest('.size-tile');
                if (!target || target.classList.contains('disabled')) {
                    return;
                }

                this.selectInGroup(target, '.size-tile');
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
                this.refreshSwatchStates(form);
            });

            $(document).on('found_variation', '.variations_form', (event) => {
                const form = event.currentTarget;
                this.syncSelectedSwatches(form);
            });

            $(document).on('reset_data', '.variations_form', (event) => {
                const form = event.currentTarget;
                this.resetSwatches(form);
            });
        }

        initializeFromForms() {
            document.querySelectorAll('.variations_form').forEach((form) => {
                this.refreshSwatchStates(form);
            });
        }

        selectInGroup(element, selector) {
            const container = element.closest(selector === '.swatch' ? '.color-palette' : '.size-grid');
            if (!container) {
                return;
            }

            container.querySelectorAll(selector).forEach((node) => {
                node.classList.remove('selected');
                node.setAttribute('aria-pressed', 'false');
            });

            element.classList.add('selected');
            element.setAttribute('aria-pressed', 'true');
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

            if (window.jQuery) {
                window.jQuery(select).trigger('change');
            } else {
                const changeEvent = new Event('change', { bubbles: true });
                select.dispatchEvent(changeEvent);
            }

            if (select.form) {
                this.syncSelectedSwatches(select.form);
            }
        }

        refreshSwatchStates(form) {
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
                        // TEMPORARY: Disable auto-disabling to test if this is causing the visual issue
                        // element.classList.toggle('disabled', shouldDisable);
                        // element.setAttribute('aria-disabled', shouldDisable ? 'true' : 'false');

                        if (shouldDisable && element.classList.contains('selected')) {
                            element.classList.remove('selected');
                            element.setAttribute('aria-pressed', 'false');
                        }
                    });
                });
            });

            this.syncSelectedSwatches(form);
        }

        syncSelectedSwatches(form) {
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

        resetSwatches(form) {
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
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            new SingleProductSwatches();
        });
    } else {
        new SingleProductSwatches();
    }

})();