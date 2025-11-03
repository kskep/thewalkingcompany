/**
 * Sticky Add to Cart - Magazine Style
 * Enhanced sticky bar with selection display
 *
 * @package E-Shop Theme
 */

(function($) {
    'use strict';

    function initStickyATC() {
        const stickyBar = document.getElementById('sticky-atc-bar');
        const productActions = document.querySelector('.product-actions');
        const stickySelection = document.querySelector('.sticky-atc__selection');
        const stickyButton = document.querySelector('.sticky-atc__button');

        if (!stickyBar || !productActions) return;

        // Function to update selection text
        function updateSelectionText() {
            const selectedSize = document.querySelector('.size-option-single.selected, .swatch-input:checked + .swatch-label');
            const selectedColor = document.querySelector('.attribute-option-single.selected, .color-swatch-input:checked + .swatch-label');

            let sizeText = '';
            let colorText = '';

            if (selectedSize) {
                if (selectedSize.classList.contains('size-option-single')) {
                    sizeText = selectedSize.dataset.size || '';
                } else {
                    sizeText = selectedSize.textContent.trim();
                }
            }

            if (selectedColor) {
                if (selectedColor.classList.contains('attribute-option-single')) {
                    colorText = selectedColor.textContent.trim();
                } else {
                    colorText = selectedColor.textContent.trim();
                }
            }

            if (stickySelection && (sizeText || colorText)) {
                stickySelection.textContent = `• ${sizeText} / ${colorText}`;
            }
        }

        // Observe size and color selection changes
        const sizeOptions = document.querySelectorAll('.size-option-single, .swatch-input');
        const colorOptions = document.querySelectorAll('.attribute-option-single, .color-swatch-input');

        sizeOptions.forEach(option => {
            option.addEventListener('change', updateSelectionText);
        });

        colorOptions.forEach(option => {
            option.addEventListener('change', updateSelectionText);
        });

        // Also watch for clicks on button-style options
        document.querySelectorAll('.size-option-single, .attribute-option-single').forEach(option => {
            option.addEventListener('click', updateSelectionText);
        });

        // Sticky Bar visibility logic
        const observer = new IntersectionObserver(
            ([entry]) => {
                // isIntersecting is true when the element is in view
                stickyBar.classList.toggle('is-visible', !entry.isIntersecting);
            },
            { rootMargin: '0px 0px -100% 0px' } // Trigger when the element is fully out of view from the top
        );

        observer.observe(productActions);

        // Add to cart functionality for sticky bar
        if (stickyButton) {
            stickyButton.addEventListener('click', function() {
                // Trigger the main add to cart button
                const mainATC = document.querySelector('.single_add_to_cart_button');
                if (mainATC && !mainATC.disabled) {
                    mainATC.click();
                }
            });
        }

        // Update selection when page loads
        setTimeout(updateSelectionText, 100);
    }

    // Initialize on DOM ready
    $(document).ready(function() {
        initStickyATC();
    });

    // Re-initialize when variations change
    $(document).on('found_variation', '.variations_form', function() {
        setTimeout(initStickyATC, 100);
    });

    $(document).on('reset_image', '.variations_form', function() {
        setTimeout(initStickyATC, 100);
    });

})(jQuery);