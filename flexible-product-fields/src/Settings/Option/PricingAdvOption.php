<?php

namespace WPDesk\FPF\Free\Settings\Option;

use WPDesk\FPF\Free\Settings\Tab\PricingTab;

/**
 * {@inheritdoc}
 */
class PricingAdvOption extends OptionAbstract {

	const FIELD_NAME = 'pricing_adv';

	/**
	 * {@inheritdoc}
	 */
	public function get_option_name(): string {
		return self::FIELD_NAME;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_option_tab(): string {
		return PricingTab::TAB_NAME;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_option_type(): string {
		return self::FIELD_TYPE_INFO_ADV;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_option_label(): string {
		$url_upgrade_pro = esc_url( apply_filters( 'flexible_product_fields/short_url', '#', 'fpf-settings-option-field-price-upgrade-link' ) );
		return sprintf(
		/* translators: %1$s and %3$s: anchor opening tags, %2$s and %4$s: anchor closing tags */
			__( 'Price settings are available in the PRO version. %1$sUpgrade to PRO%2$s', 'flexible-product-fields' ),
			'<a target="_blank" style="color:#003399;" class="fpfArrowLink" href="' . $url_upgrade_pro . '"><span class="dashicons dashicons-star-filled" style="font-size: 13px;vertical-align: bottom;"></span>',
			'</a>'
		);
	}
}
