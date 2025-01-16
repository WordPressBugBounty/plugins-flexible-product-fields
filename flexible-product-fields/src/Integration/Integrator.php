<?php

namespace WPDesk\FPF\Free\Integration;

use FPF_Product;

/**
 * .
 */
class Integrator implements IntegratorInterface {

	/**
	 * Major version of integration script.
	 *
	 * @var int
	 */
	const INTEGRATOR_VERSION = 1000;

	/**
	 * Version of plugin.
	 *
	 * @var string
	 */
	private $version_plugin = FLEXIBLE_PRODUCT_FIELDS_VERSION;

	/**
	 * Version of plugin core (for compatibility with dependent plugins).
	 *
	 * @var string
	 */
	private $version_dev = FLEXIBLE_PRODUCT_FIELDS_VERSION_DEV;

	private FPF_Product $fpf_product;

	public function __construct( FPF_Product $fpf_product ) {
		$this->fpf_product = $fpf_product;
	}

	public function get_fpf_product(): FPF_Product {
		return $this->fpf_product;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_version(): string {
		$version_major = explode( '.', $this->version_plugin )[0];
		$version_minor = explode( '.', $this->version_plugin )[1];
		$version_patch = explode( '.', $this->version_plugin )[2];

		return sprintf(
			'%d.%d.%d',
			self::INTEGRATOR_VERSION,
			( ( $version_major * 1000 ) + $version_minor ),
			$version_patch
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_version_dev(): string {
		$version_dev_major = explode( '.', $this->version_dev )[0];
		$version_dev_minor = explode( '.', $this->version_dev )[1];
		$version_major     = explode( '.', $this->version_plugin )[0];
		$version_minor     = explode( '.', $this->version_plugin )[1];

		return sprintf(
			'%d.%d.%d',
			$version_dev_major,
			$version_dev_minor,
			( ( $version_major * 1000 ) + $version_minor )
		);
	}
}
