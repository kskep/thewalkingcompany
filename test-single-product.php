<!DOCTYPE html>
<html lang="el">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Single Product Page Test</title>
    
    <!-- Include required CSS -->
    <link rel="stylesheet" href="css/pages/single-product.css">
    <link rel="stylesheet" href="css/components/product-gallery.css">
    <link rel="stylesheet" href="css/components/color-variants.css">
    <link rel="stylesheet" href="css/components/size-selection.css">
    <link rel="stylesheet" href="css/components/wishlist.css">
    
    <!-- Mock CSS Variables for Testing -->
    <style>
        :root {
            --primary-pink: #EE81B3;
            --primary-pink-dark: #D946A0;
            --white: #FFFFFF;
            --off-white: #F9FAFB;
            --ink: #2F2A26;
            --text-secondary: #6B7280;
            --text-lighter: #9CA3AF;
            --text-light: #D1D5DB;
            --border-light: #E5E7EB;
            --border-lighter: #F3F4F6;
            --border-medium: #D1D5DB;
            --background-light: #F9FAFB;
            --letter-spacing-wide: 0.5px;
            --letter-spacing-wider: 1px;
            --line-height-relaxed: 1.6;
        }
        
        /* Mock body styles */
        body {
            font-family: 'Roboto', sans-serif;
            background: var(--off-white);
            margin: 0;
            padding: 0;
        }
        
        /* Mock container */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 1rem;
        }
    </style>
</head>
<body>
    <div class="single-product-wrapper">
        <div class="single-product-container">
            <div class="container">
                <div class="single-product-grid">
                    
                    <!-- Left Column: Product Gallery -->
                    <div class="product-gallery-column">
                        <div class="product-gallery-container" data-product-id="123">
                            <div class="product-main-gallery">
                                <div class="swiper product-main-slider">
                                    <div class="swiper-wrapper">
                                        <div class="swiper-slide">
                                            <div class="gallery-image-wrapper">
                                                <img 
                                                    src="https://images.unsplash.com/photo-1549298916-b41d501d3772?w=800&h=800&fit=crop" 
                                                    alt="Μοκασίνια με Λεπτομέρεια Snake Print" 
                                                    class="gallery-main-image"
                                                    loading="lazy"
                                                />
                                                <button class="zoom-trigger" aria-label="Zoom image">
                                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                                        <path d="M21 21L16.514 16.506M19 10.5C19 15.194 15.194 19 10.5 19S2 15.194 2 10.5 5.806 2 10.5 2 19 5.806 19 10.5Z" stroke="currentColor" stroke-width="2"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="swiper-slide">
                                            <div class="gallery-image-wrapper">
                                                <img 
                                                    src="https://images.unsplash.com/photo-1509638108783-eac3c7733f57?w=800&h=800&fit=crop" 
                                                    alt="Μοκασίνια με Λεπτομέρεια Snake Print - Πλάγια όψη" 
                                                    class="gallery-main-image"
                                                    loading="lazy"
                                                />
                                            </div>
                                        </div>
                                        <div class="swiper-slide">
                                            <div class="gallery-image-wrapper">
                                                <img 
                                                    src="https://images.unsplash.com/photo-1560347876-aeef00ee58a1?w=800&h=800&fit=crop" 
                                                    alt="Μοκασίνια με Λεπτομέρεια Snake Print - Εσωτερικό" 
                                                    class="gallery-main-image"
                                                    loading="lazy"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="gallery-navigation">
                                        <button class="swiper-button-prev gallery-nav-btn" aria-label="Previous image">
                                            <svg width="24" height="24" viewBox="0 0 24 24">
                                                <path d="M15 18L9 12L15 6" stroke="currentColor" stroke-width="2"/>
                                            </svg>
                                        </button>
                                        <button class="swiper-button-next gallery-nav-btn" aria-label="Next image">
                                            <svg width="24" height="24" viewBox="0 0 24 24">
                                                <path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2"/>
                                            </svg>
                                        </button>
                                    </div>
                                    
                                    <div class="gallery-progress">
                                        <span class="current-slide">1</span>
                                        <span class="separator">/</span>
                                        <span class="total-slides">3</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="product-thumbnails-gallery">
                                <div class="swiper product-thumbs-slider">
                                    <div class="swiper-wrapper">
                                        <div class="swiper-slide">
                                            <button class="thumbnail-button active" data-slide-index="0">
                                                <img 
                                                    src="https://images.unsplash.com/photo-1549298916-b41d501d3772?w=150&h=150&fit=crop" 
                                                    alt="Main image" 
                                                    class="thumbnail-image"
                                                />
                                            </button>
                                        </div>
                                        <div class="swiper-slide">
                                            <button class="thumbnail-button" data-slide-index="1">
                                                <img 
                                                    src="https://images.unsplash.com/photo-1509638108783-eac3c7733f57?w=150&h=150&fit=crop" 
                                                    alt="Side view" 
                                                    class="thumbnail-image"
                                                />
                                            </button>
                                        </div>
                                        <div class="swiper-slide">
                                            <button class="thumbnail-button" data-slide-index="2">
                                                <img 
                                                    src="https://images.unsplash.com/photo-1560347876-aeef00ee58a1?w=150&h=150&fit=crop" 
                                                    alt="Inside view" 
                                                    class="thumbnail-image"
                                                />
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Product Badge Overlay -->
                        <div class="product-badges-overlay">
                            <div class="product-badge badge-sale">
                                <span class="badge-text">-25%</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right Column: Product Details -->
                    <div class="product-details-column">
                        <div class="product-summary-container">
                            
                            <!-- Product Title & SKU -->
                            <div class="product-title-container">
                                <h1 class="product-title">
                                    Μοκασίνια με Λεπτομέρεια Snake Print
                                </h1>
                                <div class="product-sku">
                                    <span class="sku-label">Κωδικός:</span>
                                    <span class="sku-value">4683152</span>
                                </div>
                            </div>
                            
                            <!-- Product Pricing -->
                            <div class="product-pricing-container">
                                <div class="pricing-display sale-active">
                                    <div class="price-main">
                                        <span class="current-price">39.99€</span>
                                    </div>
                                    <div class="price-original">
                                        <span class="original-label">Προτ. Λιαν. Τιμή:</span>
                                        <span class="original-price">49.99€</span>
                                    </div>
                                    <div class="discount-badge">
                                        <span class="discount-text">-20%</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Color Variants Section -->
                            <div class="product-variants-section">
                                <h3 class="variants-heading">ΕΠΙΛΟΓΗ ΧΡΩΜΑΤΟΣ</h3>
                                <div class="color-variants-container">
                                    <div class="color-variant selected" data-color-name="Animal Print Μαύρο Φίδι">
                                        <img src="https://images.unsplash.com/photo-1549298916-b41d501d3772?w=80&h=80&fit=crop" alt="Animal Print Μαύρο Φίδι" />
                                    </div>
                                    <div class="color-variant" data-color-name="Animal Print Καφέ Φίδι">
                                        <img src="https://images.unsplash.com/photo-1509638108783-eac3c7733f57?w=80&h=80&fit=crop" alt="Animal Print Καφέ Φίδι" />
                                    </div>
                                    <div class="color-variant out-of-stock" data-color-name="Κλασσικό Μαύρο">
                                        <img src="https://images.unsplash.com/photo-1560347876-aeef00ee58a1?w=80&h=80&fit=crop" alt="Κλασσικό Μαύρο" />
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Size Selection -->
                            <div class="size-selection-container">
                                <label class="size-selection-label">Size</label>
                                <div class="size-options">
                                    <button type="button" class="size-option selected" data-size="36">36</button>
                                    <button type="button" class="size-option" data-size="37">37</button>
                                    <button type="button" class="size-option" data-size="38">38</button>
                                    <button type="button" class="size-option" data-size="39">39</button>
                                    <button type="button" class="size-option out-of-stock" data-size="40" disabled>40</button>
                                    <button type="button" class="size-option" data-size="41">41</button>
                                </div>
                            </div>
                            
                            <!-- Product Actions -->
                            <div class="product-actions-container">
                                <div class="action-buttons-row">
                                    <button type="button" class="btn-primary add-to-cart-btn" data-product-id="123">
                                        <span class="btn-text">ΠΡΟΣΘΗΚΗ ΣΤΟ ΚΑΛΑΘΙ</span>
                                        <div class="btn-loading" style="display: none;">
                                            <svg class="btn-spinner" width="20" height="20" viewBox="0 0 24 24">
                                                <path d="M12 2v4m0 12v4M4.93 4.93l2.83 2.83m8.48 8.48l2.83 2.83M2 12h4m12 0h4M4.93 19.07l2.83-2.83m8.48-8.48l2.83-2.83"/>
                                            </svg>
                                        </div>
                                    </button>
                                    <button type="button" class="btn-secondary wishlist-btn" data-product-id="123">
                                        <svg class="heart-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                            <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
                                        </svg>
                                        <span class="wishlist-text">Προσθήκη στα αγαπημένα</span>
                                    </button>
                                </div>
                                
                                <div class="product-availability" id="product-availability">
                                    <span class="stock-status in-stock">
                                        <svg width="16" height="16" viewBox="0 0 24 24">
                                            <path d="M9 12l2 2 4-4M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Σε απόθεμα
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Product Description -->
                            <div class="product-short-description">
                                <h4 class="description-title">Περιγραφή</h4>
                                <div class="description-content">
                                    Μοκασίνια με snake print λεπτομέρεια. Μαλακά και άνετα για καθημερινή χρήση. Κατασκευασμένα από premium υλικά για μακροχρόνια αντοχή.
                                </div>
                            </div>
                            
                        </div>
                        
                        <!-- Product Information Accordions -->
                        <div class="product-accordions-container">
                            
                            <!-- ΧΑΡΑΚΤΗΡΙΣΤΙΚΑ -->
                            <div class="product-accordion">
                                <button class="accordion-header" aria-expanded="false" aria-controls="panel-characteristics">
                                    <span class="accordion-title">ΧΑΡΑΚΤΗΡΙΣΤΙΚΑ</span>
                                    <svg class="accordion-icon" width="24" height="24">
                                        <path d="M12 5v14m0 0l6-6m-6 6l-6-6"/>
                                    </svg>
                                </button>
                                <div class="accordion-panel" id="panel-characteristics">
                                    <div class="accordion-content">
                                        <div class="product-attributes">
                                            <div class="attribute-row">
                                                <dt class="attribute-label">Υλικό</dt>
                                                <dd class="attribute-value">Δερματινα</dd>
                                            </div>
                                            <div class="attribute-row">
                                                <dt class="attribute-label">Χρώμα</dt>
                                                <dd class="attribute-value">Animal Print</dd>
                                            </div>
                                            <div class="attribute-row">
                                                <dt class="attribute-label">Στυλ</dt>
                                                <dd class="attribute-value">Μοκασίνια</dd>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- ΑΠΟΣΤΟΛΕΣ & ΕΠΙΣΤΡΟΦΕΣ -->
                            <div class="product-accordion">
                                <button class="accordion-header" aria-expanded="false" aria-controls="panel-shipping">
                                    <span class="accordion-title">ΑΠΟΣΤΟΛΕΣ & ΕΠΙΣΤΡΟΦΕΣ</span>
                                    <svg class="accordion-icon" width="24" height="24">
                                        <path d="M12 5v14m0 0l6-6m-6 6l-6-6"/>
                                    </svg>
                                </button>
                                <div class="accordion-panel" id="panel-shipping">
                                    <div class="accordion-content">
                                        <div class="shipping-info">
                                            <h4>Τρόποι Αποστολής</h4>
                                            <ul class="shipping-methods">
                                                <li><strong>Standard Delivery</strong> <span>3-5 εργάσιμες ημέρες - €4.99</span></li>
                                                <li><strong>Express Delivery</strong> <span>1-2 εργάσιμες ημέρες - €8.99</span></li>
                                                <li><strong>Αυθημερόν Παράδοση</strong> <span>Θεσσαλονίκη/Αθήνα - €12.99</span></li>
                                            </ul>
                                            
                                            <h4>Πολιτική Επιστροφών</h4>
                                            <div class="returns-policy">
                                                <p>Δικαιούστε επιστροφή ή ανταλλαγή εντός 14 ημερών από την παραλαβή του προϊόντος.</p>
                                                <ul>
                                                    <li>Το προϊόν πρέπει να είναι σε άριστη κατάσταση</li>
                                                    <li>Με ετικέτες και συσκευασία</li>
                                                    <li>Δεν δέχονται επιστροφές σε εσώρρουχα για λόγους υγιεινής</li>
                                                </ul>
                                                <p><strong>Χρέωση Επιστροφής:</strong> Ο πελάτης επιβαρύνεται με τα έξοδα αποστολής</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- ΧΡΕΙΑΖΕΣΑΙ ΒΟΗΘΕΙΑ -->
                            <div class="product-accordion">
                                <button class="accordion-header" aria-expanded="false" aria-controls="panel-help">
                                    <span class="accordion-title">ΧΡΕΙΑΖΕΣΑΙ ΒΟΗΘΕΙΑ;</span>
                                    <svg class="accordion-icon" width="24" height="24">
                                        <path d="M12 5v14m0 0l6-6m-6 6l-6-6"/>
                                    </svg>
                                </button>
                                <div class="accordion-panel" id="panel-help">
                                    <div class="accordion-content">
                                        <div class="help-section">
                                            <h4>Επικοινωνία</h4>
                                            <div class="contact-info">
                                                <p><strong>Τηλέφωνο:</strong> <a href="tel:+302311234567">+30 231 123 4567</a></p>
                                                <p><strong>Email:</strong> <a href="mailto:info@thewalkingcompany.gr">info@thewalkingcompany.gr</a></p>
                                                <p><strong>Ώρες Λειτουργίας:</strong> Δευτέρα - Παρασκευή: 9:00 - 18:00</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="js/single-product.js"></script>
    
    <script>
        // Mock data for testing
        window.woocommerce_params = {
            ajax_url: '/wp-admin/admin-ajax.php',
            nonce: 'test_nonce'
        };
        
        // Initialize components on page load
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Single Product Page Test Page Loaded');
            console.log('Components initialized:');
            console.log('- Product Title & SKU');
            console.log('- Pricing Display');
            console.log('- Gallery Navigation');
            console.log('- Color Variants');
            console.log('- Size Selection');
            console.log('- Add to Cart & Wishlist');
            console.log('- Information Accordions');
        });
    </script>
</body>
</html>