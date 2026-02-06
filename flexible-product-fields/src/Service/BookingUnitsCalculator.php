<?php

namespace WPDesk\FPF\Free\Service;

use WPDesk\FPF\Free\DTO\DateDTO;
use WPDesk\FPF\Free\DTO\DateRangeDTO;
use WPDesk\FPF\Free\DTO\TimeSlotsDTO;
use WPDesk\FPF\Free\DTO\DateDTOInterface;
use DateTime;

class BookingUnitsCalculator {

	public static function calculate( DateDTOInterface $dto, string $date_format, bool $allow_adjacent_bookings ): int {
		if ( $dto instanceof DateDTO ) {
			return $dto->get_days_count();
		}

		if ( $dto instanceof DateRangeDTO ) {
			return self::calculate_for_date_range( $dto, $date_format, $allow_adjacent_bookings );
		}

		if ( $dto instanceof TimeSlotsDTO ) {
			return $dto->get_slots_count();
		}

		return 0;
	}

	private static function calculate_for_date_range( DateRangeDTO $dto, string $date_format, bool $allow_adjacent_bookings ): int {
		$start = $dto->get_start_date();
		$end   = $dto->get_end_date();

		if ( empty( $start ) || empty( $end ) ) {
			return 0;
		}

		$start_datetime = \DateTime::createFromFormat( $date_format, $start );
		if ( $start_datetime === false ) {
			return 0;
		}
		$end_datetime = \DateTime::createFromFormat( $date_format, $end );
		if ( $end_datetime === false ) {
			return 0;
		}

		$days_in_range = self::get_days_in_range( $start_datetime, $end_datetime );
		if ( $days_in_range === 0 ) {
			return 0;
		}

		if ( $allow_adjacent_bookings ) {
			// should pay for nights only.
			return $days_in_range - 1;
		}

		return $days_in_range;
	}

	private static function get_days_in_range( DateTime $start, DateTime $end ): int {
		$start_timestamp = $start->getTimestamp();
		$end_timestamp   = $end->getTimestamp();

		// add 1 to include both the start and end dates in the count.
		return (int) floor( ( $end_timestamp - $start_timestamp ) / DAY_IN_SECONDS ) + 1;
	}
}
