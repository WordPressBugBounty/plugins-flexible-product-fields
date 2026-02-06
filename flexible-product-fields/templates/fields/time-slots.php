<?php
/**
 * Time Slots field template.
 *
 * This template can be overridden by copying it to yourtheme/flexible-product-fields/fields/time-slots.php
 *
 * @var int                 $field_group_id ID of post (post type - fpf_fields).
 * @var string              $key            Field ID.
 * @var string              $type           Field type.
 * @var mixed[]             $args           Custom attributes for field.
 * @var string              $class          CSS class name or space-separated list of classes.
 * @var TimeSlotsDTO|null   $value          Time slots.
 * @var array<string,mixed> $field_data   Field data.
 */

use WPDesk\FPF\Free\DTO\TimeSlotsDTO;

global $product;
$product_id = $product instanceof WC_Product ? $product->get_id() : 0;

$value = $value ?? new TimeSlotsDTO();
$args['class'][]      = 'load-datepicker';
$args['id']           = esc_attr( 'fpf-date-input-' . $key );
$args['autocomplete'] = 'off';
$args['type']         = 'text';

woocommerce_form_field( $key, $args, $value->get_date() );
?>
<div class="fpf-field fpf-<?php echo esc_attr( $type ); ?>" id="fpf-time-slots-<?php echo esc_attr( $key ); ?>">
	<div id="fpf-time-slots-react-<?php echo esc_attr( $key ); ?>"
		class="fpf-time-slots-mount"
		data-field-data="<?php echo esc_attr( wp_json_encode( $field_data ) ); ?>"
		data-field-value="<?php echo esc_attr( $value->to_json() ); ?>"
		data-product-id="<?php echo esc_attr( (string) $product_id ); ?>">
	</div>
</div>
