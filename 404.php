<?php
/**
 * Custom 404 template.
 *
 * @package HelloBizChild
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : '';
if ( empty( $shop_url ) ) {
    $shop_url = home_url( '/' );
}

get_header();
?>

<div class="mdb-page-wrapper mdb-404-page">
    <main id="primary" class="site-main">
        <section class="mdb-404-hero" aria-labelledby="mdb-404-title">
            <div class="container">
                <div class="mdb-404-card">
                    <p class="mdb-404-kicker"><?php esc_html_e( 'Error 404', 'hello-biz-child' ); ?></p>

                    <h1 id="mdb-404-title"><?php esc_html_e( 'This page has rolled up the blinds.', 'hello-biz-child' ); ?></h1>

                    <p class="mdb-404-copy">
                        <?php esc_html_e( 'The link may be old, the address may be mistyped, or the page has moved. Jump back into the catalogue and keep building your perfect fit.', 'hello-biz-child' ); ?>
                    </p>

                    <div class="mdb-404-actions">
                        <a class="mdb-404-btn mdb-404-btn--primary" href="<?php echo esc_url( $shop_url ); ?>">
                            <?php esc_html_e( 'Browse Blinds', 'hello-biz-child' ); ?>
                        </a>

                        <a class="mdb-404-btn mdb-404-btn--ghost" href="<?php echo esc_url( home_url( '/' ) ); ?>">
                            <?php esc_html_e( 'Go Home', 'hello-biz-child' ); ?>
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </main>
</div>

<?php
get_footer();
