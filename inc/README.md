# inc/ Module Guidelines

To keep PHP files small and maintainable, follow these rules:

- Keep each PHP module under ~300 lines.
- Group narrowly related helpers in one module (e.g., WooCommerce product display helpers).
- Avoid placing unrelated features into the same file.
- Prefer creating a new file under `inc/<feature>/` when a module grows beyond 300 lines.
- Name files by feature: `inc/woocommerce/product-display.php`, `inc/auth/session.php`, etc.
- Functions should generally be under 50 lines and do one thing.
- Document the purpose of the module at the top of the file.

Loading modules:

- Register includes only in `functions.php`.
- Avoid `require`/`include` in submodules to prevent deep trees.

CSS/JS counterparts:

- If a PHP helper has a UI behavior, co-locate JS in `/js/<feature>.js` and enqueue it separately.
- Keep JS modules under ~500 lines each.

