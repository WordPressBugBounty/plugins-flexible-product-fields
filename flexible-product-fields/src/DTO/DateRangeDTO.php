<?php

namespace WPDesk\FPF\Free\DTO;

use WPDesk\FPF\Free\DTO\DateDTOInterface;

class DateRangeDTO implements DateDTOInterface {

	private string $start_date;

	private string $end_date;

	public function __construct( string $start_date = '', string $end_date = '' ) {
		$this->start_date = $start_date;
		$this->end_date   = $end_date;
	}

	public function getIterator(): \Traversable {
		return new \ArrayIterator( [ $this->start_date, $this->end_date ] );
	}

	public function is_empty(): bool {
		return empty( $this->start_date ) && empty( $this->end_date );
	}

	public function __toString(): string {
		return $this->start_date . ' - ' . $this->end_date;
	}

	public function get_start_date(): string {
		return $this->start_date;
	}

	public function get_end_date(): string {
		return $this->end_date;
	}

	public function to_cart_format(): array {
		return [ $this->start_date, $this->end_date ];
	}

	public static function from_input( string $date_start, string $date_end ): self {
		return new self( $date_start, $date_end );
	}

	public function get_type(): string {
		return 'date_range';
	}
}
