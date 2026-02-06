<?php

namespace WPDesk\FPF\Free\DTO;

class TimeSlotsDTO implements DateDTOInterface {

	/**
	 * @var array<int,array<string,mixed>>
	 */
	private array $slots;

	/**
	 * @param array<int,array<string,mixed>> $slots
	 */
	public function __construct( array $slots = [] ) {
		$this->slots = $slots;
	}

	public function getIterator(): \Traversable {
		$dates = array_map( fn( $slot ) => $slot['date'] ?? '', $this->slots );
		return new \ArrayIterator( $dates );
	}

	public function is_empty(): bool {
		return empty( $this->slots );
	}

	public function get_slots_count(): int {
		return count( $this->slots );
	}

	public function to_json(): string {
		return wp_json_encode( $this->slots );
	}

	public function get_date(): string {
		return $this->slots[0]['date'] ?? '';
	}

	/**
	 * @return array<int,array<string,mixed>>
	 */
	public function to_cart_format(): array {
		return $this->slots;
	}

	public function __toString(): string {
		if ( $this->is_empty() ) {
			return '';
		}

		return implode(
			' | ',
			array_map(
				function ( $slot ) {
					$date       = $slot['date'] ?? '';
					$start_time = $slot['start_time'] ?? '';
					$end_time   = $slot['end_time'] ?? '';

					return $date . '(' . $start_time . '-' . $end_time . ')';
				},
				$this->slots
			)
		);
	}

	/**
	 * @param array<int,array<string,mixed>> $time_slots
	 */
	public static function from_input( array $time_slots ): self {
		return new self( $time_slots );
	}

	public function get_type(): string {
		return 'time_slots';
	}
}
