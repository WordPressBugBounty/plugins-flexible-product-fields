<?php

namespace WPDesk\FPF\Free\Service\TemplateFinder;

use WPDesk\FPF\Free\Service\TemplateFinder\TemplateQuery;
use WPDesk\FPF\Free\Service\TemplateFinder\Collections\FinderCollection;
use WPDesk\FPF\Free\Service\TemplateFinder\Finder\ProductTemplateFinder;
use WPDesk\FPF\Free\Service\TemplateFinder\ProductHandler\ProductHandler;
use WPDesk\FPF\Free\Service\TemplateFinder\Collections\TemplateCollection;
use WPDesk\FPF\Free\Service\TemplateFinder\Finder\TemplateFinderInterface;
use WPDesk\FPF\Free\Service\TemplateFinder\ProductHandler\ProductHandlerInterface;
use WPDesk\FPF\Free\Service\TemplateFinder\ProductHandler\VariationProductHandler;
use FPF_Product_Fields;

/**
 * Finds templates (we can have multiple) for a given product.
 */
class TemplateFinder {

	protected FinderCollection $finders;

	protected ProductHandlerInterface $product_handler;

	protected TemplateQuery $template_query;

	protected TemplateFinderCache $cache;

	protected FPF_Product_Fields $product_fields;

	public function __construct( TemplateQuery $template_query, FPF_Product_Fields $product_fields ) {
		$this->template_query = $template_query;
		$this->product_fields = $product_fields;
		$this->finders        = new FinderCollection();
		$this->cache          = new TemplateFinderCache();
		$this->init_finders();
	}

	public function find( \WC_Product $product, string $section = '' ): TemplateCollection {
		if ( $this->cache->has( $product, $section ) ) {
			return $this->cache->get( $product, $section );
		}

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

		$templates->init_fields( $this->product_fields->get_field_types() );

		$this->cache->set( $product, $section, $templates );

		return $templates;
	}

	private function get_product_handler( \WC_Product $product ): ProductHandlerInterface {
		if ( $product instanceof \WC_Product_Variation ) {
			return new VariationProductHandler( $product );
		}

		return new ProductHandler( $product );
	}

	private function init_finders(): void {
		$this->finders->add( new ProductTemplateFinder() );
	}

	public function append_finder( TemplateFinderInterface $finder ): void {
		$this->finders->add( $finder );
	}

	public function get_template_query(): TemplateQuery {
		return $this->template_query;
	}
}
