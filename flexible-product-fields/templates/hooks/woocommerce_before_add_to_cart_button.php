<?php
/**
 * Before Add To Cart Button
 *
 * This template can be overridden by copying it to
 * yourtheme/flexible-product-fields/hooks/woocommerce_before_add_to_cart_button.php
 *
 * @var string $fpf_nonce
 * @var string $product_id
 * @var array<string> $fields
 *
 * @author        WP Desk
 * @package       Flexible Product Fields/Templates
 * @version       1.0.0
 */

?>
<div class="fpf-fields before-add-to-cart">
	<input type="hidden" name="_fpf_nonce" value="<?php echo esc_attr( $fpf_nonce ); ?>">
	<input type="hidden" name="_fpf_product_id" value="<?php echo esc_attr( $product_id ); ?>">
	<?php foreach ( $fields as $field ) : ?>
		<?php echo $field; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<?php endforeach; ?>
	<div class="fpf-totals">
		<dl id="fpf_totals"></dl>
	</div>
</div>
