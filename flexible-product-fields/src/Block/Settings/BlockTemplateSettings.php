<?php

namespace WPDesk\FPF\Free\Block\Settings;

/**
 * Manages settings for a template rendered via a Gutenberg block.
 */
class BlockTemplateSettings {

	private int $template_id;

	private bool $show_other_templates;

	public function __construct( int $template_id = 0, bool $show_other_templates = false ) {
		$this->template_id          = $template_id;
		$this->show_other_templates = $show_other_templates;
	}

	public function get_template_id(): int {
		return $this->template_id;
	}

	public function should_show_other_templates(): bool {
		return $this->show_other_templates;
	}
}
