<?php
/**
 * Secure AJAX add-to-cart handler.
 *
 * @package Aussbond_Add_To_Cart_Button
 */

namespace Aussbond_Add_To_Cart_Button;

defined( 'ABSPATH' ) || exit;

/**
 * AJAX controller.
 */
final class Ajax {
	public const ACTION       = 'aussbond_atc_add_to_cart';
	public const NONCE_ACTION_REFRESH = 'aussbond_atc_refresh_nonce';
	public const NONCE_ACTION = 'aussbond_atc_nonce';

	/**
	 * Singleton instance.
	 *
	 * @var Ajax|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 */
	public static function instance(): Ajax {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Wire AJAX hooks.
	 */
	private function __construct() {
		add_action( 'wp_ajax_' . self::ACTION, array( $this, 'add_to_cart' ) );
		add_action( 'wp_ajax_nopriv_' . self::ACTION, array( $this, 'add_to_cart' ) );
		add_action( 'wp_ajax_' . self::NONCE_ACTION_REFRESH, array( $this, 'refresh_nonce' ) );
		add_action( 'wp_ajax_nopriv_' . self::NONCE_ACTION_REFRESH, array( $this, 'refresh_nonce' ) );
	}

	/**
	 * Return a fresh nonce for the current visitor/session.
	 */
	public function refresh_nonce(): void {
		wp_send_json_success(
			array(
				'nonce' => wp_create_nonce( self::NONCE_ACTION ),
			)
		);
	}

	/**
	 * Add simple or variable products to cart through AJAX.
	 */
	public function add_to_cart(): void {
		check_ajax_referer( self::NONCE_ACTION, 'nonce' );

		if ( ! function_exists( 'wc_get_product' ) || ! function_exists( 'wc_load_cart' ) ) {
			$this->send_error( __( 'WooCommerce is not available.', 'aussbond-add-to-cart-button' ) );
		}

		wc_load_cart();

		$product_id   = isset( $_POST['product_id'] ) && is_scalar( $_POST['product_id'] ) ? absint( wp_unslash( $_POST['product_id'] ) ) : 0;
		$variation_id = isset( $_POST['variation_id'] ) && is_scalar( $_POST['variation_id'] ) ? absint( wp_unslash( $_POST['variation_id'] ) ) : 0;
		$quantity     = isset( $_POST['quantity'] ) && is_scalar( $_POST['quantity'] ) ? wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) : 1;
		$attributes   = $this->sanitize_attributes();

		if ( 0 >= $product_id || 0 >= $quantity ) {
			$this->send_error( __( 'Invalid add-to-cart request.', 'aussbond-add-to-cart-button' ) );
		}

		$product = wc_get_product( $product_id );

		if ( ! $product || ! $product->exists() || 'publish' !== $product->get_status() ) {
			$this->send_error( __( 'This product is not available.', 'aussbond-add-to-cart-button' ) );
		}

		if ( ! in_array( $product->get_type(), array( 'simple', 'variable' ), true ) ) {
			$this->send_error( __( 'This widget supports simple and variable products only.', 'aussbond-add-to-cart-button' ) );
		}

		$cart_item_product = $product;

		if ( $product->is_type( 'variable' ) ) {
			$variation_data = $this->prepare_variation_data( $product, $variation_id, $attributes );
			$variation      = $variation_data['variation'];
			$attributes     = $variation_data['attributes'];

			if ( ! $variation ) {
				$this->send_error( __( 'Please choose valid product options.', 'aussbond-add-to-cart-button' ) );
			}

			$variation_id      = $variation->get_id();
			$cart_item_product = $variation;
		} elseif ( 0 !== $variation_id ) {
			$this->send_error( __( 'Invalid variation for this product.', 'aussbond-add-to-cart-button' ) );
		}

		if ( $this->is_duplicate_submission( $product_id, $variation_id, $quantity, $attributes ) ) {
			$this->send_error( __( 'Please wait a moment before submitting again.', 'aussbond-add-to-cart-button' ), 429 );
		}

		$quantity     = $this->normalize_quantity( $cart_item_product, $quantity );
		$stock_filter = $this->get_managed_stock_filter( $cart_item_product );

		add_filter( 'woocommerce_product_is_in_stock', $stock_filter, 10, 2 );

		$stock_error = $this->get_stock_validation_error( $cart_item_product, $quantity );

		if ( '' !== $stock_error ) {
			remove_filter( 'woocommerce_product_is_in_stock', $stock_filter, 10 );
			$this->send_error( $stock_error );
		}

		if ( 0 < $variation_id ) {
			$passed_validation = apply_filters(
				'woocommerce_add_to_cart_validation',
				true,
				$product_id,
				$quantity,
				$variation_id,
				$attributes
			);
		} else {
			$passed_validation = apply_filters(
				'woocommerce_add_to_cart_validation',
				true,
				$product_id,
				$quantity
			);
		}

		if ( ! $passed_validation ) {
			remove_filter( 'woocommerce_product_is_in_stock', $stock_filter, 10 );
			$this->send_error_from_notices();
		}

		$cart_item_key = 0 < $variation_id
			? WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $attributes )
			: WC()->cart->add_to_cart( $product_id, $quantity );

		remove_filter( 'woocommerce_product_is_in_stock', $stock_filter, 10 );

		if ( ! $cart_item_key ) {
			$this->send_error_from_notices();
		}

		do_action( 'woocommerce_ajax_added_to_cart', $product_id );

		wc_add_to_cart_message( array( $product_id => $quantity ), true );

		wp_send_json_success(
			array(
				'messages'  => wc_print_notices( true ),
				'fragments' => $this->get_cart_fragments(),
				'cart_hash' => WC()->cart->get_cart_hash(),
			)
		);
	}

	/**
	 * Sanitize posted variation attributes.
	 */
	private function sanitize_attributes(): array {
		$attributes = array();
		$raw        = isset( $_POST['attributes'] ) && is_array( $_POST['attributes'] ) ? wp_unslash( $_POST['attributes'] ) : array();

		foreach ( $raw as $name => $value ) {
			$name = sanitize_key( $name );

			if ( '' === $name || 0 !== strpos( $name, 'attribute_' ) || ! is_scalar( $value ) ) {
				continue;
			}

			$attributes[ $name ] = wc_clean( $value );
		}

		return $attributes;
	}

	/**
	 * Validate variation ownership and normalize selected attributes.
	 *
	 * @param \WC_Product_Variable $product      Parent variable product.
	 * @param int                  $variation_id Variation ID.
	 * @param array                $attributes   Posted variation attributes.
	 */
	private function prepare_variation_data( \WC_Product_Variable $product, int $variation_id, array $attributes ): array {
		if ( 0 >= $variation_id && class_exists( 'WC_Data_Store' ) ) {
			$data_store   = \WC_Data_Store::load( 'product' );
			$variation_id = $data_store ? absint( $data_store->find_matching_product_variation( $product, $attributes ) ) : 0;
		}

		if ( 0 >= $variation_id ) {
			return array(
				'variation'  => null,
				'attributes' => array(),
			);
		}

		$variation = wc_get_product( $variation_id );

		if ( ! $variation instanceof \WC_Product_Variation ) {
			return array(
				'variation'  => null,
				'attributes' => array(),
			);
		}

		if ( $product->get_id() !== $variation->get_parent_id() ) {
			return array(
				'variation'  => null,
				'attributes' => array(),
			);
		}

		$variation_attributes = $variation->get_variation_attributes();
		$normalized           = array();

		foreach ( $variation_attributes as $name => $value ) {
			$name     = sanitize_key( $name );
			$expected = rawurldecode( (string) $value );
			$selected = isset( $attributes[ $name ] ) ? rawurldecode( (string) $attributes[ $name ] ) : '';

			if ( '' === $expected ) {
				$normalized[ $name ] = $selected;
				continue;
			}

			if ( '' === $selected || $selected !== $expected ) {
				return array(
					'variation'  => null,
					'attributes' => array(),
				);
			}

			$normalized[ $name ] = (string) $value;
		}

		return array(
			'variation'  => $variation,
			'attributes' => $normalized,
		);
	}

	/**
	 * Normalize quantity against product purchase rules.
	 *
	 * @param \WC_Product $product  Product being added.
	 * @param int|float   $quantity Requested quantity.
	 */
	private function normalize_quantity( \WC_Product $product, $quantity ) {
		$quantity = max( 1, wc_stock_amount( $quantity ) );

		if ( $product->is_sold_individually() ) {
			return 1;
		}

		$max_purchase_quantity = $product->get_max_purchase_quantity();

		if ( 0 < $max_purchase_quantity ) {
			$quantity = min( $quantity, $max_purchase_quantity );
		}

		return $quantity;
	}

	/**
	 * Return a stock validation error when WooCommerce native checks fail.
	 *
	 * @param \WC_Product $product  Product or variation being added.
	 * @param int|float   $quantity Quantity.
	 */
	private function get_stock_validation_error( \WC_Product $product, $quantity ): string {
		if ( ! $product->is_purchasable() ) {
			return __( 'This product cannot be purchased.', 'aussbond-add-to-cart-button' );
		}

		if ( ! $this->is_add_to_cart_allowed( $product ) ) {
			return __( 'This product is out of stock.', 'aussbond-add-to-cart-button' );
		}

		if ( ! $product->has_enough_stock( $quantity ) && ! $product->backorders_allowed() ) {
			return __( 'There is not enough stock available for the selected quantity.', 'aussbond-add-to-cart-button' );
		}

		return '';
	}

	/**
	 * Force WooCommerce's stock check to accept the selected item when managed stock allows it.
	 *
	 * @param \WC_Product $target_product Product or variation being added.
	 */
	private function get_managed_stock_filter( \WC_Product $target_product ): callable {
		return static function ( $is_in_stock, $checked_product ) use ( $target_product ) {
			if (
				$checked_product instanceof \WC_Product
				&& (int) $checked_product->get_id() === (int) $target_product->get_id()
				&& (
					$checked_product->backorders_allowed()
					|| ( $checked_product->managing_stock() && null !== $checked_product->get_stock_quantity() && 0 < (float) $checked_product->get_stock_quantity() )
				)
			) {
				return true;
			}

			return $is_in_stock;
		};
	}

	/**
	 * Check whether WooCommerce should allow this product into the cart.
	 *
	 * @param \WC_Product $product Product or variation.
	 */
	private function is_add_to_cart_allowed( \WC_Product $product ): bool {
		return $product->is_purchasable()
			&& (
				$product->is_in_stock()
				|| $product->backorders_allowed()
				|| ( $product->managing_stock() && null !== $product->get_stock_quantity() && 0 < (float) $product->get_stock_quantity() )
			);
	}

	/**
	 * Throttle exact duplicate submissions for a very short window.
	 *
	 * @param int       $product_id   Product ID.
	 * @param int       $variation_id Variation ID.
	 * @param int|float $quantity     Quantity.
	 * @param array     $attributes   Variation attributes.
	 */
	private function is_duplicate_submission( int $product_id, int $variation_id, $quantity, array $attributes ): bool {
		$customer_id = WC()->session ? WC()->session->get_customer_id() : '';
		$user_id     = get_current_user_id();
		$ip_address  = isset( $_SERVER['REMOTE_ADDR'] ) ? wc_clean( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		$user_agent  = isset( $_SERVER['HTTP_USER_AGENT'] ) ? wc_clean( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';
		$payload     = wp_json_encode( array( $product_id, $variation_id, $quantity, $attributes ) );
		$key         = 'aussbond_atc_' . md5( $customer_id . '|' . $user_id . '|' . $ip_address . '|' . $user_agent . '|' . $payload );

		if ( get_transient( $key ) ) {
			return true;
		}

		set_transient( $key, 1, 2 );

		return false;
	}

	/**
	 * Build WooCommerce mini cart fragments for frontend replacement.
	 */
	private function get_cart_fragments(): array {
		ob_start();
		woocommerce_mini_cart();
		$mini_cart = ob_get_clean();

		return apply_filters(
			'woocommerce_add_to_cart_fragments',
			array(
				'div.widget_shopping_cart_content' => '<div class="widget_shopping_cart_content">' . $mini_cart . '</div>',
			)
		);
	}

	/**
	 * Send the current WooCommerce notices as an error response.
	 */
	private function send_error_from_notices(): void {
		$messages = wc_print_notices( true );

		if ( '' === trim( wp_strip_all_tags( $messages ) ) ) {
			$messages = wc_print_notice( esc_html__( 'Unable to add this product to the cart.', 'aussbond-add-to-cart-button' ), 'error', array(), true );
		}

		wp_send_json_error(
			array(
				'messages' => $messages,
			)
		);
	}

	/**
	 * Send a sanitized WooCommerce-style error response.
	 *
	 * @param string $message     Error message.
	 * @param int    $status_code HTTP status code.
	 */
	private function send_error( string $message, int $status_code = 400 ): void {
		wp_send_json_error(
			array(
				'messages' => wc_print_notice( esc_html( $message ), 'error', array(), true ),
			),
			$status_code
		);
	}
}
