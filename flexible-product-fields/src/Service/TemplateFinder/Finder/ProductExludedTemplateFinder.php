<?php

namespace WPDesk\FPF\Free\Service\TemplateFinder\Finder;

use WPDesk\FPF\Free\Service\TemplateFinder\ProductHandler\ProductHandlerInterface;
use WPDesk\FPF\Free\Service\TemplateFinder\Collections\TemplateCollection;
use WPDesk\FPF\Free\Service\TemplateFinder\TemplateQuery;

/**
 * Finds template excluding given product.
 */
class ProductExludedTemplateFinder implements TemplateFinderInterface {

	private const TEMPLATE_TYPE     = 'excluded_product';
	private const TEMPLATE_META_KEY = '_excluded_product_id';

	public function find_templates( ProductHandlerInterface $product_handler, TemplateQuery $template_query ): TemplateCollection {
		$product_ids = $product_handler->get_product_ids();

		$templates = $template_query->get_templates( self::TEMPLATE_TYPE );

		if ( empty( $templates ) ) {
			return new TemplateCollection( [] );
		}

		$filtered_templates = array_filter(
			$templates,
			function ( $template ) use ( $product_ids ) {
				$excluded_product_ids = get_post_meta( $template->ID, self::TEMPLATE_META_KEY );

				if ( empty( $excluded_product_ids ) ) {
					return true;
				}

				foreach ( $product_ids as $product_id ) {
					if ( in_array( (string) $product_id, $excluded_product_ids, true ) ) {
						return false;
					}
				}

				return true;
			}
		);

		return new TemplateCollection( $filtered_templates );
	}
}
