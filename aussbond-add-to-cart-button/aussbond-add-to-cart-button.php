<?php
/**
 * Plugin Name: Aussbond Add-to-Cart Button
 * Plugin URI: https://aussbond.com/
 * Description: Adds a customizable Elementor WooCommerce add-to-cart widget for simple and variable products.
 * Version: 1.0.10
 * Requires at least: 6.6
 * Tested up to: 7.0
 * Requires PHP: 7.4
 * Requires Plugins: woocommerce, elementor
 * Author: Aussbond
 * Author URI: https://aussbond.com/
 * Text Domain: aussbond-add-to-cart-button
 * Domain Path: /languages
 * WC requires at least: 9.0
 * WC tested up to: 10.7
 *
 * @package Aussbond_Add_To_Cart_Button
 */

defined( 'ABSPATH' ) || exit;

define( 'AUSSBOND_ATC_VERSION', '1.0.10' );
define( 'AUSSBOND_ATC_FILE', __FILE__ );
define( 'AUSSBOND_ATC_PATH', plugin_dir_path( __FILE__ ) );
define( 'AUSSBOND_ATC_URL', plugin_dir_url( __FILE__ ) );
define( 'AUSSBOND_ATC_BASENAME', plugin_basename( __FILE__ ) );

add_action(
	'before_woocommerce_init',
	static function (): void {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
		}
	}
);

require_once AUSSBOND_ATC_PATH . 'includes/class-plugin.php';
require_once AUSSBOND_ATC_PATH . 'includes/class-ajax.php';

\Aussbond_Add_To_Cart_Button\Plugin::instance();
