<?php

namespace WPDesk\FPF\Free\Service\TemplateFinder\Collections;

use WP_Post;

/**
 * Collection of product fields templates (fpf post type objects).
 * with some additional (template releated) information.
 */
class TemplateCollection {

	/**
	 * @var WP_Post[]
	 */
	private array $posts = [];

	/**
	 * @var array<array<string, mixed>>
	 */
	private array $fields = [];

	/**
	 * @param WP_Post[] $posts
	 */
	public function __construct( array $posts = [] ) {
		$this->posts  = $posts;
		$this->fields = $this->init_fields();
	}

	public function merge( TemplateCollection $templates ): void {
		$this->posts  = array_merge( $this->posts, $templates->posts );
		$this->fields = array_merge( $this->fields, $templates->fields );
	}

	/**
	 * @return array<array<string, mixed>>
	 */
	public function init_fields(): array {
		return array_reduce(
			$this->posts,
			fn ( array $carry, WP_Post $post ) => array_merge(
				$carry,
				$post->fields_meta ?? []
			),
			[]
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	public function legacy_results(): array {
		$posts = \apply_filters( 'flexible_product_fields_sort_groups', $this->posts );

		return [
			'posts'          => $posts,
			'fields'         => $this->fields,
			'has_required'   => (bool) count(
				array_filter(
					$this->fields,
					fn( $field) => isset( $field['required'] ) && filter_var( $field['required'], FILTER_VALIDATE_BOOLEAN )
				)
			),
			'display_fields' => [],
		];
	}

	/**
	 * @param array<string, array<string, mixed>> $fields_definitions
	 */
	public function add_fields_definitions( array $fields_definitions ): void {
		$this->fields = array_reduce(
			$this->fields,
			function ( array $carry, array $field ) use ( $fields_definitions ) {
				$type       = $field['type'] ?? '';
				$definition = $fields_definitions[ $type ] ?? [];
				if ( $type === '' || count( $definition ) === 0 ) {
					return $carry;
				}

				if ( ! filter_var( $definition['is_available'] ?? false, FILTER_VALIDATE_BOOLEAN ) ) {
					return $carry;
				}

				$field['has_price']            = $definition['has_price'] ?? false;
				$field['has_price_in_options'] = $definition['has_price_in_options'] ?? false;
				$field['has_options']          = $definition['has_options'] ?? false;
				$carry[]                       = $field;
				return $carry;
			},
			[]
		);
	}
}
