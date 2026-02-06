<?php

namespace WPDesk\FPF\Free\Helper;

/**
 * Handles conversion of date formats from the plugin's internal JS-like format to other formats.
 */
class DateFormatConverter {

	public const DEFAULT_DATE_FORMAT = 'dd.mm.yy';

	/**
	 * @var array<string, string>
	 */
	private static array $js_like_to_php_map = [
		'dd' => 'd',
		'd'  => 'j',
		'mm' => 'm',
		'm'  => 'n',
		'yy' => 'Y',
		'y'  => 'y',
		'DD' => 'l',
		'D'  => 'D',
		'MM' => 'F',
		'M'  => 'M',
	];

	/**
	 * A map to handle exceptions between the plugin's stored format and the JS library's format.
	 * Our internal format is based on the vanillajs-datepicker format (see link below), but for
	 * historical and backward-compatibility reasons, we handle year formats differently.
	 * - 'yy' was used for 4-digit years, which we now map to the library's explicit 'yyyy'.
	 * - 'y' was used for 2-digit years, which we now force to be 4-digit ('yy') in the library
	 *   for a more consistent user experience.
	 *
	 * @see https://mymth.github.io/vanillajs-datepicker/#/date-string+format
	 *
	 * @var array<string, string>
	 */
	private static array $js_like_to_js_lib_map = [
		'yy' => 'yyyy',
		'y'  => 'yy',
	];

	/**
	 * Converts stored JS-like date format to PHP date format.
	 *
	 * @param string $js_like_format Stored JS-like date format.
	 *
	 * @return string PHP date format.
	 */
	public static function to_php( string $js_like_format ): string {
		return strtr( $js_like_format, self::$js_like_to_php_map );
	}

	/**
	 * Converts stored JS-like date format to a format for the vanillajs-datepicker library.
	 *
	 * @param string $js_like_format Stored JS-like date format.
	 *
	 * @return string JS library date format.
	 */
	public static function to_js_lib( string $js_like_format ): string {
		return strtr( $js_like_format, self::$js_like_to_js_lib_map );
	}

	/**
	 * Returns delimiter for multiple dates in a date field.
	 *
	 * @return string Delimiter character.
	 */
	public static function get_dates_delimiter(): string {
		static $delimiter;

		if ( null !== $delimiter ) {
			return $delimiter;
		}

		/**
		 * Filters the delimiter for multiple dates in a date field.
		 *
		 * @param string $delimiter  The delimiter character. Default ','.
		 *
		 * @param array $field_data This is deprecated and not needed as we support dates delimiters per installation, not per field.
		 */
		return apply_filters( 'fpf_date_field_delimiter', ',', [] );
	}
}
