<?php

namespace WPDesk\FPF\Free\Field\Type;

use WPDesk\FPF\Free\Field\Types;
use WPDesk\FPF\Free\Settings\Option\DisableProductImageChangeOption;
use WPDesk\FPF\Free\Settings\Option\CssOption;
use WPDesk\FPF\Free\Settings\Option\DefaultOption;
use WPDesk\FPF\Free\Settings\Option\FieldLabelOption;
use WPDesk\FPF\Free\Settings\Option\FieldNameOption;
use WPDesk\FPF\Free\Settings\Option\FieldPriorityOption;
use WPDesk\FPF\Free\Settings\Option\FieldTypeOption;
use WPDesk\FPF\Free\Settings\Option\LogicAdvOption;
use WPDesk\FPF\Free\Settings\Option\OptionsImageOption;
use WPDesk\FPF\Free\Settings\Option\OptionsOption;
use WPDesk\FPF\Free\Settings\Option\OptionsValueOption;
use WPDesk\FPF\Free\Settings\Option\OptionsWithImageOption;
use WPDesk\FPF\Free\Settings\Option\PreviewLabelOption;
use WPDesk\FPF\Free\Settings\Option\PreviewWidthOption;
use WPDesk\FPF\Free\Settings\Option\PricingAdvOption;
use WPDesk\FPF\Free\Settings\Option\RequiredOption;
use WPDesk\FPF\Free\Settings\Option\TooltipOption;
use WPDesk\FPF\Free\Settings\Tab\AdvancedTab;
use WPDesk\FPF\Free\Settings\Tab\GeneralTab;
use WPDesk\FPF\Free\Settings\Tab\LogicTab;
use WPDesk\FPF\Free\Settings\Tab\PricingTab;

/**
 * Supports "Radio with images" field type.
 */
class RadioImagesType extends TypeAbstract {

	const FIELD_TYPE = 'radio-images';

	/**
	 * {@inheritdoc}
	 */
	public function get_field_type(): string {
		return self::FIELD_TYPE;
	}

	/**
	 * Returns value of field type used in HTML.
	 *
	 * @return string Field type.
	 */
	public function get_raw_field_type(): string {
		return RadioType::FIELD_TYPE;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_field_type_label(): string {
		return __( 'Radio with images', 'flexible-product-fields' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_field_group(): string {
		return Types::FIELD_GROUP_OPTION;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_field_type_icon(): string {
		return 'icon-images';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_options_objects(): array {
		return [
			GeneralTab::TAB_NAME  => [
				FieldPriorityOption::FIELD_NAME    => new FieldPriorityOption(),
				FieldTypeOption::FIELD_NAME        => new FieldTypeOption(),
				FieldLabelOption::FIELD_NAME       => new FieldLabelOption(),
				RequiredOption::FIELD_NAME         => new RequiredOption(),
				CssOption::FIELD_NAME              => new CssOption(),
				TooltipOption::FIELD_NAME          => new TooltipOption(),
				DisableProductImageChangeOption::FIELD_NAME => new DisableProductImageChangeOption(),
				OptionsWithImageOption::FIELD_NAME => new OptionsWithImageOption(),
				DefaultOption::FIELD_NAME          => new DefaultOption(),
				FieldNameOption::FIELD_NAME        => new FieldNameOption(),
			],
			AdvancedTab::TAB_NAME => [
				PreviewWidthOption::FIELD_NAME => new PreviewWidthOption(),
				PreviewLabelOption::FIELD_NAME => new PreviewLabelOption(),
			],
			PricingTab::TAB_NAME  => [
				PricingAdvOption::FIELD_NAME => new PricingAdvOption(),
			],
			LogicTab::TAB_NAME    => [
				LogicAdvOption::FIELD_NAME => new LogicAdvOption(),
			],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_field_template_vars( array $field_data ): array {
		$template_vars = parent::get_field_template_vars( $field_data );

		$media_ids           = [];
		$options_image_props = [];
		foreach ( $field_data[ OptionsOption::FIELD_NAME ] as $option ) {
			$image_id = $option[ OptionsImageOption::FIELD_NAME ];
			$media_ids[ $option[ OptionsValueOption::FIELD_NAME ] ] = $image_id;

			if ( $this->is_product_image_change_disabled( $field_data, $option ) ) {
				continue;
			}

			$image_props             = \wc_get_product_attachment_props( $image_id );
			$image_props['image_id'] = $image_id;

			$options_image_props[ $option[ OptionsValueOption::FIELD_NAME ] ] = $image_props;
		}

		$template_vars['media_ids']           = $media_ids;
		$template_vars['preview_width']       = $field_data[ PreviewWidthOption::FIELD_NAME ] ?? 0;
		$template_vars['preview_show_label']  = ! (bool) ( $field_data[ PreviewLabelOption::FIELD_NAME ] ?? false );
		$template_vars['options_image_props'] = $options_image_props;

		return $template_vars;
	}

	/**
	 * Check if product image change is disabled.
	 *
	 * @param array<string, mixed> $field_data Field data.
	 * @param array<string, mixed> $option     Option data.
	 *
	 * @return bool
	 */
	private function is_product_image_change_disabled( array $field_data, array $option ): bool {
		$disable_product_image_change = $field_data['disable_product_image_change'] ?? false;

		// Setting option has a higher priority, if this is set to true no filter would work.
		if ( filter_var( $disable_product_image_change, FILTER_VALIDATE_BOOLEAN ) === true ) {
			return true;
		}

		/**
		 * Filter to disable product image change.
		 *
		 * @since 2.5.1
		 *
		 * @param bool $disable_product_image_change
		 * @param array<string, mixed> $field_data
		 * @param array<string, mixed> $option
		 *
		 * @return bool
		 */
		$disable_product_image_change = \apply_filters( 'fpf/radio_with_images/disable_product_image_change', false, $field_data, $option );
		if ( filter_var( $disable_product_image_change, FILTER_VALIDATE_BOOLEAN ) === true ) {
			return true;
		}

		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_available(): bool {
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function has_required(): bool {
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function has_css_class(): bool {
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function has_tooltip(): bool {
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function has_options(): bool {
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function has_price_info_in_options(): bool {
		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function has_logic_info(): bool {
		return true;
	}
}
