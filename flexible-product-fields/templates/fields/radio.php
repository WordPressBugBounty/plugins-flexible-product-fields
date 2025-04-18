<?php
/**
 * This template can be overridden by copying it to yourtheme/flexible-product-fields/fields/radio.php
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

$value = ( ( $value === null ) && ( ( $args['default'] ?? '' ) !== '' ) ) ? $args['default'] : $value;

?>
<div class="fpf-field fpf-<?php echo esc_attr( $type ); ?>">
	<fieldset class="form-row <?php echo esc_attr( $class ); ?>" id="<?php echo esc_attr( $key ); ?>_field">
		<legend><?php echo wp_kses_post( $args['label'] ); ?></legend>
		<?php foreach ( $args['options'] as $option_value => $option_label ) : ?>
			<label for="<?php echo esc_attr( $key . '_' . $option_value ); ?>">
				<input type="radio" class="input-radio fpf-input-field"
					value="<?php echo esc_html( $option_value ); ?>"
					name="<?php echo esc_attr( $key ); ?>"
					id="<?php echo esc_attr( $key . '_' . $option_value ); ?>"
					data-image-props="<?php echo isset( $options_image_props[ $option_value ] ) ? esc_attr( wc_esc_json( wp_json_encode( $options_image_props[ $option_value ] ) ) ) : ''; ?>"
					<?php checked( (string) $option_value, $value ); ?>
				>
				<?php echo wp_kses_post( $option_label ); ?>
			</label>
		<?php endforeach; ?>
	</fieldset>
</div>
