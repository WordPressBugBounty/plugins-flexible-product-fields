<?php

use WPDesk\FPF\Free\Block\Settings\BlockTemplateSettings;
use WPDesk\FPF\Free\DTO\DateDTOInterface;
use WPDesk\FPF\Free\Helper\DateFormatConverter;
use WPDesk\FPF\Free\Service\BookingCartFormatter;
use WPDesk\FPF\Free\Service\BookingUnitsCalculator;

/**
 * Handles WooCommerce add to cart.
 */
class FPF_Cart {

	/**
	 * Priority before default
	 */
	const HOOK_BEFORE_DEFAULT = 9;

	/**
	 * Priority after default
	 */
	const HOOK_AFTER_DEFAULT = 20;

	/**
	 * Plugin
	 *
	 * @var Flexible_Product_Fields_Plugin
	 */
	private $_plugin;

	/**
	 * @var FPF_Product_Fields
	 */
	private $_product_fields = null;

	/**
	 * @var FPF_Product|null
	 */
	private $_product = null;

	/**
	 * Product price.
	 *
	 * @var FPF_Product_Price|null
	 */
	private $product_price = null;

	private BookingCartFormatter $booking_formatter;

	/**
	 * FPF_Cart constructor.
	 *
	 * @param Flexible_Product_Fields_Plugin $plugin
	 * @param FPF_Product_Fields             $product_fields
	 * @param FPF_Product                    $product
	 * @param FPF_Product_Price              $product_price
	 */
	public function __construct( Flexible_Product_Fields_Plugin $plugin, FPF_Product_Fields $product_fields, FPF_Product $product, FPF_Product_Price $product_price, BookingCartFormatter $booking_formatter ) {
		$this->_plugin           = $plugin;
		$this->_product_fields   = $product_fields;
		$this->_product          = $product;
		$this->product_price     = $product_price;
		$this->booking_formatter = $booking_formatter;
		add_action( 'plugins_loaded', [ $this, 'hooks' ] );
	}

	/**
	 * Define hooks
	 */
	public function hooks() {
		add_filter( 'woocommerce_add_to_cart_handler', [ $this, 'woocommerce_add_to_cart_handler' ], 10, 2 );
		add_filter( 'woocommerce_get_cart_item_from_session', [ $this, 'woocommerce_get_cart_item_from_session' ], self::HOOK_AFTER_DEFAULT, 2 );
		add_filter( 'woocommerce_get_item_data', [ $this, 'woocommerce_get_item_data' ], 10, 2 );
		add_action( 'woocommerce_new_order_item', [ $this, 'woocommerce_new_order_item' ], 10, 3 );
		// moreConvert integration.
		add_action( 'wlfmc_add_to_list_handler', [ $this, 'woocommerce_add_to_cart_handler' ], 10, 2 );
		add_filter( 'wlfmc_third_party_item_price', [ $this, 'woocommerce_add_cart_item' ], 20 );
		add_filter( 'wlfmc_add_to_cart_handler', [ $this, 'woocommerce_add_cart_item_data' ], 10, 3 );
	}

	/**
	 * Handle filters and actions for add to cart process but not for grouped product.
	 *
	 * @param string $type
	 * @param int $product_id
	 *
	 * @return string
	 */
	public function woocommerce_add_to_cart_handler( $type, $product_id ) {
		add_filter( 'woocommerce_add_cart_item', [ $this, 'woocommerce_add_cart_item' ], self::HOOK_AFTER_DEFAULT, 1 );
		add_filter( 'woocommerce_add_cart_item_data', [ $this, 'woocommerce_add_cart_item_data' ], self::HOOK_AFTER_DEFAULT, 3 );
		return $type;
	}

	private function validate_product_referer_id( array $product_data ): bool {
		$product_id = $product_data['product_id'] ?? null;
		$referer_id = $product_data['flexible_product_fields_product_id'] ?? null;

		if ( $product_id === null || $referer_id === null ) {
			return false;
		}

		$referer_product = \wc_get_product( $referer_id );
		if ( ! $referer_product instanceof \WC_Product ) {
			return false;
		}

		if ( $referer_product->get_type() === FPF_Product_Extendend_Info::PRODUCT_TYPE_GROUPED ) {
			return true;
		}

		return true;
	}

	/**
	 * @deprecated since 2.12.0
	 *
	 * @param int $item_id
	 * @param array $values
	 */
	public function woocommerce_add_order_item_meta( $item_id, $values ) {
		$fields = $values['flexible_product_fields'] ?? [];
		if ( $fields && $this->validate_product_referer_id( $values ) ) {
			foreach ( $fields as $field ) {
				$name = $field['name'];
				wc_add_order_item_meta( $item_id, $name, $field['value'] );
			}
		}
	}

	/**
	 * @param int $item_id
	 * @param mixed $item
	 * @param int $order_id
	 */
	public function woocommerce_new_order_item( $item_id, $item, $order_id ) {
		if ( $item instanceof WC_Order_Item_Product ) {
			if ( ! empty( $item->legacy_values ) && ! empty( $item->legacy_values['flexible_product_fields'] )
				&& $this->validate_product_referer_id( $item->legacy_values ) ) {
				foreach ( $item->legacy_values['flexible_product_fields'] as $field ) {
					if ( filter_var( $field['is_reservation'] ?? false, FILTER_VALIDATE_BOOLEAN ) ) {
						continue; // we will handle those fields in a Booking Addon plugin
					}

					$name = $field['name'];
					wc_add_order_item_meta( $item_id, $name, $field['value'] );
				}
			}
		}
	}

	/**
	 * @param array $cart_item
	 * @param array $values
	 *
	 * @return mixed
	 */
	public function woocommerce_get_cart_item_from_session( $cart_item, $values ) {
		$fields = $values['flexible_product_fields'] ?? [];
		if ( ! $fields || ! $this->validate_product_referer_id( $values ) ) {
			return $cart_item;
		}

		$cart_item['flexible_product_fields'] = $values['flexible_product_fields'];
		return $this->woocommerce_add_cart_item( $cart_item );
	}

	/**
	 * @param array $cart_item
	 * @return array mixed
	 */
	public function woocommerce_add_cart_item( $cart_item ) {
		$fields = $cart_item['flexible_product_fields'] ?? [];
		if ( ! $fields || ! $this->validate_product_referer_id( $cart_item ) ) {
			return $cart_item;
		}

		$product_id = empty( $cart_item['variation_id'] ) ? $cart_item['product_id'] : $cart_item['variation_id'];
		$product    = $cart_item['data'] ?? \wc_get_product( $product_id );
		if ( ! $product instanceof \WC_Product ) {
			return $cart_item;
		}

		$product_price = isset( $cart_item['pricing_item_meta_data']['_price'] )
			? (float) $cart_item['pricing_item_meta_data']['_price']
			: (float) $product->get_price( 'edit' );
		$measurement   = isset( $cart_item['pricing_item_meta_data']['_measurement_needed'] ) ? (float) $cart_item['pricing_item_meta_data']['_measurement_needed'] : 1;

		// Separate fields while preserving keys
		$non_booking_pricing_fields = [];
		$per_booking_pricing_fields = [];

		foreach ( $fields as $key => $field ) {
			if ( ! isset( $field['price_type'] ) || $field['price_type'] === '' ) {
				continue;
			}

			if ( $field['price_type'] === 'per_booking' ) {
				$per_booking_pricing_fields[ $key ] = $field;
			} else {
				$non_booking_pricing_fields[ $key ] = $field;
			}
		}

		$extra_cost = 0;
		foreach ( $non_booking_pricing_fields as $key => $field ) {
			if ( isset( $field['price_type'] ) && $field['price_type'] != '' && isset( $field['price'] ) && floatval( $field['price'] ) != 0 ) {
				$field_price            = (float) $field['price'];
				$field_price_type       = (string) $field['price_type'];
				$field_calculation_type = (string) ( $field['calculation_type'] ?? '' );

				$field_price_base_currency    = $this->product_price->calculate_price( $field_price, $field_price_type, $product, $field_calculation_type, $product_price, $measurement );
				$field_price_current_currency = (float) $this->product_price->multicurrency_calculate_price_to_display( $field_price_base_currency );

				if ( $field_calculation_type === 'fee' ) {
					$title = (string) $field['name'];
					$this->add_fee( $title, $field_price_base_currency );
				} else {
					$extra_cost += $field_price_base_currency;

					$org_value = isset( $field['org_value'] ) ? (string) $field['org_value'] : '';
					$cart_item['flexible_product_fields'][ $key ]['value'] = $this->field_display_value( $field_price_current_currency, $org_value );
				}
			}
		}

		$per_booking_extra_cost = 0;
		foreach ( $per_booking_pricing_fields as $key => $field ) {
			$booking_units = (int) ( $field['booking_units'] ?? 1 );
			if ( $booking_units <= 1 ) {
				continue;
			}

			$booking_base_price_option = $field['booking_base_price'] ?? 'product_only';
			$unit_cost_base            = ( $booking_base_price_option === 'product_with_options' ) ? ( $product_price + $extra_cost ) : $product_price;
			$field_extra_cost_base     = ( $booking_units - 1 ) * $unit_cost_base;
			$per_booking_extra_cost   += $field_extra_cost_base;

			$cart_item['flexible_product_fields'][ $key ]['value'] = $this->booking_formatter->format( $field, $unit_cost_base );
		}

		$price = $product_price + $extra_cost + $per_booking_extra_cost;
		$cart_item['data']->set_price( $price );

		return $cart_item;
	}

	/**
	 * Get field data from posted fields.
	 *
	 * @param array                          $field Field.
	 * @param array                          $field_type Settings of field type.
	 * @param string|null|DateDTOInterface   $value Value of field.
	 * @param int                            $product_id Product ID.
	 * @param int                            $variation_id Variation ID.
	 *
	 * @return array|bool|WP_Error
	 */
	private function get_field_data( $field, $field_type, $value, $product_id, $variation_id ) {
		if ( $this->is_field_value_empty( $value ) ) {
			return false;
		}

		$ret = [
			'name'  => $field['title'],
			'id'    => $field['id'],
			'value' => (string) $value,
		];

		if ( $field['type'] == 'checkbox' ) {
			if ( ! isset( $field['value'] ) ) {
				$ret['value'] = __( 'yes', 'flexible-product-fields' );
			} else {
				$ret['value'] = $field['value'];
			}
		}

		if ( $this->is_reservation_field( $field ) ) {
			$ret['is_reservation'] = true;
			$ret['booking_data']   = $value->to_cart_format();
		}

		if ( $field_type['has_price'] ) {
			if ( ! isset( $field['price_type'] ) ) {
				$field['price_type'] = 'fixed';
			}
			if ( $field['price_type'] === 'per_booking' ) {
				$ret['price_type']         = 'per_booking';
				$ret['booking_base_price'] = $field['booking_base_price'] ?? 'product_only';
				$ret['type']               = $value->get_type();

				$date_format             = $field['date_format'] ?? DateFormatConverter::DEFAULT_DATE_FORMAT;
				$date_format_php         = DateFormatConverter::to_php( $date_format );
				$allow_adjacent_bookings = filter_var( $field['allow_adjacent_bookings'] ?? false, FILTER_VALIDATE_BOOLEAN );

				$ret['booking_units'] = BookingUnitsCalculator::calculate( $value, $date_format_php, $allow_adjacent_bookings );
				$ret['org_value']     = isset( $ret['value'] ) ? (string) $ret['value'] : '';

			} elseif ( isset( $field['price_type'] ) && $field['price_type'] !== '' && isset( $field['price'] ) && $field['price'] !== '' ) {
				$ret['price_type']       = $field['price_type'];
				$ret['price']            = $field['price'];
				$ret['calculation_type'] = $field['calculation_type'] ?? '';
			}
		}

		if ( $field_type['has_options'] ) {
			foreach ( $field['options'] as $option ) {
				if ( trim( $option['value'] ) === $ret['value'] ) {
					$ret['value'] = $option['label'];
					if ( ! $field_type['has_price_in_options'] ) {
						continue;
					}

					$price_values            = $field['price_values'] ?? [];
					$option_price_type       = $price_values[ $option['value'] ]['price_type'] ?? ( $option['price_type'] ?? '' );
					$option_price_value      = $price_values[ $option['value'] ]['price'] ?? ( $option['price'] ?? '' );
					$option_calculation_type = $price_values[ $option['value'] ]['calculation_type'] ?? ( $option['calculation_type'] ?? '' );

					if ( ( $option_price_type === '' ) || ( $option_price_value === '' ) ) {
						continue;
					}

					$ret['price_type']       = $option_price_type;
					$ret['price']            = $option_price_value;
					$ret['calculation_type'] = $option_calculation_type;
				}
			}
		}

		if ( isset( $ret['price_type'] ) && $ret['price_type'] !== '' && isset( $ret['price'] ) && $ret['price'] !== '' ) {
			if ( $variation_id ) {
				$product_id = $variation_id;
			}

			$product                      = wc_get_product( $product_id );
			$field_price                  = floatval( $ret['price'] );
			$field_price_type             = (string) $ret['price_type'];
			$field_calculation_type       = (string) $ret['calculation_type'];
			$field_price_current_currency = $this->field_price_multicurrency( $field_price, $field_price_type, $product, $field_calculation_type );

			$ret['org_value'] = isset( $ret['value'] ) ? (string) $ret['value'] : '';
			$ret['value']     = $this->field_display_value( $field_price_current_currency, $ret['org_value'] );
		}

		return $ret;
	}

	/**
	 * Calculates price for a specific product field, and returns it with the current currency.
	 *
	 * @param float      $price_or_percent The price (default currency) or percentage to calculate the price from.
	 * @param string     $price_type       The type of price (fixed or percentage).
	 * @return float The current price in the current currency.
	 */
	private function field_price_multicurrency( float $price_or_percent, string $price_type, \WC_Product $product, string $calculation_type ): float {
		$price = $this->product_price->calculate_price( $price_or_percent, $price_type, $product, $calculation_type );
		$price = $this->product_price->multicurrency_calculate_price_to_display( $price );

		return (float) $price;
	}

	/**
	 * Decides on how product field with price, should be displayed in the cart.
	 */
	private function field_display_value( float $price, string $label ): string {
		$display_value = trim( $label );

		/**
		 * Filter if product field price infromation should be appended to the field value.
		 *
		 * @param bool $add_price_info
		 * @since 2.5.0
		 */
		$add_price_info = apply_filters( 'flexible_product_fields/display_value/add_price_info', true );
		if ( ! $add_price_info ) {
			return $display_value;
		}

		$price = $this->product_price->wc_price( $price );

		return $display_value . ' (' . $price . ')';
	}

	/**
	 * @param array $other_data
	 * @param array $cart_item
	 *
	 * @return array
	 */
	public function woocommerce_get_item_data( $other_data, $cart_item ) {
		$fields = $cart_item['flexible_product_fields'] ?? [];
		if ( ! $fields || ! $this->validate_product_referer_id( $cart_item ) ) {
			return $other_data;
		}

		foreach ( $fields as $field ) {
			$name         = $field['name'];
			$other_data[] = [
				'name'    => $name,
				'value'   => $field['value'],
				'display' => $field['display'] ?? '',
			];
		}

		return $other_data;
	}


	/**
	 * @param array $cart_item_data
	 * @param int $product_id
	 * @param int $variation_id
	 *
	 * @return array
	 * @throws \Exception
	 */
	public function woocommerce_add_cart_item_data( $cart_item_data, $product_id, $variation_id ) {
		if ( ! isset( $_POST['_fpf_product_id'], $_POST['_fpf_nonce'] ) ) {
			return $cart_item_data;
		}

		if ( ! in_array( (int) $_POST['_fpf_product_id'], [ $product_id, $variation_id ], true ) ) {
			return $cart_item_data;
		}

		$post_data = wc_clean( wp_unslash( $_POST ) );

		if ( ! wp_verify_nonce( $post_data['_fpf_nonce'], 'fpf_add_to_cart_nonce' ) ) {
			throw new \Exception( 'Nonce verification failed.' );
		}

		$product_data = wc_get_product( $post_data['_fpf_product_id'] );

		$block_settings = null;
		if ( isset( $post_data['_fpf_block_template_id'] ) && (int) $post_data['_fpf_block_template_id'] > 0 ) {
			$template_id          = (int) $post_data['_fpf_block_template_id'];
			$show_other_templates = (bool) $post_data['_fpf_block_show_other_templates'] ?? false;
			$block_settings       = new BlockTemplateSettings( $template_id, $show_other_templates );
		}

		$fields       = $this->_product->get_translated_fields_for_product( $product_data, false, $block_settings );
		$fields       = apply_filters( 'flexible_product_fields_apply_logic_rules', $fields, $post_data );
		$fields_types = $this->_product_fields->get_field_types_by_type();

		foreach ( $fields['fields'] as $field ) {
			if ( ! isset( $fields_types[ $field['type'] ] ) ) {
				continue;
			}

			$field_type   = $fields_types[ $field['type'] ];
			$field_values = $field_type['type_object']->get_field_value( $field['id'] );

			try {
				do_action( 'flexible_product_fields/validate_field', $field, $field_values, $field_type );
			} catch ( \Exception $e ) {
				throw new \Exception( $e->getMessage() );
			}

			if ( ! is_array( $field_values ) ) {
				$field_values = [ $field_values ];
			}

			foreach ( $field_values as $field_value ) {
				$data = $this->get_field_data( $field, $field_type, $field_value, $product_id, $variation_id );
				if ( $data ) {
					if ( ! isset( $cart_item_data['flexible_product_fields'] ) ) {
						$cart_item_data['flexible_product_fields'] = [];
					}
					$cart_item_data['flexible_product_fields'][] = $data;
				}
			}
		}

		if ( isset( $cart_item_data['flexible_product_fields'] ) ) {
			$cart_item_data['flexible_product_fields_product_id'] = $post_data['_fpf_product_id'];
		}

		return $cart_item_data;
	}

	/**
	 * @param array<string, mixed> $field
	 */
	private function is_reservation_field( array $field ): bool {
		if ( ! isset( $field['type'] ) ) {
			return false;
		}

		$field_type = (string) $field['type'];

		$daily_capacity = (int) ( $field['daily_capacity'] ?? 0 );
		if ( 'fpfdate' === $field_type && $daily_capacity === 0 ) {
			return false;
		}

		if ( ! in_array( $field_type, [ 'fpfdate', 'time_slots' ], true ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Add fee to cart.
	 */
	private function add_fee( string $fee_title, float $fee ): void {
		add_action(
			'woocommerce_cart_calculate_fees',
			function ( $cart ) use ( $fee_title, $fee ) {
				if ( $cart->is_empty() ) {
					return;
				}
				$cart->add_fee( $fee_title, $fee );
			}
		);
	}

	/**
	 * @param string|null|DateDTOInterface $value Value of field.
	 */
	private function is_field_value_empty( $value ): bool {
		if ( $value === null ) {
			return true;
		}

		if ( $value === '' ) {
			return true;
		}

		if ( $value instanceof DateDTOInterface && $value->is_empty() ) {
			return true;
		}

		return false;
	}
}
