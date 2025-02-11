<?php

namespace WPDesk\FPF\Free\Settings;

use WC_Data_Store;
use WC_Product;
use VendorFPF\WPDesk\PluginBuilder\Plugin\Hookable;
use WPDesk\FPF\Free\Service\TemplateFinder\TemplateFinder;

/**
 * Filters for FPF fields post type listings.
 */
class FieldListSearchModifier implements Hookable {

	private TemplateFinder $template_finder;

	const ASSIGN_TO_META  = '_assign_to';
	const PRODUCT_ID_META = '_product_id';
	const FPF_POST_TYPE   = 'fpf_fields';

	public function __construct( TemplateFinder $template_finder ) {
		$this->template_finder = $template_finder;
	}

	public function hooks() {
		\add_filter( 'posts_where', [ $this, 'where_clause' ], 10, 2 );
	}

	/**
	 * Add where clauses to the search query.
	 *
	 * @param string   $where The WHERE clause of the query.
	 * @param \WP_Query $query
	 *
	 * @return string
	 */
	public function where_clause( $where, $query ): string {
		if (
			! \is_admin() ||
			! $query->is_main_query() ||
			$query->get( 'post_type' ) !== self::FPF_POST_TYPE ||
			$query->get( 's' ) === ''
		) {
			return $where;
		}

		$search_term = $query->get( 's' );

		$product_condition = $this->get_product_condition( $search_term );

		// if no product was found, leave query as it was.
		if ( $product_condition === '' ) {
			return $where;
		}

		global $wpdb;

		$search_condition = $wpdb->prepare(
			"({$wpdb->posts}.post_title LIKE %s OR {$wpdb->posts}.post_excerpt LIKE %s OR {$wpdb->posts}.post_content LIKE %s)",
			'%' . $wpdb->esc_like( $search_term ) . '%',
			'%' . $wpdb->esc_like( $search_term ) . '%',
			'%' . $wpdb->esc_like( $search_term ) . '%'
		);

		$post_type_condition = $wpdb->prepare(
			"{$wpdb->posts} . post_type = %s",
			self::FPF_POST_TYPE
		);

		// We could not create such condition in the WP_Query (OR between search and meta query).
		return " AND {$post_type_condition} AND ( {$search_condition} OR {$product_condition} )";
	}

	/**
	 * If there is only one product in search results, return sql condition for this product.
	 */
	private function get_product_condition( string $search_term ): string {
		$product_ids = $this->find_products(
			\sanitize_text_field( \wp_unslash( $search_term ) )
		);

		// We are showing field groups for a product only if there is one product in the search results.
		if ( count( $product_ids ) !== 1 ) {
			return '';
		}

		$product = \wc_get_product( reset( $product_ids ) );
		if ( ! $product instanceof WC_Product ) {
			return '';
		}

		$template_ids = $this->get_template_ids( $product );
		if ( count( $template_ids ) === 0 ) {
			return '';
		}

		global $wpdb;

		$product_ids_list = implode(
			',',
			array_map(
				fn ( $template_id ) => $wpdb->prepare( '%s', $template_id ),
				$template_ids
			)
		);

		return " ( {$wpdb->posts}.ID IN ({$product_ids_list}) )";
	}

	/**
	 * @return int[]
	 */
	private function get_template_ids( WC_Product $product ): array {
		$templates = $this->template_finder->find( $product );

		return array_map(
			fn( $template ) => $template->ID,
			$templates->get_posts()
		);
	}

	/**
	 * Get products by search query.
	 *
	 * @return int[] product ids
	 */
	private function find_products( string $search ): array {
		$data_store = WC_Data_Store::load( 'product' );
		if ( $data_store->get_current_class_name() !== 'WC_Product_Data_Store_CPT' ) {
			return [];
		}

		/** @phpstan-ignore method.notFound (phpstan do not know that this object has search_products method, but we do) */
		$product_ids = $data_store->search_products(
			$search,
			'',
			true, // Include variations.
			true
		);

		return array_filter(
			$product_ids,
			fn( $product_id ) => $product_id > 0
		);
	}
}
