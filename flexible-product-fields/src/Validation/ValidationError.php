<?php

declare( strict_types=1 );

namespace WPDesk\FPF\Free\Validation;

/**
 * Represents a single validation error with its rule identifier and message.
 */
final class ValidationError {

	private string $rule_id;

	private string $message;

	public function __construct( string $rule_id, string $message ) {
		$this->rule_id = $rule_id;
		$this->message = $message;
	}

	public function get_rule_id(): string {
		return $this->rule_id;
	}

	public function get_message(): string {
		return $this->message;
	}

	public function set_message( string $message ): void {
		$this->message = $message;
	}
}
