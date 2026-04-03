<?php
/**
 * Custom Theme Header Template
 *
 * Renders the two-row header independently of Elementor.
 * Row 1 (top bar): phone · email | login + cart
 * Row 2 (main bar): logo | nav menu | free-samples link
 *
 * All content fields are controlled via the WordPress Customizer
 * under Appearance → Customize → Theme Header.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Output the custom header HTML.
 * Hooked to 'wp_body_open' so it appears before Elementor's content.
 */
function mdb_render_custom_header() {
	// Only render when the setting is enabled.
	if ( ! get_theme_mod( 'mdb_header_enabled', true ) ) {
		return;
	}

	// -----------------------------------------------------------------------
	// Customizer values
	// -----------------------------------------------------------------------
	$phone_number   = get_theme_mod( 'mdb_header_phone', '1300 011 561' );
	$phone_href     = 'tel:' . preg_replace( '/\D/', '', $phone_number );
	$email_address  = get_theme_mod( 'mdb_header_email', 'hello@mydirectblinds.com.au' );
	$login_text     = get_theme_mod( 'mdb_header_login_text', 'Login' );
	$login_url      = get_theme_mod( 'mdb_header_login_url', wp_login_url() );
	$logo_url       = get_theme_mod( 'mdb_header_logo_url', get_site_url() . '/' );
	$free_samp_text = get_theme_mod( 'mdb_header_free_samples_text', '» Free Samples «' );
	$free_samp_url  = get_theme_mod( 'mdb_header_free_samples_url', '/choose/free-samples/' );
	$show_cart      = get_theme_mod( 'mdb_header_show_cart', true );
	$nav_menu_id    = get_theme_mod( 'mdb_header_nav_menu', 0 );

	// Logo image (uses WordPress custom logo or customizer override)
	$logo_img_id    = get_theme_mod( 'mdb_header_logo_image', get_theme_mod( 'custom_logo' ) );
	$logo_img_src   = $logo_img_id ? wp_get_attachment_image_url( $logo_img_id, 'full' ) : '';
	$logo_img_alt   = $logo_img_id ? get_post_meta( $logo_img_id, '_wp_attachment_image_alt', true ) : get_bloginfo( 'name' );

	// Site icon (used as favicon in the mobile sticky header).
	// get_site_icon_url() returns the icon set under Customizer → Site Identity.
	$site_icon_url  = get_site_icon_url( 64 );

	// Cart count (WooCommerce)
	$cart_count = 0;
	if ( $show_cart && function_exists( 'WC' ) && WC()->cart ) {
		$cart_count = (int) WC()->cart->get_cart_contents_count();
	}
	$cart_url = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : '/cart/';

	// Nav menu
	$nav_html = '';
	if ( $nav_menu_id ) {
		$nav_html = wp_nav_menu( array(
			'menu'            => $nav_menu_id,
			'container'       => false,
			'menu_class'      => 'mdb-header-nav-list',
			'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
			'echo'            => false,
			'depth'           => 0,
			'walker'          => new MDB_Header_Nav_Walker(),
		) );
	}

	// Mobile nav (accordion style for off-canvas drawer)
	$mobile_nav_html = '';
	if ( $nav_menu_id ) {
		$mobile_nav_html = wp_nav_menu( array(
			'menu'        => $nav_menu_id,
			'container'   => false,
			'menu_class'  => 'mdb-mnav-list',
			'items_wrap'  => '<ul id="%1$s" class="%2$s">%3$s</ul>',
			'echo'        => false,
			'depth'       => 2,
			'walker'      => new MDB_Mobile_Nav_Walker(),
		) );
	}

	// -----------------------------------------------------------------------
	// SVG icons (inline, no extra HTTP requests)
	// -----------------------------------------------------------------------
	$phone_svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" aria-hidden="true" focusable="false"><path d="M497.39 361.8l-112-48a24 24 0 0 0-28 6.9l-49.6 60.6A370.66 370.66 0 0 1 130.6 204.11l60.6-49.6a23.94 23.94 0 0 0 6.9-28l-48-112A24.16 24.16 0 0 0 122.6.61l-104 24A24 24 0 0 0 0 48c0 256.5 207.9 464 464 464a24 24 0 0 0 23.4-18.6l24-104a24.29 24.29 0 0 0-14.01-27.6z"/></svg>';
	$email_svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" aria-hidden="true" focusable="false"><path d="M502.3 190.8c3.9-3.1 9.7-.2 9.7 4.7V400c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V195.6c0-5 5.7-7.8 9.7-4.7 22.4 17.4 52.1 39.5 154.1 113.6 21.1 15.4 56.7 47.8 92.2 47.6 35.7.3 72-32.8 92.3-47.6 102-74.1 131.6-96.3 154-113.7zM256 320c23.2.4 56.6-29.2 73.4-41.4 132.7-96.3 142.8-104.7 173.4-128.7 5.8-4.5 9.2-11.5 9.2-18.9v-19c0-26.5-21.5-48-48-48H48C21.5 64 0 85.5 0 112v19c0 7.4 3.4 14.3 9.2 18.9 30.6 23.9 40.7 32.4 173.4 128.7 16.8 12.2 50.2 41.8 73.4 41.4z"/></svg>';
	$cart_svg  = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" aria-hidden="true" focusable="false"><path d="M0 24C0 10.7 10.7 0 24 0H69.5c22 0 41.5 12.8 50.6 32h411c26.3 0 45.5 25 38.6 50.4l-41 152.3c-8.5 31.4-37 53.3-69.5 53.3H170.7l5.4 28.5c2.2 11.3 12.1 19.5 23.6 19.5H488c13.3 0 24 10.7 24 24s-10.7 24-24 24H199.7c-34.6 0-64.3-24.6-70.7-58.5L77.4 54.5c-.7-3.8-4-6.5-7.9-6.5H24C10.7 48 0 37.3 0 24zM128 464a48 48 0 1 1 96 0 48 48 0 1 1 -96 0zm336-48a48 48 0 1 1 0 96 48 48 0 1 1 0-96z"/></svg>';

	?>
	<header class="mdb-custom-header" id="mdb-custom-header" role="banner"
		<?php if ( $site_icon_url ) : ?>data-favicon-src="<?php echo esc_url( $site_icon_url ); ?>"<?php endif; ?>
	>

		<div class="mdb-header-inner">

			<!-- Logo — left column, spans full header height (topbar + mainbar) -->
			<a class="mdb-header-logo" href="<?php echo esc_url( $logo_url ); ?>">
				<?php if ( $logo_img_src ) : ?>
				<img
					src="<?php echo esc_url( $logo_img_src ); ?>"
					alt="<?php echo esc_attr( $logo_img_alt ); ?>"
					width="245"
					height="64"
				>
				<?php else : ?>
				<span class="mdb-header-logo-text"><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>
				<?php endif; ?>
			</a>

			<!-- Right column: dark topbar row stacked above white nav row -->
			<div class="mdb-header-right">

				<!-- ============================================================
				     TOP BAR: phone · email  |  login · cart
				     ============================================================ -->
				<div class="mdb-header-topbar">

					<div class="mdb-topbar-left">
						<?php if ( $phone_number ) : ?>
						<a class="mdb-topbar-link" href="<?php echo esc_attr( $phone_href ); ?>">
							<?php echo $phone_svg; // phpcs:ignore ?>
							<span><?php echo esc_html( $phone_number ); ?></span>
						</a>
						<span class="mdb-topbar-sep" aria-hidden="true">•</span>
						<?php endif; ?>

						<?php if ( $email_address ) : ?>
						<a class="mdb-topbar-link" href="mailto:<?php echo esc_attr( $email_address ); ?>">
							<?php echo $email_svg; // phpcs:ignore ?>
							<span><?php echo esc_html( $email_address ); ?></span>
						</a>
						<?php endif; ?>
					</div>

					<div class="mdb-topbar-right">
						<?php if ( $login_text ) : ?>
						<a class="mdb-topbar-login" href="<?php echo esc_url( $login_url ); ?>">
							<strong><?php echo esc_html( $login_text ); ?></strong>
						</a>
						<?php endif; ?>

						<?php if ( $show_cart ) : ?>
						<button
							class="mdb-topbar-cart"
							type="button"
							aria-label="<?php echo esc_attr( sprintf( __( 'Cart (%d items)', 'mdb-theme' ), $cart_count ) ); ?>"
							data-cart-url="<?php echo esc_url( $cart_url ); ?>"
						>
							<?php echo $cart_svg; // phpcs:ignore ?>
							<span class="mdb-topbar-cart-count<?php echo $cart_count > 0 ? '' : ' is-hidden'; ?>">
								<?php echo (int) $cart_count; ?>
							</span>
						</button>
						<?php endif; ?>
					</div>

				</div><!-- .mdb-header-topbar -->

				<!-- ============================================================
				     NAV BAR: nav menu  |  free samples
				     ============================================================ -->
				<div class="mdb-header-mainbar">

					<?php if ( $nav_html ) : ?>
					<nav class="mdb-header-nav" aria-label="<?php esc_attr_e( 'Main navigation', 'mdb-theme' ); ?>">
						<?php echo $nav_html; // phpcs:ignore — walker controls escaping ?>
					</nav>
					<?php endif; ?>

					<?php if ( $free_samp_text && $free_samp_url ) : ?>
					<a class="mdb-header-free-samples" href="<?php echo esc_url( $free_samp_url ); ?>">
						<?php echo esc_html( $free_samp_text ); ?>
					</a>
					<?php endif; ?>

				</div><!-- .mdb-header-mainbar -->

			</div><!-- .mdb-header-right -->

			<!-- Mobile: cart icon + hamburger toggle (visible only on mobile) -->
			<div class="mdb-mobile-actions">
				<?php if ( $show_cart ) : ?>
				<button
					class="mdb-topbar-cart mdb-mobile-cart"
					type="button"
					aria-label="<?php echo esc_attr( sprintf( __( 'Cart (%d items)', 'mdb-theme' ), $cart_count ) ); ?>"
					data-cart-url="<?php echo esc_url( $cart_url ); ?>"
				>
					<?php echo $cart_svg; // phpcs:ignore ?>
					<span class="mdb-topbar-cart-count<?php echo $cart_count > 0 ? '' : ' is-hidden'; ?>">
						<?php echo (int) $cart_count; ?>
					</span>
				</button>
				<?php endif; ?>
				<button
					class="mdb-mobile-toggle"
					type="button"
					aria-label="<?php esc_attr_e( 'Open navigation menu', 'mdb-theme' ); ?>"
					aria-expanded="false"
					aria-controls="mdb-mobile-drawer"
				>
					<span class="mdb-mobile-toggle__bar"></span>
					<span class="mdb-mobile-toggle__bar"></span>
					<span class="mdb-mobile-toggle__bar"></span>
				</button>
			</div><!-- .mdb-mobile-actions -->

		</div><!-- .mdb-header-inner -->

	</header><!-- .mdb-custom-header -->

	<!-- ======================================================================
	     Off-canvas mobile navigation drawer
	     ====================================================================== -->
	<div id="mdb-mobile-drawer" class="mdb-mobile-drawer" aria-hidden="true">
		<div class="mdb-mobile-drawer__overlay"></div>
		<div class="mdb-mobile-drawer__panel" role="dialog" aria-label="<?php esc_attr_e( 'Site navigation', 'mdb-theme' ); ?>">

			<div class="mdb-mobile-drawer__header">
				<a class="mdb-mobile-drawer__logo" href="<?php echo esc_url( $logo_url ); ?>">
					<?php if ( $logo_img_src ) : ?>
					<img
						src="<?php echo esc_url( $logo_img_src ); ?>"
						alt="<?php echo esc_attr( $logo_img_alt ); ?>"
						width="160"
						height="42"
					>
					<?php else : ?>
					<span><?php echo esc_html( get_bloginfo( 'name' ) ); ?></span>
					<?php endif; ?>
				</a>
				<button
					class="mdb-mobile-drawer__close"
					type="button"
					aria-label="<?php esc_attr_e( 'Close navigation menu', 'mdb-theme' ); ?>"
				>
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
				</button>
			</div><!-- .mdb-mobile-drawer__header -->

			<div class="mdb-mobile-drawer__body">
				<?php if ( $mobile_nav_html ) : ?>
				<nav class="mdb-mobile-drawer__nav" aria-label="<?php esc_attr_e( 'Mobile navigation', 'mdb-theme' ); ?>">
					<?php echo $mobile_nav_html; // phpcs:ignore — walker controls escaping ?>
				</nav>
				<?php endif; ?>

				<?php if ( $free_samp_text && $free_samp_url ) : ?>
				<a class="mdb-mnav-free-samples" href="<?php echo esc_url( $free_samp_url ); ?>">
					<?php echo esc_html( $free_samp_text ); ?>
				</a>
				<?php endif; ?>
			</div><!-- .mdb-mobile-drawer__body -->

			<div class="mdb-mobile-drawer__footer">
				<?php if ( $phone_number ) : ?>
				<a class="mdb-mnav-contact" href="<?php echo esc_attr( $phone_href ); ?>">
					<?php echo $phone_svg; // phpcs:ignore ?>
					<span><?php echo esc_html( $phone_number ); ?></span>
				</a>
				<?php endif; ?>

				<?php if ( $email_address ) : ?>
				<a class="mdb-mnav-contact" href="mailto:<?php echo esc_attr( $email_address ); ?>">
					<?php echo $email_svg; // phpcs:ignore ?>
					<span><?php echo esc_html( $email_address ); ?></span>
				</a>
				<?php endif; ?>

				<?php if ( $login_text ) : ?>
				<a class="mdb-mnav-login" href="<?php echo esc_url( $login_url ); ?>">
					<?php echo esc_html( $login_text ); ?>
				</a>
				<?php endif; ?>
			</div><!-- .mdb-mobile-drawer__footer -->

		</div><!-- .mdb-mobile-drawer__panel -->
	</div><!-- #mdb-mobile-drawer -->
	<?php
}
add_action( 'wp_body_open', 'mdb_render_custom_header', 5 );

/**
 * Suppress the Hello Biz fallback default header when our custom header is enabled.
 * The parent theme renders it via the 'hello-plus-theme/display-default-header' filter.
 */
function mdb_suppress_default_header( $display ) {
	if ( get_theme_mod( 'mdb_header_enabled', true ) ) {
		return false;
	}
	return $display;
}
add_filter( 'hello-plus-theme/display-default-header', 'mdb_suppress_default_header' );

/**
 * Render the standalone mini-cart side panel in the footer.
 *
 * This is completely independent of any Elementor widget. WooCommerce's own
 * wc-cart-fragments.js automatically refreshes `div.widget_shopping_cart_content`
 * via AJAX whenever the cart changes, so no Elementor dependency is needed.
 *
 * Our cart icon buttons (in the topbar and sticky header) call openMdbCart()
 * in custom.js to open this panel.
 */
function mdb_render_mini_cart_panel() {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return;
	}
	$cart_url = wc_get_cart_url();
	?>
	<div id="mdb-mini-cart" class="mdb-mini-cart" aria-hidden="true" role="dialog" aria-label="<?php esc_attr_e( 'Shopping Cart', 'mdb-theme' ); ?>">
		<div class="mdb-mini-cart__overlay" aria-hidden="true"></div>
		<div class="mdb-mini-cart__panel" role="document">
			<div class="mdb-mini-cart__header">
				<span class="mdb-mini-cart__title"><?php esc_html_e( 'Your Cart', 'mdb-theme' ); ?></span>
				<button class="mdb-mini-cart__close" type="button" aria-label="<?php esc_attr_e( 'Close cart', 'mdb-theme' ); ?>">
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
				</button>
			</div>
			<div class="mdb-mini-cart__body">
				<div class="widget_shopping_cart_content">
					<?php woocommerce_mini_cart(); ?>
				</div>
			</div>
		</div>
	</div>
	<?php
}
add_action( 'wp_footer', 'mdb_render_mini_cart_panel', 100 );


// =============================================================================
// Walker: renders menu item icons stored in the custom _mdb_menu_icon meta field.
// =============================================================================
if ( ! class_exists( 'MDB_Header_Nav_Walker' ) ) {
	class MDB_Header_Nav_Walker extends Walker_Nav_Menu {

		// Opening of a sub-level. Depth 0 = children of a top-level item → mega panel.
		public function start_lvl( &$output, $depth = 0, $args = null ) {
			if ( 0 === $depth ) {
				$output .= '<div class="mdb-mega-panel"><ul class="mdb-mega-grid">';
			} else {
				$output .= '<ul class="sub-menu">';
			}
		}

		// Closing of a sub-level.
		public function end_lvl( &$output, $depth = 0, $args = null ) {
			if ( 0 === $depth ) {
				$output .= '</ul></div>';
			} else {
				$output .= '</ul>';
			}
		}

		public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
			$classes      = empty( $item->classes ) ? array() : (array) $item->classes;
			$classes[]    = 'menu-item-' . $item->ID;
			// WordPress reliably adds 'menu-item-has-children' to items with sub-items.
			$has_children = in_array( 'menu-item-has-children', $classes, true );

			if ( 0 === $depth && $has_children ) {
				$classes[] = 'mdb-mega-parent';
			}

			$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) );
			$class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';

			$output .= '<li' . $class_names . '>';

			$atts           = array();
			$atts['href']   = ! empty( $item->url ) ? $item->url : '';
			$atts['title']  = ! empty( $item->attr_title ) ? $item->attr_title : '';
			$atts['target'] = ! empty( $item->target ) ? $item->target : '';
			$atts['rel']    = ! empty( $item->xfn ) ? $item->xfn : '';
			$atts           = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth );

			$attributes = '';
			foreach ( $atts as $attr => $value ) {
				if ( ! empty( $value ) ) {
					$attributes .= ' ' . esc_attr( $attr ) . '="' . esc_attr( $value ) . '"';
				}
			}

			$title    = apply_filters( 'the_title', $item->title, $item->ID );
			$icon_id  = get_post_meta( $item->ID, '_mdb_menu_icon', true );
			$icon_src = $icon_id ? wp_get_attachment_image_url( (int) $icon_id, 'thumbnail' ) : '';

			$icon_html = $icon_src
				? '<img class="mdb-nav-icon" src="' . esc_url( $icon_src ) . '" alt="" aria-hidden="true" loading="lazy">'
				: '';

			if ( 0 === $depth && $has_children ) {
				// Top-level item with children → toggle button (panel opens on hover/focus).
				$chevron = '<svg class="mdb-nav-chevron" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" aria-hidden="true"><path d="M143 352.3L7 216.3c-9.4-9.4-9.4-24.6 0-33.9l22.6-22.6c9.4-9.4 24.6-9.4 33.9 0l96.4 96.4 96.4-96.4c9.4-9.4 24.6-9.4 33.9 0l22.6 22.6c9.4 9.4 9.4 24.6 0 33.9l-136 136c-9.2 9.4-24.4 9.4-33.8 0z"/></svg>';
				$output .= '<button class="mdb-nav-link mdb-nav-toggle" type="button" aria-haspopup="true" aria-expanded="false">'
					. $icon_html . '<span>' . esc_html( $title ) . '</span>' . $chevron
					. '</button>';
			} else {
				// Regular link.
				$output .= '<a class="mdb-nav-link"' . $attributes . '>'
					. $icon_html . '<span>' . esc_html( $title ) . '</span>'
					. '</a>';
			}
		}

		public function end_el( &$output, $item, $depth = 0, $args = null ) {
			$output .= '</li>';
		}
	}
}

// =============================================================================
// Mobile Walker: renders an accordion-friendly nav for the off-canvas drawer.
// Top-level items with children become toggle buttons; sub-items are a flat list.
// =============================================================================
if ( ! class_exists( 'MDB_Mobile_Nav_Walker' ) ) {
	class MDB_Mobile_Nav_Walker extends Walker_Nav_Menu {

		public function start_lvl( &$output, $depth = 0, $args = null ) {
			$output .= '<ul class="mdb-mnav-sub" hidden>';
		}

		public function end_lvl( &$output, $depth = 0, $args = null ) {
			$output .= '</ul>';
		}

		public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
			$classes      = empty( $item->classes ) ? array() : (array) $item->classes;
			$classes[]    = 'menu-item-' . $item->ID;
			$has_children = in_array( 'menu-item-has-children', $classes, true );

			if ( 0 === $depth ) {
				$classes[] = 'mdb-mnav-item';
				if ( $has_children ) {
					$classes[] = 'mdb-mnav-item--has-children';
				}
			} else {
				$classes[] = 'mdb-mnav-sub-item';
			}

			$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) );
			$class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';
			$output .= '<li' . $class_names . '>';

			$atts           = array();
			$atts['href']   = ! empty( $item->url ) ? $item->url : '';
			$atts['title']  = ! empty( $item->attr_title ) ? $item->attr_title : '';
			$atts['target'] = ! empty( $item->target ) ? $item->target : '';
			$atts['rel']    = ! empty( $item->xfn ) ? $item->xfn : '';
			$atts           = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth );

			$attributes = '';
			foreach ( $atts as $attr => $value ) {
				if ( ! empty( $value ) ) {
					$attributes .= ' ' . esc_attr( $attr ) . '="' . esc_attr( $value ) . '"';
				}
			}

			$title    = apply_filters( 'the_title', $item->title, $item->ID );
			$icon_id  = get_post_meta( $item->ID, '_mdb_menu_icon', true );
			$icon_src = $icon_id ? wp_get_attachment_image_url( (int) $icon_id, 'thumbnail' ) : '';
			$icon_html = $icon_src
				? '<img class="mdb-nav-icon" src="' . esc_url( $icon_src ) . '" alt="" aria-hidden="true" loading="lazy">'
				: '';

			$chevron = '<svg class="mdb-mnav-chevron" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512" aria-hidden="true"><path d="M143 352.3L7 216.3c-9.4-9.4-9.4-24.6 0-33.9l22.6-22.6c9.4-9.4 24.6-9.4 33.9 0l96.4 96.4 96.4-96.4c9.4-9.4 24.6-9.4 33.9 0l22.6 22.6c9.4 9.4 9.4 24.6 0 33.9l-136 136c-9.2 9.4-24.4 9.4-33.8 0z"/></svg>';

			if ( 0 === $depth && $has_children ) {
				// Toggle button — opens the hidden sub-list via JS accordion.
				$output .= '<button class="mdb-mnav-link mdb-mnav-toggle" type="button" aria-expanded="false">'
					. '<span class="mdb-mnav-label">' . $icon_html . '<span>' . esc_html( $title ) . '</span></span>'
					. $chevron
					. '</button>';
			} else {
				// Regular link.
				$output .= '<a class="mdb-mnav-link"' . $attributes . '>'
					. $icon_html . '<span>' . esc_html( $title ) . '</span>'
					. '</a>';
			}
		}

		public function end_el( &$output, $item, $depth = 0, $args = null ) {
			$output .= '</li>';
		}
	}
}

// =============================================================================
// Menu item custom fields — adds an icon image picker to each item in
// Appearance → Menus.
// =============================================================================

/**
 * Render the icon field inside each menu item row.
 */
function mdb_nav_menu_item_icon_field( $item_id ) {
	$icon_id  = get_post_meta( $item_id, '_mdb_menu_icon', true );
	$icon_src = $icon_id ? wp_get_attachment_image_url( (int) $icon_id, 'thumbnail' ) : '';
	?>
	<div class="mdb-menu-icon-field" style="margin-top:8px">
		<span class="description" style="display:block;font-weight:600;margin-bottom:4px"><?php esc_html_e( 'Menu Icon / Image', 'mdb-theme' ); ?></span>
		<input type="hidden"
			name="mdb_menu_icon[<?php echo (int) $item_id; ?>]"
			id="mdb_menu_icon_<?php echo (int) $item_id; ?>"
			value="<?php echo esc_attr( (int) $icon_id ); ?>">
		<div id="mdb-icon-preview-<?php echo (int) $item_id; ?>" style="margin-bottom:6px">
			<?php if ( $icon_src ) : ?>
				<img src="<?php echo esc_url( $icon_src ); ?>" style="max-height:40px;display:block">
			<?php endif; ?>
		</div>
		<button
			type="button"
			class="button button-small mdb-icon-upload"
			data-item-id="<?php echo (int) $item_id; ?>">
			<?php esc_html_e( 'Select Icon', 'mdb-theme' ); ?>
		</button>
		<button
			type="button"
			class="button button-small mdb-icon-remove"
			data-item-id="<?php echo (int) $item_id; ?>"
			style="<?php echo $icon_id ? '' : 'display:none'; ?>">
			<?php esc_html_e( 'Remove', 'mdb-theme' ); ?>
		</button>
	</div>
	<?php
}
add_action( 'wp_nav_menu_item_custom_fields', 'mdb_nav_menu_item_icon_field' );

/**
 * Save the icon attachment ID when the menu is saved.
 */
function mdb_save_nav_menu_item_icon( $menu_id, $menu_item_db_id ) {
	if ( ! isset( $_POST['mdb_menu_icon'][ $menu_item_db_id ] ) ) {
		return;
	}
	$icon_id = absint( $_POST['mdb_menu_icon'][ $menu_item_db_id ] );
	if ( $icon_id > 0 ) {
		update_post_meta( $menu_item_db_id, '_mdb_menu_icon', $icon_id );
	} else {
		delete_post_meta( $menu_item_db_id, '_mdb_menu_icon' );
	}
}
add_action( 'wp_update_nav_menu_item', 'mdb_save_nav_menu_item_icon', 10, 2 );

/**
 * Enqueue wp.media and the inline JS for the icon picker on the Menus screen.
 */
function mdb_nav_menu_icon_admin_scripts( $hook ) {
	if ( 'nav-menus.php' !== $hook ) {
		return;
	}
	wp_enqueue_media();
	$js = <<<'JS'
(function($){
	$(document).on('click', '.mdb-icon-upload', function(){
		var itemId = $(this).data('item-id');
		var frame = wp.media({
			title: 'Select Menu Icon',
			button: { text: 'Use this image' },
			multiple: false
		});
		frame.on('select', function(){
			var att = frame.state().get('selection').first().toJSON();
			$('#mdb_menu_icon_' + itemId).val(att.id);
			$('#mdb-icon-preview-' + itemId).html('<img src="' + att.url + '" style="max-height:40px;display:block">');
			$('.mdb-icon-remove[data-item-id="' + itemId + '"]').show();
		});
		frame.open();
	});
	$(document).on('click', '.mdb-icon-remove', function(){
		var itemId = $(this).data('item-id');
		$('#mdb_menu_icon_' + itemId).val('');
		$('#mdb-icon-preview-' + itemId).html('');
		$(this).hide();
	});
})(jQuery);
JS;
	wp_add_inline_script( 'wp-util', $js );
}
add_action( 'admin_enqueue_scripts', 'mdb_nav_menu_icon_admin_scripts' );
