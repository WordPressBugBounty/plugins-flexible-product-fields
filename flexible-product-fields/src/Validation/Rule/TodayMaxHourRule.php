<?php

namespace WPDesk\FPF\Free\Validation\Rule;

use WPDesk\FPF\Free\Helper\DateFormatConverter;
use WPDesk\FPF\Free\DTO\DateDTOInterface;
use WPDesk\FPF\Free\Helper\FieldSettingFilters;

/**
 * Supports "Time of day closing" validation rule for fields.
 */
class TodayMaxHourRule implements RuleInterface {

	/**
	 * {@inheritdoc}
	 */
	public function validate_value( array $field_data, array $field_type, $value ): bool {
		if ( ! $value instanceof DateDTOInterface ) {
			return true;
		}

		if ( ! ( $field_type['has_today_max_hour'] ?? false ) ) {
			return true;
		}

		$max_hour = FieldSettingFilters::get_today_max_hour( $field_data );
		if ( $max_hour === '' ) {
			return true;
		}
		$max_hour_datetime = \DateTime::createFromFormat( 'H:i', $max_hour );
		if ( $max_hour_datetime === false || wp_date( 'H:i' ) <= $max_hour_datetime->format( 'H:i' ) ) {
			return true;
		}

		$date_today = wp_date( 'Ymd' );

		$date_format = $field_data['date_format'] ?? DateFormatConverter::DEFAULT_DATE_FORMAT;
		$date_format = DateFormatConverter::to_php( $date_format );

		foreach ( $value as $date ) {
			$datetime = \DateTime::createFromFormat( $date_format, $date );
			if ( $datetime === false || $datetime->format( 'Ymd' ) === $date_today ) {
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
			__( '%s has a current date that is no longer available.', 'flexible-product-fields' ),
			'<strong>' . $field_data['title'] . '</strong>'
		);
	}
}
