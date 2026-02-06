<?php

namespace WPDesk\FPF\Free\DTO;

use WPDesk\FPF\Free\Helper\DateFormatConverter;

class DateDTO implements DateDTOInterface {

	/**
	 * @var array<int,string>
	 */
	private array $dates;

	/**
	 * @param array<int,string> $dates
	 */
	public function __construct( array $dates = [] ) {
		$this->dates = $dates;
	}

	public function getIterator(): \Traversable {
		return new \ArrayIterator( $this->dates );
	}

	public function is_empty(): bool {
		return empty( $this->dates );
	}

	public function __toString(): string {
		$dates_delimiter = DateFormatConverter::get_dates_delimiter();
		return implode( $dates_delimiter, $this->dates );
	}

	public static function from_input( string $date_string ): self {
		$dates_delimiter = DateFormatConverter::get_dates_delimiter();
		$dates           = explode( $dates_delimiter, $date_string );
		$dates           = array_map( 'trim', $dates );
		$dates           = array_filter( $dates, fn( $date ) => ! empty( $date ) );

		return new self( $dates );
	}

	public function get_days_count(): int {
		return count( $this->dates );
	}

	public function to_cart_format(): array {
		return $this->dates;
	}

	public function get_type(): string {
		return 'date';
	}
}
