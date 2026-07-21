# WordPress 7 Compatibility Report

Plugin: Aussbond Add-to-Cart Button
Version checked: 1.0.11
Check date: 2026-05-30

## Compatibility Result

The plugin is marked compatible with WordPress 7.0 at the package/static-review level.

WordPress 7.0 was released on 2026-05-20. The compatibility review was re-run on 2026-05-30 after updating the package metadata. The plugin already declares:

- Requires at least: 6.6
- Tested up to: 7.0
- Requires PHP: 7.4
- WooCommerce requires at least: 9.0
- WooCommerce tested up to: 10.7

## WordPress 7 Review Notes

- Uses supported WordPress hooks and APIs: `before_woocommerce_init`, `wp_enqueue_scripts`, `wp_ajax_*`, `wp_ajax_nopriv_*`, `wp_register_style()`, `wp_register_script()`, `wp_localize_script()`, and nonce helpers.
- Uses the current Elementor widget registration hook: `elementor/widgets/register`.
- Uses WooCommerce product/cart APIs instead of direct database writes.
- Declares WooCommerce custom order tables and cart/checkout blocks compatibility.
- Does not use removed legacy PHP APIs such as `mysql_*`, `create_function()`, or PHP `each()`.
- Does not use old WordPress APIs found in common compatibility scans such as `screen_icon()`, `like_escape()`, or `register_sidebar_widget()`.
- AJAX add-to-cart requests continue to use nonce validation, sanitized inputs, stock validation, and WooCommerce cart validation.

## Local Checks Performed

| Check | Result |
| --- | --- |
| WordPress 7.0 release verified from WordPress.org documentation | Pass |
| Plugin headers checked for WordPress 7 metadata | Pass |
| Static scan for obvious removed/deprecated APIs | Pass |
| JavaScript syntax check | Pass |
| Installable ZIP integrity check | Pass |
| PHP lint with local `php -l` | Not run; local PHP CLI is not installed |
| WP-CLI plugin smoke test | Not run; local WP-CLI is not installed |

## Remaining Recommendation

Before installing on a live WooCommerce store, run one staging-site smoke test with WordPress 7.0, WooCommerce, Elementor, and the active production theme. Test a simple product, a variable product, an in-stock managed-stock variation, an out-of-stock backorder variation, and an out-of-stock non-backorder variation.

## References

- WordPress 7.0 documentation: https://wordpress.org/documentation/wordpress-version/version-7-0/
- WordPress versions list: https://wordpress.org/documentation/article/wordpress-versions/
- WordPress PHP compatibility handbook: https://make.wordpress.org/core/handbook/references/php-compatibility-and-wordpress-versions/
