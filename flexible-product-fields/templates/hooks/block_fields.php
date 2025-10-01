<?php

use WPDesk\FPF\Free\Block\Settings\BlockTemplateSettings;

/**
 * This template can be overridden by copying it to
 * yourtheme/flexible-product-fields/hooks/block_fields.php
 *
 * @var string $fpf_nonce
 * @var string $product_id
 * @var BlockTemplateSettings $block_settings
 * @var array<string> $fields
 *
 * @author        WP Desk
 * @package       Flexible Product Fields/Templates
 * @version       1.0.0
 */

?>
<div class="fpf-fields">
	<input type="hidden" name="_fpf_nonce" value="<?php echo esc_attr( $fpf_nonce ); ?>">
	<input type="hidden" name="_fpf_product_id" value="<?php echo esc_attr( $product_id ); ?>">
	<input type="hidden" name="_fpf_block_template_id" value="<?php echo esc_attr( (string) $block_settings->get_template_id() ); ?>">
	<input type="hidden" name="_fpf_block_show_other_templates" value="<?php echo esc_attr( $block_settings->should_show_other_templates() ? '1' : '0' ); ?>">

	<?php foreach ( $fields as $field ) : ?>
		<?php echo $field; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<?php endforeach; ?>
	<div class="fpf-totals">
		<dl id="fpf_totals"></dl>
	</div>
</div>
