<?php
/**
 * The template for displaying the Checkout page
 *
 * @package MDB_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

get_header();
?>

<div class="mdb-page-wrapper mdb-checkout-page">
    
    <!-- Hero / Header Area with Progress Steps -->
    <div class="mdb-checkout-hero">
        <div class="container">
            <h1><?php the_title(); ?></h1>
            <?php 
            if ( function_exists( 'mdb_render_checkout_progress' ) ) {
                echo do_shortcode('[mdb_checkout_progress]'); 
            }
            ?>
        </div>
    </div>

    <!-- Main Content Area -->
    <main id="primary" class="site-main">
        <div class="container">
            <?php
            while ( have_posts() ) :
                the_post();

                // Standard WordPress Page Content (contains [woocommerce_checkout] shortcode)
                the_content();

            endwhile; // End of the loop.
            ?>
        </div>
    </main><!-- #primary -->

</div>

<?php
get_footer();
