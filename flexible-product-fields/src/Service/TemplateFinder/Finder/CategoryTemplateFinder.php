<?php

namespace WPDesk\FPF\Free\Service\TemplateFinder\Finder;

use WPDesk\FPF\Free\Service\TemplateFinder\ProductHandler\ProductHandlerInterface;
use WPDesk\FPF\Free\Service\TemplateFinder\Collections\TemplateCollection;
use WPDesk\FPF\Free\Service\TemplateFinder\TemplateQuery;

/**
 * Finds template by assigned category.
 */
class CategoryTemplateFinder implements TemplateFinderInterface {

	private const TEMPLATE_TYPE     = 'category';
	private const TEMPLATE_META_KEY = '_category_id';

	public function find_templates( ProductHandlerInterface $product_handler, TemplateQuery $template_query ): TemplateCollection {
		$values = $product_handler->get_category_ids();

		// wc will automatically assign product to uncategorized so this is only in case someone will change this behaviour.
		if ( count( $values ) === 0 ) {
			return new TemplateCollection();
		}

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
