<?php

namespace WPDesk\FPF\Free\Service\TemplateFinder\Finder;

use WPDesk\FPF\Free\Service\TemplateFinder\ProductHandler\ProductHandlerInterface;
use WPDesk\FPF\Free\Service\TemplateFinder\Collections\TemplateCollection;
use WPDesk\FPF\Free\Service\TemplateFinder\TemplateQuery;

/**
 * Finds template assigned to a product.
 */
class ProductTemplateFinder implements TemplateFinderInterface {

	private const TEMPLATE_TYPE     = 'product';
	private const TEMPLATE_META_KEY = '_product_id';

	public function find_templates( ProductHandlerInterface $product_handler, TemplateQuery $template_query ): TemplateCollection {
		$values          = $product_handler->get_product_ids();
		$meta_query_args = [
			'key'     => self::TEMPLATE_META_KEY,
			'value'   => count( $values ) === 1 ? $values[0] : $values,
			'compare' => count( $values ) === 1 ? '=' : 'IN',
		];

		$templates = $template_query->get_templates(
			self::TEMPLATE_TYPE,
			$meta_query_args
		);

		return new TemplateCollection( $templates );
	}
}
