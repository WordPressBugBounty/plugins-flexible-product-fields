<?php

namespace WPDesk\FPF\Free\Settings\Route;

/**
 * {@inheritdoc}
 */
class ProductsRoute extends RouteAbstract implements RouteInterface {

	const REST_API_ROUTE = 'products';

	/**
	 * {@inheritdoc}
	 */
	public function get_endpoint_route(): string {
		return self::REST_API_ROUTE;
	}

	/**
	 * @return string[]
	 */
	protected function get_post_types(): array {
		return [ 'product' ];
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_endpoint_response( array $params ) {
		$args = [
			'posts_per_page' => -1,
			'post_type'      => $this->get_post_types(),
			'post_status'    => [ 'publish', 'draft' ],
			'orderby'        => 'title',
			'order'          => 'ASC',
			'lang'           => '',
		];

		$field_search = $params['field_search'] ?? '';
		$field_values = $params['field_values'] ?? [];

		if ( $field_search !== '' ) {
			$args['s'] = $field_search;
		} elseif ( $field_values ) {
			$args['post__in'] = $field_values;
		}

		$posts  = get_posts( $args );
		$values = [];
		foreach ( $posts as $post ) {
			$values[ $post->ID ] = sprintf( '%s (#%d)', $post->post_title, $post->ID );
		}

		return $values;
	}
}
