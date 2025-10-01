<?php

namespace WPDesk\FPF\Free\Settings\Option;

use WPDesk\FPF\Free\Settings\Validation\Error\GroupAssignProError;

/**
 * {@inheritdoc}
 */
class GroupAssignOption extends OptionAbstract {

	const FIELD_NAME = 'assign_to';

	public const OPTION_ASSIGN_TO_PRODUCT           = 'product';
	public const OPTION_ASSIGN_TO_CATEGORY          = 'category';
	public const OPTION_ASSIGN_TO_TAG               = 'tag';
	public const OPTION_ASSIGN_TO_ALL               = 'all';
	public const OPTION_ASSIGN_TO_PRODUCT_EXCLUDED  = 'excluded_product';
	public const OPTION_ASSIGN_TO_CATEGORY_EXCLUDED = 'excluded_category';
	public const OPTION_ASSIGN_TO_TAG_EXCLUDED      = 'excluded_tag';

	/**
	 * {@inheritdoc}
	 */
	public function get_option_name(): string {
		return self::FIELD_NAME;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_option_type(): string {
		return self::FIELD_TYPE_SELECT;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_option_label(): string {
		return __( 'Assign to', 'flexible-product-fields' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_validation_rules(): array {
		return [
			'^(' . self::OPTION_ASSIGN_TO_PRODUCT . '|)$' => new GroupAssignProError(),
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_values(): array {
		return [
			''                                       => __( 'None', 'flexible-product-fields' ),
			self::OPTION_ASSIGN_TO_PRODUCT           => __( 'Product', 'flexible-product-fields' ),
			self::OPTION_ASSIGN_TO_PRODUCT_EXCLUDED  => __( 'Product excluded', 'flexible-product-fields' ),
			self::OPTION_ASSIGN_TO_CATEGORY          => __( 'Category', 'flexible-product-fields' ),
			self::OPTION_ASSIGN_TO_CATEGORY_EXCLUDED => __( 'Category excluded', 'flexible-product-fields' ),
			self::OPTION_ASSIGN_TO_TAG               => __( 'Tag', 'flexible-product-fields' ),
			self::OPTION_ASSIGN_TO_TAG_EXCLUDED      => __( 'Tag excluded', 'flexible-product-fields' ),
			self::OPTION_ASSIGN_TO_ALL               => __( 'All products', 'flexible-product-fields' ),
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_default_value() {
		return '';
	}

	public function get_disabled_values(): array {
		if ( is_flexible_products_fields_pro_active() ) {
			return [];
		}

		return [
			self::OPTION_ASSIGN_TO_PRODUCT_EXCLUDED,
			self::OPTION_ASSIGN_TO_CATEGORY,
			self::OPTION_ASSIGN_TO_CATEGORY_EXCLUDED,
			self::OPTION_ASSIGN_TO_TAG,
			self::OPTION_ASSIGN_TO_TAG_EXCLUDED,
			self::OPTION_ASSIGN_TO_ALL,
		];
	}
}
