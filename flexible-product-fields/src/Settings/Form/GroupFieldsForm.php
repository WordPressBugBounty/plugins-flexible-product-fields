<?php

namespace WPDesk\FPF\Free\Settings\Form;

use WPDesk\FPF\Free\Field\FieldData;
use WPDesk\FPF\Free\Settings\Option\FieldNameOption;
use WPDesk\FPF\Free\Settings\Form\Sanitizer\FieldDataSanitizer;

/**
 * {@inheritdoc}
 */
class GroupFieldsForm implements FormInterface {

	private FieldDataSanitizer $sanitizer;

	const FORM_TYPE          = 'fields';
	const FORM_FIELD_NAME    = 'fpf_fields';
	const SETTINGS_POST_META = '_fields';

	public function __construct() {
		$this->sanitizer = new FieldDataSanitizer();
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_form_type(): string {
		return self::FORM_TYPE;
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public function get_posted_data(): array {
		if ( ! isset( $_POST[ self::FORM_FIELD_NAME ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce verified in src/Settings/Page.php
			return [];
		}

		$posted_data = \json_decode( \wp_unslash( $_POST[ self::FORM_FIELD_NAME ] ), true ); // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Nonce verified in src/Settings/Page.php, Sanitization is done in the next line

		if ( json_last_error() !== JSON_ERROR_NONE ) {
			return [];
		}

		return $this->sanitizer->sanitize( $posted_data );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_form_data( array $form_data, \WP_Post $post ): array {
		$section_fields = get_post_meta( $post->ID, self::SETTINGS_POST_META, true ) ?: [];
		if ( ! $section_fields ) {
			return $form_data;
		}

		foreach ( $section_fields as $field_name => $field_data ) {
			$new_field_data = FieldData::get_field_data( $field_data );
			if ( ! $new_field_data ) {
				continue;
			}
			$form_data[ $field_name ] = $new_field_data;
		}
		return $form_data;
	}

	/**
	 * {@inheritdoc}
	 */
	public function save_form_data( \WP_Post $post ) {
		$values = $this->get_posted_data();
		if ( count( $values ) === 0 ) {
			return;
		}
		$posted_fields = [];
		foreach ( $values as $field ) {
			$posted_fields[ $field[ FieldNameOption::FIELD_NAME ] ] = $field;
		}

		$section_fields = [];
		foreach ( $posted_fields as $field_data ) {
			$new_field_data = FieldData::get_field_data( $field_data, false );
			if ( ! $new_field_data ) {
				continue;
			}
			$section_fields[] = $new_field_data;
		}

		update_post_meta( $post->ID, self::SETTINGS_POST_META, $section_fields );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_options_list(): array {
		return [];
	}
}
