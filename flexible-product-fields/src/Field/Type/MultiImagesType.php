<?php

namespace WPDesk\FPF\Free\Field\Type;

use WPDesk\FPF\Free\Field\Types;
use WPDesk\FPF\Free\Settings\Option\CssOption;
use WPDesk\FPF\Free\Settings\Option\DefaultCheckedOption;
use WPDesk\FPF\Free\Settings\Option\FieldLabelOption;
use WPDesk\FPF\Free\Settings\Option\FieldNameOption;
use WPDesk\FPF\Free\Settings\Option\FieldPriorityOption;
use WPDesk\FPF\Free\Settings\Option\FieldTypeOption;
use WPDesk\FPF\Free\Settings\Option\LogicAdvOption;
use WPDesk\FPF\Free\Settings\Option\OptionsCheckboxOption;
use WPDesk\FPF\Free\Settings\Option\OptionsValueOption;
use WPDesk\FPF\Free\Settings\Option\OptionsImageOptionOptional;
use WPDesk\FPF\Free\Settings\Option\PricingAdvOption;
use WPDesk\FPF\Free\Settings\Option\RequiredOption;
use WPDesk\FPF\Free\Settings\Option\SelectedMaxOption;
use WPDesk\FPF\Free\Settings\Option\SelectedMinOption;
use WPDesk\FPF\Free\Settings\Option\TooltipOption;
use WPDesk\FPF\Free\Settings\Option\PreviewLabelOption;
use WPDesk\FPF\Free\Settings\Option\PreviewWidthOption;
use WPDesk\FPF\Free\Settings\Option\OptionsMultImagesOption;
use WPDesk\FPF\Free\Settings\Tab\AdvancedTab;
use WPDesk\FPF\Free\Settings\Tab\GeneralTab;
use WPDesk\FPF\Free\Settings\Tab\LogicTab;
use WPDesk\FPF\Free\Settings\Tab\PricingTab;


class MultiImagesType extends TypeAbstract {

	public const FIELD_TYPE = 'multi-images';

	public function get_field_type(): string {
		return self::FIELD_TYPE;
	}

	public function get_field_type_label(): string {
		return __( 'Multi-images', 'flexible-product-fields' );
	}

	public function get_field_group(): string {
		return Types::FIELD_GROUP_OPTION;
	}

	public function get_field_type_icon(): string {
		return 'icon-images';
	}

	public function get_options_objects(): array {
		return [
			GeneralTab::TAB_NAME  => [
				FieldPriorityOption::FIELD_NAME     => new FieldPriorityOption(),
				FieldTypeOption::FIELD_NAME         => new FieldTypeOption(),
				FieldLabelOption::FIELD_NAME        => new FieldLabelOption(),
				RequiredOption::FIELD_NAME          => new RequiredOption(),
				OptionsMultImagesOption::FIELD_NAME => new OptionsMultImagesOption(),
				CssOption::FIELD_NAME               => new CssOption(),
				TooltipOption::FIELD_NAME           => new TooltipOption(),
				FieldNameOption::FIELD_NAME         => new FieldNameOption(),
			],
			AdvancedTab::TAB_NAME => [
				SelectedMinOption::FIELD_NAME  => new SelectedMinOption(),
				SelectedMaxOption::FIELD_NAME  => new SelectedMaxOption(),
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

	public function get_field_template_vars( array $field_data ): array {
		$template_vars = parent::get_field_template_vars( $field_data );

		$default_checked = [];
		$media_ids       = [];
		foreach ( $field_data[ OptionsCheckboxOption::FIELD_NAME ] as $option ) {
			if ( $option[ DefaultCheckedOption::FIELD_NAME ] === '1' ) {
				$default_checked[] = $option[ OptionsValueOption::FIELD_NAME ];
			}

			if ( ! isset( $option[ OptionsImageOptionOptional::FIELD_NAME ] ) || $option[ OptionsImageOptionOptional::FIELD_NAME ] === '' ) {
				continue;
			}

			$image_id = $option[ OptionsImageOptionOptional::FIELD_NAME ];
			$media_ids[ $option[ OptionsValueOption::FIELD_NAME ] ] = $image_id;
		}

		$template_vars['preview_width']      = $field_data[ PreviewWidthOption::FIELD_NAME ] ?? 0;
		$template_vars['preview_show_label'] = ! (bool) ( $field_data[ PreviewLabelOption::FIELD_NAME ] ?? false );
		$template_vars['media_ids']          = $media_ids;
		$template_vars['default_checked']    = $default_checked;
		$template_vars['selected_min']       = $field_data[ SelectedMinOption::FIELD_NAME ] ?: null;
		$template_vars['selected_max']       = $field_data[ SelectedMaxOption::FIELD_NAME ] ?: null;

		return $template_vars;
	}

	/**
	 * @return array<int, string>|null
	 */
	public function get_field_value( string $field_id, bool $is_request = false ): ?array {
		$form_data = ( $is_request ) ? $_REQUEST : $_POST; // phpcs:ignore
		$field_id  = str_replace( '[]', '', $field_id );
		if ( ! isset( $form_data[ $field_id ] ) ) {
			return null;
		}

		$posted_values = wp_unslash( $form_data[ $field_id ] );
		$field_values  = [];
		foreach ( $posted_values as $posted_value ) {
			$field_values[] = sanitize_textarea_field( $posted_value );
		}
		return $field_values;
	}

	public function is_available(): bool {
		return true;
	}

	public function has_required(): bool {
		return true;
	}

	public function has_css_class(): bool {
		return true;
	}

	public function has_tooltip(): bool {
		return true;
	}

	public function has_options(): bool {
		return true;
	}

	public function has_price_info_in_options(): bool {
		return true;
	}

	public function has_logic_info(): bool {
		return true;
	}
}
