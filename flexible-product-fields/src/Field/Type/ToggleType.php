<?php

namespace WPDesk\FPF\Free\Field\Type;

class ToggleType extends CheckboxType {

	public const FIELD_TYPE = 'toggle';

	public function get_field_type(): string {
		return self::FIELD_TYPE;
	}

	public function get_field_type_label(): string {
		return __( 'Toggle', 'flexible-product-fields' );
	}

	public function get_field_type_icon(): string {
		return 'icon-check-square';
	}
}
