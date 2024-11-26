<?php

namespace WPDesk\FPF\Free\Service\TemplateFinder\Finder;

use WPDesk\FPF\Free\Service\TemplateFinder\ProductHandler\ProductHandlerInterface;
use WPDesk\FPF\Free\Service\TemplateFinder\Collections\TemplateCollection;
use WPDesk\FPF\Free\Service\TemplateFinder\TemplateQuery;

/**
 * Finds all templates for all products.
 */
class AllTemplateFinder implements TemplateFinderInterface {

	private const TEMPLATE_TYPE = 'all';

	public function find_templates( ProductHandlerInterface $product_handler, TemplateQuery $template_query ): TemplateCollection {
		$templates = $template_query->get_templates(
			self::TEMPLATE_TYPE
		);

		return new TemplateCollection( $templates );
	}
}
