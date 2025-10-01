<?php

namespace WPDesk\FPF\Free\Block;

use Flexible_Product_Fields_Plugin;
use VendorFPF\WPDesk\PluginBuilder\Plugin\Hookable;

/**
 * Adds context to the block editor.
 */
class TemplateBlockContext implements Hookable {

	private Flexible_Product_Fields_Plugin $plugin;

	public function __construct( Flexible_Product_Fields_Plugin $plugin ) {
		$this->plugin = $plugin;
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

		$context['fpf_plugin'] = $this->plugin;
		return $context;
	}
}
