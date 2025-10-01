<?php
namespace WPDesk\FPF\Free\Block;

use FPF_Product;
use VendorFPF\WPDesk\PluginBuilder\Plugin\Hookable;
use WPDesk\FPF\Free\Block\Settings\BlockTemplateSettings;

/**
 * Ajax action for loading template previews in the block editor.
 */
class AjaxTemplatePreviewLoader implements Hookable {

	private FPF_Product $fpf_product;

	private const ACTION = 'fpf_template_preview';

	public function __construct( FPF_Product $fpf_product ) {
		$this->fpf_product = $fpf_product;
	}

	public function hooks() {
		\add_action( 'wp_ajax_' . self::ACTION, [ $this, 'get_template_preview' ] );
		\add_action( 'wp_ajax_nopriv_' . self::ACTION, [ $this, 'get_template_preview' ] );
	}

	public function get_template_preview(): void {
		if ( ! isset( $_POST['nonce'] ) || ! \wp_verify_nonce( \sanitize_text_field( \wp_unslash( $_POST['nonce'] ) ), 'fpf_block_nonce' ) ) {
			\wp_send_json_error( 'Invalid nonce' );
		}

		$template_id = (int) \sanitize_key( $_POST['template_id'] ?? 0 );

		if ( $template_id === 0 ) {
			\wp_send_json_error( 'Invalid template ID' );
		}

		$show_other_fields = isset( $_POST['show_other_fields'] ) ? (bool) $_POST['show_other_fields'] : false;

		// Get the template post
		$template = \get_post( $template_id );

		if ( ! $template || $template->post_type !== 'fpf_fields' ) {
			\wp_send_json_error( 'Template not found' );
		}

		$block_settings = new BlockTemplateSettings(
			$template_id,
			$show_other_fields
		);

		// Render the template preview
		$preview_html = $this->render_template_preview( $block_settings );

		$response = [
			'preview_html' => $preview_html,
			'template_id'  => $template_id,
		];

		\wp_send_json_success( $response );
	}

	/**
	 * Render the template preview HTML.
	 */
	private function render_template_preview( BlockTemplateSettings $block_settings ): string {
		$mock_product = new \WC_Product_Simple();
		$mock_product->set_id( 0 );

		$preview_html = $this->fpf_product->render_fields( $mock_product, $block_settings );

		if ( empty( $preview_html ) ) {
			$preview_html = '<p>' . \esc_html__( 'No fields found in this template.', 'flexible-product-fields' ) . '</p>';
		}

		return $preview_html;
	}
}
