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
		$this->posts = $posts;
	}

	public function merge( TemplateCollection $templates ): void {
		$this->posts = \apply_filters(
			'flexible_product_fields_sort_groups_posts',
			array_merge( $this->posts, $templates->posts )
		);
	}

	/**
	 * @return array<array<string, mixed>>
	 */
	public function get_fields(): array {
		return $this->fields;
	}

	/**
	 * @return WP_Post[]
	 */
	public function get_posts(): array {
		return $this->posts;
	}

	/**
	 * @return array<array<string, mixed>>
	 */
	private function get_raw_fields(): array {
		return array_reduce(
			$this->posts,
			fn ( array $carry, WP_Post $post ) => array_merge(
				$carry,
				$post->fields_meta ?? []
			),
			[]
		);
	}

	public function has_required_fields(): bool {
		return (bool) count(
			array_filter(
				$this->fields,
				fn( $field ) => isset( $field['required'] ) && filter_var( $field['required'], FILTER_VALIDATE_BOOLEAN )
			)
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	public function legacy_results(): array {
		return [
			'posts'          => $this->posts,
			'fields'         => $this->fields,
			'has_required'   => $this->has_required_fields(),
			'display_fields' => [],
		];
	}

	/**
	 * @param array<string, array<string, mixed>> $fields_definitions
	 */
	public function init_fields( array $fields_definitions ): void {
		$this->fields = array_reduce(
			$this->get_raw_fields(),
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
