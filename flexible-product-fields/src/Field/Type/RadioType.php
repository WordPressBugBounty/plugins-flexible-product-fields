<?php

namespace WPDesk\FPF\Free\Field\Type;

use WPDesk\FPF\Free\Field\Types;
use WPDesk\FPF\Free\Settings\Tab\LogicTab;
use WPDesk\FPF\Free\Settings\Tab\GeneralTab;
use WPDesk\FPF\Free\Settings\Tab\PricingTab;
use WPDesk\FPF\Free\Settings\Option\CssOption;
use WPDesk\FPF\Free\Settings\Option\DefaultOption;
use WPDesk\FPF\Free\Settings\Option\OptionsOption;
use WPDesk\FPF\Free\Settings\Option\TooltipOption;
use WPDesk\FPF\Free\Settings\Option\LogicAdvOption;
use WPDesk\FPF\Free\Settings\Option\RequiredOption;
use WPDesk\FPF\Free\Settings\Option\FieldNameOption;
use WPDesk\FPF\Free\Settings\Option\FieldTypeOption;
use WPDesk\FPF\Free\Settings\Option\FieldLabelOption;
use WPDesk\FPF\Free\Settings\Option\PricingAdvOption;
use WPDesk\FPF\Free\Settings\Option\OptionsVariantImageOption;
use WPDesk\FPF\Free\Settings\Option\OptionsValueOption;
use WPDesk\FPF\Free\Settings\Option\FieldPriorityOption;

/**
 * Supports "Radio" field type.
 */
class RadioType extends TypeAbstract {

	const FIELD_TYPE = 'radio';

	/**
	 * {@inheritdoc}
	 */
	public function get_field_type(): string {
		return self::FIELD_TYPE;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_field_type_label(): string {
		return __( 'Radio', 'flexible-product-fields' );
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
		return 'icon-list-ul';
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_options_objects(): array {
		return [
			GeneralTab::TAB_NAME => [
				FieldPriorityOption::FIELD_NAME => new FieldPriorityOption(),
				FieldTypeOption::FIELD_NAME     => new FieldTypeOption(),
				FieldLabelOption::FIELD_NAME    => new FieldLabelOption(),
				RequiredOption::FIELD_NAME      => new RequiredOption(),
				CssOption::FIELD_NAME           => new CssOption(),
				TooltipOption::FIELD_NAME       => new TooltipOption(),
				OptionsOption::FIELD_NAME       => new OptionsOption(),
				DefaultOption::FIELD_NAME       => new DefaultOption(),
				FieldNameOption::FIELD_NAME     => new FieldNameOption(),
			],
			PricingTab::TAB_NAME => [
				PricingAdvOption::FIELD_NAME => new PricingAdvOption(),
			],
			LogicTab::TAB_NAME   => [
				LogicAdvOption::FIELD_NAME => new LogicAdvOption(),
			],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_field_template_vars( array $field_data ): array {
		$template_vars = parent::get_field_template_vars( $field_data );

		$options_image_props = [];
		foreach ( $field_data[ OptionsOption::FIELD_NAME ] as $option ) {
			if ( ! isset( $option[ OptionsVariantImageOption::FIELD_NAME ] ) ) {
				continue;
			}

			$image_id                = $option[ OptionsVariantImageOption::FIELD_NAME ];
			$image_props             = \wc_get_product_attachment_props( $image_id );
			$image_props['image_id'] = $image_id;

			$options_image_props[ $option[ OptionsValueOption::FIELD_NAME ] ] = $image_props;
		}

		$template_vars['options_image_props'] = $options_image_props;

		return $template_vars;
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
