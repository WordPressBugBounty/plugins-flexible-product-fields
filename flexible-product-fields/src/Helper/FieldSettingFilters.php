<?php

namespace WPDesk\FPF\Free\Helper;

class FieldSettingFilters {

	/**
	 * Returns the value of the "Today Max Hour" field setting.
	 *
	 * @param array<string, mixed> $field_settings Field settings.
	 *
	 * @return string
	 */
	public static function get_today_max_hour( array $field_settings ): string {
		static $today_max_hour;
		if ( null !== $today_max_hour ) {
			return $today_max_hour;
		}

		/**
		 * Filters the value of a field setting.
		 *
		 * @param string $today_max_hour The value of the field setting.
		 * @param array<string, mixed> $field_settings The field settings.
		 */
		$today_max_hour = apply_filters(
			'flexible_product_fields/field_setting_today_max_hour',
			$field_settings['today_max_hour'] ?? '',
			$field_settings
		);

		$today_max_hour = is_string( $today_max_hour ) ? $today_max_hour : '';

		return $today_max_hour;
	}
}
