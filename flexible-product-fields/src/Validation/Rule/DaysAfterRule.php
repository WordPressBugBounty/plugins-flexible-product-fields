<?php

namespace WPDesk\FPF\Free\Validation\Rule;

use WPDesk\FPF\Free\Helper\CalendarAttributeHelper;

/**
 * Supports "Days after" validation rule for fields.
 */
class DaysAfterRule implements RuleInterface {

	private const DATE_FORMAT = 'Ymd';

	/**
	 * {@inheritdoc}
	 */
	public function validate_value( array $field_data, array $field_type, $value ): bool {
		if ( ! ( $field_type['has_days_after'] ?? false ) ) {
			return true;
		}

		$days_after = $field_data['days_after'] ?? '';
		$date_max = CalendarAttributeHelper::get_date_max( $days_after, self::DATE_FORMAT, $field_data );
		if ( $date_max === '' ) {
			return true;
		}

		$dates    = ( $value ) ? explode( ',', $value ) : [];
		foreach ( $dates as $date ) {
			if ( gmdate( self::DATE_FORMAT, strtotime( $date ) ) > $date_max ) {
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
