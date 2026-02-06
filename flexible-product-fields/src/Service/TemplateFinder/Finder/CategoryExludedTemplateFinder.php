<?php

namespace WPDesk\FPF\Free\Service\TemplateFinder\Finder;

use WPDesk\FPF\Free\Service\TemplateFinder\ProductHandler\ProductHandlerInterface;
use WPDesk\FPF\Free\Service\TemplateFinder\Collections\TemplateCollection;
use WPDesk\FPF\Free\Service\TemplateFinder\TemplateQuery;

/**
 * Finds template by excluded category.
 */
class CategoryExludedTemplateFinder implements TemplateFinderInterface {

	private const TEMPLATE_TYPE     = 'excluded_category';
	private const TEMPLATE_META_KEY = '_excluded_category_id';

	public function find_templates( ProductHandlerInterface $product_handler, TemplateQuery $template_query ): TemplateCollection {
		$category_ids = $product_handler->get_category_ids();

		$templates = $template_query->get_templates( self::TEMPLATE_TYPE );

		if ( empty( $templates ) ) {
			return new TemplateCollection( [] );
		}

		$filtered_templates = array_filter(
			$templates,
			function ( $template ) use ( $category_ids ) {
				$excluded_category_ids = get_post_meta( $template->ID, self::TEMPLATE_META_KEY );

				if ( empty( $excluded_category_ids ) ) {
					return true;
				}

				foreach ( $category_ids as $category_id ) {
					if ( in_array( (string) $category_id, $excluded_category_ids, true ) ) {
						return false;
					}
				}

				return true;
			}
		);

		return new TemplateCollection( $filtered_templates );
	}
}
