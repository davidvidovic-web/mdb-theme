<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive
 *
 * This template overrides the default WooCommerce template to provide a custom implementation
 * tailored for the MDB Theme.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package MDB_Theme/WooCommerce/Templates
 * @version 8.6.0
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

/**
 * Hook: woocommerce_before_main_content.
 *
 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
 * @hooked woocommerce_breadcrumb - 20
 * @hooked WC_Structured_Data::generate_website_data() - 30
 */
do_action( 'woocommerce_before_main_content' );

?>
<header class="woocommerce-products-header mdb-shop-header">
	<?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>
		<h1 class="woocommerce-products-header__title page-title mdb-shop-title"><?php woocommerce_page_title(); ?></h1>
	<?php endif; ?>

	<?php
	/**
	 * Hook: woocommerce_archive_description.
	 *
	 * @hooked woocommerce_taxonomy_archive_description - 10
	 * @hooked woocommerce_product_archive_description - 10
	 */
	do_action( 'woocommerce_archive_description' );
	?>
</header>

<div class="mdb-shop-layout">
	<aside class="mdb-shop-sidebar">
		<div class="mdb-shop-widget">
			<h3 class="mdb-widget-title">Categories</h3>
			<ul class="mdb-category-list">
				<?php
				$current_cat_id = is_product_category() ? get_queried_object_id() : 0;
				$categories = get_terms( [
					'taxonomy' => 'product_cat',
					'hide_empty' => true,
				] );

				// "All" specific link
				$shop_page_url = get_permalink( wc_get_page_id( 'shop' ) );
				$is_all_active = ( ! is_product_category() ) ? 'active' : '';
				echo '<li class="' . esc_attr( $is_all_active ) . '"><a href="' . esc_url( $shop_page_url ) . '">All Blinds</a></li>';

				if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) {
					foreach ( $categories as $category ) {
						// Optionally skip "uncategorized"
						if ( $category->slug === 'uncategorized' ) { continue; }
						$is_active = ( $current_cat_id === $category->term_id ) ? 'active' : '';
						echo '<li class="' . esc_attr( $is_active ) . '"><a href="' . esc_url( get_term_link( $category ) ) . '">' . esc_html( $category->name ) . ' <span class="count">(' . $category->count . ')</span></a></li>';
					}
				}
				?>
			</ul>
		</div>
		
		<?php dynamic_sidebar( 'shop-sidebar' ); // Optional fallback for other widgets ?>
	</aside>

	<main class="mdb-shop-main">
		<div class="mdb-shop-toolbar">
			<?php
			/**
			 * Hook: woocommerce_before_shop_loop.
			 *
			 * @hooked woocommerce_output_all_notices - 10
			 * @hooked woocommerce_result_count - 20
			 * @hooked woocommerce_catalog_ordering - 30
			 */
			do_action( 'woocommerce_before_shop_loop' );
			?>
		</div>

		<?php
		if ( woocommerce_product_loop() ) {
			woocommerce_product_loop_start();

			if ( wc_get_loop_prop( 'total' ) ) {
				while ( have_posts() ) {
					the_post();

					/**
					 * Hook: woocommerce_shop_loop.
					 */
					do_action( 'woocommerce_shop_loop' );

					wc_get_template_part( 'content', 'product' );
				}
			}

			woocommerce_product_loop_end();

			/**
			 * Hook: woocommerce_after_shop_loop.
			 *
			 * @hooked woocommerce_pagination - 10
			 */
			do_action( 'woocommerce_after_shop_loop' );
		} else {
			/**
			 * Hook: woocommerce_no_products_found.
			 *
			 * @hooked wc_no_products_found - 10
			 */
			do_action( 'woocommerce_no_products_found' );
		}
		?>
	</main>
</div>

<?php
/**
 * Hook: woocommerce_after_main_content.
 *
 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
 */
do_action( 'woocommerce_after_main_content' );

/**
 * Hook: woocommerce_sidebar.
 *
 * @hooked woocommerce_get_sidebar - 10
 */
do_action( 'woocommerce_sidebar' );

get_footer( 'shop' );
?>
