<?php

declare( strict_types=1 );

namespace WPDesk\FPF\Free\Validation;

/**
 * Accumulates validation errors from multiple rules for a single field.
 */
final class ValidationResult {

	/**
	 * @var ValidationError[]
	 */
	private array $errors = [];

	public function add_error( string $rule_id, string $message ): void {
		$this->errors[] = new ValidationError( $rule_id, $message );
	}

	public function is_valid(): bool {
		return count( $this->errors ) === 0;
	}

	/**
	 * @return ValidationError[]
	 */
	public function get_errors(): array {
		return $this->errors;
	}

	public function merge( self $result ): self {
		foreach ( $result->errors as $error ) {
			$this->errors[] = $error;
		}

		return $this;
	}

	/**
	 * @return string[]
	 */
	public function get_messages(): array {
		return array_map(
			static fn( ValidationError $error ) => $error->get_message(),
			$this->errors
		);
	}
}
