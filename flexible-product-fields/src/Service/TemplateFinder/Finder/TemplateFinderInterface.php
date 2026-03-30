<?php

namespace WPDesk\FPF\Free\Service\TemplateFinder\Finder;

use WPDesk\FPF\Free\Service\TemplateFinder\ProductHandler\ProductHandlerInterface;
use WPDesk\FPF\Free\Service\TemplateFinder\Collections\TemplateCollection;
use WPDesk\FPF\Free\Service\TemplateFinder\TemplateQuery;

/**
 * Finds template for a given product
 */
interface TemplateFinderInterface {

	public function find_templates( ProductHandlerInterface $product_handler, TemplateQuery $template_query ): TemplateCollection;
}
