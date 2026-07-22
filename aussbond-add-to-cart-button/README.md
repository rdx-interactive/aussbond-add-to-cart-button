# Aussbond Add-to-Cart Button

Version: 1.0.23

A production-ready WooCommerce add-to-cart Elementor widget for simple and variable products. The widget renders product options, quantity controls, a customizable add-to-cart button, AJAX cart submission, WooCommerce notices, and mini-cart fragment refreshes without a page reload.

## Compatibility

Verified against current public compatibility targets on May 30, 2026:

- PHP: 7.4 or newer; compatible with PHP 8.1+
- WordPress: requires 6.6+, tested up to 7.0
- WooCommerce: requires 9.0+, tested up to 10.7
- Elementor: compatible with Elementor 4.0.9 and the current Elementor widget registration API

Official references checked:

- WordPress 7.0 release documentation: https://wordpress.org/documentation/wordpress-version/version-7-0/
- WordPress versions list: https://wordpress.org/documentation/article/wordpress-versions/
- WordPress PHP compatibility handbook: https://make.wordpress.org/core/handbook/references/php-compatibility-and-wordpress-versions/
- WooCommerce WordPress.org plugin page: https://wordpress.org/plugins/woocommerce/
- Elementor WordPress.org plugin page: https://wordpress.org/plugins/elementor/

## Installation

1. In WordPress admin, go to Plugins > Add New > Upload Plugin.
2. Upload `aussbond-add-to-cart-button-v1.0.23.zip`.
3. Activate the plugin.
4. Make sure WooCommerce and Elementor are active.

## Usage

1. Edit a page, product template, or product page with Elementor.
2. Search for `Aussbond Add-to-Cart Button`.
3. Drag the widget into the layout.
4. Leave Product ID as `0` to use the current product, or enter a specific WooCommerce product ID.
5. Customize button text, backorder text, colors, typography, spacing, border, width, alignment, quantity field style, and attribute field style from the Elementor panel.

## Behavior

- Simple products show quantity and the custom add-to-cart button.
- Variable products show WooCommerce variation attribute dropdowns before the quantity and button.
- The button text changes to the configured backorder label when the selected product or variation stock status is not `instock`.
- Add-to-cart submits through AJAX, stays on the product page, and avoids WooLentor checkout redirects.
- WooCommerce success/error notices are shown inside the widget.
- WooCommerce mini-cart fragments are refreshed after a successful add-to-cart action.

## Security

The plugin includes:

- Nonce verification for all AJAX add-to-cart requests.
- Sanitized product IDs, variation IDs, quantities, and attributes.
- Escaped frontend/admin output.
- Parent-product validation for variation IDs.
- Stock and purchasability checks through WooCommerce product APIs.
- WooCommerce add-to-cart validation filter support.
- A short duplicate-submission guard for repeated identical AJAX requests.

## Versioning

This plugin follows semantic versioning.

- `1.0.23`: Shows the success popup and refreshes cart fragments when another plugin redirects a successful AJAX add-to-cart response to checkout HTML.
- `1.0.22`: Applied the WooLentor Backorder compatibility bypass directly inside the AJAX add-to-cart flow.
- `1.0.21`: Stopped redirecting after add-to-cart and replaced inline success notices with one popup message.
- `1.0.20`: Added scoped WooLentor Backorder compatibility so Aussbond button submissions are not blocked when WooLentor treats an empty backorder limit as 0 available.
- `1.0.19`: Respect WooCommerce maximum purchase and backorder limits when rendering variation button states, so exhausted backorder variations are not submitted to the cart.
- `1.0.18`: Fixed WooLentor AJAX add-to-cart conflicts by removing the standard WooCommerce AJAX button class and submitting the plugin form directly through the native WooCommerce product-page flow.
- `1.0.17`: Switched all add-to-cart submissions to the native WooCommerce product form flow while keeping selected variation resolution before submit.
- `1.0.16`: Explicitly persists WooCommerce cart totals, session, and cart cookies after successful AJAX add-to-cart to prevent accepted cart items from disappearing on the cart page.
- `1.0.15`: Added a logged-in customer fallback to native WooCommerce form submission so wholesale plugins can process add-to-cart requests through the standard product-page flow.
- `1.0.14`: Improved compatibility with wholesale and WooCommerce validation plugins by posting variation attributes as native top-level WooCommerce fields during AJAX add-to-cart.
- `1.0.13`: Added a fresh AJAX nonce refresh before add-to-cart submission to support logged-in wholesale customers on cached product pages.
- `1.0.12`: Fixed preselected/default variable products by rendering the matching variation ID and enabled button state server-side before frontend variation scripts run.
- `1.0.11`: Fixed variable-product submissions when a variation option is visibly selected but WooCommerce has not populated the hidden variation ID before the custom AJAX submit runs.
- `1.0.10`: Fixed live AJAX add-to-cart registration so cart requests are handled whenever WooCommerce is active, even if Elementor has not finished loading during admin-ajax requests.
- `1.0.9`: Fixed add-to-cart submission compatibility for simple products by matching WooCommerce native validation arguments, and added a native form fallback field for environments where custom AJAX is interrupted.
- `1.0.8`: Renamed the user-facing plugin and Elementor widget display name to `Aussbond Add-to-Cart Button` while keeping the plugin slug stable.
- `1.0.7`: Renamed the user-facing plugin and Elementor widget display name to `Aussbond ACB` while keeping the plugin slug stable.
- `1.0.6`: Hid duplicate visible variation attribute labels like “Size” by default when the custom attribute heading is used, with an Elementor switch to show them if needed.
- `1.0.5`: Added editable main heading and attribute heading controls, fixed heading font-size frontend output, kept quantity number arrows visible, and improved button size/alignment controls.
- `1.0.4`: Fixed WooCommerce purple button override, added full Elementor button styling controls, and added responsive quantity/button layout controls.
- `1.0.3`: Fixed variation-level managed-stock add-to-cart by normalizing selected variation attributes and honoring variation stock quantity, stock status, backorders, and purchasability.
- `1.0.2`: Added scoped WooCommerce stock handling for backorder-enabled products/variations whose stored stock status is still `outofstock`.
- `1.0.1`: Fixed backorder add-to-cart handling for managed-stock products and variations.
- `1.0.0`: Initial production release.

## Files

- `aussbond-add-to-cart-button.php`: Main plugin bootstrap.
- `includes/class-plugin.php`: Dependency checks, asset registration, Elementor registration.
- `includes/class-elementor-widget.php`: Elementor widget controls and rendering.
- `includes/class-ajax.php`: Secure AJAX add-to-cart handler.
- `assets/js/aussbond-add-to-cart-button.js`: Frontend AJAX and variation behavior.
- `assets/css/aussbond-add-to-cart-button.css`: Default widget styling.
- `SECURITY-TEST-REPORT.md`: Basic security review notes.
- `WORDPRESS-7-COMPATIBILITY.md`: WordPress 7 compatibility review notes.
- `VULNERABILITY-CHECK-2026-05-30.md`: Static vulnerability check and public vulnerability database review notes.
