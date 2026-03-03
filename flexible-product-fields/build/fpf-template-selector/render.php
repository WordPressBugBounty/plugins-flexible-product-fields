<?php

use WPDesk\FPF\Free\Block\Settings\BlockTemplateSettings;
use WPDesk\FPF\Free\Service\FieldsDisplayerInterface;

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

$fields_displayer = $block->context['fields_displayer'] ?? null;
if ( ! $fields_displayer instanceof FieldsDisplayerInterface ) {
	return;
}

global $product;

$block_settings = new BlockTemplateSettings( $template_id, $show_other_fields );

?>
<div class="wp-block-fpf-template-selector">
	<?php
		echo $fields_displayer->get_fields_for_block( $product, $block_settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	?>
</div>
