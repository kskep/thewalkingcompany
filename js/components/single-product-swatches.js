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
        }

        bindSwatchEvents() {
            document.addEventListener('click', (e) => {
                if (e.target.closest('.swatch')) {
                    const swatch = e.target.closest('.swatch');
                    const colorPalette = swatch.closest('.color-palette');
                    
                    // Remove selected class from all swatches in this palette
                    colorPalette.querySelectorAll('.swatch').forEach(s => {
                        s.classList.remove('selected');
                    });
                    
                    // Add selected class to clicked swatch
                    swatch.classList.add('selected');
                    
                    // Update the WooCommerce variation selection if available
                    this.updateWooCommerceVariation(swatch);
                }
            });
        }

        bindSizeEvents() {
            document.addEventListener('click', (e) => {
                if (e.target.closest('.size-tile')) {
                    const sizeTile = e.target.closest('.size-tile');
                    const sizeGrid = sizeTile.closest('.size-grid');
                    
                    // Remove selected class from all size tiles in this grid
                    sizeGrid.querySelectorAll('.size-tile').forEach(tile => {
                        tile.classList.remove('selected');
                    });
                    
                    // Add selected class to clicked size tile
                    sizeTile.classList.add('selected');
                    
                    // Update the WooCommerce variation selection if available
                    this.updateWooCommerceVariation(sizeTile);
                }
            });
        }

        updateWooCommerceVariation(element) {
            // Update WooCommerce variation select elements
            const attribute = element.dataset.attribute;
            const value = element.dataset.value;
            
            if (attribute && value) {
                // Find the corresponding WooCommerce select element
                const select = document.querySelector(`select[name="${attribute}"]`);
                if (select) {
                    select.value = value;
                    
                    // Trigger WooCommerce variation change event
                    const event = new Event('change', { bubbles: true });
                    select.dispatchEvent(event);
                }
            }
        }
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            new SingleProductSwatches();
        });
    } else {
        new SingleProductSwatches();
    }

})();