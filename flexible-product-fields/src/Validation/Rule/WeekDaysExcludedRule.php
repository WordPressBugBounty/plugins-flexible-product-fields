<?php

namespace WPDesk\FPF\Free\Validation\Rule;

use WPDesk\FPF\Free\Helper\DateFormatConverter;
use WPDesk\FPF\Free\DTO\DateDTOInterface;

/**
 * Supports "Excluded days of week" validation rule for fields.
 */
class WeekDaysExcludedRule implements RuleInterface {

	/**
	 * {@inheritdoc}
	 */
	public function validate_value( array $field_data, array $field_type, $value ): bool {
		if ( ! $value instanceof DateDTOInterface ) {
			return true;
		}

		if ( ! ( $field_type['has_days_excluded'] ?? false ) ) {
			return true;
		}

		$days_excluded = $field_data['days_excluded'] ?? [];
		if ( ! $days_excluded ) {
			return true;
		}

		$date_format = $field_data['date_format'] ?? DateFormatConverter::DEFAULT_DATE_FORMAT;
		$date_format = DateFormatConverter::to_php( $date_format );

		foreach ( $value as $date ) {
			$datetime = \DateTime::createFromFormat( $date_format, $date );
			if ( $datetime === false || in_array( $datetime->format( 'w' ), $days_excluded, false ) ) {
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
			__( '%s has a date with an excluded day of the week.', 'flexible-product-fields' ),
			'<strong>' . $field_data['title'] . '</strong>'
		);
	}
}
