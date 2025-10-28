/**
 * Size Transformation JavaScript
 * 
 * Handles front-end transformation of full clothing size names 
 * to abbreviations for dynamic content and AJAX-loaded elements.
 * 
 * @package TheWalkingCompany
 */

(function($) {
    'use strict';

    window.TWCSizeTransformation = {
        
        /**
         * Initialize the size transformation functionality
         */
        init: function() {
            this.bindEvents();
            this.transformExistingSizes();
        },

        /**
         * Bind event handlers
         */
        bindEvents: function() {
            var self = this;
            
            // Transform sizes when new content is loaded via AJAX
            $(document).on('ajaxComplete', function() {
                self.transformExistingSizes();
            });
            
            // Transform sizes when variations are updated
            $(document).on('found_variation', function() {
                self.transformExistingSizes();
            });
            
            // Transform sizes when variation form is reset
            $(document).on('reset_data', function() {
                self.transformExistingSizes();
            });
            
            // Watch for DOM changes (for dynamically added content)
            if (window.MutationObserver) {
                var observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.type === 'childList' && mutation.addedNodes.length) {
                            self.transformExistingSizes();
                        }
                    });
                });
                
                observer.observe(document.body, {
                    childList: true,
                    subtree: true
                });
            }
        },

        /**
         * Transform all existing size labels on the page
         */
        transformExistingSizes: function() {
            var self = this;
            
            // Transform size selector buttons
            this.transformSizeSelectorButtons();
            
            // Transform variation dropdowns
            this.transformVariationDropdowns();
            
            // Transform attribute labels
            this.transformAttributeLabels();
            
            // Transform any element with data-size-transform attribute
            this.transformDataAttributes();
        },

        /**
         * Transform size selector buttons
         */
        transformSizeSelectorButtons: function() {
            var self = this;
            
            $('.size-selector-button, .variable-item.button').each(function() {
                var $button = $(this);
                var originalText = $button.text().trim();
                var transformedText = self.transformSize(originalText);
                
                if (originalText !== transformedText) {
                    $button.text(transformedText);
                    // Store original text in data attribute for reference
                    $button.attr('data-original-size', originalText);
                }
            });
        },

        /**
         * Transform variation dropdowns
         */
        transformVariationDropdowns: function() {
            var self = this;
            
            // Transform select options
            $('select.variation-select, select[name^="attribute_"]').each(function() {
                var $select = $(this);
                var isSizeAttribute = false;
                
                // Check if this is a size-related attribute
                var selectName = $select.attr('name') || '';
                if (selectName.indexOf('select-size') !== -1 || selectName.indexOf('size-selection') !== -1) {
                    isSizeAttribute = true;
                }
                
                // Also check by class or data attributes
                if ($select.hasClass('size-attribute') || $select.data('size-transform')) {
                    isSizeAttribute = true;
                }
                
                if (isSizeAttribute) {
                    $select.find('option').each(function() {
                        var $option = $(this);
                        var originalText = $option.text().trim();
                        var transformedText = self.transformSize(originalText);
                        
                        if (originalText !== transformedText) {
                            $option.text(transformedText);
                            $option.attr('data-original-size', originalText);
                        }
                    });
                }
            });
        },

        /**
         * Transform attribute labels
         */
        transformAttributeLabels: function() {
            var self = this;
            
            // Transform span and label elements that contain size information
            $('.attribute-label, .variation-label, .product-attribute').each(function() {
                var $element = $(this);
                var originalText = $element.text().trim();
                var transformedText = self.transformSize(originalText);
                
                if (originalText !== transformedText) {
                    $element.text(transformedText);
                    $element.attr('data-original-size', originalText);
                }
            });
        },

        /**
         * Transform elements with data-size-transform attribute
         */
        transformDataAttributes: function() {
            var self = this;
            
            $('[data-size-transform="true"]').each(function() {
                var $element = $(this);
                var originalText = $element.text().trim();
                var transformedText = self.transformSize(originalText);
                
                if (originalText !== transformedText) {
                    $element.text(transformedText);
                    $element.attr('data-original-size', originalText);
                }
            });
        },

        /**
         * Transform a single size string to its abbreviation
         * 
         * @param {string} size The original size string
         * @return {string} The transformed size abbreviation
         */
        transformSize: function(size) {
            // Use the mapping from localized data if available
            if (typeof twcSizeTransform !== 'undefined' && twcSizeTransform.mapping) {
                return twcSizeTransform.mapping[size] || size;
            }
            
            // Fallback mapping if not localized
            var mapping = {
                'XSmall/Small': 'XS/S',
                'One Size': 'OS',
                'XSmall': 'XS',
                'Small': 'S',
                'Medium': 'M',
                'Large': 'L',
                'XLarge': 'XL',
                'XXLarge': 'XXL',
                'XXXLarge': 'XXXL',
                'Small/Medium': 'S/M',
                'Medium/Large': 'M/L',
                'Large/XLarge': 'L/XL'
            };
            
            return mapping[size] || size;
        },

        /**
         * Get the original size from a transformed element
         * 
         * @param {jQuery} $element The element to check
         * @return {string} The original size string
         */
        getOriginalSize: function($element) {
            return $element.attr('data-original-size') || $element.text().trim();
        },

        /**
         * Revert all transformations to original sizes
         */
        revertTransformations: function() {
            $('[data-original-size]').each(function() {
                var $element = $(this);
                var originalText = $element.attr('data-original-size');
                if (originalText) {
                    $element.text(originalText);
                }
            });
        },

        /**
         * Manually transform specific elements
         * 
         * @param {jQuery} $elements Elements to transform
         */
        transformElements: function($elements) {
            var self = this;
            
            $elements.each(function() {
                var $element = $(this);
                var originalText = $element.text().trim();
                var transformedText = self.transformSize(originalText);
                
                if (originalText !== transformedText) {
                    $element.text(transformedText);
                    $element.attr('data-original-size', originalText);
                }
            });
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        TWCSizeTransformation.init();
    });

    // Make it globally available
    window.TWCSizeTransformation = TWCSizeTransformation;

})(jQuery);