<?php

namespace WPDesk\FPF\Free\Service;

use VendorFPF\WPDesk\View\Renderer\Renderer;
use WPDesk\FPF\Free\Service\FieldsDisplayerInterface;
use WC_Product;
use WPDesk\FPF\Free\Block\Settings\BlockTemplateSettings;
use FPF_Product;


class FieldsDisplayer implements FieldsDisplayerInterface {

	private Renderer $renderer;

	private FPF_Product $fpf_product;

	public function __construct( Renderer $renderer, FPF_Product $fpf_product ) {
		$this->renderer    = $renderer;
		$this->fpf_product = $fpf_product;
	}

	public function display_fields_for_hook( WC_Product $product, string $hook_name ): void {
		$fields = $this->fpf_product->create_fields_for_product( $product, $hook_name );

		$this->renderer->output_render(
			'hooks/' . $hook_name,
			[
				'fields'         => $fields['display_fields'],
				'product_id'     => $product->get_id(),
			]
		);
	}

	public function get_fields_for_block( WC_Product $product, ?BlockTemplateSettings $block_settings = null ): string {
		$fields = $this->fpf_product->create_fields_for_product( $product, false, $block_settings );

		return $this->renderer->render(
			'hooks/block_fields',
			[
				'fields'         => $fields['display_fields'],
				'product_id'     => $product->get_id(),
				'block_settings' => $block_settings,
			]
		);
	}
}
