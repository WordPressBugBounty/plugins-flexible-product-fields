<?php
namespace WPDesk\FPF\Free\Helper;

/**
 * A helper class for date field attributes.
 */
class CalendarAttributeHelper {

	/**
	 * Get date-min Calender attribute. This attribute defines min
	 * active date to be available to be set in the calendar. A positive number in $days_before
	 * variable, defines number of days before current date. A negative number in
	 * $days_before variable, defines number of days after current date.
	 *
	 * @param mixed  $days_before
	 * @param string $date_format
	 * @param array<string,mixed> $field
	 *
	 * @return string
	 */
	public static function get_date_min( $days_before, string $date_format, array $field ) {
		$date_min = ( $days_before !== '' )
			? gmdate(
				$date_format,
				strtotime(
					sprintf(
						'%s %s%d day',
						\wp_date( 'Y-m-d H:i:s' ),
						(int) $days_before < 0 ? '+' : '-',
						\absint( $days_before )
					)
				)
			)
			: '';

		/**
		 * Filter min caledar date
		 *
		 * @param string $date_min
		 * @param array<string,mixed> $field
		 * @param string $date_format
		 *
		 * @return string
		 */
		return \apply_filters( 'flexible_product_fields/field_args/date_min', $date_min, $field, $date_format );
	}

	/**
	 * Get date-max Calender attribute. This attribute defines max
	 * active date to be available to be set in the calendar.
	 *
	 * @param mixed  $days_after
	 * @param string $date_format
	 * @param array<string,mixed> $field
	 *
	 * @return string
	 */
	public static function get_date_max( $days_after, string $date_format, array $field ) {
		$date_max = ( $days_after !== '' )
			? gmdate(
				$date_format,
				strtotime(
					sprintf(
						'%s +%s day',
						\wp_date( 'Y-m-d H:i:s' ),
						$days_after
					)
				)
			)
			: '';

		/**
		 * Filter max calendar date
		 *
		 * @param string $date_max
		 * @param array<string,mixed> $field
		 * @param string $date_format
		 *
		 * @return string
		 */
		return \apply_filters( 'flexible_product_fields/field_args/date_max', $date_max, $field, $date_format );
	}
}
