<?php

namespace WPDesk\FPF\Free\Settings\Option;

use WPDesk\FPF\Free\Settings\Tab\GeneralTab;

class DisableProductImageChangeOption extends OptionAbstract {

	const FIELD_NAME = 'disable_product_image_change';

	public function get_option_name(): string {
		return self::FIELD_NAME;
	}

	public function get_option_tab(): string {
		return GeneralTab::TAB_NAME;
	}

	public function get_option_type(): string {
		return self::FIELD_TYPE_CHECKBOX;
	}

	public function get_option_label(): string {
		return __( 'Disable changing product image', 'flexible-product-fields' );
	}

	public function get_label_tooltip(): string {
		return __( 'When disabled, the main product image will not be changed when selecting variations.', 'flexible-product-fields' );
	}
}
