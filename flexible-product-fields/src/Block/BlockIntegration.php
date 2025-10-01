<?php

namespace WPDesk\FPF\Free\Block;

use VendorFPF\WPDesk\PluginBuilder\Plugin\Hookable;

/**
 * Handles Gutenberg block registration using WordPress 6.8+ metadata collection approach.
 */
class BlockIntegration implements Hookable {

	private string $plugin_dir;

	public function __construct( string $plugin_dir ) {
		$this->plugin_dir = $plugin_dir;
	}

	public function hooks() {
		add_action( 'init', [ $this, 'register_blocks' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_block_editor_assets' ] );
	}

	/**
	 * Register blocks using WordPress 6.8+ metadata collection approach.
	 * Follows the same pattern as the working copyright-date-block.
	 *
	 * @return void
	 */
	public function register_blocks() {
		$build_dir     = $this->plugin_dir . '/build';
		$manifest_file = $build_dir . '/blocks-manifest.php';

		/**
		 * Registers the block(s) metadata from the `blocks-manifest.php` and registers the block type(s)
		 * based on the registered block metadata.
		 * Added in WordPress 6.8 to simplify the block metadata registration process added in WordPress 6.7.
		 */
		if ( function_exists( 'wp_register_block_types_from_metadata_collection' ) ) {
			wp_register_block_types_from_metadata_collection( $build_dir, $manifest_file );
			return;
		}

		/**
		 * Fallback to registering the block(s) metadata from the `blocks-manifest.php` file.
		 * Added to WordPress 6.7 to improve the performance of block type registration.
		 */
		if ( function_exists( 'wp_register_block_metadata_collection' ) ) {
			wp_register_block_metadata_collection( $build_dir, $manifest_file );
		}

		$manifest_data = require $manifest_file;
		foreach ( array_keys( $manifest_data ) as $block_type ) {
			register_block_type( $build_dir . "/{$block_type}" );
		}
	}

	/**
	 * Enqueue block editor assets.
	 *
	 * @return void
	 */
	public function enqueue_block_editor_assets() {
		wp_localize_script(
			'fpf-template-selector-editor-script',
			'fpfBlockData',
			[
				'templates' => $this->get_available_templates(),
				'nonce'     => wp_create_nonce( 'fpf_block_nonce' ),
				'ajaxUrl'   => admin_url( 'admin-ajax.php' ),
			]
		);
	}

	/**
	 * Get available templates for the block selector.
	 *
	 * @return array<array<string, mixed>> Array of template data.
	 */
	private function get_available_templates(): array {
		$templates = get_posts(
			[
				'post_type'      => 'fpf_fields',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
			]
		);

		$template_options = [];
		foreach ( $templates as $template ) {
			$template_options[] = [
				'value' => $template->ID,
				'label' => $template->post_title,
			];
		}

		return $template_options;
	}
}
