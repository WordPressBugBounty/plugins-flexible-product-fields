<?php

namespace WPDesk\FPF\Free\Validation\Rule;

use WPDesk\FPF\Free\Helper\DateFormatConverter;
use WPDesk\FPF\Free\DTO\DateDTOInterface;
use WPDesk\FPF\Free\Service\BookingUnitsCalculator;

/**
 * Supports "Selected days limit" validation rule for fields.
 */
class DaysLimitRule implements RuleInterface {

	/**
	 * {@inheritdoc}
	 */
	public function validate_value( array $field_data, array $field_type, $value ): bool {
		if ( ! $value instanceof DateDTOInterface ) {
			return true;
		}

		if ( ! ( $field_type['has_max_dates'] ?? false ) ) {
			return true;
		}

		$days_limit = $field_data['max_dates'] ?? 0;
		if ( ! $days_limit ) {
			return true;
		}

		$date_format             = $field_data['date_format'] ?? DateFormatConverter::DEFAULT_DATE_FORMAT;
		$date_format             = DateFormatConverter::to_php( $date_format );
		$allow_adjacent_bookings = filter_var( $field_data['allow_adjacent_bookings'] ?? false, FILTER_VALIDATE_BOOLEAN );

		$days = BookingUnitsCalculator::calculate( $value, $date_format, $allow_adjacent_bookings );

		return ( $days <= $days_limit );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_error_message( array $field_data ): string {
		return sprintf(
		/* translators: %s: field label */
			__( '%s has too many dates.', 'flexible-product-fields' ),
			'<strong>' . $field_data['title'] . '</strong>'
		);
	}
}
