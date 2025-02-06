<?php

namespace WPDesk\FPF\Free\Settings\Option;

/**
 * Class is responsible for creating options for fields like radio, select etc.
 * It is similar to OptionsWithImageOption but it is not specific to radio with image field.
 */
class OptionsWithProductImageOption extends OptionsOption {

	/**
	 * @return array<int, array<string, string>>
	 */
	public function get_default_value(): array {
		return [
			[
				OptionsValueOption::FIELD_NAME         => '',
				OptionsLabelOption::FIELD_NAME         => '',
				OptionsImageOptionOptional::FIELD_NAME => '',
			],
		];
	}

	public function get_children(): array {
		return [
			OptionsValueOption::FIELD_NAME         => new OptionsValueOption(),
			OptionsLabelOption::FIELD_NAME         => new OptionsLabelOption(),
			OptionsImageOptionOptional::FIELD_NAME => new OptionsImageOptionOptional(),
		];
	}
}
