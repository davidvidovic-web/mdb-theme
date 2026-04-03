<?php
/**
 * My Addresses
 *
 * Overrides /woocommerce/templates/myaccount/my-address.php
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.3.0
 */

defined( 'ABSPATH' ) || exit;

$customer_id = get_current_user_id();

if ( ! wc_ship_to_billing_address_only() && wc_shipping_enabled() ) {
	$get_addresses = apply_filters(
		'woocommerce_my_account_get_addresses',
		array(
			'billing'  => __( 'Billing address', 'woocommerce' ),
			'shipping' => __( 'Shipping address', 'woocommerce' ),
		),
		$customer_id
	);
} else {
	$get_addresses = apply_filters(
		'woocommerce_my_account_get_addresses',
		array(
			'billing' => __( 'Billing address', 'woocommerce' ),
		),
		$customer_id
	);
}
?>

<p class="mdb-address-intro">
	<?php echo esc_html( apply_filters( 'woocommerce_my_account_my_address_description', __( 'The following addresses will be used on the checkout page by default.', 'woocommerce' ) ) ); ?>
</p>

<div class="mdb-address-grid">

	<?php foreach ( $get_addresses as $name => $address_title ) :
		$address = wc_get_account_formatted_address( $name );
	?>

		<div class="mdb-address-card woocommerce-Address <?php echo esc_attr( "mdb-address-card--{$name}" ); ?>">

			<header class="mdb-address-card__header woocommerce-Address-title">
				<h2 class="mdb-address-card__title"><?php echo esc_html( $address_title ); ?></h2>
				<a
					href="<?php echo esc_url( wc_get_endpoint_url( 'edit-address', $name ) ); ?>"
					class="mdb-address-card__edit edit"
					aria-label="<?php echo $address ? esc_attr( sprintf( __( 'Edit %s', 'woocommerce' ), $address_title ) ) : esc_attr( sprintf( __( 'Add %s', 'woocommerce' ), $address_title ) ); ?>"
				>
					<?php echo $address ? esc_html__( 'Edit', 'woocommerce' ) : esc_html__( 'Add', 'woocommerce' ); ?>
				</a>
			</header>

			<address class="mdb-address-card__address">
				<?php if ( $address ) : ?>
					<?php echo wp_kses_post( $address ); ?>
				<?php else : ?>
					<span class="mdb-address-card__empty">
						<?php esc_html_e( 'You have not set up this type of address yet.', 'woocommerce' ); ?>
					</span>
				<?php endif; ?>
			</address>

			<?php
			/**
			 * Hook: woocommerce_after_my_account_address
			 *
			 * @param string $name Address type.
			 */
			do_action( 'woocommerce_after_my_account_address', $name );
			?>

		</div>

	<?php endforeach; ?>

</div>
