=== Aussbond Add-to-Cart Button ===
Contributors: aussbond
Developer Name: RDX Interactive, Cochin
Tags: woocommerce, elementor, add to cart, ajax cart, variable products
Requires at least: 6.6
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 1.0.23
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

No. The plugin adds products through AJAX and stays on the product page.

= Does it update the mini cart? =

Yes. WooCommerce cart fragments are refreshed after successful AJAX add-to-cart requests.

== Changelog ==

= 1.0.23 =

Show the success popup and refresh cart fragments when another plugin redirects a successful AJAX add-to-cart response to checkout HTML.

= 1.0.22 =

Applied the WooLentor Backorder compatibility bypass directly inside the AJAX add-to-cart flow.

= 1.0.21 =

Stopped redirecting after add-to-cart and replaced inline success notices with one popup message.

= 1.0.20 =

Added scoped WooLentor Backorder compatibility so Aussbond button submissions are not blocked when WooLentor treats an empty backorder limit as 0 available.

= 1.0.19 =

Respect WooCommerce maximum purchase and backorder limits when rendering variation button states, so exhausted backorder variations are not submitted to the cart.

= 1.0.18 =

Fixed WooLentor AJAX add-to-cart conflicts by removing the standard WooCommerce AJAX button class and submitting the plugin form directly through the native WooCommerce product-page flow.

= 1.0.17 =

Switched all add-to-cart submissions to the native WooCommerce product form flow while keeping selected variation resolution before submit.

= 1.0.16 =

Explicitly persists WooCommerce cart totals, session, and cart cookies after successful AJAX add-to-cart to prevent accepted cart items from disappearing on the cart page.

= 1.0.15 =

Added a logged-in customer fallback to native WooCommerce form submission so wholesale plugins can process add-to-cart requests through the standard product-page flow.

= 1.0.14 =

Improved compatibility with wholesale and WooCommerce validation plugins by posting variation attributes as native top-level WooCommerce fields during AJAX add-to-cart.

= 1.0.13 =

Added a fresh AJAX nonce refresh before add-to-cart submission to support logged-in wholesale customers on cached product pages.

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
