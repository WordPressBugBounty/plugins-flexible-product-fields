<?php

namespace WPDesk\FPF\Free\Settings\Form;

/**
 * Interface for form settings.
 */
interface FormInterface {

	/**
	 * Returns type of form.
	 *
	 * @return string Type of form.
	 */
	public function get_form_type(): string;

	/**
	 * Returns basic settings for form.
	 *
	 * @param array<string, mixed> $form_data .
	 * @param \WP_Post $post      .
	 *
	 * @return array<string, mixed> Settings of form.
	 */
	public function get_form_data( array $form_data, \WP_Post $post ): array;

	/**
	 * .
	 */
	public function get_posted_data(): array;

	/**
	 * @param \WP_Post $post .
	 *
	 * @return void
	 */
	public function save_form_data( \WP_Post $post );

	/**
	 * Returns list of option objects.
	 *
	 * @return \WPDesk\FPF\Free\Settings\Option\OptionInterface[] List of options.
	 */
	public function get_options_list(): array;
}
