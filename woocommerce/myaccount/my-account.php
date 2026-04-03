<?php
/**
 * My Account page
 *
 * Overrides /woocommerce/templates/myaccount/my-account.php
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.5.0
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="mdb-account">

	<aside class="mdb-account__sidebar">
		<?php do_action( 'woocommerce_account_navigation' ); ?>
	</aside>

	<div class="mdb-account__content">
		<?php do_action( 'woocommerce_account_content' ); ?>
	</div>

</div>
