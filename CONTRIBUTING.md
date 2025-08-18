# Contributing Guidelines (E‑Shop Theme)

This project aims to keep files small and focused to improve maintainability and performance.

## File Size Rules
- PHP module files: keep under ~300 lines
- CSS files: keep under ~500 lines
- JS modules: keep under ~500 lines
- Functions: ideally under 50 lines

When a file approaches the limit, split logically into a new module:
- Create a folder for the feature if needed: `inc/woocommerce/<feature>.php`
- Update `functions.php` to `require_once` the new module
- For JS, create `js/<feature>.js` and enqueue it in `functions.php`

## Organization Rules
- Group by feature, not by type (e.g., `product-display.php` contains product UI helpers)
- Avoid nested includes inside modules; include everything from `functions.php`
- Add a short header comment to each file explaining its purpose

## PR Checklist
- [ ] New/updated files respect size limits
- [ ] No unrelated changes in the same PR
- [ ] Tested on shop archive and single product pages
- [ ] Lint PHP and JS to WordPress standards

Thank you for helping keep the codebase clean and modular!
