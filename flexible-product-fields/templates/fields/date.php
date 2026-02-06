<?php
/**
 * This template can be overridden by copying it to yourtheme/flexible-product-fields/fields/date.php
 *
 * @var int             $field_group_id ID of post (post type - fpf_fields).
 * @var string          $key            Field ID.
 * @var string          $type           Field type.
 * @var mixed[]         $args           Custom attributes for field.
 * @var string          $class          CSS class name or space-separated list of classes.
 * @var DateDTO|null $value          Date.
 */

use WPDesk\FPF\Free\DTO\DateDTO;

?>
<div class="fpf-field fpf-<?php echo esc_attr( $type ); ?>">
	<?php
	$args['class'] = explode( ' ', $class );
	$args['autocomplete'] = 'off';
	woocommerce_form_field( $key, $args, (string) $value );
	?>
</div>
