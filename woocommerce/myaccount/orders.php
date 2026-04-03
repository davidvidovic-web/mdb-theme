<?php
/**
 * Orders — Account page
 *
 * Overrides /woocommerce/templates/myaccount/orders.php
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.5.0
 */

defined( 'ABSPATH' ) || exit;

$wp_button_class = wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '';

do_action( 'woocommerce_before_account_orders', $has_orders );
?>

<?php if ( $has_orders ) : ?>

	<div class="mdb-orders">

		<table class="mdb-orders-table woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
			<thead>
				<tr class="mdb-orders-table__head">
					<?php foreach ( wc_get_account_orders_columns() as $column_id => $column_name ) : ?>
						<th scope="col" class="mdb-orders-table__th mdb-orders-table__th--<?php echo esc_attr( $column_id ); ?> woocommerce-orders-table__header woocommerce-orders-table__header-<?php echo esc_attr( $column_id ); ?>">
							<span class="nobr"><?php echo esc_html( $column_name ); ?></span>
						</th>
					<?php endforeach; ?>
				</tr>
			</thead>

			<tbody>
				<?php foreach ( $customer_orders->orders as $customer_order ) :
					$order      = wc_get_order( $customer_order );
					$item_count = $order->get_item_count() - $order->get_item_count_refunded();
					$status     = $order->get_status();
				?>
					<tr class="mdb-orders-table__row mdb-orders-table__row--<?php echo esc_attr( $status ); ?> woocommerce-orders-table__row woocommerce-orders-table__row--status-<?php echo esc_attr( $status ); ?> order">

						<?php foreach ( wc_get_account_orders_columns() as $column_id => $column_name ) :
							$is_order_number = 'order-number' === $column_id;
						?>

							<?php if ( $is_order_number ) : ?>
								<th class="mdb-orders-table__cell mdb-orders-table__cell--<?php echo esc_attr( $column_id ); ?> woocommerce-orders-table__cell woocommerce-orders-table__cell-<?php echo esc_attr( $column_id ); ?>" data-title="<?php echo esc_attr( $column_name ); ?>" scope="row">
							<?php else : ?>
								<td class="mdb-orders-table__cell mdb-orders-table__cell--<?php echo esc_attr( $column_id ); ?> woocommerce-orders-table__cell woocommerce-orders-table__cell-<?php echo esc_attr( $column_id ); ?>" data-title="<?php echo esc_attr( $column_name ); ?>">
							<?php endif; ?>

								<?php if ( has_action( 'woocommerce_my_account_my_orders_column_' . $column_id ) ) : ?>

									<?php do_action( 'woocommerce_my_account_my_orders_column_' . $column_id, $order ); ?>

								<?php elseif ( $is_order_number ) : ?>

									<a class="mdb-orders-table__order-link" href="<?php echo esc_url( $order->get_view_order_url() ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'View order number %s', 'woocommerce' ), $order->get_order_number() ) ); ?>">
										<span class="mdb-orders-table__order-num"><?php echo esc_html( '#' . $order->get_order_number() ); ?></span>
									</a>

								<?php elseif ( 'order-date' === $column_id ) : ?>

									<time class="mdb-orders-table__date" datetime="<?php echo esc_attr( $order->get_date_created()->date( 'c' ) ); ?>">
										<?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?>
									</time>

								<?php elseif ( 'order-status' === $column_id ) : ?>

									<span class="mdb-orders-badge mdb-orders-badge--<?php echo esc_attr( $status ); ?>">
										<?php echo esc_html( wc_get_order_status_name( $status ) ); ?>
									</span>

								<?php elseif ( 'order-total' === $column_id ) : ?>

									<span class="mdb-orders-table__total">
										<?php echo wp_kses_post( $order->get_formatted_order_total() ); ?>
									</span>
									<span class="mdb-orders-table__items">
										<?php echo esc_html( sprintf( _n( '%s item', '%s items', $item_count, 'woocommerce' ), $item_count ) ); ?>
									</span>

								<?php elseif ( 'order-actions' === $column_id ) : ?>

									<?php
									$actions = wc_get_account_orders_actions( $order );
									if ( ! empty( $actions ) ) :
										foreach ( $actions as $key => $action ) :
											$aria = empty( $action['aria-label'] )
												? sprintf( __( '%1$s order number %2$s', 'woocommerce' ), $action['name'], $order->get_order_number() )
												: $action['aria-label'];
											echo '<a href="' . esc_url( $action['url'] ) . '" class="mdb-orders-action-btn woocommerce-button button ' . esc_attr( $key ) . esc_attr( $wp_button_class ) . '" aria-label="' . esc_attr( $aria ) . '">' . esc_html( $action['name'] ) . '</a>';
										endforeach;
									endif;
									?>

								<?php endif; ?>

							<?php echo $is_order_number ? '</th>' : '</td>'; ?>

						<?php endforeach; ?>

					</tr>

				<?php endforeach; ?>
			</tbody>
		</table>

		<?php do_action( 'woocommerce_before_account_orders_pagination' ); ?>

		<?php if ( 1 < $customer_orders->max_num_pages ) : ?>
			<nav class="mdb-orders-pagination woocommerce-pagination" aria-label="<?php esc_attr_e( 'Orders navigation', 'woocommerce' ); ?>">
				<?php if ( 1 !== $current_page ) : ?>
					<a class="mdb-orders-pagination__btn mdb-orders-pagination__btn--prev woocommerce-button button<?php echo esc_attr( $wp_button_class ); ?>" href="<?php echo esc_url( wc_get_endpoint_url( 'orders', $current_page - 1 ) ); ?>">
						&larr; <?php esc_html_e( 'Previous', 'woocommerce' ); ?>
					</a>
				<?php endif; ?>
				<?php if ( intval( $customer_orders->max_num_pages ) !== $current_page ) : ?>
					<a class="mdb-orders-pagination__btn mdb-orders-pagination__btn--next woocommerce-button button<?php echo esc_attr( $wp_button_class ); ?>" href="<?php echo esc_url( wc_get_endpoint_url( 'orders', $current_page + 1 ) ); ?>">
						<?php esc_html_e( 'Next', 'woocommerce' ); ?> &rarr;
					</a>
				<?php endif; ?>
			</nav>
		<?php endif; ?>

	</div><!-- /.mdb-orders -->

<?php else : ?>

	<div class="mdb-orders-empty">
		<p class="mdb-orders-empty__msg woocommerce-message woocommerce-message--info woocommerce-Message woocommerce-Message--info wc-empty-cart-message">
			<?php esc_html_e( 'No order has been made yet.', 'woocommerce' ); ?>
		</p>
		<a class="mdb-btn mdb-btn--primary woocommerce-Button button<?php echo esc_attr( $wp_button_class ); ?>" href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>">
			<?php esc_html_e( 'Browse products', 'woocommerce' ); ?>
		</a>
	</div>

<?php endif; ?>

<?php do_action( 'woocommerce_after_account_orders', $has_orders ); ?>
