<?php

namespace WPDesk\FPF\Free\Service;

use FPF_Product_Price;

class BookingCartFormatter {

	/**
	 * @var FPF_Product_Price
	 */
	private $product_price;

	public function __construct( FPF_Product_Price $product_price ) {
		$this->product_price = $product_price;
	}

	/**
	 * Formats the booking data for cart display based on the field type.
	 *
	 * @param array<string, mixed> $field_data Raw field data from the cart item.
	 * @param float                $unit_cost_base The cost of one extra unit in base currency.
	 *
	 * @return string HTML formatted string.
	 */
	public function format( array $field_data, float $unit_cost_base ): string {
		$booking_data = $field_data['booking_data'] ?? [];
		$field_type   = $field_data['type'] ?? '';

		if ( empty( $booking_data ) ) {
			return '';
		}

		switch ( $field_type ) {
			case 'date_range':
				$booking_units = (int) ( $field_data['booking_units'] ?? 1 );
				return $this->format_as_range( $booking_data, $unit_cost_base, $booking_units );

			case 'time_slots':
				$label_formatter = function ( $item ): string {
					if ( ! is_array( $item ) ) {
						return '';
					}
					$date       = $item['date'] ?? '';
					$start_time = $item['start_time'] ?? '';
					$end_time   = $item['end_time'] ?? '';
					return sprintf( '%s (%s-%s)', $date, $start_time, $end_time );
				};

				return $this->format_as_list( $booking_data, $unit_cost_base, $label_formatter );

			case 'date':
				return $this->format_as_list(
					$booking_data,
					$unit_cost_base,
					fn( $item ) => (string) $item
				);

			default:
				return '';
		}
	}

	/**
	 * Formats booking data as a list (for time slots and multiple dates).
	 *
	 * @param array<int, mixed> $booking_data
	 */
	private function format_as_list( array $booking_data, float $unit_cost_base, callable $label_formatter ): string {
		$unit_cost_display    = (float) $this->product_price->multicurrency_calculate_price_to_display( $unit_cost_base );
		$formatted_unit_price = $this->product_price->wc_price( $unit_cost_display );

		$formatted_parts = [];
		foreach ( $booking_data as $index => $item ) {
			$label = call_user_func( $label_formatter, $item );

			if ( $index === 0 ) {
				$suffix = ' (' . __( 'Included', 'flexible-product-fields' ) . ')';
			} else {
				$suffix = ' (+' . $formatted_unit_price . ')';
			}
			$formatted_parts[] = $label . $suffix;
		}

		return implode( ' & ', $formatted_parts );
	}

	/**
	 * Formats booking data as a range summary.
	 *
	 * @param array<int, mixed> $booking_data
	 */
	private function format_as_range( array $booking_data, float $unit_cost, int $booking_units ): string {
		$start_date = $booking_data[0] ?? '';
		$end_date   = $booking_data[1] ?? '';

		if ( empty( $start_date ) || empty( $end_date ) ) {
			return '';
		}

		// One booking unit is priced in, no need to display extra costs.
		if ( $booking_units <= 1 ) {
			return '';
		}

		// Calculate price for addittional units.
		$extra_units              = $booking_units - 1;
		$total_extra_cost         = $extra_units * $unit_cost;
		$total_extra_cost_display = (float) $this->product_price->multicurrency_calculate_price_to_display( $total_extra_cost );
		$formatted_total_extra    = $this->product_price->wc_price( $total_extra_cost_display );

		$range_string = sprintf( '%s - %s', $start_date, $end_date );

		if ( $total_extra_cost > 0 ) {
			return sprintf( '%s (+%s) (%s)', $range_string, $extra_units, $formatted_total_extra );
		}

		return sprintf( '%s (%s)', $range_string, $extra_units );
	}
}
