<?php
/**
 * Theme Header Options
 *
 * Provides:
 *   1. A dashboard admin page: Appearance → Theme Header
 *   2. A Customizer panel: Appearance → Customize → Theme Header
 *
 * Both read/write the same theme_mod keys so changes in either place are
 * immediately reflected everywhere.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// =============================================================================
// 1. Admin settings page — Appearance → Theme Header
// =============================================================================

function mdb_header_admin_menu() {
	add_theme_page(
		__( 'Theme Header Settings', 'mdb-theme' ),
		__( 'Theme Header', 'mdb-theme' ),
		'edit_theme_options',
		'mdb-header-settings',
		'mdb_header_admin_page'
	);
}
add_action( 'admin_menu', 'mdb_header_admin_menu' );

function mdb_header_admin_page() {
	if ( ! current_user_can( 'edit_theme_options' ) ) {
		wp_die( esc_html__( 'You do not have permission to access this page.', 'mdb-theme' ) );
	}

	// Handle form submission.
	if ( isset( $_POST['mdb_header_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['mdb_header_nonce'] ) ), 'mdb_header_save' ) ) {

		set_theme_mod( 'mdb_header_enabled',          isset( $_POST['mdb_header_enabled'] ) );
		set_theme_mod( 'mdb_header_show_cart',         isset( $_POST['mdb_header_show_cart'] ) );
		set_theme_mod( 'mdb_header_phone',             sanitize_text_field( wp_unslash( $_POST['mdb_header_phone'] ?? '' ) ) );
		set_theme_mod( 'mdb_header_email',             sanitize_email( wp_unslash( $_POST['mdb_header_email'] ?? '' ) ) );
		set_theme_mod( 'mdb_header_login_text',        sanitize_text_field( wp_unslash( $_POST['mdb_header_login_text'] ?? '' ) ) );
		set_theme_mod( 'mdb_header_login_url',         esc_url_raw( wp_unslash( $_POST['mdb_header_login_url'] ?? '' ) ) );
		set_theme_mod( 'mdb_header_logo_url',          esc_url_raw( wp_unslash( $_POST['mdb_header_logo_url'] ?? '' ) ) );
		set_theme_mod( 'mdb_header_nav_menu',          absint( $_POST['mdb_header_nav_menu'] ?? 0 ) );
		set_theme_mod( 'mdb_header_free_samples_text', sanitize_text_field( wp_unslash( $_POST['mdb_header_free_samples_text'] ?? '' ) ) );
		set_theme_mod( 'mdb_header_free_samples_url',  esc_url_raw( wp_unslash( $_POST['mdb_header_free_samples_url'] ?? '' ) ) );

		// Logo image attachment ID — set via hidden input populated by media uploader.
		if ( isset( $_POST['mdb_header_logo_image'] ) && '' !== $_POST['mdb_header_logo_image'] ) {
			set_theme_mod( 'mdb_header_logo_image', absint( $_POST['mdb_header_logo_image'] ) );
		}

		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Settings saved.', 'mdb-theme' ) . '</p></div>';
	}

	// Current values.
	$enabled       = get_theme_mod( 'mdb_header_enabled', true );
	$phone         = get_theme_mod( 'mdb_header_phone', '1300 011 561' );
	$email         = get_theme_mod( 'mdb_header_email', 'hello@mydirectblinds.com.au' );
	$login_text    = get_theme_mod( 'mdb_header_login_text', 'Login' );
	$login_url     = get_theme_mod( 'mdb_header_login_url', wp_login_url() );
	$logo_url      = get_theme_mod( 'mdb_header_logo_url', home_url( '/' ) );
	$logo_img_id   = get_theme_mod( 'mdb_header_logo_image', get_theme_mod( 'custom_logo' ) );
	$logo_img_src  = $logo_img_id ? wp_get_attachment_image_url( $logo_img_id, 'medium' ) : '';
	$show_cart     = get_theme_mod( 'mdb_header_show_cart', true );
	$nav_menu      = get_theme_mod( 'mdb_header_nav_menu', 0 );
	$fs_text       = get_theme_mod( 'mdb_header_free_samples_text', '» Free Samples «' );
	$fs_url        = get_theme_mod( 'mdb_header_free_samples_url', '/choose/free-samples/' );

	$menus         = wp_get_nav_menus();
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Theme Header Settings', 'mdb-theme' ); ?></h1>
		<form method="post" action="">
			<?php wp_nonce_field( 'mdb_header_save', 'mdb_header_nonce' ); ?>

			<table class="form-table" role="presentation">

				<tr>
					<th scope="row"><?php esc_html_e( 'Enable Custom Header', 'mdb-theme' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="mdb_header_enabled" value="1" <?php checked( $enabled ); ?>>
							<?php esc_html_e( 'Show the custom theme header on the front end', 'mdb-theme' ); ?>
						</label>
					</td>
				</tr>

				<tr><th colspan="2"><h2 style="margin:0"><?php esc_html_e( 'Logo', 'mdb-theme' ); ?></h2></th></tr>

				<tr>
					<th scope="row"><label for="mdb_header_logo_image"><?php esc_html_e( 'Logo Image', 'mdb-theme' ); ?></label></th>
					<td>
						<input type="hidden" name="mdb_header_logo_image" id="mdb_header_logo_image" value="<?php echo esc_attr( (int) $logo_img_id ); ?>">
						<div id="mdb-logo-preview" style="margin-bottom:8px">
							<?php if ( $logo_img_src ) : ?>
								<img src="<?php echo esc_url( $logo_img_src ); ?>" style="max-height:60px;display:block">
							<?php endif; ?>
						</div>
						<button type="button" class="button" id="mdb-logo-upload-btn"><?php esc_html_e( 'Select / Change Logo', 'mdb-theme' ); ?></button>
						<button type="button" class="button" id="mdb-logo-remove-btn" style="<?php echo $logo_img_id ? '' : 'display:none'; ?>"><?php esc_html_e( 'Remove Logo', 'mdb-theme' ); ?></button>
					</td>
				</tr>

				<tr>
					<th scope="row"><label for="mdb_header_logo_url"><?php esc_html_e( 'Logo Link URL', 'mdb-theme' ); ?></label></th>
					<td><input type="text" name="mdb_header_logo_url" id="mdb_header_logo_url" class="regular-text" value="<?php echo esc_attr( $logo_url ); ?>"></td>
				</tr>

				<tr><th colspan="2"><h2 style="margin:0"><?php esc_html_e( 'Top Bar', 'mdb-theme' ); ?></h2></th></tr>

				<tr>
					<th scope="row"><label for="mdb_header_phone"><?php esc_html_e( 'Phone Number', 'mdb-theme' ); ?></label></th>
					<td><input type="text" name="mdb_header_phone" id="mdb_header_phone" class="regular-text" value="<?php echo esc_attr( $phone ); ?>">
					<p class="description"><?php esc_html_e( 'Displayed with a phone icon. Leave blank to hide.', 'mdb-theme' ); ?></p></td>
				</tr>

				<tr>
					<th scope="row"><label for="mdb_header_email"><?php esc_html_e( 'Email Address', 'mdb-theme' ); ?></label></th>
					<td><input type="email" name="mdb_header_email" id="mdb_header_email" class="regular-text" value="<?php echo esc_attr( $email ); ?>">
					<p class="description"><?php esc_html_e( 'Displayed with an envelope icon. Leave blank to hide.', 'mdb-theme' ); ?></p></td>
				</tr>

				<tr><th colspan="2"><h2 style="margin:0"><?php esc_html_e( 'Login Link', 'mdb-theme' ); ?></h2></th></tr>

				<tr>
					<th scope="row"><label for="mdb_header_login_text"><?php esc_html_e( 'Login Link Text', 'mdb-theme' ); ?></label></th>
					<td><input type="text" name="mdb_header_login_text" id="mdb_header_login_text" class="regular-text" value="<?php echo esc_attr( $login_text ); ?>">
					<p class="description"><?php esc_html_e( 'Leave blank to hide the login link.', 'mdb-theme' ); ?></p></td>
				</tr>

				<tr>
					<th scope="row"><label for="mdb_header_login_url"><?php esc_html_e( 'Login URL', 'mdb-theme' ); ?></label></th>
					<td><input type="text" name="mdb_header_login_url" id="mdb_header_login_url" class="regular-text" value="<?php echo esc_attr( $login_url ); ?>"></td>
				</tr>

				<tr><th colspan="2"><h2 style="margin:0"><?php esc_html_e( 'Cart Icon', 'mdb-theme' ); ?></h2></th></tr>

				<tr>
					<th scope="row"><?php esc_html_e( 'Show Cart Icon', 'mdb-theme' ); ?></th>
					<td>
						<label>
							<input type="checkbox" name="mdb_header_show_cart" value="1" <?php checked( $show_cart ); ?>>
							<?php esc_html_e( 'Display the WooCommerce cart icon in the top bar', 'mdb-theme' ); ?>
						</label>
					</td>
				</tr>

				<tr><th colspan="2"><h2 style="margin:0"><?php esc_html_e( 'Navigation Menu', 'mdb-theme' ); ?></h2></th></tr>

				<tr>
					<th scope="row"><label for="mdb_header_nav_menu"><?php esc_html_e( 'Main Navigation Menu', 'mdb-theme' ); ?></label></th>
					<td>
						<select name="mdb_header_nav_menu" id="mdb_header_nav_menu">
							<option value="0"><?php esc_html_e( '— Select a menu —', 'mdb-theme' ); ?></option>
							<?php foreach ( $menus as $menu ) : ?>
								<option value="<?php echo esc_attr( $menu->term_id ); ?>" <?php selected( $nav_menu, $menu->term_id ); ?>>
									<?php echo esc_html( $menu->name ); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<p class="description">
							<?php printf(
								/* translators: %s: URL to Menus admin screen */
								wp_kses( __( 'Manage menus under <a href="%s">Appearance → Menus</a>.', 'mdb-theme' ), array( 'a' => array( 'href' => array() ) ) ),
								esc_url( admin_url( 'nav-menus.php' ) )
							); ?>
						</p>
					</td>
				</tr>

				<tr><th colspan="2"><h2 style="margin:0"><?php esc_html_e( 'Free Samples Link', 'mdb-theme' ); ?></h2></th></tr>

				<tr>
					<th scope="row"><label for="mdb_header_free_samples_text"><?php esc_html_e( 'Link Text', 'mdb-theme' ); ?></label></th>
					<td><input type="text" name="mdb_header_free_samples_text" id="mdb_header_free_samples_text" class="regular-text" value="<?php echo esc_attr( $fs_text ); ?>">
					<p class="description"><?php esc_html_e( 'Leave blank to hide.', 'mdb-theme' ); ?></p></td>
				</tr>

				<tr>
					<th scope="row"><label for="mdb_header_free_samples_url"><?php esc_html_e( 'Link URL', 'mdb-theme' ); ?></label></th>
					<td><input type="text" name="mdb_header_free_samples_url" id="mdb_header_free_samples_url" class="regular-text" value="<?php echo esc_attr( $fs_url ); ?>"><p class="description"><?php esc_html_e( 'Accepts full URLs (https://...) or relative paths (/page/).', 'mdb-theme' ); ?></p></td>
				</tr>

			</table>

			<?php submit_button( __( 'Save Settings', 'mdb-theme' ) ); ?>
		</form>
	</div>

	<script>
	(function($){
		$('#mdb-logo-upload-btn').on('click', function(e){
			e.preventDefault();
			var frame = wp.media({ title: 'Select Logo', button: { text: 'Use this image' }, multiple: false });
			frame.on('select', function(){
				var att = frame.state().get('selection').first().toJSON();
				$('#mdb_header_logo_image').val(att.id);
				$('#mdb-logo-preview').html('<img src="' + att.url + '" style="max-height:60px;display:block">');
				$('#mdb-logo-remove-btn').show();
			});
			frame.open();
		});
		$('#mdb-logo-remove-btn').on('click', function(e){
			e.preventDefault();
			$('#mdb_header_logo_image').val('');
			$('#mdb-logo-preview').html('');
			$(this).hide();
		});
	})(jQuery);
	</script>
	<?php
}

// Enqueue wp.media on our settings page.
function mdb_header_admin_enqueue( $hook ) {
	if ( 'appearance_page_mdb-header-settings' !== $hook ) {
		return;
	}
	wp_enqueue_media();
}
add_action( 'admin_enqueue_scripts', 'mdb_header_admin_enqueue' );


/**
 * Register all Customizer settings and controls.
 */
function mdb_header_customizer_register( WP_Customize_Manager $wp_customize ) {

	// =========================================================================
	// Panel
	// =========================================================================
	$wp_customize->add_panel( 'mdb_header_panel', array(
		'title'       => __( 'Theme Header', 'mdb-theme' ),
		'description' => __( 'Controls for the custom theme header. Enable the header below to display it on the front end.', 'mdb-theme' ),
		'priority'    => 25,
	) );

	// =========================================================================
	// Section: General
	// =========================================================================
	$wp_customize->add_section( 'mdb_header_general', array(
		'title'    => __( 'General', 'mdb-theme' ),
		'panel'    => 'mdb_header_panel',
		'priority' => 10,
	) );

	// --- Enable / Disable ---
	$wp_customize->add_setting( 'mdb_header_enabled', array(
		'default'           => true,
		'sanitize_callback' => 'mdb_sanitize_checkbox',
		'transport'         => 'refresh',
	) );
	$wp_customize->add_control( 'mdb_header_enabled', array(
		'label'       => __( 'Enable custom header', 'mdb-theme' ),
		'description' => __( 'When enabled this header renders before Elementor content. Disable it if you want to use the Elementor header instead.', 'mdb-theme' ),
		'section'     => 'mdb_header_general',
		'type'        => 'checkbox',
	) );

	// =========================================================================
	// Section: Logo
	// =========================================================================
	$wp_customize->add_section( 'mdb_header_logo', array(
		'title'    => __( 'Logo', 'mdb-theme' ),
		'panel'    => 'mdb_header_panel',
		'priority' => 20,
	) );

	// Logo image
	$wp_customize->add_setting( 'mdb_header_logo_image', array(
		'default'           => get_theme_mod( 'custom_logo' ),
		'sanitize_callback' => 'absint',
		'transport'         => 'refresh',
	) );
	$wp_customize->add_control( new WP_Customize_Media_Control( $wp_customize, 'mdb_header_logo_image', array(
		'label'     => __( 'Logo image', 'mdb-theme' ),
		'section'   => 'mdb_header_logo',
		'mime_type' => 'image',
	) ) );

	// Logo link URL
	$wp_customize->add_setting( 'mdb_header_logo_url', array(
		'default'           => home_url( '/' ),
		'sanitize_callback' => 'esc_url_raw',
		'transport'         => 'refresh',
	) );
	$wp_customize->add_control( 'mdb_header_logo_url', array(
		'label'   => __( 'Logo link URL', 'mdb-theme' ),
		'section' => 'mdb_header_logo',
		'type'    => 'url',
	) );

	// =========================================================================
	// Section: Top Bar
	// =========================================================================
	$wp_customize->add_section( 'mdb_header_topbar', array(
		'title'    => __( 'Top Bar', 'mdb-theme' ),
		'panel'    => 'mdb_header_panel',
		'priority' => 30,
	) );

	// Phone number
	$wp_customize->add_setting( 'mdb_header_phone', array(
		'default'           => '1300 011 561',
		'sanitize_callback' => 'sanitize_text_field',
		'transport'         => 'refresh',
	) );
	$wp_customize->add_control( 'mdb_header_phone', array(
		'label'       => __( 'Phone number', 'mdb-theme' ),
		'description' => __( 'Displayed with a phone icon. Spaces are stripped for the tel: link.', 'mdb-theme' ),
		'section'     => 'mdb_header_topbar',
		'type'        => 'text',
	) );

	// Email address
	$wp_customize->add_setting( 'mdb_header_email', array(
		'default'           => 'hello@mydirectblinds.com.au',
		'sanitize_callback' => 'sanitize_email',
		'transport'         => 'refresh',
	) );
	$wp_customize->add_control( 'mdb_header_email', array(
		'label'   => __( 'Email address', 'mdb-theme' ),
		'section' => 'mdb_header_topbar',
		'type'    => 'email',
	) );

	// =========================================================================
	// Section: Login
	// =========================================================================
	$wp_customize->add_section( 'mdb_header_login', array(
		'title'    => __( 'Login Link', 'mdb-theme' ),
		'panel'    => 'mdb_header_panel',
		'priority' => 40,
	) );

	// Login link text
	$wp_customize->add_setting( 'mdb_header_login_text', array(
		'default'           => 'Login',
		'sanitize_callback' => 'sanitize_text_field',
		'transport'         => 'refresh',
	) );
	$wp_customize->add_control( 'mdb_header_login_text', array(
		'label'       => __( 'Login link text', 'mdb-theme' ),
		'description' => __( 'Leave blank to hide the login link.', 'mdb-theme' ),
		'section'     => 'mdb_header_login',
		'type'        => 'text',
	) );

	// Login URL
	$wp_customize->add_setting( 'mdb_header_login_url', array(
		'default'           => wp_login_url(),
		'sanitize_callback' => 'esc_url_raw',
		'transport'         => 'refresh',
	) );
	$wp_customize->add_control( 'mdb_header_login_url', array(
		'label'       => __( 'Login URL', 'mdb-theme' ),
		'description' => __( 'Default is the WordPress login page. Change to a WooCommerce My Account page if preferred.', 'mdb-theme' ),
		'section'     => 'mdb_header_login',
		'type'        => 'url',
	) );

	// =========================================================================
	// Section: Cart
	// =========================================================================
	$wp_customize->add_section( 'mdb_header_cart', array(
		'title'    => __( 'Cart Icon', 'mdb-theme' ),
		'panel'    => 'mdb_header_panel',
		'priority' => 50,
	) );

	// Show cart
	$wp_customize->add_setting( 'mdb_header_show_cart', array(
		'default'           => true,
		'sanitize_callback' => 'mdb_sanitize_checkbox',
		'transport'         => 'refresh',
	) );
	$wp_customize->add_control( 'mdb_header_show_cart', array(
		'label'   => __( 'Show cart icon', 'mdb-theme' ),
		'section' => 'mdb_header_cart',
		'type'    => 'checkbox',
	) );

	// =========================================================================
	// Section: Navigation
	// =========================================================================
	$wp_customize->add_section( 'mdb_header_navigation', array(
		'title'    => __( 'Navigation Menu', 'mdb-theme' ),
		'panel'    => 'mdb_header_panel',
		'priority' => 60,
	) );

	// Build a list of registered menus
	$menus        = wp_get_nav_menus();
	$menu_choices = array( 0 => __( '— Select a menu —', 'mdb-theme' ) );
	foreach ( $menus as $menu ) {
		$menu_choices[ $menu->term_id ] = $menu->name;
	}

	$wp_customize->add_setting( 'mdb_header_nav_menu', array(
		'default'           => 0,
		'sanitize_callback' => 'absint',
		'transport'         => 'refresh',
	) );
	$wp_customize->add_control( 'mdb_header_nav_menu', array(
		'label'       => __( 'Main navigation menu', 'mdb-theme' ),
		'description' => __( 'Select the WordPress menu to display in the header. Manage menus under Appearance → Menus.', 'mdb-theme' ),
		'section'     => 'mdb_header_navigation',
		'type'        => 'select',
		'choices'     => $menu_choices,
	) );

	// =========================================================================
	// Section: Free Samples
	// =========================================================================
	$wp_customize->add_section( 'mdb_header_free_samples', array(
		'title'    => __( 'Free Samples Link', 'mdb-theme' ),
		'panel'    => 'mdb_header_panel',
		'priority' => 70,
	) );

	// Free Samples text
	$wp_customize->add_setting( 'mdb_header_free_samples_text', array(
		'default'           => '» Free Samples «',
		'sanitize_callback' => 'sanitize_text_field',
		'transport'         => 'refresh',
	) );
	$wp_customize->add_control( 'mdb_header_free_samples_text', array(
		'label'       => __( 'Link text', 'mdb-theme' ),
		'description' => __( 'Leave blank to hide this link.', 'mdb-theme' ),
		'section'     => 'mdb_header_free_samples',
		'type'        => 'text',
	) );

	// Free Samples URL
	$wp_customize->add_setting( 'mdb_header_free_samples_url', array(
		'default'           => '/choose/free-samples/',
		'sanitize_callback' => 'esc_url_raw',
		'transport'         => 'refresh',
	) );
	$wp_customize->add_control( 'mdb_header_free_samples_url', array(
		'label'   => __( 'Link URL', 'mdb-theme' ),
		'section' => 'mdb_header_free_samples',
		'type'    => 'url',
	) );
}
add_action( 'customize_register', 'mdb_header_customizer_register' );


// =============================================================================
// Sanitization helpers
// =============================================================================
if ( ! function_exists( 'mdb_sanitize_checkbox' ) ) {
	function mdb_sanitize_checkbox( $value ) {
		return (bool) $value;
	}
}
