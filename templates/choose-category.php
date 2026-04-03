<?php
/**
 * Template: /choose/{category}/
 *
 * Hierarchical display logic:
 *   - If the current category has direct child categories → show those children as cards.
 *   - If it has no children → show the products that belong directly to it.
 *
 * Loaded via mdb_choose_template_redirect() in inc/choose-category.php.
 *
 * @package MDB_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// -------------------------------------------------------------------------
// Resolve category and page number
// -------------------------------------------------------------------------

$cat_slug = sanitize_title( get_query_var( 'mdb_choose_cat' ) );
$paged    = max( 1, (int) get_query_var( 'paged' ) );

$term = get_term_by( 'slug', $cat_slug, 'product_cat' );

if ( ! $term || is_wp_error( $term ) ) {
	global $wp_query;
	$wp_query->set_404();
	status_header( 404 );
	nocache_headers();
	get_template_part( '404' );
	exit;
}

// Is this category (or any ancestor) the free-samples tree?
$is_free_samples = function_exists( 'mdb_is_free_samples_cat' ) && mdb_is_free_samples_cat( $term );

// -------------------------------------------------------------------------
// Decide: show child categories OR products
// -------------------------------------------------------------------------

$child_terms = get_terms( array(
	'taxonomy'   => 'product_cat',
	'parent'     => $term->term_id,
	'hide_empty' => true,
	'orderby'    => 'count',
	'order'      => 'DESC',
) );

$has_children = ! empty( $child_terms ) && ! is_wp_error( $child_terms );

// -------------------------------------------------------------------------
// Product query (only used when no child categories exist)
// -------------------------------------------------------------------------

$products_query = null;

if ( ! $has_children ) {

	$tax_query_args = array(
		array(
			'taxonomy' => 'product_cat',
			'field'    => 'term_id',
			'terms'    => $term->term_id,
		),
	);

	if ( $is_free_samples ) {
		// Free samples: paginated standard WooCommerce product loop.
		$per_page = (int) get_option( 'posts_per_page', 12 );

		$products_query = new WP_Query( array(
			'post_type'           => 'product',
			'post_status'         => 'publish',
			'posts_per_page'      => $per_page,
			'paged'               => $paged,
			'ignore_sticky_posts' => true,
			'tax_query'           => $tax_query_args,
		) );

		$columns = function_exists( 'wc_get_default_products_per_row' ) ? wc_get_default_products_per_row() : 4;

		wc_set_loop_prop( 'total',        $products_query->found_posts );
		wc_set_loop_prop( 'total_pages',  $products_query->max_num_pages );
		wc_set_loop_prop( 'current_page', $paged );
		wc_set_loop_prop( 'columns',      $columns );
		wc_set_loop_prop( 'is_shortcode', true );

	} else {
		// All other categories: show all products in the selector widget (no pagination).
		$products_query = new WP_Query( array(
			'post_type'           => 'product',
			'post_status'         => 'publish',
			'posts_per_page'      => -1,
			'orderby'             => 'menu_order',
			'order'               => 'ASC',
			'ignore_sticky_posts' => true,
			'tax_query'           => $tax_query_args,
		) );
	}
}

// -------------------------------------------------------------------------
// Output
// -------------------------------------------------------------------------

get_header( 'shop' );
?>

<div class="mdb-choose-layout">

	<header class="mdb-choose-header">
		<?php if ( $term->parent ) :
			$parent_term = get_term( $term->parent, 'product_cat' );
			if ( $parent_term && ! is_wp_error( $parent_term ) ) : ?>
				<a href="<?php echo esc_url( home_url( '/choose/' . $parent_term->slug . '/' ) ); ?>" class="mdb-choose-back">
					&larr; <?php printf( esc_html__( 'Return to %s', 'mdb-theme' ), esc_html( $parent_term->name ) ); ?>
				</a>
			<?php endif;
		endif; ?>
		<h1 class="mdb-choose-title">
			<?php echo esc_html( $term->name ); ?>
		</h1>

		<?php if ( $term->description ) : ?>
			<div class="mdb-choose-description">
				<?php echo wp_kses_post( wpautop( $term->description ) ); ?>
			</div>
		<?php endif; ?>
	</header>

	<main class="mdb-choose-main">

		<?php if ( $has_children ) : ?>
			<?php // ---- Show child categories as cards ---- ?>

			<ul class="mdb-category-grid">
				<?php foreach ( $child_terms as $child ) : ?>
					<?php
					$child_url   = home_url( '/choose/' . $child->slug . '/' );
					$thumbnail   = get_term_meta( $child->term_id, 'thumbnail_id', true );
					$image_src   = $thumbnail ? wp_get_attachment_image_url( $thumbnail, 'medium' ) : wc_placeholder_img_src( 'medium' );
					?>
					<li class="mdb-category-card">
						<a href="<?php echo esc_url( $child_url ); ?>">
							<div class="mdb-category-card__image">
								<img src="<?php echo esc_url( $image_src ); ?>"
								     alt="<?php echo esc_attr( $child->name ); ?>"
								     loading="lazy">
							</div>
							<div class="mdb-category-card__info">
								<h2 class="mdb-category-card__name"><?php echo esc_html( $child->name ); ?></h2>
								<?php if ( $child->count > 0 ) : ?>
									<span class="mdb-category-card__count">
										<?php printf(
											/* translators: %d number of items */
											esc_html( _n( '%d item', '%d items', $child->count, 'mdb-theme' ) ),
											(int) $child->count
										); ?>
									</span>
								<?php endif; ?>
							</div>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>

		<?php elseif ( $products_query && $products_query->have_posts() ) : ?>

			<?php if ( $is_free_samples ) : ?>
				<?php // ---- Free samples: standard WooCommerce product cards ---- ?>

				<?php woocommerce_product_loop_start(); ?>

				<?php while ( $products_query->have_posts() ) : $products_query->the_post(); ?>
					<?php wc_get_template_part( 'content', 'product' ); ?>
				<?php endwhile; ?>

				<?php woocommerce_product_loop_end(); ?>
				<?php wp_reset_postdata(); ?>

				<?php if ( $products_query->max_num_pages > 1 ) : ?>
					<nav class="woocommerce-pagination">
						<?php
						echo paginate_links( array(
							'base'      => home_url( '/choose/' . $cat_slug . '/page/%#%/' ),
							'format'    => '',
							'current'   => $paged,
							'total'     => $products_query->max_num_pages,
							'prev_text' => '&laquo; ' . esc_html__( 'Previous', 'woocommerce' ),
							'next_text' => esc_html__( 'Next', 'woocommerce' ) . ' &raquo;',
							'type'      => 'list',
						) );
						?>
					</nav>
				<?php endif; ?>

			<?php else : ?>
				<?php // ---- All other categories: MDB Product Customizer widget view ---- ?>

				<div class="mdb-widget mdb-pc-widget">
					<div class="mdb-pc-grid" data-columns="3" style="--mdb-pc-columns:3;">
						<?php
						$pc_index = 0;
						while ( $products_query->have_posts() ) :
							$products_query->the_post();
							$wc_product = wc_get_product( get_the_ID() );
							if ( ! $wc_product ) {
								$pc_index++;
								continue;
							}

							// Image.
							$pc_thumb_id = get_post_thumbnail_id();
							$pc_image    = $pc_thumb_id
								? wp_get_attachment_image_url( $pc_thumb_id, 'medium' )
								: wc_placeholder_img_src( 'medium' );

							// Features from short description (one per line).
							$pc_features = [];
							$pc_short    = wp_strip_all_tags( $wc_product->get_short_description() );
							if ( $pc_short ) {
								$pc_features = array_values(
									array_filter( array_map( 'trim', explode( "\n", $pc_short ) ) )
								);
							}
						?>
						<div class="mdb-pc-card<?php echo $pc_index === 0 ? ' is-selected' : ''; ?>"
							 data-product-url="<?php echo esc_url( get_permalink() ); ?>"
							 data-product-name="<?php echo esc_attr( get_the_title() ); ?>"
							 role="button"
							 tabindex="0"
							 aria-pressed="<?php echo $pc_index === 0 ? 'true' : 'false'; ?>">

							<div class="mdb-pc-check" aria-hidden="true">
								<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="16" height="16" fill="#ffffff">
									<path d="M9 16.17L4.83 12l-1.42 1.41L9 19 21 7l-1.41-1.41L9 16.17z"/>
								</svg>
							</div>

							<div class="mdb-pc-image">
								<img src="<?php echo esc_url( $pc_image ); ?>"
								     alt="<?php echo esc_attr( get_the_title() ); ?>"
								     loading="lazy">
							</div>

							<div class="mdb-pc-content">
								<h3 class="mdb-pc-product-name"><?php echo esc_html( get_the_title() ); ?></h3>
								<p class="mdb-pc-price">
									<span class="mdb-pc-price-prefix"><?php esc_html_e( 'Starting from', 'mdb-theme' ); ?>&nbsp;</span>
									<span class="mdb-pc-price-amount"><?php echo wp_kses_post( $wc_product->get_price_html() ); ?></span>
								</p>
								<?php if ( $pc_features ) : ?>
									<ul class="mdb-pc-features">
										<?php foreach ( $pc_features as $pc_feature ) : ?>
											<li><?php echo esc_html( $pc_feature ); ?></li>
										<?php endforeach; ?>
									</ul>
								<?php endif; ?>
							</div>

						</div><!-- .mdb-pc-card -->
						<?php
							$pc_index++;
						endwhile;
						wp_reset_postdata();
						?>
					</div><!-- .mdb-pc-grid -->

					<div class="mdb-pc-footer">
						<a class="mdb-pc-btn"
						   href="#"
						   data-base-url="">
							<?php esc_html_e( 'Start Customising', 'mdb-theme' ); ?>
						</a>
					</div>
				</div><!-- .mdb-pc-widget -->

			<?php endif; ?>

		<?php else : ?>
			<?php wp_reset_postdata(); ?>
			<p class="woocommerce-info">
				<?php esc_html_e( 'No products were found in this category.', 'mdb-theme' ); ?>
			</p>

		<?php endif; ?>

	</main>

</div><!-- .mdb-choose-layout -->

<?php get_footer( 'shop' ); ?>
