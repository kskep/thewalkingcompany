<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Single Product Integration Test</title>
    <link rel="stylesheet" href="css/base.css">
    <link rel="stylesheet" href="css/pages/single-product.css">
    <link rel="stylesheet" href="css/components/product-gallery.css">
    <link rel="stylesheet" href="css/components/size-selection.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container mx-auto px-4 py-8">
        <div id="product-123" class="product">
            
            <!-- Main Product Container with CSS Grid Layout -->
            <div class="product-main-container">
                
                <!-- Left Column: Product Image Gallery -->
                <div class="product-gallery-column">
                    <!-- Simulated Product Gallery Component -->
                    <div class="product-gallery" data-product-id="123">
                        
                        <!-- Sale Badge -->
                        <span class="onsale" aria-label="On sale">Sale</span>
                        
                        <!-- Main Image Container -->
                        <div class="product-gallery__main">
                            <div class="product-gallery__main-image-wrapper">
                                <div class="product-gallery__main-image is-active" data-index="0" role="tabpanel" id="gallery-image-0" aria-hidden="false">
                                    <img src="https://via.placeholder.com/800x800/000000/FFFFFF?text=Product+Image+1" alt="Product Image 1" title="Product Image 1" class="product-gallery__main-image-img" loading="eager" decoding="async" width="800" height="800" style="max-width: 100%; height: auto;">
                                </div>
                                <div class="product-gallery__main-image" data-index="1" role="tabpanel" id="gallery-image-1" aria-hidden="true">
                                    <img src="https://via.placeholder.com/800x800/333333/FFFFFF?text=Product+Image+2" alt="Product Image 2" title="Product Image 2" class="product-gallery__main-image-img" loading="eager" decoding="async" width="800" height="800" style="max-width: 100%; height: auto;">
                                </div>
                                <div class="product-gallery__main-image" data-index="2" role="tabpanel" id="gallery-image-2" aria-hidden="true">
                                    <img src="https://via.placeholder.com/800x800/666666/FFFFFF?text=Product+Image+3" alt="Product Image 3" title="Product Image 3" class="product-gallery__main-image-img" loading="eager" decoding="async" width="800" height="800" style="max-width: 100%; height: auto;">
                                </div>
                            </div>
                        </div>
                        
                        <!-- Thumbnail Gallery -->
                        <div class="product-gallery__thumbnails">
                            <div class="product-gallery__thumbnails-wrapper">
                                <button class="product-gallery__thumbnail is-active" data-index="0" aria-label="View image 1 of 3: Product Image 1" aria-controls="gallery-image-0" aria-selected="true" role="tab">
                                    <img src="https://via.placeholder.com/150x150/000000/FFFFFF?text=1" alt="Product Image 1" loading="lazy" decoding="async">
                                </button>
                                <button class="product-gallery__thumbnail" data-index="1" aria-label="View image 2 of 3: Product Image 2" aria-controls="gallery-image-1" aria-selected="false" role="tab">
                                    <img src="https://via.placeholder.com/150x150/333333/FFFFFF?text=2" alt="Product Image 2" loading="lazy" decoding="async">
                                </button>
                                <button class="product-gallery__thumbnail" data-index="2" aria-label="View image 3 of 3: Product Image 3" aria-controls="gallery-image-2" aria-selected="false" role="tab">
                                    <img src="https://via.placeholder.com/150x150/666666/FFFFFF?text=3" alt="Product Image 3" loading="lazy" decoding="async">
                                </button>
                            </div>
                        </div>
                        
                        <!-- Image Counter -->
                        <div class="product-gallery__counter" aria-live="polite">
                            <span class="product-gallery__counter-current">1</span>
                            <span class="product-gallery__counter-separator">/</span>
                            <span class="product-gallery__counter-total">3</span>
                        </div>
                    </div>
                </div>
                
                <!-- Right Column: Product Details & Actions -->
                <div class="product-details-column">
                    <div class="product-details-wrapper">
                        
                        <!-- Product Title & Code -->
                        <div class="product-header">
                            <h1 class="product_title">Test Product Name</h1>
                            <p class="price">
                                <span class="woocommerce-Price-amount amount">
                                    <bdi><span class="woocommerce-Price-currencySymbol">$</span>89.99</bdi>
                                </span>
                            </p>
                            <div class="woocommerce-product-rating">
                                <div class="star-rating">
                                    <span style="width:80%">Rated <strong>4.00</strong> out of 5</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Product Actions (Variations, Add to Cart) -->
                        <div class="product-actions">
                            <!-- Simulated Size Selection Component -->
                            <div class="size-selection-component" data-attribute-name="pa_select-size">
                                <h3 class="size-selection-label">Select Size</h3>
                                
                                <div class="size-selector-buttons" role="radiogroup" aria-label="Product sizes">
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
                                </div>
                                
                                <div class="size-guide-link">
                                    <button type="button" class="size-guide-trigger" aria-label="View size guide">
                                        Size Guide
                                    </button>
                                </div>
                            </div>
                            
                            <!-- Simulated Color Selection -->
                            <div class="variation-wrapper mb-6" data-attribute="color">
                                <label class="variation-label block text-sm font-semibold text-gray-900 mb-3">
                                    Color:
                                    <span class="selected-value text-gray-600 font-normal"></span>
                                </label>
                                <div class="attribute-options-single flex flex-wrap gap-2">
                                    <span class="attribute-option-single px-4 py-2 border-2 border-gray-300 rounded-lg text-sm font-medium transition-all duration-200 cursor-pointer bg-white text-gray-700 hover:bg-gray-50 hover:border-gray-400" data-value="Black" data-attribute="color">
                                        Black
                                    </span>
                                    <span class="attribute-option-single px-4 py-2 border-2 border-gray-300 rounded-lg text-sm font-medium transition-all duration-200 cursor-pointer bg-white text-gray-700 hover:bg-gray-50 hover:border-gray-400" data-value="White" data-attribute="color">
                                        White
                                    </span>
                                </div>
                            </div>
                            
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
            
        </div><!-- #product-123 -->
    </div>

    <script>
        // Basic functionality test
        document.addEventListener('DOMContentLoaded', function() {
            // Test gallery thumbnail clicks
            const thumbnails = document.querySelectorAll('.product-gallery__thumbnail');
            const mainImages = document.querySelectorAll('.product-gallery__main-image');
            const counterCurrent = document.querySelector('.product-gallery__counter-current');
            
            thumbnails.forEach((thumbnail, index) => {
                thumbnail.addEventListener('click', function() {
                    // Remove active class from all thumbnails and images
                    thumbnails.forEach(t => t.classList.remove('is-active'));
                    mainImages.forEach(img => img.classList.remove('is-active'));
                    
                    // Add active class to clicked thumbnail and corresponding image
                    thumbnail.classList.add('is-active');
                    mainImages[index].classList.add('is-active');
                    
                    // Update counter
                    if (counterCurrent) {
                        counterCurrent.textContent = index + 1;
                    }
                });
            });
            
            // Test size selection
            const sizeButtons = document.querySelectorAll('.size-selector-button:not(.out-of-stock)');
            sizeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    // Remove selected class from all buttons
                    sizeButtons.forEach(b => b.classList.remove('selected'));
                    
                    // Add selected class to clicked button
                    this.classList.add('selected');
                    this.setAttribute('aria-checked', 'true');
                });
            });
            
            // Test color selection
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
        });
    </script>
</body>
</html>