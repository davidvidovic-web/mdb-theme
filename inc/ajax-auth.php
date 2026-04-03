<?php
/**
 * AJAX Login & Registration handlers
 *
 * Hooked on wp_ajax_nopriv_* so they are only reachable by guests (non-authenticated).
 * Both actions verify a WooCommerce nonce to prevent CSRF.
 *
 * @package MDB Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ---------------------------------------------------------------------------
// AJAX Login
// ---------------------------------------------------------------------------

add_action( 'wp_ajax_nopriv_mdb_ajax_login', 'mdb_handle_ajax_login' );

function mdb_handle_ajax_login(): void {
	// Verify nonce (WooCommerce login nonce used in the template).
	$nonce = isset( $_POST['woocommerce-login-nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['woocommerce-login-nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'woocommerce-login' ) ) {
		wp_send_json_error( array( 'message' => __( 'Security check failed. Please refresh the page and try again.', 'mdb-theme' ) ) );
	}

	$username = isset( $_POST['username'] ) ? sanitize_user( wp_unslash( $_POST['username'] ) ) : '';
	$password = isset( $_POST['password'] ) ? wp_unslash( $_POST['password'] ) : ''; // passwords must not be sanitized
	$remember = ! empty( $_POST['rememberme'] );

	if ( empty( $username ) || empty( $password ) ) {
		wp_send_json_error( array( 'message' => __( 'Please enter your username/email and password.', 'mdb-theme' ) ) );
	}

	$user = wp_signon(
		array(
			'user_login'    => $username,
			'user_password' => $password,
			'remember'      => $remember,
		),
		is_ssl()
	);

	if ( is_wp_error( $user ) ) {
		// Return a generic message so we don't leak which field is wrong.
		wp_send_json_error( array( 'message' => __( 'Incorrect username or password.', 'mdb-theme' ) ) );
	}

	wp_send_json_success( array(
		'redirect' => apply_filters( 'woocommerce_login_redirect', wc_get_page_permalink( 'myaccount' ), $user ),
	) );
}

// ---------------------------------------------------------------------------
// AJAX Register
// ---------------------------------------------------------------------------

add_action( 'wp_ajax_nopriv_mdb_ajax_register', 'mdb_handle_ajax_register' );

function mdb_handle_ajax_register(): void {
	// Verify nonce (WooCommerce register nonce used in the template).
	$nonce = isset( $_POST['woocommerce-register-nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['woocommerce-register-nonce'] ) ) : '';
	if ( ! wp_verify_nonce( $nonce, 'woocommerce-register' ) ) {
		wp_send_json_error( array( 'message' => __( 'Security check failed. Please refresh the page and try again.', 'mdb-theme' ) ) );
	}

	$email    = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$username = isset( $_POST['username'] ) ? sanitize_user( wp_unslash( $_POST['username'] ) ) : '';
	$password = isset( $_POST['password'] ) ? wp_unslash( $_POST['password'] ) : '';

	if ( empty( $email ) ) {
		wp_send_json_error( array( 'message' => __( 'Please enter your email address.', 'mdb-theme' ) ) );
	}

	if ( ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => __( 'Please enter a valid email address.', 'mdb-theme' ) ) );
	}

	// Delegate actual user creation to WooCommerce (handles username auto-gen,
	// password email, duplicate checks, etc.).
	$username_required = 'no' !== get_option( 'woocommerce_registration_generate_username' ) ? false : true;
	$password_required = 'no' !== get_option( 'woocommerce_registration_generate_password' ) ? false : true;

	if ( $username_required && empty( $username ) ) {
		wp_send_json_error( array( 'message' => __( 'Please enter a username.', 'mdb-theme' ) ) );
	}

	if ( $password_required && empty( $password ) ) {
		wp_send_json_error( array( 'message' => __( 'Please enter a password.', 'mdb-theme' ) ) );
	}

	// Attempt to create the customer via WooCommerce.
	$new_customer_id = wc_create_new_customer( $email, $username, $password );

	if ( is_wp_error( $new_customer_id ) ) {
		wp_send_json_error( array( 'message' => $new_customer_id->get_error_message() ) );
	}

	// Log the new customer in immediately.
	wc_set_customer_auth_cookie( $new_customer_id );

	do_action( 'woocommerce_register_post', $username, $email, new WP_Error() );

	wp_send_json_success( array(
		'redirect' => apply_filters( 'woocommerce_registration_redirect', wc_get_page_permalink( 'myaccount' ) ),
	) );
}
