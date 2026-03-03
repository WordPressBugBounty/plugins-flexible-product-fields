<?php

namespace WPDesk\FPF\Free\Block;

use VendorFPF\WPDesk\PluginBuilder\Plugin\Hookable;
use WPDesk\FPF\Free\Service\FieldsDisplayerInterface;

/**
 * Adds context to the block editor.
 */
class TemplateBlockContext implements Hookable {

	private FieldsDisplayerInterface $fields_displayer;

	public function __construct( FieldsDisplayerInterface $fields_displayer ) {
		$this->fields_displayer = $fields_displayer;
	}

	public function hooks() {
		add_filter( 'render_block_context', $this, 10, 2 );
	}

	/**
	 * @param array<string, mixed> $context The block context.
	 * @param array<string, mixed> $parsed_block The parsed block.
	 *
	 * @return array<string, mixed>
	 */
	public function __invoke( $context, $parsed_block ) {
		if ( $parsed_block['blockName'] !== 'fpf/template-selector' ) {
			return $context;
		}

		$context['fields_displayer'] = $this->fields_displayer;
		return $context;
	}
}
