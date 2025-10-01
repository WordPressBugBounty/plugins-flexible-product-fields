<?php

use WPDesk\FPF\Free\Block\Settings\BlockTemplateSettings;

/**
 * Server-side rendering of the `fpf/template-selector` block.
 *
 * @var array    $attributes Block attributes.
 * @var string   $content    Block default content.
 * @var WP_Block $block      Block instance.
 */

$template_id       = (int) ( $attributes['templateId'] ?? 0 );
$show_other_fields = $attributes['showOtherFields'] ?? true;

if ( $template_id === 0 ) {
	return;
}

$fpf_plugin  = $block->context['fpf_plugin'] ?? null;
if ( ! $fpf_plugin instanceof \Flexible_Product_Fields_Plugin ) {
	return;
}

$fpf_product = $fpf_plugin->get_fpf_product();

global $product;

$block_settings = new BlockTemplateSettings( $template_id, $show_other_fields );

$wrapper_attributes = get_block_wrapper_attributes();
?>
<div class="wp-block-fpf-template-selector">
	<?php
		echo $fpf_product->render_fields( $product, $block_settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	?>
</div>
