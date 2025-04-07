<?php
/**
 * Product price.
 *
 * @package Flexible Product Fields
 */
use WPDesk\FPF\Free\Helper\PluginHelper;

/**
 * Product price calculation and format.
 */
class FPF_Product_Price {

	/**
	 * Get price in formatted by WooCommerce.
	 *
	 * @param float $price .
	 *
	 * @return string
	 */
	public function wc_price( $price ) {
		$price = wc_price( $price );
		$price = strip_tags( $price );
		return $price;
	}

	/**
	 * Calculate price.
	 *
	 * @param float      $price_or_percent Price or percent (percent, if price type is percent).
	 * @param string     $price_type Price type.
	 * @param WC_Product $product Product.
	 * @param string     $calculation_type Calculation type.
	 * @param float      $product_price Product price.
	 * @param float      $measurement Measurement.
	 *
	 * @return float
	 */
	public function calculate_price( $price_or_percent, $price_type, WC_Product $product, string $calculation_type = '', float $product_price = 0, float $measurement = 1 ) {
		$sign             = ( $price_or_percent < 0 ) ? -1 : 1;
		$price_or_percent = abs( $price_or_percent );
		$product_price    = (int) $product_price === 0 ? (float) $product->get_price( 'edit' ) : $product_price;

		if ( $calculation_type === 'per_measurement' ) {
			$price = $this->calculate_price_per_measurement( $price_or_percent, $price_type, $product_price, $measurement );
		} else {
			$price = $this->calculate_price_per_item( $price_or_percent, $price_type, $product_price );
		}

		$price = round( $price, wc_get_price_decimals() );

		$price = $this->multicurrency_calculate_price( $price );

		/**
		 * Can modify calculated price.
		 *
		 * @param float      $price Calculated price.
		 * @param WC_Product $product Processed product.
		 *
		 * @return float
		 */
		$price = apply_filters( 'flexible_product_fields_calculate_price', $price, $product );

		$price = $sign * $price;

		return $price;
	}

	private function calculate_price_per_measurement( float $field_price, string $field_price_type, float $product_price, float $measurement ): float {
		return $field_price_type === 'percent'
			? ( $product_price * $field_price ) / 100
			: $field_price * $measurement;
	}

	private function calculate_price_per_item( float $field_price, string $field_price_type, float $product_price ): float {
		return $field_price_type === 'percent'
			? ( $product_price * $field_price ) / 100
			: $field_price;
	}

	/**
	 * Pass calculated price to multi currency plugins.
	 * Returns converted value for changed currency (if changed).
	 * Currently supported multi currency plugins:
	 *   - WPML
	 *
	 * @param float $price .
	 *
	 * @return float
	 */
	private function multicurrency_calculate_price( $price ) {
		return apply_filters( 'wcml_raw_price_amount', $price );
	}

	/**
	 * Pass calculated price to multi currency plugins.
	 * Returns converted value to display for changed currency (if changed).
	 * Currently supported multi currency plugins:
	 *   - Multi Currency for WooCommerce
	 *   - Currency Switcher for WooCommerce
	 *   - WooCommerce Currency Switcher (WOOCS)
	 *
	 * @param float $price .
	 *
	 * @return float
	 */
	public function multicurrency_calculate_price_to_display( $price ) {
		$new_price = $price;
		if ( function_exists( 'wmc_get_price' ) ) {
			$new_price = wmc_get_price( $new_price );
		}
		if ( function_exists( 'alg_get_current_currency_code' ) && function_exists( 'alg_get_product_price_by_currency' ) ) {
			$new_price = alg_get_product_price_by_currency( $new_price, alg_get_current_currency_code() );
		}
		$new_price = apply_filters( 'woocs_convert_price', $new_price );
		return $new_price;
	}

	/**
	 * Check if pricing settings are valid.
	 *
	 * @param string $price_type Price type.
	 * @param string $price Price value.
	 * @param string $calculation_type Calculation type.
	 *
	 * @return bool
	 */
	public function is_valid_pricing_settings( string $price_type, string $price, string $calculation_type ): bool {
		if ( $price_type === '' || ! is_numeric( $price ) ) {
			return false;
		}
		
		if ( $calculation_type === 'per_measurement' && ! PluginHelper::is_flexible_quantity_active() ) {
			return false;
		}

		return true;
	}

	/**
	 * Prepare price to display.
	 *
	 * @param WC_Product $product .
	 * @param float      $price_to_display .
	 *
	 * @return float|string
	 */
	public function prepare_price_to_display( WC_Product $product, $price_to_display ) {
		$price_sign       = $price_to_display < 0 ? -1 : 1;
		$price_to_display = $this->multicurrency_calculate_price_to_display( abs( $price_to_display ) );
		$tax_display_mode = get_option( 'woocommerce_tax_display_shop' );
		if ( 'excl' === $tax_display_mode ) {
			$price_to_display = wpdesk_get_price_excluding_tax( $product, 1, $price_to_display );
		} else {
			$price_to_display = wpdesk_get_price_including_tax( $product, 1, $price_to_display );
		}
		return $price_sign * $price_to_display;
	}
}
