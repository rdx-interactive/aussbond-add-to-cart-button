<?php
/**
 * Core plugin bootstrap.
 *
 * @package Aussbond_Add_To_Cart_Button
 */

namespace Aussbond_Add_To_Cart_Button;

defined( 'ABSPATH' ) || exit;

/**
 * Main plugin coordinator.
 */
final class Plugin {
	/**
	 * Singleton instance.
	 *
	 * @var Plugin|null
	 */
	private static $instance = null;

	/**
	 * Get singleton instance.
	 */
	public static function instance(): Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Wire WordPress hooks.
	 */
	private function __construct() {
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		add_action( 'plugins_loaded', array( $this, 'boot' ), 20 );
		add_action( 'admin_notices', array( $this, 'render_dependency_notice' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_assets' ) );
		add_action( 'elementor/widgets/register', array( $this, 'register_elementor_widgets' ) );
	}

	/**
	 * Load plugin translations.
	 */
	public function load_textdomain(): void {
		load_plugin_textdomain(
			'aussbond-add-to-cart-button',
			false,
			dirname( AUSSBOND_ATC_BASENAME ) . '/languages'
		);
	}

	/**
	 * Start runtime services when dependencies are present.
	 */
	public function boot(): void {
		if ( $this->woocommerce_available() ) {
			Compatibility::instance();
			Ajax::instance();
		}
	}

	/**
	 * Register frontend assets. Elementor enqueues them only when the widget is rendered.
	 */
	public function register_assets(): void {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		wp_register_style(
			'aussbond-atc-button',
			AUSSBOND_ATC_URL . 'assets/css/aussbond-add-to-cart-button.css',
			array(),
			AUSSBOND_ATC_VERSION
		);

		wp_register_script(
			'aussbond-atc-button',
			AUSSBOND_ATC_URL . 'assets/js/aussbond-add-to-cart-button.js',
			array( 'jquery', 'wc-add-to-cart-variation' ),
			AUSSBOND_ATC_VERSION,
			true
		);

		wp_localize_script(
			'aussbond-atc-button',
			'AussbondATC',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'action'  => Ajax::ACTION,
				'refreshNonceAction' => Ajax::NONCE_ACTION_REFRESH,
				'nonce'   => wp_create_nonce( Ajax::NONCE_ACTION ),
				'i18n'    => array(
					'adding'           => esc_html__( 'Adding...', 'aussbond-add-to-cart-button' ),
					'chooseOptions'    => esc_html__( 'Please choose product options before adding this item to your cart.', 'aussbond-add-to-cart-button' ),
					'genericError'     => esc_html__( 'Something went wrong. Please try again.', 'aussbond-add-to-cart-button' ),
					'duplicateRequest' => esc_html__( 'Please wait a moment before submitting again.', 'aussbond-add-to-cart-button' ),
					'outOfStock'       => esc_html__( 'Out of Stock', 'aussbond-add-to-cart-button' ),
					'addedToCart'      => esc_html__( 'Product added to cart.', 'aussbond-add-to-cart-button' ),
				),
			)
		);
	}

	/**
	 * Register Elementor widget.
	 *
	 * @param object $widgets_manager Elementor widgets manager.
	 */
	public function register_elementor_widgets( object $widgets_manager ): void {
		if ( ! $this->dependencies_met() ) {
			return;
		}

		require_once AUSSBOND_ATC_PATH . 'includes/class-elementor-widget.php';

		$widgets_manager->register( new Elementor_Widget() );
	}

	/**
	 * Show a clear admin notice when required plugins are unavailable.
	 */
	public function render_dependency_notice(): void {
		if ( ! current_user_can( 'activate_plugins' ) || $this->dependencies_met() ) {
			return;
		}

		$missing = array();

		if ( ! class_exists( 'WooCommerce' ) ) {
			$missing[] = esc_html__( 'WooCommerce', 'aussbond-add-to-cart-button' );
		}

		if ( ! did_action( 'elementor/loaded' ) || ! class_exists( '\Elementor\Widget_Base' ) ) {
			$missing[] = esc_html__( 'Elementor', 'aussbond-add-to-cart-button' );
		}

		if ( empty( $missing ) ) {
			return;
		}

		printf(
			'<div class="notice notice-warning"><p>%s</p></div>',
			esc_html(
				sprintf(
					/* translators: %s: Missing plugin names. */
					__( 'Aussbond Add-to-Cart Button requires the following active plugins: %s.', 'aussbond-add-to-cart-button' ),
					implode( ', ', $missing )
				)
			)
		);
	}

	/**
	 * Check runtime dependencies.
	 */
	private function dependencies_met(): bool {
		return $this->woocommerce_available()
			&& did_action( 'elementor/loaded' )
			&& class_exists( '\Elementor\Widget_Base' );
	}

	/**
	 * Check whether WooCommerce is available for frontend/AJAX cart services.
	 */
	private function woocommerce_available(): bool {
		return class_exists( 'WooCommerce' );
	}
}
