<?php
/**
 * Elementor widget implementation.
 *
 * @package Aussbond_Add_To_Cart_Button
 */

namespace Aussbond_Add_To_Cart_Button;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Widget_Base;

defined( 'ABSPATH' ) || exit;

/**
 * Aussbond add-to-cart Elementor widget.
 */
final class Elementor_Widget extends Widget_Base {
	/**
	 * Elementor widget slug.
	 */
	public function get_name() {
		return 'aussbond_add_to_cart_button';
	}

	/**
	 * Elementor widget title.
	 */
	public function get_title() {
		return esc_html__( 'Aussbond Add-to-Cart Button', 'aussbond-add-to-cart-button' );
	}

	/**
	 * Elementor icon.
	 */
	public function get_icon() {
		return 'eicon-cart';
	}

	/**
	 * Elementor categories.
	 */
	public function get_categories() {
		return array( 'woocommerce-elements', 'general' );
	}

	/**
	 * Search keywords.
	 */
	public function get_keywords() {
		return array( 'aussbond', 'woocommerce', 'cart', 'add to cart', 'product', 'button' );
	}

	/**
	 * Frontend script dependencies.
	 */
	public function get_script_depends() {
		return array( 'aussbond-atc-button' );
	}

	/**
	 * Frontend style dependencies.
	 */
	public function get_style_depends() {
		return array( 'aussbond-atc-button' );
	}

	/**
	 * Register Elementor controls.
	 */
	protected function register_controls() {
		$this->register_content_controls();
		$this->register_heading_style_controls();
		$this->register_layout_style_controls();
		$this->register_button_style_controls();
		$this->register_quantity_style_controls();
		$this->register_attribute_style_controls();
	}

	/**
	 * Render widget output.
	 */
	protected function render() {
		if ( ! function_exists( 'wc_get_product' ) ) {
			return;
		}

		$settings       = $this->get_settings_for_display();
		$product        = $this->resolve_product( $settings );
		$heading_text   = $this->get_safe_label( $settings, 'heading_text', __( 'Add to Cart', 'aussbond-add-to-cart-button' ) );
		$attribute_heading_text = $this->get_safe_label( $settings, 'attribute_heading_text', __( 'Select Options', 'aussbond-add-to-cart-button' ) );
		$show_variation_attribute_labels = 'yes' === ( $settings['show_variation_attribute_labels'] ?? '' );
		$button_text    = $this->get_safe_label( $settings, 'button_text', __( 'Add to Cart', 'aussbond-add-to-cart-button' ) );
		$backorder_text = $this->get_safe_label( $settings, 'backorder_button_text', __( 'Back Order', 'aussbond-add-to-cart-button' ) );

		if ( ! $product ) {
			$this->render_editor_notice( __( 'Select a valid WooCommerce product ID, or place this widget on a product page/template.', 'aussbond-add-to-cart-button' ) );
			return;
		}

		if ( ! in_array( $product->get_type(), array( 'simple', 'variable' ), true ) ) {
			$this->render_editor_notice( __( 'This widget supports simple and variable WooCommerce products only.', 'aussbond-add-to-cart-button' ) );
			return;
		}

		$this->add_render_attribute(
			'wrapper',
			array(
				'class'           => array( 'aussbond-atc', 'aussbond-atc--' . sanitize_html_class( $product->get_type() ) ),
				'data-widget-id'  => esc_attr( $this->get_id() ),
				'data-product-id' => esc_attr( (string) $product->get_id() ),
			)
		);

		echo '<div ' . $this->get_render_attribute_string( 'wrapper' ) . '>';
		$this->render_main_heading( $heading_text );

		if ( $product->is_type( 'variable' ) ) {
			$this->render_variable_form( $product, $button_text, $backorder_text, $attribute_heading_text, $show_variation_attribute_labels );
		} else {
			$this->render_simple_form( $product, $button_text, $backorder_text, 'yes' === ( $settings['show_simple_attributes'] ?? 'yes' ) );
		}

		echo '</div>';
	}

	/**
	 * Register content controls.
	 */
	private function register_content_controls(): void {
		$this->start_controls_section(
			'section_content',
			array(
				'label' => esc_html__( 'Product', 'aussbond-add-to-cart-button' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'product_id',
			array(
				'label'       => esc_html__( 'Product ID', 'aussbond-add-to-cart-button' ),
				'type'        => Controls_Manager::NUMBER,
				'min'         => 0,
				'step'        => 1,
				'default'     => 0,
				'description' => esc_html__( 'Leave empty or 0 to use the current product on a WooCommerce product page/template.', 'aussbond-add-to-cart-button' ),
			)
		);

		$this->add_control(
			'heading_text',
			array(
				'label'   => esc_html__( 'Main Heading Text', 'aussbond-add-to-cart-button' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Add to Cart', 'aussbond-add-to-cart-button' ),
				'dynamic' => array( 'active' => true ),
			)
		);

		$this->add_control(
			'attribute_heading_text',
			array(
				'label'   => esc_html__( 'Attribute Heading Text', 'aussbond-add-to-cart-button' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Select Options', 'aussbond-add-to-cart-button' ),
				'dynamic' => array( 'active' => true ),
			)
		);

		$this->add_control(
			'show_variation_attribute_labels',
			array(
				'label'        => esc_html__( 'Show Individual Attribute Labels', 'aussbond-add-to-cart-button' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'aussbond-add-to-cart-button' ),
				'label_off'    => esc_html__( 'Hide', 'aussbond-add-to-cart-button' ),
				'return_value' => 'yes',
				'default'      => '',
				'description'  => esc_html__( 'Hide labels like “Size” when using the custom attribute heading above the field.', 'aussbond-add-to-cart-button' ),
			)
		);

		$this->add_control(
			'button_text',
			array(
				'label'   => esc_html__( 'Button Text', 'aussbond-add-to-cart-button' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Add to Cart', 'aussbond-add-to-cart-button' ),
				'dynamic' => array( 'active' => true ),
			)
		);

		$this->add_control(
			'backorder_button_text',
			array(
				'label'   => esc_html__( 'Back Order Button Text', 'aussbond-add-to-cart-button' ),
				'type'    => Controls_Manager::TEXT,
				'default' => esc_html__( 'Back Order', 'aussbond-add-to-cart-button' ),
				'dynamic' => array( 'active' => true ),
			)
		);

		$this->add_control(
			'show_simple_attributes',
			array(
				'label'        => esc_html__( 'Show Simple Product Attributes', 'aussbond-add-to-cart-button' ),
				'type'         => Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Show', 'aussbond-add-to-cart-button' ),
				'label_off'    => esc_html__( 'Hide', 'aussbond-add-to-cart-button' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Register main heading styling controls.
	 */
	private function register_heading_style_controls(): void {
		$this->start_controls_section(
			'section_heading_style',
			array(
				'label' => esc_html__( 'Main Heading', 'aussbond-add-to-cart-button' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'heading_alignment',
			array(
				'label'     => esc_html__( 'Alignment', 'aussbond-add-to-cart-button' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'left'   => array(
						'title' => esc_html__( 'Left', 'aussbond-add-to-cart-button' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center' => array(
						'title' => esc_html__( 'Center', 'aussbond-add-to-cart-button' ),
						'icon'  => 'eicon-text-align-center',
					),
					'right'  => array(
						'title' => esc_html__( 'Right', 'aussbond-add-to-cart-button' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'default'   => 'left',
				'selectors' => array(
					'{{WRAPPER}} .aussbond-atc .aussbond-atc-main-heading.aussbond-atc-main-heading' => 'text-align: {{VALUE}} !important;',
				),
			)
		);

		$this->add_control(
			'heading_color',
			array(
				'label'     => esc_html__( 'Color', 'aussbond-add-to-cart-button' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .aussbond-atc .aussbond-atc-main-heading.aussbond-atc-main-heading' => 'color: {{VALUE}} !important;',
				),
			)
		);

		$this->add_responsive_control(
			'heading_font_size',
			array(
				'label'      => esc_html__( 'Font Size', 'aussbond-add-to-cart-button' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem' ),
				'range'      => array(
					'px' => array(
						'min' => 8,
						'max' => 96,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .aussbond-atc .aussbond-atc-main-heading.aussbond-atc-main-heading' => 'font-size: {{SIZE}}{{UNIT}} !important;',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'heading_typography',
				'label'    => esc_html__( 'Typography', 'aussbond-add-to-cart-button' ),
				'selector' => '{{WRAPPER}} .aussbond-atc .aussbond-atc-main-heading.aussbond-atc-main-heading',
			)
		);

		$this->add_responsive_control(
			'heading_margin',
			array(
				'label'      => esc_html__( 'Margin', 'aussbond-add-to-cart-button' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', 'rem', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .aussbond-atc .aussbond-atc-main-heading.aussbond-atc-main-heading' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Register quantity/button layout controls.
	 */
	private function register_layout_style_controls(): void {
		$this->start_controls_section(
			'section_layout_style',
			array(
				'label' => esc_html__( 'Layout', 'aussbond-add-to-cart-button' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'purchase_layout',
			array(
				'label'                => esc_html__( 'Quantity and Button Layout', 'aussbond-add-to-cart-button' ),
				'type'                 => Controls_Manager::SELECT,
				'default'              => 'row',
				'tablet_default'       => 'row',
				'mobile_default'       => 'column',
				'options'              => array(
					'row'    => esc_html__( 'Inline / Same Row', 'aussbond-add-to-cart-button' ),
					'column' => esc_html__( 'Stacked / Vertical', 'aussbond-add-to-cart-button' ),
				),
				'selectors'            => array(
					'{{WRAPPER}} .aussbond-atc .aussbond-atc-purchase-row' => 'flex-direction: {{VALUE}};',
				),
			)
		);

		$this->add_responsive_control(
			'purchase_alignment',
			array(
				'label'                => esc_html__( 'Alignment', 'aussbond-add-to-cart-button' ),
				'type'                 => Controls_Manager::CHOOSE,
				'options'              => array(
					'left'   => array(
						'title' => esc_html__( 'Left', 'aussbond-add-to-cart-button' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center' => array(
						'title' => esc_html__( 'Center', 'aussbond-add-to-cart-button' ),
						'icon'  => 'eicon-text-align-center',
					),
					'right'  => array(
						'title' => esc_html__( 'Right', 'aussbond-add-to-cart-button' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'default'              => 'left',
				'selectors'            => array(
					'{{WRAPPER}} .aussbond-atc .aussbond-atc-purchase-row' => '{{VALUE}}',
				),
				'selectors_dictionary' => array(
					'left'   => 'justify-content: flex-start; align-items: center; text-align: left;',
					'center' => 'justify-content: center; align-items: center; text-align: center;',
					'right'  => 'justify-content: flex-end; align-items: center; text-align: right;',
				),
			)
		);

		$this->add_responsive_control(
			'purchase_gap',
			array(
				'label'      => esc_html__( 'Gap', 'aussbond-add-to-cart-button' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem' ),
				'default'    => array(
					'size' => 12,
					'unit' => 'px',
				),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 80,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .aussbond-atc .aussbond-atc-purchase-row' => 'gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Register button styling controls.
	 */
	private function register_button_style_controls(): void {
		$this->start_controls_section(
			'section_button_style',
			array(
				'label' => esc_html__( 'Button', 'aussbond-add-to-cart-button' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'button_alignment',
			array(
				'label'                => esc_html__( 'Alignment', 'aussbond-add-to-cart-button' ),
				'type'                 => Controls_Manager::CHOOSE,
				'options'              => array(
					'left'   => array(
						'title' => esc_html__( 'Left', 'aussbond-add-to-cart-button' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center' => array(
						'title' => esc_html__( 'Center', 'aussbond-add-to-cart-button' ),
						'icon'  => 'eicon-text-align-center',
					),
					'right'  => array(
						'title' => esc_html__( 'Right', 'aussbond-add-to-cart-button' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'default'              => 'left',
				'selectors'            => array(
					'{{WRAPPER}} .aussbond-atc .aussbond-atc-actions' => '{{VALUE}}',
				),
				'selectors_dictionary' => array(
					'left'   => 'justify-content: flex-start; text-align: left;',
					'center' => 'justify-content: center; text-align: center;',
					'right'  => 'justify-content: flex-end; text-align: right;',
				),
			)
		);

		$this->add_control(
			'button_width_mode',
			array(
				'label'     => esc_html__( 'Button Width', 'aussbond-add-to-cart-button' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'auto',
				'options'   => array(
					'auto'   => esc_html__( 'Auto', 'aussbond-add-to-cart-button' ),
					'full'   => esc_html__( 'Full Width', 'aussbond-add-to-cart-button' ),
					'custom' => esc_html__( 'Custom', 'aussbond-add-to-cart-button' ),
				),
				'selectors' => array(
					'{{WRAPPER}} .aussbond-atc .aussbond-atc-button.aussbond-atc-button' => '{{VALUE}}',
				),
				'selectors_dictionary' => array(
					'auto'   => 'width: auto !important;',
					'full'   => 'width: 100% !important;',
					'custom' => '',
				),
			)
		);

		$this->add_responsive_control(
			'button_custom_width',
			array(
				'label'      => esc_html__( 'Custom Width', 'aussbond-add-to-cart-button' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', '%', 'em', 'rem' ),
				'range'      => array(
					'px' => array(
						'min' => 80,
						'max' => 800,
					),
					'%'  => array(
						'min' => 5,
						'max' => 100,
					),
				),
				'condition'  => array(
					'button_width_mode' => 'custom',
				),
				'selectors'  => array(
					'{{WRAPPER}} .aussbond-atc .aussbond-atc-button.aussbond-atc-button' => 'width: {{SIZE}}{{UNIT}} !important;',
				),
			)
		);

		$this->add_responsive_control(
			'button_height',
			array(
				'label'      => esc_html__( 'Button Height', 'aussbond-add-to-cart-button' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem' ),
				'range'      => array(
					'px' => array(
						'min' => 32,
						'max' => 160,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .aussbond-atc .aussbond-atc-button.aussbond-atc-button' => 'height: {{SIZE}}{{UNIT}} !important; min-height: {{SIZE}}{{UNIT}} !important;',
				),
			)
		);

		$this->start_controls_tabs( 'button_style_tabs' );

		$this->start_controls_tab(
			'button_normal_tab',
			array(
				'label' => esc_html__( 'Normal', 'aussbond-add-to-cart-button' ),
			)
		);

		$this->add_control(
			'button_background_color',
			array(
				'label'     => esc_html__( 'Button Color', 'aussbond-add-to-cart-button' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#111827',
				'selectors' => array(
					'{{WRAPPER}} .aussbond-atc .aussbond-atc-button.aussbond-atc-button' => 'background-color: {{VALUE}} !important;',
				),
			)
		);

		$this->add_control(
			'button_text_color',
			array(
				'label'     => esc_html__( 'Text Color', 'aussbond-add-to-cart-button' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => array(
					'{{WRAPPER}} .aussbond-atc .aussbond-atc-button.aussbond-atc-button' => 'color: {{VALUE}} !important;',
				),
			)
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'button_hover_tab',
			array(
				'label' => esc_html__( 'Hover', 'aussbond-add-to-cart-button' ),
			)
		);

		$this->add_control(
			'button_hover_background_color',
			array(
				'label'     => esc_html__( 'Button Hover Color', 'aussbond-add-to-cart-button' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#2563eb',
				'selectors' => array(
					'{{WRAPPER}} .aussbond-atc .aussbond-atc-button.aussbond-atc-button:hover, {{WRAPPER}} .aussbond-atc .aussbond-atc-button.aussbond-atc-button:focus' => 'background-color: {{VALUE}} !important;',
				),
			)
		);

		$this->add_control(
			'button_hover_text_color',
			array(
				'label'     => esc_html__( 'Hover Text Color', 'aussbond-add-to-cart-button' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .aussbond-atc .aussbond-atc-button.aussbond-atc-button:hover, {{WRAPPER}} .aussbond-atc .aussbond-atc-button.aussbond-atc-button:focus' => 'color: {{VALUE}} !important;',
				),
			)
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'button_typography',
				'label'    => esc_html__( 'Typography', 'aussbond-add-to-cart-button' ),
				'selector' => '{{WRAPPER}} .aussbond-atc .aussbond-atc-button.aussbond-atc-button',
			)
		);

		$this->add_responsive_control(
			'button_padding',
			array(
				'label'      => esc_html__( 'Padding', 'aussbond-add-to-cart-button' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', 'rem', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .aussbond-atc .aussbond-atc-button.aussbond-atc-button' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				),
			)
		);

		$this->add_responsive_control(
			'button_margin',
			array(
				'label'      => esc_html__( 'Margin', 'aussbond-add-to-cart-button' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', 'rem', '%' ),
				'selectors'  => array(
					'{{WRAPPER}} .aussbond-atc .aussbond-atc-button.aussbond-atc-button' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'button_border',
				'label'    => esc_html__( 'Border Style', 'aussbond-add-to-cart-button' ),
				'selector' => '{{WRAPPER}} .aussbond-atc .aussbond-atc-button.aussbond-atc-button',
			)
		);

		$this->add_responsive_control(
			'button_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'aussbond-add-to-cart-button' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem' ),
				'selectors'  => array(
					'{{WRAPPER}} .aussbond-atc .aussbond-atc-button.aussbond-atc-button' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			array(
				'name'     => 'button_box_shadow',
				'label'    => esc_html__( 'Box Shadow', 'aussbond-add-to-cart-button' ),
				'selector' => '{{WRAPPER}} .aussbond-atc .aussbond-atc-button.aussbond-atc-button',
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Register quantity field styling controls.
	 */
	private function register_quantity_style_controls(): void {
		$this->start_controls_section(
			'section_quantity_style',
			array(
				'label' => esc_html__( 'Quantity Field', 'aussbond-add-to-cart-button' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_responsive_control(
			'quantity_width',
			array(
				'label'      => esc_html__( 'Width', 'aussbond-add-to-cart-button' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem', '%' ),
				'range'      => array(
					'px' => array(
						'min' => 48,
						'max' => 240,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .aussbond-atc-quantity .qty' => 'width: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->add_control(
			'quantity_text_color',
			array(
				'label'     => esc_html__( 'Text Color', 'aussbond-add-to-cart-button' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .aussbond-atc-quantity .qty' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'quantity_background_color',
			array(
				'label'     => esc_html__( 'Background Color', 'aussbond-add-to-cart-button' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .aussbond-atc-quantity .qty' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'quantity_border',
				'selector' => '{{WRAPPER}} .aussbond-atc-quantity .qty',
			)
		);

		$this->add_responsive_control(
			'quantity_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'aussbond-add-to-cart-button' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem' ),
				'selectors'  => array(
					'{{WRAPPER}} .aussbond-atc-quantity .qty' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'quantity_padding',
			array(
				'label'      => esc_html__( 'Padding', 'aussbond-add-to-cart-button' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', 'rem' ),
				'selectors'  => array(
					'{{WRAPPER}} .aussbond-atc-quantity .qty' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Register attribute field styling controls.
	 */
	private function register_attribute_style_controls(): void {
		$this->start_controls_section(
			'section_attribute_style',
			array(
				'label' => esc_html__( 'Attribute Fields', 'aussbond-add-to-cart-button' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			)
		);

		$this->add_control(
			'attribute_heading_style_heading',
			array(
				'label' => esc_html__( 'Attribute Heading', 'aussbond-add-to-cart-button' ),
				'type'  => Controls_Manager::HEADING,
			)
		);

		$this->add_responsive_control(
			'attribute_heading_alignment',
			array(
				'label'     => esc_html__( 'Alignment', 'aussbond-add-to-cart-button' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => array(
					'left'   => array(
						'title' => esc_html__( 'Left', 'aussbond-add-to-cart-button' ),
						'icon'  => 'eicon-text-align-left',
					),
					'center' => array(
						'title' => esc_html__( 'Center', 'aussbond-add-to-cart-button' ),
						'icon'  => 'eicon-text-align-center',
					),
					'right'  => array(
						'title' => esc_html__( 'Right', 'aussbond-add-to-cart-button' ),
						'icon'  => 'eicon-text-align-right',
					),
				),
				'default'   => 'left',
				'selectors' => array(
					'{{WRAPPER}} .aussbond-atc .aussbond-atc-attributes-heading.aussbond-atc-attributes-heading' => 'text-align: {{VALUE}} !important;',
				),
			)
		);

		$this->add_control(
			'attribute_heading_color',
			array(
				'label'     => esc_html__( 'Color', 'aussbond-add-to-cart-button' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .aussbond-atc .aussbond-atc-attributes-heading.aussbond-atc-attributes-heading' => 'color: {{VALUE}} !important;',
				),
			)
		);

		$this->add_responsive_control(
			'attribute_heading_font_size',
			array(
				'label'      => esc_html__( 'Font Size', 'aussbond-add-to-cart-button' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem' ),
				'range'      => array(
					'px' => array(
						'min' => 8,
						'max' => 72,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .aussbond-atc .aussbond-atc-attributes-heading.aussbond-atc-attributes-heading' => 'font-size: {{SIZE}}{{UNIT}} !important;',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			array(
				'name'     => 'attribute_heading_typography',
				'label'    => esc_html__( 'Typography', 'aussbond-add-to-cart-button' ),
				'selector' => '{{WRAPPER}} .aussbond-atc .aussbond-atc-attributes-heading.aussbond-atc-attributes-heading',
			)
		);

		$this->add_responsive_control(
			'attribute_heading_spacing',
			array(
				'label'      => esc_html__( 'Spacing', 'aussbond-add-to-cart-button' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem' ),
				'default'    => array(
					'size' => 10,
					'unit' => 'px',
				),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 80,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .aussbond-atc .aussbond-atc-attributes-heading.aussbond-atc-attributes-heading' => 'margin-bottom: {{SIZE}}{{UNIT}} !important;',
				),
			)
		);

		$this->add_control(
			'attribute_field_style_heading',
			array(
				'label'     => esc_html__( 'Attribute Fields', 'aussbond-add-to-cart-button' ),
				'type'      => Controls_Manager::HEADING,
				'separator' => 'before',
			)
		);

		$this->add_control(
			'attribute_label_color',
			array(
				'label'     => esc_html__( 'Label Color', 'aussbond-add-to-cart-button' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .aussbond-atc-attribute-label, {{WRAPPER}} .aussbond-atc-simple-attributes dt' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'attribute_text_color',
			array(
				'label'     => esc_html__( 'Text Color', 'aussbond-add-to-cart-button' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .aussbond-atc .variations select, {{WRAPPER}} .aussbond-atc-simple-attributes dd' => 'color: {{VALUE}};',
				),
			)
		);

		$this->add_control(
			'attribute_background_color',
			array(
				'label'     => esc_html__( 'Background Color', 'aussbond-add-to-cart-button' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => array(
					'{{WRAPPER}} .aussbond-atc .variations select' => 'background-color: {{VALUE}};',
				),
			)
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			array(
				'name'     => 'attribute_border',
				'selector' => '{{WRAPPER}} .aussbond-atc .variations select',
			)
		);

		$this->add_responsive_control(
			'attribute_border_radius',
			array(
				'label'      => esc_html__( 'Border Radius', 'aussbond-add-to-cart-button' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', '%', 'em', 'rem' ),
				'selectors'  => array(
					'{{WRAPPER}} .aussbond-atc .variations select' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'attribute_padding',
			array(
				'label'      => esc_html__( 'Padding', 'aussbond-add-to-cart-button' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => array( 'px', 'em', 'rem' ),
				'selectors'  => array(
					'{{WRAPPER}} .aussbond-atc .variations select' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				),
			)
		);

		$this->add_responsive_control(
			'attribute_spacing',
			array(
				'label'      => esc_html__( 'Spacing', 'aussbond-add-to-cart-button' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => array( 'px', 'em', 'rem' ),
				'range'      => array(
					'px' => array(
						'min' => 0,
						'max' => 48,
					),
				),
				'selectors'  => array(
					'{{WRAPPER}} .aussbond-atc .variations tr + tr' => 'margin-top: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .aussbond-atc-simple-attributes' => 'gap: {{SIZE}}{{UNIT}};',
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render simple product form.
	 *
	 * @param \WC_Product $product                Product.
	 * @param string      $button_text            In-stock button text.
	 * @param string      $backorder_text         Backorder/out-of-stock button text.
	 * @param bool        $show_simple_attributes Whether visible simple attributes should be shown.
	 */
	private function render_simple_form( \WC_Product $product, string $button_text, string $backorder_text, bool $show_simple_attributes ): void {
		$button_label = $this->get_product_button_label( $product, $button_text, $backorder_text );
		$is_disabled  = ! $this->is_add_to_cart_allowed( $product );

		if ( $show_simple_attributes ) {
			$this->render_simple_attributes( $product );
		}

		echo '<form class="cart aussbond-atc-form" method="post" enctype="multipart/form-data" data-product-type="simple" data-product-id="' . esc_attr( (string) $product->get_id() ) . '">';
		echo '<input type="hidden" name="product_id" value="' . esc_attr( (string) $product->get_id() ) . '" />';
		echo '<input type="hidden" name="variation_id" value="0" />';
		echo '<div class="aussbond-atc-notices" aria-live="polite"></div>';
		echo '<div class="aussbond-atc-purchase-row">';
		echo '<div class="aussbond-atc-quantity">';
		woocommerce_quantity_input(
			array(
				'min_value'   => max( 1, $product->get_min_purchase_quantity() ),
				'max_value'   => $product->get_max_purchase_quantity(),
				'input_value' => max( 1, $product->get_min_purchase_quantity() ),
			),
			$product
		);
		echo '</div>';
		echo '<div class="aussbond-atc-actions">';
		$this->render_button( $product, $button_label, $button_text, $backorder_text, $is_disabled );
		echo '</div>';
		echo '</div>';
		echo wp_kses_post( wc_get_stock_html( $product ) );
		echo '</form>';
	}

	/**
	 * Render the main widget heading.
	 *
	 * @param string $heading_text Heading text.
	 */
	private function render_main_heading( string $heading_text ): void {
		if ( '' === trim( $heading_text ) ) {
			return;
		}

		echo '<h3 class="aussbond-atc-main-heading">' . esc_html( $heading_text ) . '</h3>';
	}

	/**
	 * Render variable product form.
	 *
	 * @param \WC_Product_Variable $product        Product.
	 * @param string               $button_text    In-stock button text.
	 * @param string               $backorder_text Backorder/out-of-stock button text.
	 * @param string               $attribute_heading_text Attribute section heading.
	 * @param bool                 $show_attribute_labels Whether each variation attribute label should be visible.
	 */
	private function render_variable_form( \WC_Product_Variable $product, string $button_text, string $backorder_text, string $attribute_heading_text, bool $show_attribute_labels ): void {
		$available_variations = $this->get_available_variations_data( $product, $button_text, $backorder_text );
		$attributes           = $product->get_variation_attributes();

		if ( empty( $attributes ) || empty( $available_variations ) ) {
			$this->render_editor_notice( __( 'No purchasable variations are available for this product.', 'aussbond-add-to-cart-button' ) );
			return;
		}

		$variations_json = wp_json_encode( $available_variations );
		$variations_attr = function_exists( 'wc_esc_json' ) ? wc_esc_json( $variations_json ) : esc_attr( $variations_json );

		echo '<form class="variations_form cart aussbond-atc-form" method="post" enctype="multipart/form-data" data-product-type="variable" data-product-id="' . esc_attr( (string) $product->get_id() ) . '" data-product_variations="' . $variations_attr . '">';
		echo '<div class="aussbond-atc-notices" aria-live="polite"></div>';
		echo '<div class="aussbond-atc-attributes">';
		$this->render_attribute_heading( $attribute_heading_text );
		$this->render_variation_attributes( $product, $attributes, $show_attribute_labels );
		echo '</div>';
		echo '<div class="single_variation_wrap">';
		echo '<div class="woocommerce-variation single_variation"></div>';
		echo '<div class="woocommerce-variation-add-to-cart variations_button aussbond-atc-purchase-row">';
		echo '<div class="aussbond-atc-quantity">';
		woocommerce_quantity_input(
			array(
				'min_value'   => max( 1, $product->get_min_purchase_quantity() ),
				'max_value'   => $product->get_max_purchase_quantity(),
				'input_value' => max( 1, $product->get_min_purchase_quantity() ),
			),
			$product
		);
		echo '</div>';
		echo '<div class="aussbond-atc-actions">';
		echo '<input type="hidden" name="add-to-cart" value="' . esc_attr( (string) $product->get_id() ) . '" />';
		echo '<input type="hidden" name="product_id" value="' . esc_attr( (string) $product->get_id() ) . '" />';
		echo '<input type="hidden" name="variation_id" class="variation_id" value="0" />';
		$this->render_button( $product, $button_text, $button_text, $backorder_text, true );
		echo '</div>';
		echo '</div>';
		echo '</div>';
		echo '</form>';
	}

	/**
	 * Render the attribute selection heading.
	 *
	 * @param string $heading_text Heading text.
	 */
	private function render_attribute_heading( string $heading_text ): void {
		if ( '' === trim( $heading_text ) ) {
			return;
		}

		echo '<div class="aussbond-atc-attributes-heading">' . esc_html( $heading_text ) . '</div>';
	}

	/**
	 * Render variation dropdown fields.
	 *
	 * @param \WC_Product_Variable $product    Product.
	 * @param array                $attributes Product variation attributes.
	 * @param bool                 $show_labels Whether labels should be visible.
	 */
	private function render_variation_attributes( \WC_Product_Variable $product, array $attributes, bool $show_labels ): void {
		echo '<table class="variations" cellspacing="0" role="presentation"><tbody>';

		foreach ( $attributes as $attribute_name => $options ) {
			$field_id = 'aussbond-' . esc_attr( $this->get_id() ) . '-' . esc_attr( sanitize_title( $attribute_name ) );
			$label_class = $show_labels ? 'aussbond-atc-attribute-label' : 'aussbond-atc-attribute-label aussbond-atc-screen-reader-text';
			$th_class    = $show_labels ? 'label' : 'label aussbond-atc-variation-label-hidden';

			echo '<tr>';
			echo '<th class="' . esc_attr( $th_class ) . '"><label class="' . esc_attr( $label_class ) . '" for="' . esc_attr( $field_id ) . '">' . esc_html( wc_attribute_label( $attribute_name ) ) . '</label></th>';
			echo '<td class="value">';

			wc_dropdown_variation_attribute_options(
				array(
					'options'          => $options,
					'attribute'        => $attribute_name,
					'product'          => $product,
					'selected'         => $this->get_selected_attribute( $product, $attribute_name ),
					'id'               => $field_id,
					'class'            => 'aussbond-atc-attribute-select',
					'show_option_none' => sprintf(
						/* translators: %s: Attribute label. */
						esc_html__( 'Choose %s', 'aussbond-add-to-cart-button' ),
						wc_attribute_label( $attribute_name )
					),
				)
			);

			echo '</td>';
			echo '</tr>';
		}

		echo '</tbody></table>';
		echo '<a class="reset_variations" href="#" aria-label="' . esc_attr__( 'Clear product options', 'aussbond-add-to-cart-button' ) . '">' . esc_html__( 'Clear', 'aussbond-add-to-cart-button' ) . '</a>';
	}

	/**
	 * Render visible attributes for simple products.
	 *
	 * @param \WC_Product $product Product.
	 */
	private function render_simple_attributes( \WC_Product $product ): void {
		$attributes = $product->get_attributes();

		if ( empty( $attributes ) ) {
			return;
		}

		$rows = array();

		foreach ( $attributes as $attribute ) {
			if ( ! $attribute instanceof \WC_Product_Attribute || ! $attribute->get_visible() ) {
				continue;
			}

			$label = wc_attribute_label( $attribute->get_name() );
			$terms = array();

			if ( $attribute->is_taxonomy() ) {
				$terms = wc_get_product_terms(
					$product->get_id(),
					$attribute->get_name(),
					array(
						'fields' => 'names',
					)
				);
			} else {
				$terms = $attribute->get_options();
			}

			if ( empty( $terms ) ) {
				continue;
			}

			$rows[] = array(
				'label' => $label,
				'value' => implode( ', ', array_map( 'wc_clean', $terms ) ),
			);
		}

		if ( empty( $rows ) ) {
			return;
		}

		echo '<dl class="aussbond-atc-simple-attributes">';

		foreach ( $rows as $row ) {
			echo '<dt>' . esc_html( $row['label'] ) . '</dt>';
			echo '<dd>' . esc_html( $row['value'] ) . '</dd>';
		}

		echo '</dl>';
	}

	/**
	 * Render add-to-cart button.
	 *
	 * @param \WC_Product $product        Product.
	 * @param string      $label          Current label.
	 * @param string      $button_text    In-stock label.
	 * @param string      $backorder_text Backorder/out-of-stock label.
	 * @param bool        $disabled       Disabled state.
	 */
	private function render_button( \WC_Product $product, string $label, string $button_text, string $backorder_text, bool $disabled ): void {
		$classes = array( 'aussbond-atc-button', 'single_add_to_cart_button', 'button', 'alt' );

		if ( $disabled ) {
			$classes[] = 'disabled';
		}

		printf(
			'<button type="submit" class="%1$s" data-button-text="%2$s" data-backorder-text="%3$s" data-out-of-stock-text="%4$s" data-stock-status="%5$s" data-is-addable="%6$s" data-is-backorder="%7$s" data-product-id="%8$s" %9$s>%10$s</button>',
			esc_attr( implode( ' ', $classes ) ),
			esc_attr( $button_text ),
			esc_attr( $backorder_text ),
			esc_attr__( 'Out of Stock', 'aussbond-add-to-cart-button' ),
			esc_attr( $product->get_stock_status() ),
			esc_attr( $this->is_add_to_cart_allowed( $product ) ? '1' : '0' ),
			esc_attr( $this->is_backorder_button_state( $product ) ? '1' : '0' ),
			esc_attr( (string) $product->get_id() ),
			disabled( $disabled, true, false ),
			esc_html( $label )
		);
	}

	/**
	 * Enrich WooCommerce variation data with stock/button metadata.
	 *
	 * @param \WC_Product_Variable $product        Product.
	 * @param string               $button_text    In-stock label.
	 * @param string               $backorder_text Backorder/out-of-stock label.
	 */
	private function get_available_variations_data( \WC_Product_Variable $product, string $button_text, string $backorder_text ): array {
		$variations = $product->get_available_variations();
		$indexed    = array();

		foreach ( $variations as $variation_data ) {
			if ( isset( $variation_data['variation_id'] ) ) {
				$indexed[ (int) $variation_data['variation_id'] ] = true;
			}
		}

		foreach ( $product->get_children() as $variation_id ) {
			if ( isset( $indexed[ (int) $variation_id ] ) ) {
				continue;
			}

			$variation = wc_get_product( $variation_id );

			if ( ! $variation instanceof \WC_Product_Variation || ! $variation->is_purchasable() ) {
				continue;
			}

			$variation_data = $this->get_managed_stock_variation_data( $product, $variation );

			if ( is_array( $variation_data ) && ! empty( $variation_data ) ) {
				$variations[] = $variation_data;
			}
		}

		foreach ( $variations as &$variation_data ) {
			$variation = isset( $variation_data['variation_id'] ) ? wc_get_product( $variation_data['variation_id'] ) : null;

			if ( ! $variation instanceof \WC_Product_Variation ) {
				continue;
			}

			$stock_status = $variation->get_stock_status();
			$is_addable   = $this->is_add_to_cart_allowed( $variation );

			$variation_data['aussbond_stock_status']       = $stock_status;
			$variation_data['aussbond_button_text']        = $this->get_product_button_label( $variation, $button_text, $backorder_text );
			$variation_data['aussbond_backorders_allowed'] = $variation->backorders_allowed();
			$variation_data['aussbond_is_addable']         = $is_addable;
			$variation_data['aussbond_is_purchasable']     = $variation->is_purchasable();
			$variation_data['aussbond_is_in_stock']        = $variation->is_in_stock();
			$variation_data['aussbond_managing_stock']     = $variation->managing_stock();
			$variation_data['aussbond_stock_quantity']     = $variation->get_stock_quantity();

			$variation_data['is_in_stock'] = $is_addable;
		}

		return $variations;
	}

	/**
	 * Build WooCommerce variation data for managed-stock variations that may be hidden by stock status.
	 *
	 * @param \WC_Product_Variable  $product   Parent variable product.
	 * @param \WC_Product_Variation $variation Variation.
	 */
	private function get_managed_stock_variation_data( \WC_Product_Variable $product, \WC_Product_Variation $variation ): array {
		$stock_filter = static function ( $is_in_stock, $checked_product ) use ( $variation ) {
			if (
				$checked_product instanceof \WC_Product
				&& (int) $checked_product->get_id() === (int) $variation->get_id()
				&& $checked_product->is_purchasable()
			) {
				return true;
			}

			return $is_in_stock;
		};

		add_filter( 'woocommerce_product_is_in_stock', $stock_filter, 10, 2 );
		$variation_data = $product->get_available_variation( $variation );
		remove_filter( 'woocommerce_product_is_in_stock', $stock_filter, 10 );

		return is_array( $variation_data ) ? $variation_data : array();
	}

	/**
	 * Resolve product from widget setting, current product, or queried object.
	 *
	 * @param array $settings Widget settings.
	 */
	private function resolve_product( array $settings ): ?\WC_Product {
		$product_id = isset( $settings['product_id'] ) ? absint( $settings['product_id'] ) : 0;

		if ( 0 >= $product_id ) {
			global $product;

			if ( $product instanceof \WC_Product ) {
				if ( $product instanceof \WC_Product_Variation ) {
					$parent_product = wc_get_product( $product->get_parent_id() );

					return $parent_product instanceof \WC_Product ? $parent_product : null;
				}

				return $product;
			}

			$product_id = get_the_ID() ? absint( get_the_ID() ) : 0;
		}

		if ( 0 >= $product_id ) {
			return null;
		}

		$product = wc_get_product( $product_id );

		if ( $product instanceof \WC_Product_Variation ) {
			$product = wc_get_product( $product->get_parent_id() );
		}

		return $product instanceof \WC_Product ? $product : null;
	}

	/**
	 * Get a selected attribute value from request or product defaults.
	 *
	 * @param \WC_Product_Variable $product        Product.
	 * @param string               $attribute_name Attribute name.
	 */
	private function get_selected_attribute( \WC_Product_Variable $product, string $attribute_name ): string {
		$request_key = 'attribute_' . sanitize_title( $attribute_name );

		if ( isset( $_REQUEST[ $request_key ] ) && is_scalar( $_REQUEST[ $request_key ] ) ) {
			return wc_clean( wp_unslash( $_REQUEST[ $request_key ] ) );
		}

		return method_exists( $product, 'get_variation_default_attribute' ) ? (string) $product->get_variation_default_attribute( $attribute_name ) : '';
	}

	/**
	 * Return button label for the current product stock status.
	 *
	 * @param \WC_Product $product        Product.
	 * @param string      $button_text    In-stock label.
	 * @param string      $backorder_text Backorder/out-of-stock label.
	 */
	private function get_product_button_label( \WC_Product $product, string $button_text, string $backorder_text ): string {
		if ( ! $this->is_add_to_cart_allowed( $product ) ) {
			return __( 'Out of Stock', 'aussbond-add-to-cart-button' );
		}

		return $this->is_backorder_button_state( $product ) ? $backorder_text : $button_text;
	}

	/**
	 * Check whether a product can be submitted to WooCommerce cart.
	 *
	 * @param \WC_Product $product Product or variation.
	 */
	private function is_add_to_cart_allowed( \WC_Product $product ): bool {
		return $product->is_purchasable()
			&& (
				$product->is_in_stock()
				|| $product->backorders_allowed()
				|| $this->has_managed_stock_available( $product )
			);
	}

	/**
	 * Check whether the button should show the backorder label.
	 *
	 * @param \WC_Product $product Product or variation.
	 */
	private function is_backorder_button_state( \WC_Product $product ): bool {
		return $product->backorders_allowed() && ! $product->has_enough_stock( 1 );
	}

	/**
	 * Check variation-level or product-level managed stock quantity.
	 *
	 * @param \WC_Product $product Product or variation.
	 */
	private function has_managed_stock_available( \WC_Product $product ): bool {
		return $product->managing_stock()
			&& null !== $product->get_stock_quantity()
			&& 0 < (float) $product->get_stock_quantity();
	}

	/**
	 * Read and sanitize a text setting.
	 *
	 * @param array  $settings Settings.
	 * @param string $key      Setting key.
	 * @param string $default  Default label.
	 */
	private function get_safe_label( array $settings, string $key, string $default ): string {
		$value = isset( $settings[ $key ] ) ? sanitize_text_field( $settings[ $key ] ) : '';

		return '' !== $value ? $value : $default;
	}

	/**
	 * Render notices only for editors/admins.
	 *
	 * @param string $message Message.
	 */
	private function render_editor_notice( string $message ): void {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}

		echo '<div class="woocommerce-info aussbond-atc-editor-notice">' . esc_html( $message ) . '</div>';
	}
}
