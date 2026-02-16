<?php

namespace WPDesk\FPF\Free\Settings\Option;

class OptionsMultImagesOption extends OptionsCheckboxOption {

	public function get_default_value() {
		return [
			[
				OptionsValueOption::FIELD_NAME   => '',
				OptionsLabelOption::FIELD_NAME   => '',
				OptionsImageOption::FIELD_NAME   => '',
				DefaultCheckedOption::FIELD_NAME => '',
			],
		];
	}

	public function get_children(): array {
		return [
			OptionsValueOption::FIELD_NAME   => new OptionsValueOption(),
			OptionsLabelOption::FIELD_NAME   => new OptionsLabelOption(),
			OptionsImageOption::FIELD_NAME   => new OptionsImageOption(),
			DefaultCheckedOption::FIELD_NAME => new DefaultCheckedOption(),
		];
	}
}
