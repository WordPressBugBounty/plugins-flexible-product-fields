<?php

namespace WPDesk\FPF\Free\Settings\Option;

/**
 * This is a variant of OptionsImageOption where the image is optional.
 * Please suggest a better name for this class.
 */
class OptionsImageOptionOptional extends OptionsImageOption {

	public function get_validation_rules(): array {
		return [];
	}
}
