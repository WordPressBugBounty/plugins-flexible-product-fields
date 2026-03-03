<?php

namespace WPDesk\FPF\Free\Validation\Rule;

use WPDesk\FPF\Free\Validation\ValidationResult;

/**
 * Initializes integration of validation rule.
 */
class RuleIntegration {

	/**
	 * Class object for validation rule.
	 *
	 * @var RuleInterface
	 */
	private $rule_object;

	/**
	 * Class constructor.
	 *
	 * @param RuleInterface $rule_object Class object of validation rule.
	 */
	public function __construct( RuleInterface $rule_object ) {
		$this->rule_object = $rule_object;
	}

	/**
	 * {@inheritdoc}
	 */
	public function hooks() {
		add_filter( 'flexible_product_fields/validate_field/v2', [ $this, 'validate_field' ], 10, 4 );
	}

	/**
	 * Validates field value.
	 *
	 * @param ValidationResult $result
	 * @param array $field_data Field settings.
	 * @param mixed $value      Value of field.
	 * @param array $field_type Config for field data.
	 *
	 * @internal
	 */
	public function validate_field( ValidationResult $result, array $field_data, $value, array $field_type ): ValidationResult {
		if ( $this->rule_object->validate_value( $field_data, $field_type, $value ) ) {
			return $result;
		}

		$result->add_error(
			get_class( $this->rule_object ),
			$this->rule_object->get_error_message( $field_data )
		);

		return $result;
	}
}
