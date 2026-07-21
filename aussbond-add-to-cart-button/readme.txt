=== Aussbond Add-to-Cart Button ===
Contributors: aussbond
Developer Name: RDX Interactive, Cochin
Tags: woocommerce, elementor, add to cart, ajax cart, variable products
Requires at least: 6.6
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.0.12
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Adds a customizable WooCommerce add-to-cart Elementor widget for simple and variable products.

== Description ==

Aussbond Add-to-Cart Button provides an Elementor widget that renders WooCommerce product options, quantity selection, a customizable add-to-cart button, AJAX cart submission, WooCommerce notices, and mini-cart fragment refreshes.

== Installation ==

1. Upload the installable ZIP from Plugins > Add New > Upload Plugin.
2. Activate the plugin.
3. Confirm WooCommerce and Elementor are active.
4. Edit a page or product template with Elementor and add the Aussbond Add-to-Cart Button widget.

== Frequently Asked Questions ==

= Does this support variable products? =

Yes. It renders WooCommerce variation attribute dropdowns and validates the selected variation before adding it to the cart.

= Does it refresh the page? =

No. Add-to-cart requests are submitted through secure WordPress AJAX.

= Does it update the mini cart? =

Yes. WooCommerce cart fragments are refreshed after successful AJAX add-to-cart requests.

== Changelog ==

= 1.0.12 =

Fixed preselected/default variable products by rendering the matching variation ID and enabled button state server-side before frontend variation scripts run.

= 1.0.11 =

Fixed variable-product submissions when a variation option is visibly selected but WooCommerce has not populated the hidden variation ID before the custom AJAX submit runs.

= 1.0.10 =

Fixed live AJAX add-to-cart registration so cart requests are handled whenever WooCommerce is active, even if Elementor has not finished loading during admin-ajax requests.

= 1.0.9 =

Fixed add-to-cart submission compatibility for simple products by matching WooCommerce native validation arguments, and added a native form fallback field for environments where custom AJAX is interrupted.

= 1.0.8 =

Renamed the user-facing plugin and Elementor widget display name to Aussbond Add-to-Cart Button while keeping the plugin slug stable.

= 1.0.7 =

Renamed the user-facing plugin and Elementor widget display name to Aussbond ACB while keeping the plugin slug stable.

= 1.0.6 =

Hid duplicate visible variation attribute labels such as Size by default while keeping an Elementor switch to show them again.

= 1.0.5 =

Added editable main heading and attribute heading controls, fixed frontend heading font size output, kept quantity arrows visible, and improved button size/alignment behavior.

= 1.0.4 =

Fixed WooCommerce default button color overrides and added responsive button/quantity layout controls.

= 1.0.3 =

Fixed variable product add-to-cart when Manage Stock is enabled at variation level.

= 1.0.2 =

Improved backorder support for products and variations whose stored stock status is still out of stock while backorders are allowed.

= 1.0.1 =

Fixed backorder add-to-cart handling for managed-stock simple and variable products.

= 1.0.0 =

Initial production release.
