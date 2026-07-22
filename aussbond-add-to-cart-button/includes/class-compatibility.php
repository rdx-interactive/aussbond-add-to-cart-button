<?php
/**
 * Third-party plugin compatibility helpers.
 *
 * @package Aussbond_Add_To_Cart_Button
 */

namespace Aussbond_Add_To_Cart_Button;

defined( 'ABSPATH' ) || exit;

/**
 * Compatibility controller.
 */
final class Compatibility {
	/**
	 * Singleton instance.
	 *
	 * @var Compatibility|null
	 */
	private static $instance = null;

	/**
	 * Cart item marker for Aussbond-managed add-to-cart submissions.
	 */
	private const CART_ITEM_BYPASS_KEY = 'aussbond_atc_woolentor_backorder_bypass';

	/**
	 * Get singleton instance.
	 */
	public static function instance(): Compatibility {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Wire compatibility hooks.
	 */
	private function __construct() {
		add_filter( 'woocommerce_add_cart_item_data', array( $this, 'mark_aussbond_cart_item' ), 1, 4 );
		add_filter( 'woocommerce_add_to_cart_redirect', array( $this, 'disable_add_to_cart_redirect' ), PHP_INT_MAX );
		add_action( 'wp_loaded', array( $this, 'bypass_woolentor_backorder_limit_for_submit' ), 1 );
		add_action( 'woocommerce_check_cart_items', array( $this, 'bypass_woolentor_backorder_limit_for_marked_cart_items' ), 1 );

		if ( $this->is_aussbond_add_to_cart_request() ) {
			$this->disable_woolentor_backorder_limit_hooks();
		}
	}

	/**
	 * Mark cart items that were added through this plugin's form.
	 *
	 * @param array     $cart_item_data Existing cart item data.
	 * @param int       $product_id      Parent/simple product ID.
	 * @param int       $variation_id    Variation ID.
	 * @param int|float $quantity        Quantity.
	 */
	public function mark_aussbond_cart_item( array $cart_item_data, int $product_id, int $variation_id, $quantity ): array {
		unset( $quantity );

		if ( ! $this->is_aussbond_add_to_cart_request() ) {
			return $cart_item_data;
		}

		$product = wc_get_product( $variation_id ? $variation_id : $product_id );

		if ( $product instanceof \WC_Product && $product->backorders_allowed() ) {
			$cart_item_data[ self::CART_ITEM_BYPASS_KEY ] = '1';
		}

		return $cart_item_data;
	}

	/**
	 * Disable WooCommerce add-to-cart redirects for this plugin's AJAX request.
	 *
	 * @param string|false $url Redirect URL.
	 * @return string|false
	 */
	public function disable_add_to_cart_redirect( $url ) {
		return $this->is_aussbond_ajax_request() ? false : $url;
	}

	/**
	 * Remove WooLentor's add-to-cart backorder limit check for this plugin's request.
	 */
	public function bypass_woolentor_backorder_limit_for_submit(): void {
		if ( $this->is_aussbond_add_to_cart_request() ) {
			$this->disable_woolentor_backorder_limit_hooks();
		}
	}

	/**
	 * Remove WooLentor's cart-page backorder limit check when the cart contains this plugin's marked items.
	 */
	public function bypass_woolentor_backorder_limit_for_marked_cart_items(): void {
		if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
			return;
		}

		foreach ( WC()->cart->get_cart() as $cart_item ) {
			if ( ! empty( $cart_item[ self::CART_ITEM_BYPASS_KEY ] ) ) {
				$this->disable_woolentor_backorder_limit_hooks();
				return;
			}
		}
	}

	/**
	 * Remove WooLentor Backorder hooks that reject empty/zero limit backorders.
	 */
	public function disable_woolentor_backorder_limit_hooks(): void {
		if ( ! class_exists( '\Woolentor_Backorder' ) || ! method_exists( '\Woolentor_Backorder', 'instance' ) ) {
			return;
		}

		$woolentor_backorder = \Woolentor_Backorder::instance();

		remove_filter( 'woocommerce_add_cart_item_data', array( $woolentor_backorder, 'render_single_product_notice' ), 99 );
		remove_action( 'woocommerce_check_cart_items', array( $woolentor_backorder, 'check_cart_item_backorder_limit' ) );
	}

	/**
	 * Check whether the current add-to-cart request came from this plugin's form.
	 */
	private function is_aussbond_add_to_cart_request(): bool {
		$value = null;

		if ( isset( $_POST['aussbond_atc_request'] ) && is_scalar( $_POST['aussbond_atc_request'] ) ) {
			$value = wp_unslash( $_POST['aussbond_atc_request'] );
		} elseif ( isset( $_GET['aussbond_atc_request'] ) && is_scalar( $_GET['aussbond_atc_request'] ) ) {
			$value = wp_unslash( $_GET['aussbond_atc_request'] );
		}

		return '1' === (string) $value;
	}

	/**
	 * Check whether the current request is this plugin's AJAX add-to-cart call.
	 */
	private function is_aussbond_ajax_request(): bool {
		return wp_doing_ajax() && $this->is_aussbond_add_to_cart_request();
	}
}
