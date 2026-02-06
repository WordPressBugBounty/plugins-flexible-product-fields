<?php
/**
 * This template can be overridden by copying it to yourtheme/flexible-product-fields/fields/date-range.php
 *
 * @var int                 $field_group_id ID of post (post type - fpf_fields).
 * @var string              $key            Field ID.
 * @var string              $type           Field type.
 * @var mixed[]             $args           Custom attributes for field.
 * @var string              $class          CSS class name or space-separated list of classes.
 * @var DateRangeDTO|null   $value          Date range.
 */

use WPDesk\FPF\Free\DTO\DateRangeDTO;

$value = $value instanceof DateRangeDTO ? $value : new DateRangeDTO();
?>
<style>
	.load-datepicker {
		width: 50%;
	}
</style>
<div class="fpf-field fpf-<?php echo esc_attr( $type ); ?> fpf-date-range-container" data-date-range-field="true" data-field-name="<?php echo esc_attr( $key ); ?>">

	<div class="fpf-date-range-inputs" style="display: flex; flex-direction: row;">
	<?php
	// args for start date input
	$start_args                  = $args;
	$start_args['label']         = $args['label'] . ' ' . esc_html__( '(Start)', 'flexible-product-fields' );
	$start_args['id']            = $key . '_start';
	$start_args['name']          = $key . '[start]';
	$start_args['input_class'][] = 'fpf-date-input-start';
	$start_args['input_class'][] = 'load-datepicker-range';
	$start_args['autocomplete']  = 'off';

	// args for end date input
	$end_args                  = $args;
	$end_args['label']         = $args['label'] . ' ' . esc_html__( '(End)', 'flexible-product-fields' );
	$end_args['id']            = $key . '_end';
	$end_args['name']          = $key . '[end]';
	$end_args['input_class'][] = 'fpf-date-input-end';
	$end_args['input_class'][] = 'load-datepicker-range';
	$end_args['autocomplete']  = 'off';

	woocommerce_form_field( $start_args['name'], $start_args, $value->get_start_date() );

	woocommerce_form_field( $end_args['name'], $end_args, $value->get_end_date() );
	?>
	</div>
</div>
