<?php

namespace WPDesk\FPF\Free\Service\TemplateFinder\Collections;

use ArrayIterator;
use WPDesk\FPF\Free\Service\TemplateFinder\Finder\TemplateFinderInterface;
use IteratorAggregate;

/**
 * Simple collection class for template finders.
 *
 * @implements IteratorAggregate<int, TemplateFinderInterface>
 */
class FinderCollection implements IteratorAggregate {

	/**
	 * @var TemplateFinderInterface[]
	 */
	private array $elements = [];

	/**
	 * Adds a template finder to the collection.
	 *
	 * @param TemplateFinderInterface $element The template finder to add.
	 */
	public function add( TemplateFinderInterface $element ): void {
		$this->elements[] = $element;
	}

	/**
	 * Retrieves an iterator for the template finders.
	 *
	 * @return ArrayIterator<int, TemplateFinderInterface>
	 */
	public function getIterator(): ArrayIterator {
		return new ArrayIterator( $this->elements );
	}
}
