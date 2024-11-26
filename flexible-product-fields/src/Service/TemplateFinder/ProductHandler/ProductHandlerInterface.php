<?php

namespace WPDesk\FPF\Free\Service\TemplateFinder\ProductHandler;

/**
 * Interface to access different product types data.
 */
interface ProductHandlerInterface {

	/**
	 * @return int[]
	 */
	public function get_category_ids(): array;

	/**
	 * @return int[]
	 */
	public function get_tag_ids(): array;

	/**
	 * @return int[]
	 */
	public function get_product_ids(): array;
}
