<?php

namespace WPDesk\FPF\Free\DTO;

interface DateDTOInterface extends \IteratorAggregate {

	public function is_empty(): bool;

	/**
	 * @return array<mixed>
	 */
	public function to_cart_format(): array;

	/**
	 * Defines how to display date data in the cart.
	 */
	public function __toString(): string;

	public function get_type(): string;
}
