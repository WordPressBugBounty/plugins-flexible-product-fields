<?php

namespace WPDesk\FPF\Free\Service\TemplateFinder;

use WPDesk\FPF\Free\Service\TemplateFinder\Collections\TemplateCollection;
use WC_Product;

class TemplateFinderCache {

	/**
	 * @var array<int, array<string, TemplateCollection>>
	 */
	private $cache = [];

	private const DEFAULT_SECTION = 'default';

	/**
	 * @param WC_Product $product
	 * @param string $section
	 *
	 * @return bool
	 */
	public function has( WC_Product $product, string $section ): bool {
		return $this->get( $product, $section ) !== null;
	}

	/**
	 * @param WC_Product $product
	 * @param string $section
	 *
	 * @return ?TemplateCollection
	 */
	public function get( WC_Product $product, string $section ): ?TemplateCollection {
		$section = $section !== '' ? $section : self::DEFAULT_SECTION;
		return $this->cache[ $product->get_id() ][ $section ] ?? null;
	}

	/**
	 * @param WC_Product $product
	 * @param string $section
	 * @param TemplateCollection $templates
	 */
	public function set( WC_Product $product, string $section, TemplateCollection $templates ): void {
		$section                                       = $section !== '' ? $section : self::DEFAULT_SECTION;
		$this->cache[ $product->get_id() ][ $section ] = $templates;
	}
}
