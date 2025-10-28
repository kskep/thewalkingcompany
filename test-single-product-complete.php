<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Complete Single Product Test - The Walking Company</title>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/pages/single-product.css">
    <link rel="stylesheet" href="css/components/product-gallery.css">
    <link rel="stylesheet" href="css/components/size-selection.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Test-specific styles -->
    <style>
        /* Test visualization styles */
        .test-controls {
            position: fixed;
            top: 10px;
            right: 10px;
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 15px;
            border-radius: 0;
            z-index: 9999;
            font-family: monospace;
            font-size: 12px;
            max-width: 300px;
        }
        
        .test-controls h3 {
            margin-top: 0;
            margin-bottom: 10px;
            color: #ee81b3;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .test-controls button {
            background: #ee81b3;
            color: white;
            border: none;
            padding: 5px 10px;
            margin: 2px;
            cursor: pointer;
            border-radius: 0;
            text-transform: uppercase;
            font-size: 10px;
        }
        
        .test-controls button:hover {
            background: #d946a0;
        }
        
        .test-controls .breakpoint-indicator {
            margin-top: 10px;
            padding: 5px;
            background: rgba(255, 255, 255, 0.1);
        }
        
        /* Grid visualization */
        .grid-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            pointer-events: none;
            z-index: 9998;
            display: none;
        }
        
        .grid-overlay.active {
            display: block;
        }
        
        .grid-overlay::before {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 90rem;
            max-width: 90%;
            height: 100%;
            border-left: 1px solid rgba(238, 129, 179, 0.3);
            border-right: 1px solid rgba(238, 129, 179, 0.3);
        }
        
        .grid-overlay::after {
            content: '';
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 90rem;
            max-width: 90%;
            height: 100%;
            background: repeating-linear-gradient(
                90deg,
                transparent,
                transparent 49.9%,
                rgba(238, 129, 179, 0.1) 50%,
                rgba(238, 129, 179, 0.1) 100%
            );
        }
        
        /* Component boundaries */
        .component-boundaries .product-main-container,
        .component-boundaries .product-gallery,
        .component-boundaries .product-details-column,
        .component-boundaries .product-actions,
        .component-boundaries .size-selection-component {
            outline: 2px dashed #ee81b3;
            position: relative;
        }
        
        .component-boundaries .product-main-container::before,
        .component-boundaries .product-gallery::before,
        .component-boundaries .product-details-column::before,
        .component-boundaries .product-actions::before,
        .component-boundaries .size-selection-component::before {
            content: attr(data-component-name);
            position: absolute;
            top: -20px;
            left: 0;
            background: #ee81b3;
            color: white;
            font-size: 10px;
            padding: 2px 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Test scenario styles */
        .test-scenario {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .test-scenario h4 {
            margin-top: 0;
            color: #2F2A26;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 14px;
        }
        
        .test-results {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            padding: 10px;
            margin-top: 10px;
            font-family: monospace;
            font-size: 12px;
        }
        
        .test-results.error {
            background: #fef2f2;
            border-color: #fecaca;
        }
        
        /* Responsive breakpoint indicators */
        @media (max-width: 480px) {
            .breakpoint-indicator::after {
                content: "Mobile (≤480px)";
                color: #dc2626;
            }
        }
        
        @media (min-width: 481px) and (max-width: 768px) {
            .breakpoint-indicator::after {
                content: "Tablet (481px-768px)";
                color: #f59e0b;
            }
        }
        
        @media (min-width: 769px) and (max-width: 1024px) {
            .breakpoint-indicator::after {
                content: "Desktop Small (769px-1024px)";
                color: #3b82f6;
            }
        }
        
        @media (min-width: 1025px) {
            .breakpoint-indicator::after {
                content: "Desktop Large (≥1025px)";
                color: #059669;
            }
        }
    </style>
</head>
<body>
    <!-- Test Controls Panel -->
    <div class="test-controls">
        <h3>Test Controls</h3>
        <button onclick="toggleGrid()">Toggle Grid</button>
        <button onclick="toggleBoundaries()">Toggle Boundaries</button>
        <button onclick="runAllTests()">Run All Tests</button>
        <button onclick="testGallery()">Test Gallery</button>
        <button onclick="testSizeSelection()">Test Sizes</button>
        <button onclick="testColorVariations()">Test Colors</button>
        <button onclick="testResponsive()">Test Responsive</button>
        <button onclick="testAddToCart()">Test Add to Cart</button>
        
        <div class="breakpoint-indicator">
            <strong>Breakpoint:</strong>
        </div>
    </div>
    
    <!-- Grid Overlay -->
    <div class="grid-overlay" id="gridOverlay"></div>
    
    <!-- Test Instructions -->
    <div class="container mx-auto px-4 py-8">
        <div class="test-scenario">
            <h4>Manual Testing Instructions</h4>
            <ol>
                <li><strong>Gallery Testing:</strong> Click thumbnails to switch images, verify counter updates</li>
                <li><strong>Size Selection:</strong> Test in-stock and out-of-stock sizes, verify selection state</li>
                <li><strong>Color Variations:</strong> Click color options, verify selection updates</li>
                <li><strong>Responsive Design:</strong> Resize browser to test breakpoints</li>
                <li><strong>Add to Cart:</strong> Select size/color, test add to cart functionality</li>
                <li><strong>Keyboard Navigation:</strong> Tab through elements, test focus states</li>
                <li><strong>Accessibility:</strong> Test screen reader compatibility</li>
            </ol>
        </div>
        
        <!-- Main Product Container -->
        <div id="product-123" class="product">
            <div class="product-main-container" data-component-name="Product Main Container">
                
                <!-- Left Column: Product Image Gallery -->
                <div class="product-gallery-column" data-component-name="Gallery Column">
                    <!-- Product Gallery Component -->
                    <div class="product-gallery" data-product-id="123" data-component-name="Product Gallery">
                        
                        <!-- Sale Badge -->
                        <span class="onsale" aria-label="On sale">Sale</span>
                        
                        <!-- Main Image Container -->
                        <div class="product-gallery__main">
                            <div class="product-gallery__main-image-wrapper">
                                <div class="product-gallery__main-image is-active" data-index="0" role="tabpanel" id="gallery-image-0" aria-hidden="false">
                                    <img src="https://via.placeholder.com/800x800/000000/FFFFFF?text=Main+Product+Image" alt="Main Product Image" title="Main Product Image" class="product-gallery__main-image-img" loading="eager" decoding="async" width="800" height="800">
                                </div>
                                <div class="product-gallery__main-image" data-index="1" role="tabpanel" id="gallery-image-1" aria-hidden="true">
                                    <img src="https://via.placeholder.com/800x800/333333/FFFFFF?text=Product+Image+2" alt="Product Image 2" title="Product Image 2" class="product-gallery__main-image-img" loading="eager" decoding="async" width="800" height="800">
                                </div>
                                <div class="product-gallery__main-image" data-index="2" role="tabpanel" id="gallery-image-2" aria-hidden="true">
                                    <img src="https://via.placeholder.com/800x800/666666/FFFFFF?text=Product+Image+3" alt="Product Image 3" title="Product Image 3" class="product-gallery__main-image-img" loading="eager" decoding="async" width="800" height="800">
                                </div>
                                <div class="product-gallery__main-image" data-index="3" role="tabpanel" id="gallery-image-3" aria-hidden="true">
                                    <img src="https://via.placeholder.com/800x800/999999/FFFFFF?text=Product+Image+4" alt="Product Image 4" title="Product Image 4" class="product-gallery__main-image-img" loading="eager" decoding="async" width="800" height="800">
                                </div>
                                <div class="product-gallery__main-image" data-index="4" role="tabpanel" id="gallery-image-4" aria-hidden="true">
                                    <img src="https://via.placeholder.com/800x800/CCCCCC/000000?text=Product+Image+5" alt="Product Image 5" title="Product Image 5" class="product-gallery__main-image-img" loading="eager" decoding="async" width="800" height="800">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Thumbnail Gallery -->
                        <div class="product-gallery__thumbnails">
                            <div class="product-gallery__thumbnails-wrapper">
                                <button class="product-gallery__thumbnail is-active" data-index="0" aria-label="View image 1 of 5: Main Product Image" aria-controls="gallery-image-0" aria-selected="true" role="tab">
                                    <img src="https://via.placeholder.com/150x150/000000/FFFFFF?text=1" alt="Main Product Image" loading="lazy" decoding="async">
                                </button>
                                <button class="product-gallery__thumbnail" data-index="1" aria-label="View image 2 of 5: Product Image 2" aria-controls="gallery-image-1" aria-selected="false" role="tab">
                                    <img src="https://via.placeholder.com/150x150/333333/FFFFFF?text=2" alt="Product Image 2" loading="lazy" decoding="async">
                                </button>
                                <button class="product-gallery__thumbnail" data-index="2" aria-label="View image 3 of 5: Product Image 3" aria-controls="gallery-image-2" aria-selected="false" role="tab">
                                    <img src="https://via.placeholder.com/150x150/666666/FFFFFF?text=3" alt="Product Image 3" loading="lazy" decoding="async">
                                </button>
                                <button class="product-gallery__thumbnail" data-index="3" aria-label="View image 4 of 5: Product Image 4" aria-controls="gallery-image-3" aria-selected="false" role="tab">
                                    <img src="https://via.placeholder.com/150x150/999999/FFFFFF?text=4" alt="Product Image 4" loading="lazy" decoding="async">
                                </button>
                                <button class="product-gallery__thumbnail" data-index="4" aria-label="View image 5 of 5: Product Image 5" aria-controls="gallery-image-4" aria-selected="false" role="tab">
                                    <img src="https://via.placeholder.com/150x150/CCCCCC/000000?text=5" alt="Product Image 5" loading="lazy" decoding="async">
                                </button>
                            </div>
                        </div>
                        
                        <!-- Image Counter -->
                        <div class="product-gallery__counter" aria-live="polite">
                            <span class="product-gallery__counter-current">1</span>
                            <span class="product-gallery__counter-separator">/</span>
                            <span class="product-gallery__counter-total">5</span>
                        </div>
                    </div>
                </div>
                
                <!-- Right Column: Product Details & Actions -->
                <div class="product-details-column" data-component-name="Details Column">
                    <div class="product-details-wrapper">
                        
                        <!-- Product Title & Code -->
                        <div class="product-header">
                            <h1 class="product_title">Test Premium Product</h1>
                            <p class="price">
                                <span class="woocommerce-Price-amount amount">
                                    <bdi><span class="woocommerce-Price-currencySymbol">$</span>189.99</bdi>
                                </span>
                            </p>
                            <div class="woocommerce-product-rating">
                                <div class="star-rating">
                                    <span style="width:80%">Rated <strong>4.00</strong> out of 5</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Product Actions -->
                        <div class="product-actions" data-component-name="Product Actions">
                            <!-- Size Selection Component -->
                            <div class="size-selection-component" data-attribute-name="pa_select-size" data-component-name="Size Selection">
                                <h3 class="size-selection-label">Select Size</h3>
                                
                                <div class="size-selector-buttons" role="radiogroup" aria-label="Product sizes">
                                    <!-- Standard sizes -->
                                    <button type="button" class="size-selector-button size-36" role="radio" aria-label="Size 36" aria-checked="false" tabindex="0" data-size="36" data-attribute="pa_select-size">
                                        <span class="size-label">36</span>
                                    </button>
                                    <button type="button" class="size-selector-button size-37" role="radio" aria-label="Size 37" aria-checked="false" tabindex="0" data-size="37" data-attribute="pa_select-size">
                                        <span class="size-label">37</span>
                                    </button>
                                    <button type="button" class="size-selector-button size-38" role="radio" aria-label="Size 38" aria-checked="false" tabindex="0" data-size="38" data-attribute="pa_select-size">
                                        <span class="size-label">38</span>
                                    </button>
                                    <button type="button" class="size-selector-button size-39 out-of-stock" role="radio" aria-label="Size 39 - Out of stock" aria-checked="false" tabindex="-1" data-size="39" data-attribute="pa_select-size" disabled>
                                        <span class="size-label">39</span>
                                    </button>
                                    <button type="button" class="size-selector-button size-40" role="radio" aria-label="Size 40" aria-checked="false" tabindex="0" data-size="40" data-attribute="pa_select-size">
                                        <span class="size-label">40</span>
                                    </button>
                                    <button type="button" class="size-selector-button size-41 out-of-stock" role="radio" aria-label="Size 41 - Out of stock" aria-checked="false" tabindex="-1" data-size="41" data-attribute="pa_select-size" disabled>
                                        <span class="size-label">41</span>
                                    </button>
                                    
                                    <!-- Clothing sizes (for transformation testing) -->
                                    <button type="button" class="size-selector-button size-xsmall" role="radio" aria-label="Size XSmall" aria-checked="false" tabindex="0" data-size="XSmall" data-attribute="pa_select-size">
                                        <span class="size-label">XSmall</span>
                                    </button>
                                    <button type="button" class="size-selector-button size-small" role="radio" aria-label="Size Small" aria-checked="false" tabindex="0" data-size="Small" data-attribute="pa_select-size">
                                        <span class="size-label">Small</span>
                                    </button>
                                    <button type="button" class="size-selector-button size-medium" role="radio" aria-label="Size Medium" aria-checked="false" tabindex="0" data-size="Medium" data-attribute="pa_select-size">
                                        <span class="size-label">Medium</span>
                                    </button>
                                    <button type="button" class="size-selector-button size-large" role="radio" aria-label="Size Large" aria-checked="false" tabindex="0" data-size="Large" data-attribute="pa_select-size">
                                        <span class="size-label">Large</span>
                                    </button>
                                    <button type="button" class="size-selector-button size-xlarge" role="radio" aria-label="Size XLarge" aria-checked="false" tabindex="0" data-size="XLarge" data-attribute="pa_select-size">
                                        <span class="size-label">XLarge</span>
                                    </button>
                                    <button type="button" class="size-selector-button size-one-size" role="radio" aria-label="Size One Size" aria-checked="false" tabindex="0" data-size="One Size" data-attribute="pa_select-size">
                                        <span class="size-label">One Size</span>
                                    </button>
                                </div>
                                
                                <div class="size-guide-link">
                                    <button type="button" class="size-guide-trigger" aria-label="View size guide">
                                        Size Guide
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Color Selection -->
                            <div class="variation-wrapper" data-attribute="color">
                                <label class="variation-label">
                                    Color:
                                    <span class="selected-value"></span>
                                </label>
                                <div class="attribute-options-single">
                                    <span class="attribute-option-single" data-value="Black" data-attribute="color">
                                        Black
                                    </span>
                                    <span class="attribute-option-single" data-value="White" data-attribute="color">
                                        White
                                    </span>
                                    <span class="attribute-option-single" data-value="Red" data-attribute="color">
                                        Red
                                    </span>
                                    <span class="attribute-option-single" data-value="Blue" data-attribute="color">
                                        Blue
                                    </span>
                                    <span class="attribute-option-single" data-value="Green" data-attribute="color">
                                        Green
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Stock Status -->
                            <div class="stock in-stock">In Stock</div>
                            
                            <!-- Quantity and Add to Cart -->
                            <div class="quantity">
                                <label class="screen-reader-text" for="quantity">Quantity</label>
                                <input type="number" id="quantity" class="input-text qty text" step="1" min="1" max="" name="quantity" value="1" title="Qty" size="4" inputmode="numeric">
                            </div>
                            <button type="submit" class="single_add_to_cart_button button alt">Add to cart</button>
                        </div>
                        
                        <!-- Product Meta Information -->
                        <div class="product-meta">
                            <span class="sku_wrapper">SKU: <span>TEST-123</span></span>
                            <span class="posted_in">Category: <a href="#">Test Category</a></span>
                            <span class="tagged_as">Tags: <a rel="tag" href="#">Test Tag</a></span>
                        </div>
                        
                        <!-- Product Sharing -->
                        <div class="product-sharing">
                            <p>Share this product</p>
                        </div>
                        
                    </div>
                </div>
                
            </div><!-- .product-main-container -->
            
            <!-- Test Results Container -->
            <div id="testResults" class="test-results" style="display: none;"></div>
        </div><!-- #product-123 -->
    </div>

    <script>
        // Test Framework
        const TestFramework = {
            results: [],
            
            log: function(test, status, message) {
                this.results.push({
                    test: test,
                    status: status,
                    message: message,
                    timestamp: new Date().toLocaleTimeString()
                });
                this.updateDisplay();
            },
            
            updateDisplay: function() {
                const resultsContainer = document.getElementById('testResults');
                if (resultsContainer) {
                    resultsContainer.style.display = 'block';
                    const latestResult = this.results[this.results.length - 1];
                    resultsContainer.className = 'test-results ' + (latestResult.status === 'error' ? 'error' : '');
                    resultsContainer.innerHTML = `
                        <strong>${latestResult.test}</strong>: ${latestResult.status.toUpperCase()} - ${latestResult.message}
                        <br><small>Time: ${latestResult.timestamp}</small>
                    `;
                }
            },
            
            clear: function() {
                this.results = [];
                const resultsContainer = document.getElementById('testResults');
                if (resultsContainer) {
                    resultsContainer.style.display = 'none';
                }
            }
        };
        
        // Gallery functionality
        document.addEventListener('DOMContentLoaded', function() {
            const thumbnails = document.querySelectorAll('.product-gallery__thumbnail');
            const mainImages = document.querySelectorAll('.product-gallery__main-image');
            const counterCurrent = document.querySelector('.product-gallery__counter-current');
            
            thumbnails.forEach((thumbnail, index) => {
                thumbnail.addEventListener('click', function() {
                    // Remove active class from all thumbnails and images
                    thumbnails.forEach(t => {
                        t.classList.remove('is-active');
                        t.setAttribute('aria-selected', 'false');
                    });
                    mainImages.forEach(img => {
                        img.classList.remove('is-active');
                        img.setAttribute('aria-hidden', 'true');
                    });
                    
                    // Add active class to clicked thumbnail and corresponding image
                    thumbnail.classList.add('is-active');
                    thumbnail.setAttribute('aria-selected', 'true');
                    mainImages[index].classList.add('is-active');
                    mainImages[index].setAttribute('aria-hidden', 'false');
                    
                    // Update counter
                    if (counterCurrent) {
                        counterCurrent.textContent = index + 1;
                    }
                });
            });
            
            // Size selection functionality
            const sizeButtons = document.querySelectorAll('.size-selector-button:not(.out-of-stock)');
            sizeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Remove selected class from all buttons
                    sizeButtons.forEach(b => {
                        b.classList.remove('selected');
                        b.setAttribute('aria-checked', 'false');
                    });
                    
                    // Add selected class to clicked button
                    this.classList.add('selected');
                    this.setAttribute('aria-checked', 'true');
                });
            });
            
            // Color selection functionality
            const colorOptions = document.querySelectorAll('.attribute-option-single');
            colorOptions.forEach(option => {
                option.addEventListener('click', function() {
                    // Remove selected class from all options
                    colorOptions.forEach(o => o.classList.remove('selected'));
                    
                    // Add selected class to clicked option
                    this.classList.add('selected');
                    
                    // Update selected value display
                    const selectedValue = this.closest('.variation-wrapper').querySelector('.selected-value');
                    if (selectedValue) {
                        selectedValue.textContent = this.getAttribute('data-value');
                    }
                });
            });
            
            // Size transformation test
            const clothingSizes = ['XSmall', 'Small', 'Medium', 'Large', 'XLarge', 'One Size'];
            const sizeMapping = {
                'XSmall': 'XS',
                'Small': 'S',
                'Medium': 'M',
                'Large': 'L',
                'XLarge': 'XL',
                'One Size': 'OS'
            };
            
            // Apply size transformation to clothing size buttons
            document.querySelectorAll('.size-selector-button').forEach(button => {
                const sizeLabel = button.querySelector('.size-label');
                if (sizeLabel && clothingSizes.includes(sizeLabel.textContent)) {
                    const originalSize = sizeLabel.textContent;
                    if (sizeMapping[originalSize]) {
                        sizeLabel.textContent = sizeMapping[originalSize];
                        button.setAttribute('data-original-size', originalSize);
                    }
                }
            });
        });
        
        // Test Functions
        function toggleGrid() {
            const gridOverlay = document.getElementById('gridOverlay');
            gridOverlay.classList.toggle('active');
        }
        
        function toggleBoundaries() {
            document.body.classList.toggle('component-boundaries');
        }
        
        function testGallery() {
            TestFramework.clear();
            const thumbnails = document.querySelectorAll('.product-gallery__thumbnail');
            const mainImages = document.querySelectorAll('.product-gallery__main-image');
            const counter = document.querySelector('.product-gallery__counter-current');
            
            try {
                // Test thumbnail count
                if (thumbnails.length === 5) {
                    TestFramework.log('Gallery', 'success', 'Correct number of thumbnails (5)');
                } else {
                    TestFramework.log('Gallery', 'error', `Expected 5 thumbnails, found ${thumbnails.length}`);
                }
                
                // Test main image count
                if (mainImages.length === 5) {
                    TestFramework.log('Gallery', 'success', 'Correct number of main images (5)');
                } else {
                    TestFramework.log('Gallery', 'error', `Expected 5 main images, found ${mainImages.length}`);
                }
                
                // Test thumbnail click
                thumbnails[2].click();
                if (mainImages[2].classList.contains('is-active')) {
                    TestFramework.log('Gallery', 'success', 'Thumbnail click switches main image');
                } else {
                    TestFramework.log('Gallery', 'error', 'Thumbnail click failed to switch main image');
                }
                
                // Test counter update
                if (counter && counter.textContent === '3') {
                    TestFramework.log('Gallery', 'success', 'Counter updates correctly');
                } else {
                    TestFramework.log('Gallery', 'error', 'Counter not updating correctly');
                }
                
            } catch (error) {
                TestFramework.log('Gallery', 'error', `Test failed: ${error.message}`);
            }
        }
        
        function testSizeSelection() {
            TestFramework.clear();
            const sizeButtons = document.querySelectorAll('.size-selector-button');
            const outOfStockButtons = document.querySelectorAll('.size-selector-button.out-of-stock');
            
            try {
                // Test total size buttons
                if (sizeButtons.length >= 11) {
                    TestFramework.log('Size Selection', 'success', `Found ${sizeButtons.length} size buttons`);
                } else {
                    TestFramework.log('Size Selection', 'error', `Expected at least 11 size buttons, found ${sizeButtons.length}`);
                }
                
                // Test out of stock buttons
                if (outOfStockButtons.length >= 2) {
                    TestFramework.log('Size Selection', 'success', `Found ${outOfStockButtons.length} out-of-stock buttons`);
                } else {
                    TestFramework.log('Size Selection', 'error', `Expected at least 2 out-of-stock buttons, found ${outOfStockButtons.length}`);
                }
                
                // Test size selection
                const availableButton = document.querySelector('.size-selector-button:not(.out-of-stock)');
                if (availableButton) {
                    availableButton.click();
                    if (availableButton.classList.contains('selected')) {
                        TestFramework.log('Size Selection', 'success', 'Size selection works');
                    } else {
                        TestFramework.log('Size Selection', 'error', 'Size selection failed');
                    }
                }
                
                // Test size transformation
                const transformedSizes = document.querySelectorAll('[data-original-size]');
                if (transformedSizes.length >= 6) {
                    TestFramework.log('Size Selection', 'success', `Size transformation applied to ${transformedSizes.length} buttons`);
                } else {
                    TestFramework.log('Size Selection', 'error', `Size transformation not working properly`);
                }
                
            } catch (error) {
                TestFramework.log('Size Selection', 'error', `Test failed: ${error.message}`);
            }
        }
        
        function testColorVariations() {
            TestFramework.clear();
            const colorOptions = document.querySelectorAll('.attribute-option-single');
            
            try {
                // Test color options count
                if (colorOptions.length === 5) {
                    TestFramework.log('Color Variations', 'success', 'Found 5 color options');
                } else {
                    TestFramework.log('Color Variations', 'error', `Expected 5 color options, found ${colorOptions.length}`);
                }
                
                // Test color selection
                colorOptions[1].click();
                if (colorOptions[1].classList.contains('selected')) {
                    TestFramework.log('Color Variations', 'success', 'Color selection works');
                } else {
                    TestFramework.log('Color Variations', 'error', 'Color selection failed');
                }
                
                // Test selected value display
                const selectedValue = document.querySelector('.selected-value');
                if (selectedValue && selectedValue.textContent === 'White') {
                    TestFramework.log('Color Variations', 'success', 'Selected value display works');
                } else {
                    TestFramework.log('Color Variations', 'error', 'Selected value display failed');
                }
                
            } catch (error) {
                TestFramework.log('Color Variations', 'error', `Test failed: ${error.message}`);
            }
        }
        
        function testResponsive() {
            TestFramework.clear();
            const width = window.innerWidth;
            
            try {
                // Test current breakpoint
                let breakpoint = '';
                if (width <= 480) {
                    breakpoint = 'Mobile';
                } else if (width <= 768) {
                    breakpoint = 'Tablet';
                } else if (width <= 1024) {
                    breakpoint = 'Desktop Small';
                } else {
                    breakpoint = 'Desktop Large';
                }
                
                TestFramework.log('Responsive', 'success', `Current breakpoint: ${breakpoint} (${width}px)`);
                
                // Test grid layout
                const container = document.querySelector('.product-main-container');
                const computedStyle = window.getComputedStyle(container);
                const gridColumns = computedStyle.gridTemplateColumns;
                
                if (width <= 768 && gridColumns.includes('1fr')) {
                    TestFramework.log('Responsive', 'success', 'Mobile: Single column layout');
                } else if (width > 768 && gridColumns.includes('1fr 1fr')) {
                    TestFramework.log('Responsive', 'success', 'Desktop: Two column layout');
                } else {
                    TestFramework.log('Responsive', 'error', 'Grid layout not responding correctly');
                }
                
            } catch (error) {
                TestFramework.log('Responsive', 'error', `Test failed: ${error.message}`);
            }
        }
        
        function testAddToCart() {
            TestFramework.clear();
            
            try {
                // Test size selection requirement
                const selectedSize = document.querySelector('.size-selector-button.selected');
                if (!selectedSize) {
                    TestFramework.log('Add to Cart', 'error', 'No size selected');
                    return;
                }
                
                // Test color selection requirement
                const selectedColor = document.querySelector('.attribute-option-single.selected');
                if (!selectedColor) {
                    TestFramework.log('Add to Cart', 'error', 'No color selected');
                    return;
                }
                
                // Test quantity input
                const quantityInput = document.getElementById('quantity');
                if (quantityInput && quantityInput.value >= 1) {
                    TestFramework.log('Add to Cart', 'success', `Quantity set to ${quantityInput.value}`);
                } else {
                    TestFramework.log('Add to Cart', 'error', 'Invalid quantity');
                }
                
                // Test add to cart button
                const addToCartButton = document.querySelector('.single_add_to_cart_button');
                if (addToCartButton && !addToCartButton.disabled) {
                    TestFramework.log('Add to Cart', 'success', 'Add to cart button is enabled');
                } else {
                    TestFramework.log('Add to Cart', 'error', 'Add to cart button is disabled');
                }
                
                // Simulate add to cart (in real implementation, this would make an AJAX call)
                TestFramework.log('Add to Cart', 'success', 'Ready to add to cart (simulation)');
                
            } catch (error) {
                TestFramework.log('Add to Cart', 'error', `Test failed: ${error.message}`);
            }
        }
        
        function runAllTests() {
            TestFramework.clear();
            testGallery();
            setTimeout(() => testSizeSelection(), 500);
            setTimeout(() => testColorVariations(), 1000);
            setTimeout(() => testResponsive(), 1500);
            setTimeout(() => testAddToCart(), 2000);
        }
        
        // Initialize with a welcome message
        window.addEventListener('load', function() {
            TestFramework.log('Initialization', 'success', 'Test page loaded successfully');
        });
    </script>
</body>
</html>