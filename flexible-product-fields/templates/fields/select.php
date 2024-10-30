<?php
/**
 * This template can be overridden by copying it to yourtheme/flexible-product-fields/fields/select.php
 *
 * @var int                  $field_group_id ID of post (post type - fpf_fields).
 * @var string               $key            Field ID.
 * @var string               $type           Field type.
 * @var mixed[]              $args           Custom attributes for field.
 * @var string               $class          CSS class name or space-separated list of classes.
 * @var string|string[]      $value          Field value.
 * @var array<string, mixed> $options_image_props Array of image properties.
 *
 * @package Flexible Product Fields
 */

?>
<div class="fpf-field fpf-<?php echo esc_attr( $type ); ?>">
	<?php
	$args['class']             = explode( ' ', $class );
	$args['return']            = true;
	$args['custom_attributes'] = array_merge(
		$args['custom_attributes'],
		[ 'data-image-props' => esc_attr( wc_esc_json( wp_json_encode( $options_image_props ) ) ) ]
	);

	$output = woocommerce_form_field( $key, $args, $value );
	$output = str_replace(
		'<option value="" ',
		'<option value="" disabled ',
		$output
	);
	echo $output; // phpcs:ignore
	?>
</div>
