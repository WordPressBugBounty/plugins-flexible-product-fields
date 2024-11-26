<?php

namespace WPDesk\FPF\Free\Service\TemplateFinder\ProductHandler;

/**
 * Product data handler for variable products.
 */
class VariableProductHandler extends ProductHandler implements ProductHandlerInterface {
	public function get_product_ids(): array {
		return $this->product->get_children();
	}
}
