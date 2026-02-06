<?php

namespace WPDesk\FPF\Free\Service\TemplateFinder\Finder;

use WPDesk\FPF\Free\Service\TemplateFinder\ProductHandler\ProductHandlerInterface;
use WPDesk\FPF\Free\Service\TemplateFinder\Collections\TemplateCollection;
use WPDesk\FPF\Free\Service\TemplateFinder\TemplateQuery;

/**
 * Finds template by excluded tag.
 */
class TagExludedTemplateFinder implements TemplateFinderInterface {

	private const TEMPLATE_TYPE     = 'excluded_tag';
	private const TEMPLATE_META_KEY = '_excluded_tag_id';

	public function find_templates( ProductHandlerInterface $product_handler, TemplateQuery $template_query ): TemplateCollection {
		$tag_ids = $product_handler->get_tag_ids();

		$templates = $template_query->get_templates( self::TEMPLATE_TYPE );

		if ( empty( $templates ) ) {
			return new TemplateCollection( [] );
		}

		$filtered_templates = array_filter(
			$templates,
			function ( $template ) use ( $tag_ids ) {
				$excluded_tag_ids = get_post_meta( $template->ID, self::TEMPLATE_META_KEY );

				if ( empty( $excluded_tag_ids ) ) {
					return true;
				}

				foreach ( $tag_ids as $tag_id ) {
					if ( in_array( (string) $tag_id, $excluded_tag_ids, true ) ) {
						return false;
					}
				}

				return true;
			}
		);

		return new TemplateCollection( $filtered_templates );
	}
}
