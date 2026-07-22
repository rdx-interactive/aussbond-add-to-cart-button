# Changelog

## 1.0.18 - 2026-07-22

- Removed the `single_add_to_cart_button` class from the custom button so WooLentor's AJAX add-to-cart script does not treat it as its own AJAX target.
- Added a capture-phase click guard that resolves the selected variation, validates the selection, stops third-party click handlers, and submits the form through the browser's native form submit method.
- Keeps the WooCommerce product form fields intact so WooCommerce, WooLentor, wholesale pricing, and role-validation plugins receive the standard product-page request.

## 1.0.17 - 2026-07-21

- Stopped intercepting the product form submit event for custom AJAX.
- All customers now submit through WooCommerce's native product-page add-to-cart flow.
- The frontend still resolves the selected variation immediately before submit, so the native request contains the correct variation ID.

## 1.0.16 - 2026-07-21

- Explicitly recalculates cart totals after successful AJAX add-to-cart.
- Forces the WooCommerce customer session cookie to be set for the current visitor.
- Explicitly writes the WooCommerce cart session and cart cookies before returning the AJAX success response.

## 1.0.15 - 2026-07-21

- Added a logged-in customer compatibility mode that allows the browser to submit the native WooCommerce product form instead of intercepting it for custom AJAX.
- This lets wholesale, role-pricing, and cart-validation plugins handle add-to-cart through the same standard request path as the default WooCommerce button.
- Added explicit form actions pointing to the product permalink for both simple and variable product forms.

## 1.0.14 - 2026-07-21

- Posted selected variation attributes as top-level WooCommerce form fields such as `attribute_pa_size` in addition to the plugin's nested `attributes` payload.
- Updated server-side attribute sanitization to accept both native top-level WooCommerce variation fields and the nested AJAX attribute payload.
- Improves compatibility with wholesale, pricing, and validation plugins that inspect the native WooCommerce request fields.

## 1.0.13 - 2026-07-21

- Added a lightweight AJAX nonce refresh endpoint for the current visitor/session.
- The frontend now refreshes the nonce immediately before add-to-cart submission, preventing cached guest/product-page nonces from blocking logged-in wholesale customer cart requests.
- Keeps the existing add-to-cart nonce validation while making it resilient to page cache and role-based login sessions.

## 1.0.12 - 2026-07-21

- Fixed default/preselected variable products by resolving the selected variation during PHP render.
- The hidden `variation_id` field now starts with the matching variation ID when all variation attributes are selected.
- The button now renders with the selected variation's enabled/disabled and backorder label state before frontend JavaScript runs.

## 1.0.11 - 2026-07-21

- Fixed variable-product add-to-cart when a variation attribute is already selected but the hidden WooCommerce `variation_id` field is still `0`.
- Added a frontend fallback that resolves the selected variation from the embedded `data-product_variations` payload before AJAX validation/submission.
- Updates the hidden `variation_id` and button state from the resolved variation so selected variable products can be added reliably.

## 1.0.10 - 2026-07-21

- Fixed AJAX add-to-cart action registration on live product pages by registering the AJAX handler whenever WooCommerce is available.
- Removed the unnecessary Elementor dependency from AJAX bootstrapping, so `admin-ajax.php` requests do not return WordPress's plain `0` response when Elementor has not finished loading.
- Kept Elementor dependency checks for widget registration and admin notices.

## 1.0.9 - 2026-07-21

- Fixed simple-product AJAX add-to-cart compatibility by calling WooCommerce's validation filter with the same arguments WooCommerce uses for simple products.
- Added a native `add-to-cart` hidden field to the simple product form so the button has a browser-submit fallback if custom AJAX is blocked by a theme, cache layer, or script conflict.
- Added the `add-to-cart` product ID to the AJAX payload for closer compatibility with WooCommerce add-to-cart integrations.

## 1.0.8 - 2026-05-30

- Renamed the user-facing plugin name and Elementor widget title to `Aussbond Add-to-Cart Button`.
- Kept the plugin folder, main file, text domain, and slug unchanged for install/update continuity.

## 1.0.7 - 2026-05-30

- Renamed the user-facing plugin name and Elementor widget title to `Aussbond ACB`.
- Kept the plugin folder, main file, text domain, and slug unchanged for install/update continuity.

## Compatibility review - 2026-05-30

- Added a WordPress 7 compatibility report.
- Refreshed compatibility documentation with WordPress 7.0 release and PHP compatibility references.
- Added a package-level WordPress vulnerability check report with public WPScan/Wordfence context.

## 1.0.6 - 2026-05-22

- Hid duplicate visible variation attribute labels such as `Size` by default when the custom attribute heading is used.
- Added an Elementor switch to show individual attribute labels again if needed.
- Kept the hidden labels available for screen readers.

## 1.0.5 - 2026-05-22

- Added Elementor content controls for the main heading text and attribute field heading text.
- Added main heading controls for color, explicit font size, typography, margin, and alignment.
- Added attribute heading controls for color, explicit font size, typography, spacing, and alignment.
- Fixed frontend heading font-size output with stronger scoped selectors.
- Kept quantity number input up/down arrows visible instead of only on hover.
- Improved Add to Cart button size and alignment behavior by avoiding mobile CSS overrides that fought Elementor controls.

## 1.0.4 - 2026-05-22

- Fixed WooCommerce `.button.alt` purple background overriding the Aussbond button style.
- Added stronger scoped button selectors for Elementor preview and frontend output.
- Added Elementor controls for button height and box shadow.
- Improved button style selectors for background, text, hover colors, typography, padding, margin, border, radius, width, and shadow.
- Added responsive layout controls for inline/stacked quantity and button layout, left/center/right alignment, and gap spacing.
- Improved default CSS alignment between the quantity selector and Add to Cart button.

## 1.0.3 - 2026-05-22

- Fixed variation-level managed-stock add-to-cart behavior.
- AJAX now normalizes the selected variation ID and selected attributes before calling WooCommerce cart APIs.
- Variation stock handling now honors variation stock quantity, stock status, backorders, purchasability, enough-stock checks, and sold-individually rules.
- Frontend button state now supports Add to Cart, Back Order, and Out of Stock states for managed-stock variations.

## 1.0.2 - 2026-05-22

- Added a scoped WooCommerce stock filter during AJAX add-to-cart so backorder-enabled products and variations are accepted even if their saved stock status remains `outofstock`.
- Added backorder-enabled variations back into the Elementor variation data when WooCommerce omits them due to stock status.
- Deferred custom variation button state updates until after WooCommerce's variation handler finishes.

## 1.0.1 - 2026-05-22

- Fixed add-to-cart behavior for managed-stock products and variations where backorders are allowed.
- Backorder-enabled products now remain addable even when stock status is not plain `instock`.
- Variation button state now uses a dedicated addable flag instead of disabling only from `is_in_stock`.

## 1.0.0 - 2026-05-22

- Initial production release.
- Added Elementor widget `Aussbond ACB`.
- Added support for WooCommerce simple and variable products.
- Added variation attribute dropdowns, quantity selection, AJAX add-to-cart, WooCommerce notices, mini-cart fragment refreshes, and Elementor style controls.
- Added nonce, input sanitization, output escaping, variation validation, stock validation, and duplicate-submission throttling.
- Hardened activation compatibility by lazy-loading the Elementor widget class and avoiding PHP 8-only syntax in the bootstrap path.
