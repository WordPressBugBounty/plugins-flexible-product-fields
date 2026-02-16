<?php
/**
 * This template can be overridden by copying it to yourtheme/flexible-product-fields/fields/toggle.php
 *
 * @var int             $field_group_id  ID of post (post type - fpf_fields).
 * @var string          $key             Field ID.
 * @var string          $type            Field type.
 * @var mixed[]         $args            Custom attributes for field.
 * @var string          $class           CSS class name or space-separated list of classes.
 * @var string|null     $default_checked .
 * @var string|null     $value           Field value.
 */

$checked_by_default = filter_var( $default_checked ?? '', FILTER_VALIDATE_BOOLEAN );
?>

<div class="fpf-field fpf-field-toggle fpf-<?php echo esc_attr( $type ); ?>">
	<p class="form-row <?php echo esc_attr( $class ); ?>" id="<?php echo esc_attr( $key ); ?>_field">
		<label for="<?php echo esc_attr( $key ); ?>">
			<input type="checkbox"
				id="<?php echo esc_attr( $key ); ?>"
				class="fpf-input-field"
				name="<?php echo esc_attr( $key ); ?>"
				value="1"
				<?php echo $checked_by_default ? 'checked' : checked( $value, '1', false ); ?>>
			<span class="fpf-toggle-slider"></span>
			<span class="fpf-toggle-label">
				<?php echo wp_kses_post( $args['label'] ); ?>
			</span>
		</label>
	</p>
</div>
