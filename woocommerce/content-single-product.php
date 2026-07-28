<?php
/**
 * The template for displaying product content on the single-product page.
 *
 * Overrides WooCommerce's default to provide a two-column configurator layout:
 *   Left  — CPO options form (all steps visible, scrollable).
 *   Right — Sticky order summary sidebar.
 *
 * CPO's <form class="cart"> stays fully rendered and functional. The JS layer
 * (assets/js/configurator.js) enhances it with step headers and populates the
 * sidebar. All CPO show/hide, validation, and price calculations work natively.
 *
 * @see         https://woocommerce.com/document/template-structure/
 * @package     MDB_Theme/WooCommerce/Templates
 * @version     3.6.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Always resolve $product from the current post ID rather than relying
 * solely on the global set by WooCommerce's the_post action.  This
 * prevents stale globals (object-cache races, unusual theme loops, etc.)
 * from causing CPO to render another product's fields.
 */
$product = wc_get_product( get_the_ID() );
if ( ! $product ) {
	return;
}
// Make sure the WC global used by CPO and other hooks inside this template
// matches the freshly resolved object above.
$GLOBALS['product'] = $product;

/**
 * Hook: woocommerce_before_single_product.
 *
 * @hooked WC_Structured_Data::generate_product_data() - 10
 */
do_action( 'woocommerce_before_single_product' );

if ( post_password_required() ) {
	echo get_the_password_form(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	return;
}

// Keep WooCommerce's native product layout inside CPO builder view.
if ( function_exists( 'mdb_is_cpo_builder_request' ) && mdb_is_cpo_builder_request() ) {
	?>
	<div id="product-<?php the_ID(); ?>" <?php wc_product_class( '', $product ); ?>>

		<?php do_action( 'woocommerce_before_single_product_summary' ); ?>

		<div class="summary entry-summary">
			<?php do_action( 'woocommerce_single_product_summary' ); ?>
		</div>

		<?php do_action( 'woocommerce_after_single_product_summary' ); ?>

	</div>
	<?php
	do_action( 'woocommerce_after_single_product' );
	return;
}

/**
 * Remove default WooCommerce summary hooks that we handle ourselves in the
 * sidebar or that don't belong in this layout. Only
 * woocommerce_template_single_add_to_cart (priority 30) will remain,
 * which also fires CPO's form via woocommerce_before_add_to_cart_button.
 */
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title',   5  );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating',  10 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price',   10 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta',    40 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );
?>
<div id="product-<?php the_ID(); ?>" <?php wc_product_class( 'mdb-product-wrapper', $product ); ?>>

	<?php
	/**
	 * Hook: woocommerce_before_single_product_summary.
	 *
	 * @hooked mdb_single_product_back_link        - 5  (← Return to Selection breadcrumb)
	 * @hooked woocommerce_show_product_images     - 20 (gallery hidden via CSS — not used)
	 */
	do_action( 'woocommerce_before_single_product_summary' );
	?>

	<div class="mdb-configurator">

		<!-- ── Left: CPO options form (all steps, scrollable) ──────────────── -->
		<div class="mdb-configurator__steps">
			<?php
			/**
			 * This action now fires ONLY woocommerce_template_single_add_to_cart (p.30)
			 * because we removed every other hook above.
			 *
			 * Inside that function WooCommerce renders <form class="cart"> and fires:
			 *   woocommerce_before_add_to_cart_button  ← CPO renders #uni_cpo_options here
			 *   woocommerce_after_add_to_cart_button
			 *
			 * The default quantity input and submit button are hidden via CSS; our
			 * sidebar buttons drive the form instead.
			 */
			do_action( 'woocommerce_single_product_summary' );
			?>
		</div><!-- .mdb-configurator__steps -->

		<!-- ── Right: sticky order summary ─────────────────────────────────── -->
		<aside class="mdb-configurator__sidebar"
		       id="mdb-configurator-sidebar"
		       aria-label="<?php esc_attr_e( 'Order Summary', 'mdb-theme' ); ?>">

			<!-- Mobile-only toggle handle: tapping opens/closes the bottom drawer -->
			<button class="mdb-sidebar-handle"
			        id="mdb-sidebar-handle"
			        type="button"
			        aria-expanded="false"
			        aria-controls="mdb-order-summary-panel">
				<span class="mdb-sidebar-handle__label">
					<?php esc_html_e( 'Order Summary', 'mdb-theme' ); ?>
				</span>
				<span class="mdb-sidebar-handle__price" id="mdb-handle-price">
					<?php echo wp_kses_post( $product->get_price_html() ); ?>
				</span>
				<span class="mdb-sidebar-handle__icon" aria-hidden="true">&#8250;</span>
			</button>

			<div class="mdb-sidebar-mobile-actions" aria-hidden="false">
				<button type="button"
				        class="mdb-btn mdb-btn--addtocart mdb-btn--addtocart-mobile"
				        id="mdb-btn-addtocart-mobile">
					<?php esc_html_e( 'Add to cart', 'mdb-theme' ); ?>
				</button>
			</div>

			<div class="mdb-order-summary" id="mdb-order-summary-panel">

				<h2 class="mdb-order-summary__heading">
					<?php esc_html_e( 'Order Summary', 'mdb-theme' ); ?>
				</h2>
				<hr class="mdb-order-summary__rule">

				<p class="mdb-order-summary__product-name"><?php the_title(); ?></p>

				<button type="button"
				        class="mdb-options-toggle-btn mdb-options-toggle-btn--summary"
				        id="mdb-summary-options-toggle"
				        aria-expanded="false"
				        aria-controls="mdb-summary-options-wrapper">
					<span class="mdb-options-toggle-btn__label"><?php esc_html_e( 'View Configuration', 'mdb-theme' ); ?></span>
					<span class="mdb-options-toggle-btn__icon" aria-hidden="true">&#8250;</span>
				</button>

				<div class="mdb-options-wrapper mdb-options-wrapper--summary is-loading"
				     id="mdb-summary-options-wrapper"
				     aria-busy="true">
					<ul class="mdb-order-summary__skeleton"
					    id="mdb-summary-skeleton"
					    aria-hidden="true">
						<li class="mdb-summary-skeleton-item">
							<span class="mdb-summary-skeleton-item__label"></span>
							<span class="mdb-summary-skeleton-item__value"></span>
						</li>
						<li class="mdb-summary-skeleton-item">
							<span class="mdb-summary-skeleton-item__label"></span>
							<span class="mdb-summary-skeleton-item__value"></span>
						</li>
						<li class="mdb-summary-skeleton-item">
							<span class="mdb-summary-skeleton-item__label"></span>
							<span class="mdb-summary-skeleton-item__value"></span>
						</li>
						<li class="mdb-summary-skeleton-item">
							<span class="mdb-summary-skeleton-item__label"></span>
							<span class="mdb-summary-skeleton-item__value"></span>
						</li>
						<li class="mdb-summary-skeleton-item">
							<span class="mdb-summary-skeleton-item__label"></span>
							<span class="mdb-summary-skeleton-item__value"></span>
						</li>
					</ul>
					<ul class="mdb-order-summary__list"
					    id="mdb-summary-list"
					    aria-live="polite"
					    aria-label="<?php esc_attr_e( 'Selected options', 'mdb-theme' ); ?>">
						<!-- Populated by assets/js/configurator.js -->
					</ul>
				</div>

				<hr class="mdb-order-summary__rule mdb-order-summary__rule--pre-total">

				<div class="mdb-order-summary__total-row">
					<span class="mdb-order-summary__total-label">
						<?php esc_html_e( 'Total Price:', 'mdb-theme' ); ?>
					</span>
					<span class="mdb-total-price" id="mdb-total-price">
						<?php echo wp_kses_post( $product->get_price_html() ); ?>
					</span>
				</div>

				<div class="mdb-order-summary__actions">
					<button type="button"
					        class="mdb-btn mdb-btn--addtocart"
					        id="mdb-btn-addtocart">
						<?php esc_html_e( 'Add to cart', 'mdb-theme' ); ?>
					</button>
				</div><!-- .mdb-order-summary__actions -->

				<div class="mdb-payment-icons">
					<p class="mdb-payment-icons__label">
						<?php esc_html_e( 'Secure Payments via', 'mdb-theme' ); ?>
					</p>
					<ul class="mdb-payment-icons__list" aria-label="<?php esc_attr_e( 'Accepted payment methods', 'mdb-theme' ); ?>">
						<li class="mdb-payment-icon">Visa</li>
						<li class="mdb-payment-icon">PayPal</li>
						<li class="mdb-payment-icon">MC</li>
						<li class="mdb-payment-icon">Amex</li>
						<li class="mdb-payment-icon">Zip</li>
						<li class="mdb-payment-icon">Afterpay</li>
						<li class="mdb-payment-icon">Bank</li>
					</ul>
				</div><!-- .mdb-payment-icons -->

				<div class="mdb-order-summary__mobile-footer" aria-hidden="false">
					<button type="button"
					        class="mdb-btn mdb-btn--addtocart mdb-btn--addtocart-mobile-bottom"
					        id="mdb-btn-addtocart-mobile-bottom">
						<?php esc_html_e( 'Add to cart', 'mdb-theme' ); ?>
					</button>
				</div>

			</div><!-- .mdb-order-summary -->
		</aside><!-- .mdb-configurator__sidebar -->

	</div><!-- .mdb-configurator -->

	<?php
	/**
	 * Hook: woocommerce_after_single_product_summary.
	 *
	 * @hooked woocommerce_output_product_data_tabs - 10
	 * @hooked woocommerce_upsell_display           - 15
	 * @hooked woocommerce_output_related_products  - 20
	 */
	do_action( 'woocommerce_after_single_product_summary' );
	?>

</div><!-- #product-ID -->

<?php
/**
 * Hook: woocommerce_after_single_product.
 *
 * @hooked WC_Structured_Data::generate_product_data() - 10
 */
do_action( 'woocommerce_after_single_product' );
