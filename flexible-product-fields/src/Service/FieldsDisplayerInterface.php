<?php

namespace WPDesk\FPF\Free\Service;

use WC_Product;
use WPDesk\FPF\Free\Block\Settings\BlockTemplateSettings;


interface FieldsDisplayerInterface {

	public function display_fields_for_hook( WC_Product $product, string $hook_name ): void;

	public function get_fields_for_block( WC_Product $product, BlockTemplateSettings $block_settings ): string;
}
