# Size Variants Feature

This feature adds circular size variant buttons to product cards in the shop archive, showing all available sizes with visual indicators for out-of-stock items.

## Features

- ✅ Circular size buttons displayed on product cards
- ✅ Visual distinction between in-stock and out-of-stock sizes
- ✅ Hover effects and interactive states
- ✅ Smart sorting (numeric sizes first, then alphabetic)
- ✅ Support for various size attribute naming conventions
- ✅ Responsive design

## Setup Instructions

### 1. Create Size Attributes

1. Go to **WooCommerce > Products > Attributes**
2. Create a new attribute with one of these names (in order of priority):
   - `size-selection` (recommended)
   - `size_selection`
   - `size`

### 2. Add Size Terms

Add size terms to your attribute, for example:
- Numeric sizes: `36`, `37`, `38`, `39`, `40`, `41`, `42`
- Text sizes: `XS`, `S`, `M`, `L`, `XL`, `XXL`
- Mixed: `36`, `37`, `S`, `M`, `L`

### 3. Create Variable Products

1. Create or edit a product
2. Set **Product Type** to "Variable product"
3. Go to **Attributes** tab
4. Add your size attribute and check "Used for variations"
5. Save the product

### 4. Create Variations

1. Go to **Variations** tab
2. Click "Create variations from all attributes"
3. Set prices and stock status for each variation
4. **Important**: Set some variations to "Out of stock" to test the disabled state

## How It Works

### Display Logic

The size variants will automatically appear on product cards when:
- Product is a variable product
- Product has a size attribute (size-selection, size_selection, or size)
- Product has available variations

### Visual States

- **In Stock**: White background, dark text, hover effects enabled
- **Out of Stock**: Gray background, light text, strikethrough line, no hover effects
- **Selected**: Dark background, white text (when clicked)

### Sorting

Sizes are sorted intelligently:
1. Numeric sizes first (36, 37, 38, 39...)
2. Then alphabetic sizes (S, M, L, XL...)

## Customization

### CSS Classes

You can customize the appearance using these CSS classes:

```css
.size-variants {
    /* Container for all size options */
}

.size-option {
    /* Individual size button */
}

.size-option.active {
    /* Selected size state */
}

.size-option.opacity-50 {
    /* Out of stock size state */
}
```

### JavaScript Events

The size selection triggers JavaScript events you can listen to:

```javascript
$(document).on('click', '.size-option', function() {
    var selectedSize = $(this).data('size');
    var variationId = $(this).data('variation-id');
    var isInStock = $(this).data('in-stock');
    
    // Your custom logic here
});
```

### Function Parameters

You can modify the `eshop_get_product_size_variants()` function call in `content-product.php`:

```php
// Show up to 10 sizes instead of 8
$size_variants = eshop_get_product_size_variants($product, 10);
```

## Testing

1. Use the test page: `yoursite.com/test-size-variants.php`
2. Create variable products with size attributes
3. Set some variations as out of stock
4. Check the shop page to see size variants

## Troubleshooting

### Sizes Not Showing

1. **Check product type**: Must be "Variable product"
2. **Check attribute name**: Should be "size-selection", "size_selection", or "size"
3. **Check variations**: Product must have created variations
4. **Check attribute settings**: Attribute must be "Used for variations"

### Wrong Sorting

- Numeric sizes should come before text sizes
- If sorting is wrong, check that your size values are properly formatted
- Numeric sizes should be pure numbers (36, 37) not text ("36", "37")

### Styling Issues

1. **Check CSS loading**: Ensure `css/pages.shop.css` is loaded
2. **Check conflicts**: Other CSS might be overriding the styles
3. **Check responsive**: Test on different screen sizes

## Files Modified

- `inc/woocommerce-functions.php` - Added `eshop_get_product_size_variants()` function
- `woocommerce/content-product.php` - Added size variants display
- `css/pages.shop.css` - Added size variants styling
- `js/theme.js` - Added size selection JavaScript

## Future Enhancements

Possible improvements:
- Quick add to cart with size selection
- Size guide modal integration
- Color + size combination display
- Stock quantity indicators
- Size availability notifications
