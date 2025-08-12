<?php

namespace WPDesk\FPF\Free\Settings\Validation\Error;

/**
 * Validation error for field max length.
 */
class FieldMaxLengthError implements ValidationError {

	public function get_message(): string {
		return __( 'Field value is too long.', 'flexible-product-fields' );
	}

	public function is_fatal_error(): bool {
		return true;
	}
}
