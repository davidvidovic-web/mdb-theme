<?php
/**
 * View Order
 *
 * Overrides /woocommerce/templates/myaccount/view-order.php
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 10.6.0
 */

defined( 'ABSPATH' ) || exit;

$notes  = $order->get_customer_order_notes();
$status = $order->get_status();
?>

<div class="mdb-order-view">

	<!-- Order meta bar -->
	<div class="mdb-order-meta">

		<div class="mdb-order-meta__item">
			<span class="mdb-order-meta__label"><?php esc_html_e( 'Order', 'woocommerce' ); ?></span>
			<span class="mdb-order-meta__value mdb-order-meta__value--number"><?php echo esc_html( '#' . $order->get_order_number() ); ?></span>
		</div>

		<div class="mdb-order-meta__item">
			<span class="mdb-order-meta__label"><?php esc_html_e( 'Date placed', 'woocommerce' ); ?></span>
			<span class="mdb-order-meta__value">
				<time datetime="<?php echo esc_attr( $order->get_date_created()->date( 'c' ) ); ?>">
					<?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?>
				</time>
			</span>
		</div>

		<div class="mdb-order-meta__item">
			<span class="mdb-order-meta__label"><?php esc_html_e( 'Status', 'woocommerce' ); ?></span>
			<span class="mdb-order-meta__value">
				<span class="mdb-orders-badge mdb-orders-badge--<?php echo esc_attr( $status ); ?>">
					<?php echo esc_html( wc_get_order_status_name( $status ) ); ?>
				</span>
			</span>
		</div>

		<div class="mdb-order-meta__item">
			<span class="mdb-order-meta__label"><?php esc_html_e( 'Total', 'woocommerce' ); ?></span>
			<span class="mdb-order-meta__value mdb-order-meta__value--total">
				<?php echo wp_kses_post( $order->get_formatted_order_total() ); ?>
			</span>
		</div>

	</div><!-- /.mdb-order-meta -->

	<?php if ( $notes ) : ?>

		<div class="mdb-order-updates">
			<h2 class="mdb-order-updates__heading"><?php esc_html_e( 'Order updates', 'woocommerce' ); ?></h2>
			<ol class="mdb-order-updates__list woocommerce-OrderUpdates commentlist notes">
				<?php foreach ( $notes as $note ) : ?>
					<li class="mdb-order-update woocommerce-OrderUpdate comment note">
						<div class="mdb-order-update__inner">
							<p class="mdb-order-update__date">
								<?php echo esc_html( date_i18n( __( 'l jS \o\f F Y, h:ia', 'woocommerce' ), strtotime( $note->comment_date ) ) ); ?>
							</p>
							<div class="mdb-order-update__text">
								<?php echo wp_kses_post( wpautop( wptexturize( $note->comment_content ) ) ); ?>
							</div>
						</div>
					</li>
				<?php endforeach; ?>
			</ol>
		</div>

	<?php endif; ?>

	<?php do_action( 'woocommerce_view_order', $order_id ); ?>

</div><!-- /.mdb-order-view -->
