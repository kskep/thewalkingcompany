# inc/ Module Documentation

This directory contains modular PHP includes for The Walking Company WordPress theme.

## Module Guidelines

To keep PHP files small and maintainable, follow these rules:

- Keep each PHP module under ~300 lines.
- Group narrowly related helpers in one module (e.g., WooCommerce product display helpers).
- Avoid placing unrelated features into the same file.
- Prefer creating a new file under `inc/<feature>/` when a module grows beyond 300 lines.
- Name files by feature: `inc/woocommerce/product-display.php`, `inc/auth/session.php`, etc.
- Functions should generally be under 50 lines and do one thing.
- Document the purpose of the module at the top of the file.
- Prefix all functions with `eshop_` to avoid conflicts.
- Guard all files with `if (!defined('ABSPATH')) exit;`.

---

## Module Reference

### Core Setup

| File | Purpose | Key Functions |
|------|---------|---------------|
| `theme-setup.php` | Theme supports, menus, widgets, image sizes, body classes | `eshop_theme_setup()`, `eshop_register_menus()`, `eshop_register_sidebars()` |
| `enqueue-scripts.php` | Asset enqueueing (CSS/JS) with conditional loading | `eshop_enqueue_styles()`, `eshop_enqueue_scripts()` |
| `helpers.php` | General utility functions used across the theme | Various helper functions |

### WooCommerce

| File | Purpose | Key Functions |
|------|---------|---------------|
| `woocommerce-functions.php` | WooCommerce hooks, filters, cart fragments, related products, account menu | `eshop_cart_fragment()`, `eshop_get_account_menu_items()`, `eshop_output_related_products_from_categories()` |
| `ajax-handlers.php` | AJAX endpoints for cart and filtering | `eshop_remove_cart_item_ajax()`, `eshop_get_flying_cart_content_ajax()`, `eshop_filter_products()` |
| `class-product-filters.php` | Product filtering logic (URL params, context queries) | `Eshop_Product_Filters::handle_custom_filters()`, `eshop_get_available_attribute_terms()`, `eshop_get_current_context_product_ids()` |
| `woocommerce/product-display.php` | Product display helpers | Product card rendering utilities |
| `woocommerce/size-transformation.php` | Size variant transformation logic | Size selection helpers |

### Features

| File | Purpose | Key Functions |
|------|---------|---------------|
| `wishlist-functions.php` | Wishlist functionality (AJAX, storage, display) | `eshop_wishlist_button()`, wishlist AJAX handlers |
| `auth-functions.php` | User authentication (login, register, password reset) | Login/register modal handlers |
| `color-grouping-functions.php` | Color variant grouping for products | `eshop_get_color_variants()` |
| `config-colors.php` | Color configuration and mappings | Color definitions |
| `mega-menu-walker.php` | Custom Walker class for mega menu | `Eshop_Mega_Menu_Walker` class |

### Admin/Meta

| File | Purpose | Key Functions |
|------|---------|---------------|
| `front-fields.php` | Front page ACF/custom fields | Field definitions |
| `front-page-meta.php` | Front page meta box handling | Meta box callbacks |

---

## Dependencies

```
functions.php (loader)
    ├── theme-setup.php
    ├── helpers.php
    ├── enqueue-scripts.php
    ├── class-product-filters.php
    ├── wishlist-functions.php
    ├── woocommerce-functions.php
    ├── ajax-handlers.php
    ├── woocommerce/
    │   ├── product-display.php
    │   └── size-transformation.php
    ├── mega-menu-walker.php
    ├── color-grouping-functions.php
    ├── auth-functions.php
    ├── front-fields.php
    └── front-page-meta.php
```

---

## Loading Modules

- Register includes only in `functions.php`.
- Avoid `require`/`include` in submodules to prevent deep trees.
- Use `get_template_directory()` for paths.

Example:
```php
require_once get_template_directory() . '/inc/your-module.php';
```

---

## Adding New Modules

1. Create a new file: `inc/your-feature.php`
2. Add the security guard at the top:
   ```php
   <?php
   if (!defined('ABSPATH')) {
       exit;
   }
   ```
3. Document the module purpose in a header comment
4. Prefix all functions with `eshop_`
5. Add `require_once` in `functions.php`
6. Update this README with the new module

---

## CSS/JS Counterparts

- If a PHP helper has a UI behavior, co-locate JS in `/js/components/<feature>.js`
- Enqueue assets conditionally in `inc/enqueue-scripts.php`
- Keep JS modules under ~500 lines each
- Component styles go in `css/components/<component>.css`

### File Mapping

| PHP Module | JS Counterpart | CSS Counterpart |
|------------|----------------|-----------------|
| `woocommerce-functions.php` | `js/components/flying-cart.js` | `css/components/flying-cart.css` |
| `class-product-filters.php` | `js/components/filters.js` | `css/components/filters.css` |
| `wishlist-functions.php` | `js/components/wishlist.js` | `css/components/wishlist.css` |
| `auth-functions.php` | `js/components/auth-modal.js` | `css/components/auth-modal.css` |

---

## Security Best Practices

- Always sanitize input: `sanitize_text_field()`, `absint()`, `esc_html()`, etc.
- Verify AJAX nonces: `wp_verify_nonce($nonce, 'eshop_nonce')`
- Use `wp_send_json_success()` / `wp_send_json_error()` for AJAX responses
- Escape output: `esc_html()`, `esc_attr()`, `esc_url()`

---

## Related Documentation

- [Copilot Instructions](../.github/copilot-instructions.md) - AI coding guidelines
- [Refactoring Plan](./REFACTORING_PLAN.md) - Ongoing code improvements
