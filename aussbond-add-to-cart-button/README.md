# Aussbond ACB

Version: 1.0.7

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
2. Upload `aussbond-add-to-cart-button-v1.0.7.zip`.
3. Activate the plugin.
4. Make sure WooCommerce and Elementor are active.

## Usage

1. Edit a page, product template, or product page with Elementor.
2. Search for `Aussbond ACB`.
3. Drag the widget into the layout.
4. Leave Product ID as `0` to use the current product, or enter a specific WooCommerce product ID.
5. Customize button text, backorder text, colors, typography, spacing, border, width, alignment, quantity field style, and attribute field style from the Elementor panel.

## Behavior

- Simple products show quantity and the custom add-to-cart button.
- Variable products show WooCommerce variation attribute dropdowns before the quantity and button.
- The button text changes to the configured backorder label when the selected product or variation stock status is not `instock`.
- AJAX add-to-cart uses WordPress nonce verification and WooCommerce cart APIs.
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
