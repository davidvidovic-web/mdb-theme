<?php
/**
 * Show notices (info)
 *
 * Overrides /woocommerce/templates/notices/notice.php
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 10.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! $notices ) {
	return;
}

foreach ( $notices as $notice ) : ?>
	<div class="woocommerce-info mdb-toast mdb-toast--info"<?php echo wc_get_notice_data_attr( $notice ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?> role="status" aria-live="polite">
		<span class="mdb-toast__icon" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg></span>
		<span class="mdb-toast__body"><?php echo wc_kses_notice( $notice['notice'] ); ?></span>
		<button class="mdb-toast__close" type="button" aria-label="<?php esc_attr_e( 'Dismiss', 'woocommerce' ); ?>"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#444d62" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
	</div>
<?php endforeach;
