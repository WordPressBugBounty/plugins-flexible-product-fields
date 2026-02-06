<?php

namespace WPDesk\FPF\Free\Validation\Rule;

use WPDesk\FPF\Free\Helper\CalendarAttributeHelper;
use WPDesk\FPF\Free\Helper\DateFormatConverter;
use WPDesk\FPF\Free\DTO\DateDTOInterface;

/**
 * Supports "Days after" validation rule for fields.
 */
class DaysAfterRule implements RuleInterface {

	private const DATE_FORMAT = 'Ymd';

	/**
	 * {@inheritdoc}
	 */
	public function validate_value( array $field_data, array $field_type, $value ): bool {
		if ( ! $value instanceof DateDTOInterface ) {
			return true;
		}

		if ( ! ( $field_type['has_days_after'] ?? false ) ) {
			return true;
		}

		$days_after = $field_data['days_after'] ?? '';
		$date_max   = CalendarAttributeHelper::get_date_max( $days_after, self::DATE_FORMAT, $field_data );
		if ( $date_max === '' ) {
			return true;
		}

		$date_format = DateFormatConverter::to_php( $field_data['date_format'] ?? '' );

		foreach ( $value as $date ) {
			$datetime = \DateTime::createFromFormat( $date_format, $date );
			if ( $datetime === false || $datetime->format( self::DATE_FORMAT ) > $date_max ) {
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
			__( '%s has a date set too late.', 'flexible-product-fields' ),
			'<strong>' . $field_data['title'] . '</strong>'
		);
	}
}
