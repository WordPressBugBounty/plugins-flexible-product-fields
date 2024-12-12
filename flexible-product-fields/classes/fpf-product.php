<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use WPDesk\FPF\Free\Field\TemplateArgs;
use WPDesk\FPF\Free\Service\TemplateFinder\TemplateFinder;

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
	 * @var null|Flexible_Product_Fields
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

	/**
	 * FPF_Product constructor.
	 *
	 * @param Flexible_Product_Fields $plugin
	 * @param FPF_Product_Fields $product_fields
	 */
	public function __construct( Flexible_Product_Fields_Plugin $plugin, FPF_Product_Fields $product_fields, FPF_Product_Price $product_price ) {
		$this->_plugin         = $plugin;
		$this->_product_fields = $product_fields;
		$this->product_price   = $product_price;
		$this->template_finder = new TemplateFinder();
		$this->hooks();
	}

	/**
	 *
	 */
	public function hooks() {

		add_action( 'woocommerce_before_add_to_cart_button', [ $this, 'woocommerce_before_add_to_cart_button' ] );
		add_action( 'woocommerce_after_add_to_cart_button', [ $this, 'woocommerce_after_add_to_cart_button' ] );

		add_filter( 'woocommerce_product_supports', [ $this, 'woocommerce_product_supports' ], 10, 3 );
	}

	/**
	 * @param string $error
	 */
	public function add_error( $error ) {
		wc_add_notice( $error, 'error' );
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
	public function product_has_required_field( $product ) {
		$fields = $this->get_translated_fields_for_product( $product );
		return $fields['has_required'];
	}

	/**
	 * Get translated fields for product.
	 * Titles and labels will be translated to current language.
	 *
	 * @param WC_Product $product
	 * @param bool|string $hook
	 *
	 * @return array
	 */
	public function get_translated_fields_for_product( $product, $hook = false ) {
		$templates = $this->template_finder->find( $product, $hook );
		$templates->init_fields( $this->_product_fields->get_field_types_by_type() );

		return $this->translate_fields_titles_and_labels( $templates->legacy_results() );
	}


	/**
	 * @param WC_Product $product
	 *
	 * @return array
	 */
	public function get_logic_rules_for_product( $product ) {
		$fields = $this->get_translated_fields_for_product( $product );
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
	 */
	public function create_fields_for_product( $product, $hook ) {
		$fields = $this->get_translated_fields_for_product( $product, $hook );
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
	 * @param bool|string $hook
	 */
	public function show_fields( $hook ) {
		global $product;
		$fields = $this->create_fields_for_product( $product, $hook );
		if ( count( $fields['display_fields'] ) ) {
			echo $this->_plugin->load_template( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				$hook,
				'hooks',
				[
					'fields'    => $fields['display_fields'], // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					'fpf_nonce' => wp_create_nonce( 'fpf_add_to_cart_nonce' ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				]
			);
		}
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
			if ( $field['has_options'] ) {
				foreach ( $field['options'] as $option_key => $option ) {
					$field['options'][ $option_key ]['label'] = wpdesk__( $option['label'], 'flexible-product-fields' );
				}
			}
			$fields['fields'][ $key ] = $field;
		}
		return $fields;
	}

	/**
	 * Fired by woocommerce_before_add_to_cart_button hook.
	 */
	public function woocommerce_before_add_to_cart_button() {
		/** Prevent display fields more than once. Action may be fired by other third party plugins, ie. Woocommerce Subscriptions */
		if ( $this->is_woocommerce_before_add_to_cart_button_fired ) {
			return;
		}
		$this->is_woocommerce_before_add_to_cart_button_fired = true;
		global $product;
		$product_extended_info = new FPF_Product_Extendend_Info( $product );
		$this->show_fields( 'woocommerce_before_add_to_cart_button' );
		echo $this->_plugin->load_template( 'display', 'totals', [] );
		$fields = $this->translate_fields_titles_and_labels( $this->get_translated_fields_for_product( $product ) );
		foreach ( $fields['fields'] as $key => $field ) {
			$fields['fields'][ $key ]['price_value'] = 0;
			if ( ! isset( $field['price_type'] ) ) {
				$field['price_type']                    = 'fixed';
				$fields['fields'][ $key ]['price_type'] = 'fixed';
			}
			if ( $field['has_price'] && isset( $field['price_type'] ) && $field['price_type'] != '' && isset( $field['price'] ) && $field['price'] != '' ) {
				$price_value                               = $this->product_price->calculate_price( floatval( $field['price'] ), $field['price_type'], $product );
				$fields['fields'][ $key ]['price_value']   = $price_value;
				$fields['fields'][ $key ]['price_display'] = $this->product_price->prepare_price_to_display( $product, $price_value );
			}
			if ( $field['has_options'] ) {
				foreach ( $fields['fields'][ $key ]['options'] as $option_key => $option ) {
					$fields['fields'][ $key ]['options'][ $option_key ]['price_value'] = 0;
					if ( ! $field['has_price_in_options'] ) {
						continue;
					}

					$price_values       = $field['price_values'] ?? [];
					$option_price_type  = $price_values[ $option['value'] ]['price_type'] ?? ( $option['price_type'] ?? '' );
					$option_price_value = $price_values[ $option['value'] ]['price'] ?? ( $option['price'] ?? '' );
					if ( ( $option_price_type === '' ) || ( $option_price_value === '' ) ) {
						continue;
					}

					$price_value = $this->product_price->calculate_price( floatval( $option_price_value ), $option_price_type, $product );
					$fields['fields'][ $key ]['options'][ $option_key ]['price_type']    = $option_price_type;
					$fields['fields'][ $key ]['options'][ $option_key ]['price']         = $option_price_value;
					$fields['fields'][ $key ]['options'][ $option_key ]['price_value']   = $price_value;
					$fields['fields'][ $key ]['options'][ $option_key ]['price_display'] = $this->product_price->prepare_price_to_display( $product, $price_value );
				}
			}
		}
		$tax_display_mode = get_option( 'woocommerce_tax_display_shop' );
		if ( $tax_display_mode == 'excl' ) {
			$product_price = wpdesk_get_price_excluding_tax( $product );
		} else {
			$product_price = wpdesk_get_price_including_tax( $product );
		}
		?>
		<script type="text/javascript">
			var fpf_fields = <?php echo json_encode( $fields['fields'] ); ?>;
			var fpf_product_price = <?php echo json_encode( $product_price ); ?>;
		</script>
		<?php
	}

	/**
	 *
	 */
	public function woocommerce_after_add_to_cart_button() {
		global $product;
		$product_extended_info = new FPF_Product_Extendend_Info( $product );
		$this->show_fields( 'woocommerce_after_add_to_cart_button' );
	}
}
