<?php

namespace WPDesk\FPF\Free\Validation\Rule;

use WPDesk\FPF\Free\Helper\DateFormatConverter;
use WPDesk\FPF\Free\DTO\DateDTOInterface;

/**
 * Supports "Date format" validation rule for fields.
 */
class DateFormatRule implements RuleInterface {

	/**
	 * {@inheritdoc}
	 */
	public function validate_value( array $field_data, array $field_type, $value ): bool {
		if ( ! $value instanceof DateDTOInterface ) {
			return true;
		}

		if ( ! ( $field_type['has_date_format'] ?? false ) ) {
			return true;
		}

		$date_format = $field_data['date_format'] ?? '';
		if ( ! $date_format ) {
			return true;
		}

		$date_format = DateFormatConverter::to_php( $date_format );

		foreach ( $value as $date ) {
			if ( ! $date ) {
				continue;
			}

			$datetime = \DateTime::createFromFormat( $date_format, $date );
			if ( $datetime === false ) {
				return false; // Invalid format or impossible date
			}

			// Additional validation: ensure the parsed date matches the input exactly
			// This catches cases like "2024-02-30" which would be adjusted to "2024-03-01"
			if ( $datetime->format( $date_format ) !== $date ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_error_message( array $field_data ): string {
		return sprintf(
		/* translators: %s: field label */
			__( '%s has an invalid date format.', 'flexible-product-fields' ),
			'<strong>' . $field_data['title'] . '</strong>'
		);
	}
}
