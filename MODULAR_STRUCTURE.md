# E-Shop Theme - Modular Structure

## Overview
The theme has been refactored into a modular structure to keep files manageable and organized. This approach makes the codebase easier to maintain, debug, and extend.

## File Structure

### Core Files
- `functions.php` - Main functions file (now minimal, includes other modules)
- `style.css` - Main stylesheet with theme header
- `header.php` - Site header template
- `footer.php` - Site footer template
- `index.php` - Main template file

### Modular PHP Functions (`/inc/`)
- `theme-setup.php` - Theme setup, widgets, Gutenberg colors, basic filters
- `wishlist-functions.php` - All wishlist-related functionality
- `woocommerce-functions.php` - WooCommerce cart fragments and account functions

### Modular CSS (`/css/`)
- `base.css` - Base utilities and variables only
- `components.header.css` - Header dropdowns and navigation styles
- `components.hero-slider.css` - Hero slider component styles
- `pages.front.css` - Front page specific styles
- `pages.cart-checkout.css` - Cart and checkout page styles
- `pages.shop.css` - Shop and product archive styles

### WooCommerce Templates (`/woocommerce/`)
- `cart/cart.php` - Custom cart page template
- `checkout/form-checkout.php` - Custom checkout form template

### Page Templates
- `page-wishlist.php` - Wishlist page template

## Benefits of Modular Structure

### 1. **Performance**
- CSS files are loaded conditionally based on page type
- Only necessary styles are loaded on each page
- Reduced file sizes and faster loading

### 2. **Maintainability**
- Each component has its own file
- Easy to locate and modify specific functionality
- Reduced risk of breaking unrelated features

### 3. **Scalability**
- Easy to add new modules without cluttering main files
- Clear separation of concerns
- Better code organization

### 4. **Development Efficiency**
- Faster debugging with isolated components
- Multiple developers can work on different modules
- Easier to test individual components

## CSS Loading Strategy

The theme uses conditional CSS loading:

```php
// Always loaded
wp_enqueue_style('eshop-base', ...);
wp_enqueue_style('eshop-header', ...);

// Conditionally loaded
if (is_front_page()) {
    wp_enqueue_style('eshop-page-front', ...);
}

if (is_cart() || is_checkout()) {
    wp_enqueue_style('eshop-cart-checkout', ...);
}

if (is_shop() || is_product_category()) {
    wp_enqueue_style('eshop-shop', ...);
}
```

## PHP Module Loading

All modules are included in `functions.php`:

```php
require_once get_template_directory() . '/inc/theme-setup.php';
require_once get_template_directory() . '/inc/wishlist-functions.php';
require_once get_template_directory() . '/inc/woocommerce-functions.php';
```

## Adding New Modules

### CSS Module
1. Create new file in `/css/` directory
2. Add conditional loading in `functions.php`
3. Follow naming convention: `component.name.css` or `pages.name.css`

### PHP Module
1. Create new file in `/inc/` directory
2. Add security check: `if (!defined('ABSPATH')) { exit; }`
3. Include in `functions.php`
4. Follow naming convention: `feature-functions.php`

## Best Practices

### File Size Guidelines
- **CSS files**: Keep under 500 lines
- **PHP files**: Keep under 300 lines
- **Functions**: Keep under 50 lines each

### Naming Conventions
- **CSS files**: `component.name.css`, `pages.name.css`
- **PHP files**: `feature-functions.php`
- **Functions**: `eshop_feature_action()`

### Code Organization
- Group related functions together
- Use clear, descriptive comments
- Follow WordPress coding standards
- Keep functions focused on single responsibility

## Current Module Breakdown

### `theme-setup.php` (95 lines)
- Theme support features
- Navigation menus
- Widget areas
- Gutenberg colors
- Basic filters

### `wishlist-functions.php` (120 lines)
- Session management
- AJAX handlers
- Wishlist display functions
- Product loop integration

### `woocommerce-functions.php` (85 lines)
- Cart fragments
- Account menu functions
- WooCommerce integration

### CSS Modules
- `base.css` (15 lines) - Utilities only
- `components.header.css` (75 lines) - Header components
- `pages.cart-checkout.css` (180 lines) - Cart/checkout styles
- `pages.shop.css` (220 lines) - Shop page styles

This modular approach ensures the theme remains maintainable and performant as it grows.