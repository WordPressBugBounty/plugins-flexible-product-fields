<?php

namespace WPDesk\FPF\Free\Settings\Form\Sanitizer;

class FieldDataSanitizer {

	/**
	 * @param array<int, array<string, mixed>> $field_data
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public function sanitize( array $field_data ): array {
		return \array_map(
			function ( $value ) {
				return $this->sanitize_field_by_type( $value );
			},
			$field_data
		);
	}

	/**
	 * @param array<string, mixed> $value
	 *
	 * @return array<string, mixed>
	 */
	private function sanitize_field_by_type( $value ) {
		$field_type = $value['type'] ?? '';

		switch ( $field_type ) {
			case 'html':
				return $this->sanitize_html_field( $value );
			default:
				return \wc_clean( $value );
		}
	}

	/**
	 * @param array<string, mixed> $value
	 *
	 * @return array<string, mixed>
	 */
	private function sanitize_html_field( array $value ): array {
		$html = $value['title'] ?? '';
		\wc_clean( $value );
		$value['title'] = \wp_kses_post( $html );
		return $value;
	}
}
