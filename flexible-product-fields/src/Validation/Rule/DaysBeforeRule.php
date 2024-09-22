<?php

namespace WPDesk\FPF\Free\Validation\Rule;

use WPDesk\FPF\Free\Helper\CalendarAttributeHelper;

/**
 * Supports "Days before" validation rule for fields.
 */
class DaysBeforeRule implements RuleInterface {

	private const DATE_FORMAT = 'Ymd';

	/**
	 * {@inheritdoc}
	 */
	public function validate_value( array $field_data, array $field_type, $value ): bool {
		if ( ! ( $field_type['has_days_before'] ?? false ) ) {
			return true;
		}

		$days_before = $field_data['days_before'] ?? '';
		$date_min = CalendarAttributeHelper::get_date_min( $days_before, self::DATE_FORMAT, $field_data );
		if ( $date_min === '' ) {
			return true;
		}

		$dates    = ( $value ) ? explode( ',', $value ) : [];
		foreach ( $dates as $date ) {
			if ( gmdate( self::DATE_FORMAT, strtotime( $date ) ) < $date_min ) {
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
			__( '%s has a date set too early.', 'flexible-product-fields' ),
			'<strong>' . $field_data['title'] . '</strong>'
		);
	}
}
