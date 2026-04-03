<?php
/**
 * My Account Dashboard
 *
 * Overrides /woocommerce/templates/myaccount/dashboard.php
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 4.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$allowed_html = array(
	'a' => array(
		'href' => array(),
	),
);
?>

<div class="mdb-account-dashboard">

	<p class="mdb-account-dashboard__greeting">
		<?php
		printf(
			/* translators: 1: user display name 2: logout url */
			wp_kses( __( 'Hello, %1$s. (Not %1$s? <a href="%2$s">Log out</a>)', 'woocommerce' ), $allowed_html ),
			'<strong>' . esc_html( $current_user->display_name ) . '</strong>',
			esc_url( wc_logout_url() )
		);
		?>
	</p>

	<p class="mdb-account-dashboard__intro">
		<?php
		$dashboard_desc = __( 'From your account dashboard you can view your <a href="%1$s">recent orders</a>, manage your <a href="%2$s">billing address</a>, and <a href="%3$s">edit your password and account details</a>.', 'woocommerce' );
		if ( wc_shipping_enabled() ) {
			/* translators: 1: Orders URL 2: Addresses URL 3: Account URL. */
			$dashboard_desc = __( 'From your account dashboard you can view your <a href="%1$s">recent orders</a>, manage your <a href="%2$s">shipping and billing addresses</a>, and <a href="%3$s">edit your password and account details</a>.', 'woocommerce' );
		}
		printf(
			wp_kses( $dashboard_desc, $allowed_html ),
			esc_url( wc_get_account_endpoint_url( 'orders' ) ),
			esc_url( wc_get_account_endpoint_url( 'edit-address' ) ),
			esc_url( wc_get_account_endpoint_url( 'edit-account' ) )
		);
		?>
	</p>

	<div class="mdb-account-dashboard__quick-links">

		<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'orders' ) ); ?>" class="mdb-account-quicklink">
			<span class="mdb-account-quicklink__icon mdb-icon-orders" aria-hidden="true"></span>
			<span class="mdb-account-quicklink__label"><?php esc_html_e( 'Orders', 'woocommerce' ); ?></span>
		</a>

		<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'edit-address' ) ); ?>" class="mdb-account-quicklink">
			<span class="mdb-account-quicklink__icon mdb-icon-address" aria-hidden="true"></span>
			<span class="mdb-account-quicklink__label"><?php esc_html_e( 'Addresses', 'woocommerce' ); ?></span>
		</a>

		<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'edit-account' ) ); ?>" class="mdb-account-quicklink">
			<span class="mdb-account-quicklink__icon mdb-icon-account" aria-hidden="true"></span>
			<span class="mdb-account-quicklink__label"><?php esc_html_e( 'Account Details', 'woocommerce' ); ?></span>
		</a>

		<?php if ( wc_get_account_endpoint_url( 'downloads' ) ) : ?>
		<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'downloads' ) ); ?>" class="mdb-account-quicklink">
			<span class="mdb-account-quicklink__icon mdb-icon-downloads" aria-hidden="true"></span>
			<span class="mdb-account-quicklink__label"><?php esc_html_e( 'Downloads', 'woocommerce' ); ?></span>
		</a>
		<?php endif; ?>

	</div>

</div>

<?php
/**
 * My Account dashboard.
 *
 * @since 2.6.0
 */
do_action( 'woocommerce_account_dashboard' );

/**
 * Deprecated woocommerce_before_my_account action.
 *
 * @deprecated 2.6.0
 */
do_action( 'woocommerce_before_my_account' );

/**
 * Deprecated woocommerce_after_my_account action.
 *
 * @deprecated 2.6.0
 */
do_action( 'woocommerce_after_my_account' );
