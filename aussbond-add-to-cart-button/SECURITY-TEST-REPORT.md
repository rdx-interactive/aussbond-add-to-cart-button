# Basic Security Test Report

Plugin: Aussbond Add-to-Cart Button
Version: 1.0.12
Date: 2026-07-21

Latest vulnerability check: 2026-05-30. See `VULNERABILITY-CHECK-2026-05-30.md`.

## Scope

Reviewed the custom Elementor widget, WooCommerce AJAX add-to-cart handler, frontend JavaScript, and packaged plugin metadata.

## Checks Performed

| Area | Result | Notes |
| --- | --- | --- |
| Direct file access | Pass | PHP files use `defined( 'ABSPATH' ) || exit;`; directory index files are present. |
| AJAX nonce verification | Pass | `check_ajax_referer()` is required for add-to-cart AJAX requests. |
| Product ID validation | Pass | Product IDs are sanitized with `absint()` and resolved through `wc_get_product()`. |
| Variation ID validation | Pass | Variation must be a `WC_Product_Variation` and must belong to the posted parent product. |
| Variation attribute validation | Pass | Posted `attribute_*` values are sanitized and compared against selected variation attributes. |
| Quantity validation | Pass | Quantity is normalized through WooCommerce stock amount handling and product purchase limits. |
| Stock validation | Pass | Product/variation purchasability, stock status, and enough-stock checks are enforced before cart insertion. |
| Duplicate AJAX submissions | Pass | Identical requests are throttled briefly with a transient and client-side pending-state lock. |
| Output escaping | Pass | Custom output uses WordPress escaping helpers; WooCommerce-rendered HTML is limited to WooCommerce notice/stock/form output. |
| Mini cart update safety | Pass | Fragments are generated through WooCommerce `woocommerce_mini_cart()` and filtered through the standard fragment filter. |
| Privilege boundaries | Pass | Public add-to-cart behavior is available to logged-in and guest customers, matching WooCommerce cart behavior. |
| Static vulnerability pattern scan | Pass | No direct SQL, command execution, file upload/write, unsafe deserialization, or remote request paths were found. |
| Public vulnerability database check | Pass | No public WPScan/Wordfence search result was found for the custom plugin slug at the time of review. |
| JavaScript syntax check | Pass | `node --check assets/js/aussbond-add-to-cart-button.js` completed successfully. |
| Installable ZIP integrity | Pass | `unzip -t aussbond-add-to-cart-button-v1.0.12.zip` completed successfully. |

## Residual Risk

- A full dynamic security scan should be run inside the target WordPress installation before launch because theme code, other WooCommerce extensions, and custom cart filters can alter add-to-cart validation.
- The plugin does not collect payment, store credentials, create users, edit orders, or process checkout data.
- PHP lint, WP-CLI checks, Composer audit, and WPScan CLI checks were not run because `php`, `wp`, `composer`, and `wpscan` are not installed on this machine.

## Recommended Pre-Launch Tests

1. Test simple product add-to-cart with stock enabled and disabled.
2. Test variable products with valid, invalid, out-of-stock, and backorder-enabled variations.
3. Test guest and logged-in customers.
4. Confirm the active theme mini cart updates correctly.
5. Confirm any third-party WooCommerce add-to-cart validation plugins still block restricted products.
