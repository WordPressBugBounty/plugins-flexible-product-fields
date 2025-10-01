<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use WPDesk\FPF\Free\Field\TemplateArgs;
use WPDesk\FPF\Free\Service\TemplateFinder\TemplateFinder;
use WPDesk\FPF\Free\Service\TemplateFinder\TemplateQuery;
use WPDesk\FPF\Free\Service\TemplateFinder\Collections\TemplateCollection;
use WPDesk\FPF\Free\Block\Settings\BlockTemplateSettings;

class FPF_Product {

	/**
	 * Priority before default
	 */
	const HOOK_BEFORE_DEFAULT = 9;

	/**
	 * Priority after default
	 */
	const HOOK_AFTER_DEFAULT = 20;

	/**
	 * Is hook woocommerce_before_add_to_cart_button already fired?
	 *
	 * @var bool
	 */
	private $is_woocommerce_before_add_to_cart_button_fired = false;

	/**
	 * @var null|Flexible_Product_Fields_Plugin
	 */
	private $_plugin = null;

	/**
	 * @var FPF_Product_Fields|null
	 */
	private $_product_fields = null;

	/**
	 * Product price.
	 *
	 * @var FPF_Product_Price|null
	 */
	private $product_price = null;

	private TemplateFinder $template_finder;

	private $has_fpf_block = false;

	/**
	 * FPF_Product constructor.
	 *
	 * @param Flexible_Product_Fields_Plugin $plugin
	 * @param FPF_Product_Fields $product_fields
	 */
	public function __construct( Flexible_Product_Fields_Plugin $plugin, FPF_Product_Fields $product_fields, FPF_Product_Price $product_price ) {
		$this->_plugin         = $plugin;
		$this->_product_fields = $product_fields;
		$this->product_price   = $product_price;
		$this->template_finder = $this->_plugin->get_template_finder();
		$this->hooks();
	}

	/**
	 * Hooks.
	 */
	public function hooks() {

		add_action( 'woocommerce_before_add_to_cart_button', [ $this, 'woocommerce_before_add_to_cart_button' ] );
		add_action( 'woocommerce_after_add_to_cart_button', [ $this, 'woocommerce_after_add_to_cart_button' ] );

		add_filter( 'woocommerce_product_supports', [ $this, 'woocommerce_product_supports' ], 10, 3 );
	}

	/**
	 * @param bool $supports
	 * @param string $feature
	 * @param WC_Product $product
	 *
	 * @return bool
	 */
	public function woocommerce_product_supports( $supports, $feature, $product ) {
		if ( 'ajax_add_to_cart' === $feature && $this->product_has_required_field( $product ) ) {
			$supports = false;
		}
		return $supports;
	}

	/**
	 * @param string $type
	 *
	 * @return array
	 * @internal
	 */
	public function get_field_type( $type ) {
		$ret         = [];
		$field_types = $this->_product_fields->get_field_types();
		foreach ( $field_types as $field_type ) {
			if ( $field_type['value'] == $type ) {
				return $field_type;
			}
		}
		return $ret;
	}

	/**
	 * @param WC_Product $product
	 *
	 * @return bool
	 */
	public function product_has_required_field( $product ): bool {
		if ( ! $product instanceof \WC_Product ) {
			return false;
		}

		$templates = $this->template_finder->find( $product );
		return $templates->has_required_fields();
	}

	/**
	 * Get translated fields for product.
	 * Titles and labels will be translated to current language.
	 *
	 * @param WC_Product $product
	 * @param bool|string $hook
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public function get_translated_fields_for_product( $product, $hook = false, ?BlockTemplateSettings $block_settings = null ): array {
		if ( ! $product instanceof \WC_Product ) {
			return [];
		}

		if ( $block_settings === null ) {
			$templates = $this->template_finder->find( $product, $hook );
			return $this->translate_fields_titles_and_labels( $templates->legacy_results() );
		}

		$template_query = $this->template_finder->get_template_query();
		$block_template = $template_query->get_template_by_id( $block_settings->get_template_id() );
		$block_template = new TemplateCollection( [ $block_template ] );

		if ( $block_settings->should_show_other_templates() ) {
			$templates = $this->template_finder->find( $product );
			$block_template->merge( $templates );
		}

		$block_template->init_fields( $this->_product_fields->get_field_types() );

		return $this->translate_fields_titles_and_labels( $block_template->legacy_results() );
	}

	/**
	 * @param WC_Product $product
	 * @param BlockTemplateSettings|null $block_settings
	 *
	 * @return array
	 */
	public function get_logic_rules_for_product( $product, ?BlockTemplateSettings $block_settings = null ) {
		$fields = $this->get_translated_fields_for_product( $product, false, $block_settings );
		$rules  = [];
		foreach ( $fields['fields'] as $field ) {
			if ( isset( $field['logic'] ) && $field['logic'] == '1' && isset( $field['logic_operator'] ) && isset( $field['logic_rules'] ) ) {
				$rules[ $field['id'] ]             = [];
				$rules[ $field['id'] ]['rules']    = $field['logic_rules'];
				$rules[ $field['id'] ]['operator'] = $field['logic_operator'];
			}
		}
		return $rules;
	}

	/**
	 * @param WC_Product $product
	 * @param bool|string $hook
	 *
	 * @return array
	 * @internal
	 */
	public function create_fields_for_product( $product, $hook, ?BlockTemplateSettings $block_settings = null ) {
		$fields = $this->get_translated_fields_for_product( $product, $hook, $block_settings );
		foreach ( $fields['fields'] as $field ) {
			$fields['display_fields'][] = $this->create_field( $field, $product );
		}
		return $fields;
	}

	/**
	 * @param array $field
	 * @param WC_Product $product
	 *
	 * @return string
	 * @internal
	 */
	public function create_field( array $field, WC_Product $product ) {
		$field_type    = $this->get_field_type( $field['type'] );
		$template_vars = $field_type['type_object']->get_field_template_vars( $field );

		$template_vars['value'] = $field_type['type_object']->get_field_value( $field['id'], true );
		$template_vars['args']  = ( new TemplateArgs() )->parse_field_args(
			$field_type,
			$field_type['type_object'],
			$field,
			$this->product_price,
			$product
		);

		return $this->_plugin->load_template(
			$field_type['template_file'],
			'fields',
			$template_vars
		);
	}

	/**
	 * Translate fields titles and labels.
	 *
	 * @param array $fields
	 *
	 * @return array
	 */
	private function translate_fields_titles_and_labels( array $fields ) {
		foreach ( $fields['fields'] as $key => $field ) {
			$field['title'] = wpdesk__( $field['title'], 'flexible-product-fields' );
			if ( isset( $field['placeholder'] ) ) {
				$field['placeholder'] = wpdesk__( $field['placeholder'], 'flexible-product-fields' );
			}
			if ( isset( $field['has_options'] ) && $field['has_options'] ) {
				foreach ( $field['options'] as $option_key => $option ) {
					$field['options'][ $option_key ]['label'] = wpdesk__( $option['label'], 'flexible-product-fields' );
				}
			}
			$fields['fields'][ $key ] = $field;
		}
		return $fields;
	}

	public function render_fields_before_add_to_cart( WC_Product $product ): string {
		$fields = $this->create_fields_for_product( $product, 'woocommerce_before_add_to_cart_button' );
		return $this->_plugin->load_template(
			'woocommerce_before_add_to_cart_button',
			'hooks',
			[
				'fields'     => $fields['display_fields'],
				'product_id' => $product->get_id(),
				'fpf_nonce'  => \wp_create_nonce( 'fpf_add_to_cart_nonce' ),
			]
		);
	}

	public function render_fields_after_add_to_cart( WC_Product $product ): string {
		$fields = $this->create_fields_for_product( $product, 'woocommerce_after_add_to_cart_button' );
		return $this->_plugin->load_template(
			'woocommerce_after_add_to_cart_button',
			'hooks',
			[
				'fields'     => $fields['display_fields'],
				'product_id' => $product->get_id(),
			]
		);
	}

	public function render_fields( WC_Product $product, BlockTemplateSettings $block_settings ): string {
		$fields = $this->create_fields_for_product( $product, false, $block_settings );
		return $this->_plugin->load_template(
			'block_fields',
			'hooks',
			[
				'fields'         => $fields['display_fields'],
				'product_id'     => $product->get_id(),
				'block_settings' => $block_settings,
				'fpf_nonce'      => \wp_create_nonce( 'fpf_add_to_cart_nonce' ),
			]
		);
	}

	/**
	 * Fired by woocommerce_after_add_to_cart_button hook.
	 */
	public function woocommerce_after_add_to_cart_button(): void {
		global $product;

		if ( ! $product instanceof \WC_Product ) {
			return;
		}

		if ( $this->has_fpf_block ) {
			return;
		}

		echo $this->render_fields_after_add_to_cart( $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Fired by woocommerce_before_add_to_cart_button hook.
	 */
	public function woocommerce_before_add_to_cart_button(): void {
		/** Prevent display fields more than once. Action may be fired by other third party plugins, ie. Woocommerce Subscriptions */
		if ( $this->is_woocommerce_before_add_to_cart_button_fired ) {
			return;
		}

		$this->is_woocommerce_before_add_to_cart_button_fired = true;

		$this->has_fpf_block = $this->_plugin->has_block( 'fpf/template-selector' );
		if ( $this->has_fpf_block ) {
			return;
		}

		global $product;

		if ( ! $product instanceof \WC_Product ) {
			return;
		}

		echo $this->render_fields_before_add_to_cart( $product ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	* @return array<string, mixed> The fields data with calculated price values and display values.
	*/
	public function get_fields_data( WC_Product $product, ?BlockTemplateSettings $block_settings = null ): array {
		$fields = $this->get_translated_fields_for_product( $product, false, $block_settings );

		foreach ( $fields['fields'] as $key => $field ) {
			$fields['fields'][ $key ] = $this->process_field_prices( $field, $product );

			if ( filter_var( $field['has_options'], FILTER_VALIDATE_BOOLEAN ) ) {
				$fields['fields'][ $key ]['options'] = $this->process_field_options_prices(
					$field,
					$product
				);
			}
		}

		return $fields['fields'];
	}

	/**
	 * Process prices for a single field.
	 *
	 * @param array<string, mixed> $field
	 * @param WC_Product $product
	 *
	 * @return array<string, mixed>
	 */
	private function process_field_prices( array $field, WC_Product $product ): array {
		$field['price_value'] = 0;

		if ( ! isset( $field['price_type'] ) ) {
			$field['price_type'] = 'fixed';
		}

		if ( ! filter_var( $field['has_price'], FILTER_VALIDATE_BOOLEAN ) ) {
			return $field;
		}

		$price            = $field['price'] ?? '';
		$calculation_type = $field['calculation_type'] ?? '';

		$processed_price = $this->get_processed_price_data( $price, $field['price_type'], $calculation_type, $product );

		if ( $processed_price !== null ) {
			$field['price_value']   = $processed_price['price_value'];
			$field['price_display'] = $processed_price['price_display'];
		}

		return $field;
	}

	/**
	* Process prices for field options.
	*
	* @param array<string, mixed> $field
	* @param WC_Product $product
	*
	* @return array<string, mixed>
	*/
	private function process_field_options_prices( array $field, WC_Product $product ): array {
		$options = $field['options'] ?? [];

		if ( ! filter_var( $field['has_price_in_options'], FILTER_VALIDATE_BOOLEAN ) ) {
			return $options;
		}

		$price_values = $field['price_values'] ?? [];

		foreach ( $options as $option_key => $option ) {
			$options[ $option_key ] = $this->process_single_option_price(
				$option,
				$price_values,
				$product
			);
		}

		return $options;
	}

	/**
	 * Process price for a single option.
	 *
	 * @param array<string, mixed> $option
	 * @param array<string, mixed> $price_values
	 * @param WC_Product $product
	 *
	 * @return array<string, mixed>
	 */
	private function process_single_option_price(
		array $option,
		array $price_values,
		WC_Product $product
	): array {
		$option['price_value'] = 0;

		$option_value            = $option['value'] ?? null;
		$option_price_type       = $price_values[ $option_value ]['price_type'] ?? ( $option['price_type'] ?? '' );
		$option_price_value      = $price_values[ $option_value ]['price'] ?? ( $option['price'] ?? '' );
		$option_calculation_type = $price_values[ $option_value ]['calculation_type'] ?? ( $option['calculation_type'] ?? '' );

		$processed_price = $this->get_processed_price_data( $option_price_value, $option_price_type, $option_calculation_type, $product );

		if ( $processed_price !== null ) {
			$option['price_type']       = $option_price_type;
			$option['price']            = $option_price_value;
			$option['calculation_type'] = $option_calculation_type;
			$option['price_value']      = $processed_price['price_value'];
			$option['price_display']    = $processed_price['price_display'];
		}

		return $option;
	}

	/**
	 * Process price data based on provided settings.
	 *
	 * @param string      $price            Price value string.
	 * @param string      $price_type       Price type.
	 * @param string      $calculation_type Calculation type.
	 * @param WC_Product  $product          Product object.
	 * @return array{price_value: float, price_display: string}|null Processed price data or null if invalid.
	 */
	private function get_processed_price_data( string $price, string $price_type, string $calculation_type, WC_Product $product ): ?array {
		if ( ! $this->product_price->is_valid_pricing_settings( $price_type, $price, $calculation_type ) ) {
			return null;
		}

		$price_value = $this->product_price->calculate_price(
			(float) $price,
			$price_type,
			$product,
			$calculation_type
		);

		return [
			'price_value'   => $price_value,
			'price_display' => $this->product_price->prepare_price_to_display( $product, $price_value ),
		];
	}

	public function get_product_price_data( WC_Product $product ): string {
		$tax_display_mode = get_option( 'woocommerce_tax_display_shop' );
		if ( $tax_display_mode === 'excl' ) {
			$product_price = wpdesk_get_price_excluding_tax( $product );
		} else {
			$product_price = wpdesk_get_price_including_tax( $product );
		}
		return (string) $product_price;
	}
}
