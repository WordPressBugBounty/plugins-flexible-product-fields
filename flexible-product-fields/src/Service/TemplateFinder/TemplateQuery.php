<?php

namespace WPDesk\FPF\Free\Service\TemplateFinder;

use WP_Query;
use WP_Post;

/**
 * Query Service class is a wrapper around WP_Query to use with TemplateFinder.
 */
class TemplateQuery {

	private string $section_hook = '';

	private WP_Query $query;

	private const TEMPLATE_POST_TYPE = 'fpf_fields';
	private const META_KEY_ASSIGN_TO = '_assign_to';
	private const META_KEY_SECTION   = '_section';

	public function __construct() {
		$this->query = new WP_Query();
	}

	public function set_section_hook( string $section_hook ): void {
		$this->section_hook = $section_hook;
	}

	/**
	 * @param string $assign_to
	 * @param array<string, string> $meta_query_args
	 * @return WP_Post[]
	 */
	public function get_templates( string $assign_to, array $meta_query_args = [] ): array {
		$args = [
			'post_type'      => self::TEMPLATE_POST_TYPE,
			'posts_per_page' => -1,
			'meta_query'     => [ // phpcs:ignore WordPress.DB.SlowDBQuery
				[
					'key'     => self::META_KEY_ASSIGN_TO,
					'value'   => $assign_to,
					'compare' => '=',
				],
				$meta_query_args,
				$this->get_section_meta_query(),
			],
		];

		$cache_key     = 'fpf_template_query_' . md5( json_encode( $args ) );
		$cached_result = \wp_cache_get( $cache_key );

		if ( $cached_result !== false ) {
			return $cached_result;
		}

		$this->query->query( $args );
		if ( $this->query->post_count === 0 ) {
			return [];
		}

		$result = array_reduce(
			$this->query->posts,
			function ( array $carry, WP_Post $post ) {
				$raw_fields_meta    = \get_post_meta( $post->ID, '_fields', true );
				$post->fields_meta  = array_map( // @phpstan-ignore-line
					fn ( array $field ) => array_merge( $field, [ '_group_id' => $post->ID ] ),
					$raw_fields_meta
				);
				$carry[ $post->ID ] = $post;
				return $carry;
			},
			[]
		);

		\wp_cache_set( $cache_key, $result );
		return $result;
	}

	/**
	 * @return array<string, string>
	 */
	private function get_section_meta_query(): array {
		if ( $this->section_hook === '' ) {
			return [];
		}

		return [
			'key'     => self::META_KEY_SECTION,
			'value'   => $this->section_hook,
			'compare' => '=',
		];
	}
}
