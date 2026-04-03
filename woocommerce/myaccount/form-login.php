<?php
/**
 * Login Form
 *
 * Overrides /woocommerce/templates/myaccount/form-login.php
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.9.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

do_action( 'woocommerce_before_customer_login_form' );

$registration_enabled = 'yes' === get_option( 'woocommerce_enable_myaccount_registration' );
?>

<div class="mdb-account-login<?php echo $registration_enabled ? ' mdb-account-login--tabs' : ''; ?>">

	<?php if ( $registration_enabled ) : ?>
	<div class="mdb-auth-tabs" role="tablist">
		<button
			class="mdb-auth-tab mdb-auth-tab--active"
			role="tab"
			aria-selected="true"
			aria-controls="mdb-panel-login"
			data-tab="login"
			type="button"
		><?php esc_html_e( 'Sign In', 'woocommerce' ); ?></button>
		<button
			class="mdb-auth-tab"
			role="tab"
			aria-selected="false"
			aria-controls="mdb-panel-register"
			data-tab="register"
			type="button"
		><?php esc_html_e( 'Create Account', 'woocommerce' ); ?></button>
	</div>
	<?php endif; ?>

	<div class="mdb-account-login__card">

		<!-- Login panel -->
		<div
			class="mdb-auth-panel<?php echo $registration_enabled ? ' mdb-auth-panel--active' : ''; ?>"
			id="mdb-panel-login"
			role="tabpanel"
		>
			<?php if ( ! $registration_enabled ) : ?>
			<h2 class="mdb-account-login__heading"><?php esc_html_e( 'Sign In', 'woocommerce' ); ?></h2>
			<?php endif; ?>

			<form class="woocommerce-form woocommerce-form-login login mdb-login-form" method="post" novalidate>

				<?php do_action( 'woocommerce_login_form_start' ); ?>

				<div class="mdb-form-row">
					<label class="mdb-form-label" for="username">
						<?php esc_html_e( 'Username or email address', 'woocommerce' ); ?>
						<span class="required" aria-hidden="true">*</span>
						<span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span>
					</label>
					<input
						type="text"
						class="woocommerce-Input woocommerce-Input--text input-text mdb-form-input"
						name="username"
						id="username"
						autocomplete="username"
						value="<?php echo ( ! empty( $_POST['username'] ) && is_string( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; // @codingStandardsIgnoreLine ?>"
						required
						aria-required="true"
					/>
				</div>

				<div class="mdb-form-row">
					<label class="mdb-form-label" for="password">
						<?php esc_html_e( 'Password', 'woocommerce' ); ?>
						<span class="required" aria-hidden="true">*</span>
						<span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span>
					</label>
					<input
						class="woocommerce-Input woocommerce-Input--text input-text mdb-form-input"
						type="password"
						name="password"
						id="password"
						autocomplete="current-password"
						required
						aria-required="true"
					/>
				</div>

				<?php do_action( 'woocommerce_login_form' ); ?>

				<div class="mdb-form-row mdb-form-row--actions">
					<label class="mdb-form-checkbox">
						<input
							class="woocommerce-form__input woocommerce-form__input-checkbox"
							name="rememberme"
							type="checkbox"
							id="rememberme"
							value="forever"
						/>
						<span><?php esc_html_e( 'Remember me', 'woocommerce' ); ?></span>
					</label>
					<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
					<button
						type="submit"
						class="woocommerce-button button woocommerce-form-login__submit mdb-btn mdb-btn--primary<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>"
						name="login"
						value="<?php esc_attr_e( 'Log in', 'woocommerce' ); ?>"
					>
						<?php esc_html_e( 'Log in', 'woocommerce' ); ?>
					</button>
				</div>

				<p class="mdb-lost-password woocommerce-LostPassword lost_password">
					<a href="<?php echo esc_url( wp_lostpassword_url() ); ?>">
						<?php esc_html_e( 'Lost your password?', 'woocommerce' ); ?>
					</a>
				</p>

				<?php do_action( 'woocommerce_login_form_end' ); ?>

			</form>
		</div><!-- /#mdb-panel-login -->

		<!-- Register panel -->
		<?php if ( $registration_enabled ) : ?>
		<div
			class="mdb-auth-panel"
			id="mdb-panel-register"
			role="tabpanel"
			hidden
		>
			<form method="post" class="woocommerce-form woocommerce-form-register register mdb-login-form" <?php do_action( 'woocommerce_register_form_tag' ); ?>>

				<?php do_action( 'woocommerce_register_form_start' ); ?>

				<?php if ( 'no' === get_option( 'woocommerce_registration_generate_username' ) ) : ?>
				<div class="mdb-form-row">
					<label class="mdb-form-label" for="reg_username">
						<?php esc_html_e( 'Username', 'woocommerce' ); ?>
						<span class="required" aria-hidden="true">*</span>
						<span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span>
					</label>
					<input
						type="text"
						class="woocommerce-Input woocommerce-Input--text input-text mdb-form-input"
						name="username"
						id="reg_username"
						autocomplete="username"
						value="<?php echo ( ! empty( $_POST['username'] ) ) ? esc_attr( wp_unslash( $_POST['username'] ) ) : ''; // @codingStandardsIgnoreLine ?>"
						required
						aria-required="true"
					/>
				</div>
				<?php endif; ?>

				<div class="mdb-form-row">
					<label class="mdb-form-label" for="reg_email">
						<?php esc_html_e( 'Email address', 'woocommerce' ); ?>
						<span class="required" aria-hidden="true">*</span>
						<span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span>
					</label>
					<input
						type="email"
						class="woocommerce-Input woocommerce-Input--text input-text mdb-form-input"
						name="email"
						id="reg_email"
						autocomplete="email"
						value="<?php echo ( ! empty( $_POST['email'] ) ) ? esc_attr( wp_unslash( $_POST['email'] ) ) : ''; // @codingStandardsIgnoreLine ?>"
						required
						aria-required="true"
					/>
				</div>

				<?php if ( 'no' === get_option( 'woocommerce_registration_generate_password' ) ) : ?>
				<div class="mdb-form-row">
					<label class="mdb-form-label" for="reg_password">
						<?php esc_html_e( 'Password', 'woocommerce' ); ?>
						<span class="required" aria-hidden="true">*</span>
						<span class="screen-reader-text"><?php esc_html_e( 'Required', 'woocommerce' ); ?></span>
					</label>
					<input
						type="password"
						class="woocommerce-Input woocommerce-Input--password input-text mdb-form-input"
						name="password"
						id="reg_password"
						autocomplete="new-password"
						required
						aria-required="true"
					/>
				</div>
				<?php else : ?>
				<p class="mdb-register-note">
					<?php esc_html_e( 'A link to set a new password will be sent to your email address.', 'woocommerce' ); ?>
				</p>
				<?php endif; ?>

				<?php do_action( 'woocommerce_register_form' ); ?>

				<div class="mdb-form-row mdb-form-row--actions">
					<?php wp_nonce_field( 'woocommerce-register', 'woocommerce-register-nonce' ); ?>
					<?php do_action( 'woocommerce_register_form_end' ); ?>
					<button
						type="submit"
						class="woocommerce-Button button woocommerce-form-register__submit mdb-btn mdb-btn--primary<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>"
						name="register"
						value="<?php esc_attr_e( 'Register', 'woocommerce' ); ?>"
					>
						<?php esc_html_e( 'Create Account', 'woocommerce' ); ?>
					</button>
				</div>

			</form>
		</div><!-- /#mdb-panel-register -->
		<?php endif; ?>

	</div><!-- /.mdb-account-login__card -->

</div><!-- /.mdb-account-login -->

<?php do_action( 'woocommerce_after_customer_login_form' ); ?>
