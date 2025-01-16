<?php

namespace WPDesk\FPF\Free\Service\TemplateFinder\ProductHandler;

/**
 * Product data handler for product variations.
 */
class VariationProductHandler extends ProductHandler implements ProductHandlerInterface {

	public function get_product_ids(): array {
		return [ $this->product->get_id(), $this->product->get_parent_id() ];
	}

	public function get_category_ids(): array {
		$parent_product = \wc_get_product(
			$this->product->get_parent_id()
		);
		return $parent_product->get_category_ids();
	}

	public function get_tag_ids(): array {
		$parent_product = \wc_get_product(
			$this->product->get_parent_id()
		);
		return $parent_product->get_tag_ids();
	}
}
