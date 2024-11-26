<?php

namespace WPDesk\FPF\Free\Service\TemplateFinder;

use WPDesk\FPF\Free\Service\TemplateFinder\TemplateQuery;
use WPDesk\FPF\Free\Service\TemplateFinder\Finder\AllTemplateFinder;
use WPDesk\FPF\Free\Service\TemplateFinder\Finder\TagTemplateFinder;
use WPDesk\FPF\Free\Service\TemplateFinder\Collections\FinderCollection;
use WPDesk\FPF\Free\Service\TemplateFinder\Finder\ProductTemplateFinder;
use WPDesk\FPF\Free\Service\TemplateFinder\Finder\CategoryTemplateFinder;
use WPDesk\FPF\Free\Service\TemplateFinder\ProductHandler\ProductHandler;
use WPDesk\FPF\Free\Service\TemplateFinder\Collections\TemplateCollection;
use WPDesk\FPF\Free\Service\TemplateFinder\Finder\TagExludedTemplateFinder;
use WPDesk\FPF\Free\Service\TemplateFinder\Finder\ProductExludedTemplateFinder;
use WPDesk\FPF\Free\Service\TemplateFinder\Finder\CategoryExludedTemplateFinder;
use WPDesk\FPF\Free\Service\TemplateFinder\ProductHandler\VariableProductHandler;
use WPDesk\FPF\Free\Service\TemplateFinder\ProductHandler\ProductHandlerInterface;
use WPDesk\FPF\Free\Service\TemplateFinder\ProductHandler\VariationProductHandler;

/**
 * Finds templates (we can have multiple) for a given product.
 */
class TemplateFinder {

	protected FinderCollection $finders;

	protected ProductHandlerInterface $product_handler;

	protected TemplateQuery $template_query;

	public function __construct() {
		$this->template_query = new TemplateQuery();
		$this->finders        = new FinderCollection();
		$this->init_finders();
	}

	public function find( \WC_Product $product, string $section = '' ): TemplateCollection {
		$this->product_handler = $this->get_product_handler( $product );
		$this->template_query->set_section_hook( $section );

		$templates = new TemplateCollection();
		foreach ( $this->finders as $finder ) {
			$new_templates = $finder->find_templates(
				$this->product_handler,
				$this->template_query
			);
			$templates->merge( $new_templates );
		}

		return $templates;
	}

	private function get_product_handler( \WC_Product $product ): ProductHandlerInterface {
		return new ProductHandler( $product );
	}

	private function init_finders(): void {
		$this->finders->add( new ProductTemplateFinder() );
		$this->finders->add( new CategoryTemplateFinder() );
		$this->finders->add( new TagTemplateFinder() );
		$this->finders->add( new AllTemplateFinder() );
		$this->finders->add( new ProductExludedTemplateFinder() );
		$this->finders->add( new CategoryExludedTemplateFinder() );
		$this->finders->add( new TagExludedTemplateFinder() );
	}
}
