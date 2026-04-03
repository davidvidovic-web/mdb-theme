<?php
/**
 * Checkout Progress Steps functionality
 *
 * @package MDB_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render the checkout progress steps.
 * 
 * @param array $atts Shortcode attributes.
 * @return string HTML output.
 */
function mdb_render_checkout_progress( $atts = [] ) {
    // Only show on relevant pages if not forced via shortcode args (though usually shortcode is explicit placement)
    if ( ! function_exists( 'is_woocommerce' ) ) {
        return '';
    }

    $steps = [
        'cart' => [
            'label' => 'Shopping Cart',
            'is_active' => is_cart(),
            'is_completed' => is_checkout() || is_order_received_page(),
            'number' => '1'
        ],
        'checkout' => [
            'label' => 'Checkout Details',
            'is_active' => is_checkout() && ! is_order_received_page(),
            'is_completed' => is_order_received_page(),
            'number' => '2'
        ],
        'complete' => [
            'label' => 'Order Complete',
            'is_active' => is_order_received_page(),
            'is_completed' => false,
            'number' => '3'
        ]
    ];

    ob_start();
    ?>
    <div class="mdb-checkout-progress">
        <ul class="mdb-checkout-progress__list">
            <?php foreach ( $steps as $key => $step ) : 
                $class = 'mdb-checkout-progress__step';
                if ( $step['is_active'] ) {
                    $class .= ' mdb-checkout-progress__step--active';
                }
                if ( $step['is_completed'] ) {
                    $class .= ' mdb-checkout-progress__step--completed';
                }
            ?>
            <li class="<?php echo esc_attr( $class ); ?>">
                <div class="mdb-checkout-progress__indicator">
                    <?php echo esc_html( $step['number'] ); ?>
                </div>
                <span class="mdb-checkout-progress__label"><?php echo esc_html( $step['label'] ); ?></span>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php
    return ob_get_clean();
}

add_shortcode( 'mdb_checkout_progress', 'mdb_render_checkout_progress' );

/**
 * Optional: Auto-inject into WooCommerce content top if desired.
 * This is disabled by default to let the user place it via Shortcode in the Hero.
 * Uncomment add_action lines to enable auto-injection.
 */
function mdb_inject_checkout_progress() {
    if ( is_cart() || ( is_checkout() && ! is_order_received_page() ) || is_order_received_page() ) {
        echo do_shortcode( '[mdb_checkout_progress]' );
    }
}
// add_action( 'woocommerce_before_cart', 'mdb_inject_checkout_progress', 10 );
// add_action( 'woocommerce_before_checkout_form', 'mdb_inject_checkout_progress', 10 );
// add_action( 'woocommerce_thankyou', 'mdb_inject_checkout_progress', 5 );
