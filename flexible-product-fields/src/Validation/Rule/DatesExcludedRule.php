<?php

namespace WPDesk\FPF\Free\Validation\Rule;

use WPDesk\FPF\Free\Helper\DateFormatConverter;
use WPDesk\FPF\Free\DTO\DateDTOInterface;

/**
 * Supports "Excluded dates" validation rule for fields.
 */
class DatesExcludedRule implements RuleInterface {

	/**
	 * {@inheritdoc}
	 */
	public function validate_value( array $field_data, array $field_type, $value ): bool {
		if ( ! $value instanceof DateDTOInterface ) {
			return true;
		}

		if ( ! ( $field_type['has_dates_excluded'] ?? false ) ) {
			return true;
		}

		$dates_excluded = $field_data['dates_excluded'] ?? '';
		if ( ! $dates_excluded ) {
			return true;
		}

		$dates_excluded = explode( ',', $dates_excluded );
		$excluded_dates = [];
		foreach ( $dates_excluded as $date ) {
			$excluded_dates[] = gmdate( 'Y-m-d', strtotime( $date ) );
		}

		$date_format = DateFormatConverter::to_php( $field_data['date_format'] ?? '' );

		foreach ( $value as $date ) {
			$datetime = \DateTime::createFromFormat( $date_format, $date );
			if ( $datetime === false ) {
				return false;
			}
			if ( in_array( $datetime->format( 'Y-m-d' ), $excluded_dates, true ) ) {
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
			__( '%s has an excluded date.', 'flexible-product-fields' ),
			'<strong>' . $field_data['title'] . '</strong>'
		);
	}
}
