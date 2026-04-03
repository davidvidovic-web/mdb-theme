<?php
/**
 * My Account navigation
 *
 * Overrides /woocommerce/templates/myaccount/navigation.php
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.3.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Map endpoint slugs to CSS icon classes (pure CSS icons via pseudo-elements).
$mdb_nav_icons = array(
	'dashboard'       => 'mdb-icon-dashboard',
	'orders'          => 'mdb-icon-orders',
	'downloads'       => 'mdb-icon-downloads',
	'edit-address'    => 'mdb-icon-address',
	'payment-methods' => 'mdb-icon-payment',
	'edit-account'    => 'mdb-icon-account',
	'customer-logout' => 'mdb-icon-logout',
);

do_action( 'woocommerce_before_account_navigation' );
?>

<nav class="woocommerce-MyAccount-navigation mdb-account-nav" aria-label="<?php esc_html_e( 'Account pages', 'woocommerce' ); ?>">
	<ul class="mdb-account-nav__list">
		<?php foreach ( wc_get_account_menu_items() as $endpoint => $label ) :
			$icon_class = isset( $mdb_nav_icons[ $endpoint ] ) ? $mdb_nav_icons[ $endpoint ] : 'mdb-icon-default';
			$is_active  = wc_is_current_account_menu_item( $endpoint );
		?>
			<li class="<?php echo esc_attr( wc_get_account_menu_item_classes( $endpoint ) ); ?> mdb-account-nav__item">
				<a
					href="<?php echo esc_url( wc_get_account_endpoint_url( $endpoint ) ); ?>"
					class="mdb-account-nav__link<?php echo $is_active ? ' mdb-account-nav__link--active' : ''; ?> <?php echo esc_attr( $icon_class ); ?>"
					<?php echo $is_active ? 'aria-current="page"' : ''; ?>
				>
					<span class="mdb-account-nav__label"><?php echo esc_html( $label ); ?></span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</nav>

<?php do_action( 'woocommerce_after_account_navigation' ); ?>
