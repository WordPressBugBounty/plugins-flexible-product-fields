<?php

namespace WPDesk\FPF\Free\Service\TemplateFinder\ProductHandler;

use WC_Product;

/**
 * Default product data handler class.
 */
class ProductHandler implements ProductHandlerInterface {

	protected WC_Product $product;

	public function __construct( WC_Product $product ) {
		$this->product = $product;
	}

	public function get_product_ids(): array {
		return [ $this->product->get_id() ];
	}

	public function get_category_ids(): array {
		return $this->product->get_category_ids();
	}

	public function get_tag_ids(): array {
		return $this->product->get_tag_ids();
	}
}
