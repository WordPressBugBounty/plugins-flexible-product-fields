<?php
namespace WPDesk\FPF\Free\Helper;

class PluginHelper {

	/**
	 * @var bool|null
	 */
	private static $flexible_quantity_activation_status = null;

	public static function is_flexible_quantity_active(): bool {
		if ( self::$flexible_quantity_activation_status === null ) {
			self::$flexible_quantity_activation_status =
				is_plugin_active( 'flexible-quantity/flexible-quantity.php' ) ||
				is_plugin_active( 'flexible-quantity-measurement-price-calculator-for-woocommerce/flexible-quantity-measurement-price-calculator-for-woocommerce.php' );
		}
		return self::$flexible_quantity_activation_status;
	}
}
