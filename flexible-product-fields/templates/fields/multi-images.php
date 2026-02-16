<?php
/**
 * This template can be overridden by copying it to yourtheme/flexible-product-fields/fields/multi-images.php
 *
 * @var int             $field_group_id  ID of post (post type - fpf_fields).
 * @var string          $key             Field ID.
 * @var string          $type            Field type.
 * @var mixed[]         $args            Custom attributes for field.
 * @var string          $class           CSS class name or space-separated list of classes.
 * @var string[]        $default_checked Default checked values.
 * @var int|null        $selected_min    Minimum required selections.
 * @var int|null        $selected_max    Maximum allowed selections.
 * @var string|string[] $value           Field value.
 * @var string[]        $media_ids       Mapping: option_value => attachment_id.
 * @var int             $preview_width   Width for preview images (px).
 * @var bool            $preview_show_label Whether to display label below image.
 */

$checked_values = ( $value === null ) ? $default_checked : (array) $value;

?>
<div class="fpf-field fpf-<?php echo esc_attr( $type ); ?>">
	<fieldset class="form-row <?php echo esc_attr( $class ); ?>" id="<?php echo esc_attr( $key ); ?>_field">
		<legend>
			<?php echo wp_kses_post( $args['label'] ); ?>
			<?php if ( $selected_min || $selected_max ) : ?>
				<br>
				<span>
					<?php if ( $selected_min ) : ?>
						<?php
							echo esc_html(
								sprintf(
									/* translators: %s: minimum number of values that can be selected within multi-images field */
									__( 'Minimum: %s.', 'flexible-product-fields' ),
									$selected_min
								)
							);
						?>
					<?php endif; ?>
					<?php if ( $selected_max ) : ?>
						<?php
							echo esc_html(
								sprintf(
									/* translators: %s: maximum number of values that can be selected within multi-images field */
									__( 'Limit: %s.', 'flexible-product-fields' ),
									$selected_max
								)
							);
						?>
					<?php endif; ?>
				</span>
			<?php endif; ?>
		</legend>
		<span class="woocommerce-input-wrapper">
			<?php foreach ( $args['options'] as $option_value => $option_label ) : ?>
				<span class="fpf-multi-images__item">
					<input type="checkbox" class="input-checkbox fpf-input-field fpf-multi-images__input"
						value="<?php echo esc_html( $option_value ); ?>"
						name="<?php echo esc_attr( $key ); ?>[]"
						id="<?php echo esc_attr( $key . '_' . $option_value ); ?>"
						<?php echo in_array( $option_value, $checked_values, true ) ? 'checked' : ''; ?>
					>
					<label class="fpf-multi-images__label" for="<?php echo esc_attr( $key . '_' . $option_value ); ?>"
						title="<?php echo ( ! $preview_show_label ) ? esc_attr( strip_tags( $option_label ) ) : ''; ?>"
						style="<?php echo ( $preview_width ) ? esc_attr( "width: {$preview_width}px;" ) : ''; ?>">
						<?php echo wp_get_attachment_image( $media_ids[ $option_value ] ?? 0 ); ?>
						<?php if ( $preview_show_label ) : ?>
							<span><?php echo wp_kses_post( $option_label ); ?></span>
						<?php endif; ?>
					</label>
				</span>
			<?php endforeach; ?>
		</span>
	</fieldset>
</div>
