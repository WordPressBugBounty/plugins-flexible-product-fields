<?php

namespace WPDesk\FPF\Free\Field;

use WPDesk\FPF\Free\Field\Type\RadioType;
use WPDesk\FPF\Free\Field\Type\TypeInterface;
use WPDesk\FPF\Free\Field\Type\RadioColorsType;
use WPDesk\FPF\Free\Field\Type\RadioImagesType;
use WPDesk\FPF\Free\Field\Type\MultiCheckboxType;
use WPDesk\FPF\Free\Field\Type\MultiImagesType;
use WPDesk\FPF\Free\Settings\Option\DefaultOption;
use WPDesk\FPF\Free\Helper\CalendarAttributeHelper;
use WPDesk\FPF\Free\Settings\Option\MinuteStepOption;
use WPDesk\FPF\Free\Settings\Option\Hour12ClockOption;
use WPDesk\FPF\Free\Helper\DateFormatConverter;
use WPDesk\FPF\Free\Helper\FieldSettingFilters;

/**
 * Generates args of fields for fields generation.
 */
class TemplateArgs {

	/**
	 * Returns args for field based on field settings.
	 *
	 * @param array         $settings      Field settings.
	 * @param TypeInterface $type_object   .
	 * @param array         $field         Field settings.
	 * @param object        $product_price Class to handle pricing.
	 * @param \WC_Product   $product       Product Class.
	 *
	 * @return array Field args.
	 */
	public function parse_field_args( array $settings, TypeInterface $type_object, array $field, $product_price, \WC_Product $product ): array {
		$args = [
			'type'              => $type_object->get_raw_field_type(),
			'label'             => $field['title'],
			'placeholder'       => '',
			'input_class'       => [
				'fpf-input-field',
			],
			'custom_attributes' => [],
			'fpf_atts'          => [],
			'default'           => $field[ DefaultOption::FIELD_NAME ] ?? '',
		];

		if ( $settings['has_tooltip'] && ( $tooltip = ( $field['tooltip'] ?? '' ) ) ) {
			$args['label'] = sprintf(
				'<span class="fpf-field-tooltip" title="%1$s">%2$s <span class="fpf-field-tooltip-icon"></span></span>',
				esc_attr( $tooltip ),
				esc_html( $args['label'] )
			);
		}
		if ( $settings['has_required'] && isset( $field['required'] ) && ( $field['required'] == '1' ) ) {
			$args['label'] .= sprintf(
				' <abbr class="required" title="%s">*</abbr>',
				esc_attr__( 'Required field', 'flexible-product-fields' )
			);
		}

		if ( $settings['has_max_length'] && ( $max_length = ( $field['max_length'] ?? '' ) ) ) {
			$args['custom_attributes']['maxlength'] = $max_length;
		}
		if ( $settings['has_placeholder'] && ( ( $placeholder = ( $field['placeholder'] ?? '' ) ) || ( $placeholder !== '' ) ) ) {
			$args['placeholder'] = $placeholder;
		}
		if ( $settings['has_value_min'] && ( ( $value_min = ( $field['value_min'] ?? '' ) ) || ( $value_min !== '' ) ) ) {
			$args['custom_attributes']['min'] = $value_min;
		}
		if ( $settings['has_value_max'] && ( ( $value_max = ( $field['value_max'] ?? '' ) ) || ( $value_max !== '' ) ) ) {
			$args['custom_attributes']['max'] = $value_max;
		}
		if ( $settings['has_value_step'] && ( $value_step = ( $field['value_step'] ?? '' ) ) ) {
			$args['custom_attributes']['step'] = $value_step;
		}
		if ( $settings['has_options'] && ( $options = ( $field['options'] ?? [] ) ) && is_array( $options ) ) {
			$args['options'] = [];
			if ( $settings['has_placeholder'] && ( ( $placeholder = ( $field['placeholder'] ?? '' ) ) || ( $placeholder !== '' ) ) ) {
				$args['placeholder'] = '';
				$args['options'][''] = $placeholder;
			}

			foreach ( $options as $option ) {
				$args['options'][ $option['value'] ] = $option['label'];
			}
		}

		$date_format     = $field['date_format'] ?? DateFormatConverter::DEFAULT_DATE_FORMAT;
		$date_format_php = DateFormatConverter::to_php( $date_format );
		if ( $settings['has_date_format'] ) {
			$args['custom_attributes']['date_format']          = $date_format;
			$args['custom_attributes']['data-date-format']     = DateFormatConverter::to_js_lib( $date_format );
			$args['custom_attributes']['data-dates-delimiter'] = DateFormatConverter::get_dates_delimiter();
		}
		if ( $settings['has_days_before'] ) {
			$days_before = $field['days_before'] ?? '';

			$args['custom_attributes']['days_before']   = ( $days_before == '0' ) ? '00' : $days_before;
			$args['custom_attributes']['data-date-min'] = CalendarAttributeHelper::get_date_min( $days_before, $date_format_php, $field );
		}
		if ( $settings['has_days_after'] ) {
			$days_after = $field['days_after'] ?? '';

			$args['custom_attributes']['days_after']    = ( $days_after == '0' ) ? '00' : $days_after;
			$args['custom_attributes']['data-date-max'] = CalendarAttributeHelper::get_date_max( $days_after, $date_format_php, $field );
		}
		if ( $settings['has_dates_excluded'] ) {
			$dates = array_filter( explode( ',', $field['dates_excluded'] ?? '' ) );
			foreach ( $dates as $date_index => $date ) {
				$dates[ $date_index ] = gmdate( $date_format_php, strtotime( $date ) );
			}
			if ( $settings['has_today_max_hour'] && ( $max_hour = FieldSettingFilters::get_today_max_hour( $field ) )
				&& ( wp_date( 'H:i' ) > gmdate( 'H:i', strtotime( $max_hour ) ) ) ) {
				$dates[] = gmdate( $date_format_php, strtotime( wp_date( 'Y-m-d' ) ) );
			}

			$dates = apply_filters( 'flexible_product_fields/field_args/dates_excluded', $dates, $field, $date_format_php );
			$args['custom_attributes']['data-dates-disabled'] = implode( ',', $dates );
		}
		if ( $settings['has_today_max_hour'] ) {
			$args['custom_attributes']['data-today-max-hour'] = $field['today_max_hour'] ?? '';
		}
		if ( $settings['has_days_excluded'] ) {
			$args['custom_attributes']['data-days-disabled'] = implode( ',', $field['days_excluded'] ?? [] );
		}
		if ( $settings['has_week_start'] ) {
			$args['custom_attributes']['data-week-start'] = $field['week_start'] ?? '';
		}
		if ( $settings['has_max_dates'] ) {
			$args['custom_attributes']['data-max-dates'] = $field['max_dates'] ?? '';
		}
		if ( $field[ MinuteStepOption::FIELD_NAME ] ?? false ) {
			$args['custom_attributes']['data-minute-step'] = $field[ MinuteStepOption::FIELD_NAME ];
		}
		if ( $field[ Hour12ClockOption::FIELD_NAME ] ?? false ) {
			$args['custom_attributes']['data-hour-12'] = 1;
		}

		// Pass date range selection setting to frontend
		if ( isset( $field['date_range_selection'] ) ) {
			$args['custom_attributes']['data-date-range-selection'] = $field['date_range_selection'];
		}

		if ( filter_var( $settings['has_price'], FILTER_VALIDATE_BOOLEAN ) ) {
			$price_value      = $field['price'] ?? '';
			$price_type       = $field['price_type'] ?? 'fixed';
			$calculation_type = $field['calculation_type'] ?? '';

			if ( $product_price->is_valid_pricing_settings( $price_type, $price_value, $calculation_type ) ) {
				$price_raw      = $this->get_raw_price_for_label( $price_type, $price_value, $product_price, $product );
				$args['label'] .= sprintf(
					' <span id="%1$s_price">%2$s</span>',
					$field['id'],
					sprintf(
						apply_filters( 'flexible_product_fields/field_args/label_price', '(%s)', $price_raw, $field ),
						$this->get_price_for_label( $price_type, $price_value, $product_price, $product )
					)
				);
			}
		}

		if ( filter_var( $settings['has_price_in_options'], FILTER_VALIDATE_BOOLEAN ) && ( $args['options'] ?? [] ) ) {
			$price_values = $field['price_values'] ?? [];

			foreach ( $args['options'] as $option_value => $option_label ) {
				$option_data      = $this->get_option_data( $field['options'], $option_value );
				$price_type       = $price_values[ $option_value ]['price_type'] ?? ( $option_data['price_type'] ?? '' );
				$price_value      = $price_values[ $option_value ]['price'] ?? ( $option_data['price'] ?? '' );
				$calculation_type = $price_values[ $option_value ]['calculation_type'] ?? ( $option_data['calculation_type'] ?? '' );
				if ( ! $option_data || ! $product_price->is_valid_pricing_settings( $price_type, $price_value, $calculation_type ) ) {
					continue;
				}

				$price_raw   = $this->get_raw_price_for_label( $price_type, $price_value, $product_price, $product );
				$price_label = $this->get_price_for_label( $price_type, $price_value, $product_price, $product );

				if ( in_array(
					$field['type'],
					[ RadioType::FIELD_TYPE, MultiCheckboxType::FIELD_TYPE, RadioImagesType::FIELD_TYPE, RadioColorsType::FIELD_TYPE, MultiImagesType::FIELD_TYPE ]
				) ) {
					$args['options'][ $option_value ] .= sprintf(
						' <span id="%1$s_%2$s_price">%3$s</span>',
						$field['id'],
						$option_value,
						sprintf(
							apply_filters( 'flexible_product_fields/field_args/label_price', '(%s)', $price_raw, $field ),
							$price_label
						)
					);
				} else {
					$args['options'][ $option_value ] .= sprintf(
						' %1$s',
						sprintf(
							apply_filters( 'flexible_product_fields/field_args/label_price', '(%s)', $price_raw, $field ),
							$price_label
						)
					);
				}
			}
		}

		return $type_object->get_field_args( $args );
	}

	/**
	 * @deprecated Use DateFormatConverter::to_js_lib() instead.
	 */
	public static function convert_date_format_for_js( string $date_format ): string {
		return DateFormatConverter::to_js_lib( $date_format );
	}

	/**
	 * @deprecated Use DateFormatConverter::to_php() instead.
	 */
	public static function convert_date_format_for_php( string $date_format ): string {
		return DateFormatConverter::to_php( $date_format );
	}

	/**
	 * Returns selected option from options list.
	 *
	 * @param array  $options      Field settings.
	 * @param string $option_value Option value.
	 *
	 * @return array|null Data of option, if exists.
	 */
	private function get_option_data( array $options, string $option_value ) {
		foreach ( $options as $option ) {
			if ( $option['value'] == $option_value ) {
				return $option;
			}
		}
		return null;
	}

	/**
	 * Returns raw price value for field label.
	 *
	 * @param string      $price_type    Type of price (fixed, percent).
	 * @param mixed       $price_value   Value of price.
	 * @param object      $product_price Class to handle pricing.
	 * @param \WC_Product $product       Product Class.
	 *
	 * @return float Price value.
	 */
	private function get_raw_price_for_label( string $price_type, $price_value, $product_price, \WC_Product $product ): float {
		return $product_price->calculate_price( floatval( $price_value ), $price_type, $product );
	}

	/**
	 * Returns price value for field label.
	 *
	 * @param string      $price_type    Type of price (fixed, percent).
	 * @param mixed       $price_value   Value of price.
	 * @param object      $product_price Class to handle pricing.
	 * @param \WC_Product $product       Product Class.
	 *
	 * @return string Formatted price value.
	 */
	private function get_price_for_label( string $price_type, $price_value, $product_price, \WC_Product $product ): string {
		$price_raw     = $this->get_raw_price_for_label( $price_type, $price_value, $product_price, $product );
		$price_display = $product_price->prepare_price_to_display( $product, $price_raw );
		return $product_price->wc_price( $price_display );
	}
}
