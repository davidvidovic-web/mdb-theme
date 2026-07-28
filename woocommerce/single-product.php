<?php
/**
 * The Template for displaying all single products.
 *
 * Overrides the WooCommerce default to ensure our custom
 * content-single-product.php is loaded via the standard template-part call.
 *
 * @see         https://woocommerce.com/document/template-structure/
 * @package     MDB_Theme/WooCommerce/Templates
 * @version     1.6.4
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

while ( have_posts() ) :
	the_post();

	/**
	 * Loads our overridden content-single-product.php from the theme's woocommerce/ folder.
	 */
	wc_get_template_part( 'content', 'single-product' );

endwhile; // end of the loop.

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
// Intentionally not calling woocommerce_sidebar — this is a full-width
// configurator layout; a widget sidebar would create an unwanted third column.

get_footer( 'shop' );
