<?php
/**
 * /choose/{category}/ URL routing
 *
 * Maps:
 *   /choose/roller-blinds/        → products in the "roller-blinds" product_cat
 *   /choose/roller-blinds/page/2/ → second page of results
 *
 * After first activating this code, visit Settings → Permalinks and click
 * "Save Changes" to flush rewrite rules (or it happens automatically on
 * theme switch via the after_switch_theme hook below).
 *
 * @package MDB_Theme
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ---------------------------------------------------------------------------
// Query variable
// ---------------------------------------------------------------------------

/**
 * Register the mdb_choose_cat query variable so WordPress passes it through.
 *
 * @param string[] $vars
 * @return string[]
 */
function mdb_choose_register_query_vars( $vars ) {
	$vars[] = 'mdb_choose_cat';
	return $vars;
}
add_filter( 'query_vars', 'mdb_choose_register_query_vars' );

// ---------------------------------------------------------------------------
// Rewrite rules
// ---------------------------------------------------------------------------

/**
 * Register rewrite rules for /choose/{slug}/ and /choose/{slug}/page/{n}/.
 */
function mdb_choose_add_rewrite_rules() {
	// Paged — must be registered before the non-paged rule.
	add_rewrite_rule(
		'^choose/([^/]+)/page/([0-9]+)/?$',
		'index.php?mdb_choose_cat=$matches[1]&paged=$matches[2]',
		'top'
	);

	// First (or only) page.
	add_rewrite_rule(
		'^choose/([^/]+)/?$',
		'index.php?mdb_choose_cat=$matches[1]',
		'top'
	);
}
add_action( 'init', 'mdb_choose_add_rewrite_rules' );

/**
 * Flush rewrite rules automatically when the theme is activated so the
 * /choose/ URLs work immediately.
 */
function mdb_choose_flush_rewrite_rules() {
	mdb_choose_add_rewrite_rules();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'mdb_choose_flush_rewrite_rules' );

// ---------------------------------------------------------------------------
// Template routing
// ---------------------------------------------------------------------------

/**
 * When mdb_choose_cat is present, load our choose-category template instead
 * of whatever WordPress would normally render.
 */
function mdb_choose_template_redirect() {
	$cat_slug = get_query_var( 'mdb_choose_cat' );
	if ( ! $cat_slug ) {
		return;
	}

	$template = get_stylesheet_directory() . '/templates/choose-category.php';
	if ( file_exists( $template ) ) {
		include $template;
		exit;
	}
}
add_action( 'template_redirect', 'mdb_choose_template_redirect' );

// ---------------------------------------------------------------------------
// Document title
// ---------------------------------------------------------------------------

/**
 * Set a meaningful <title> for /choose/{category}/ pages.
 *
 * @param array $parts
 * @return array
 */
function mdb_choose_document_title( $parts ) {
	$cat_slug = get_query_var( 'mdb_choose_cat' );
	if ( ! $cat_slug ) {
		return $parts;
	}

	$term = get_term_by( 'slug', sanitize_title( $cat_slug ), 'product_cat' );
	if ( $term && ! is_wp_error( $term ) ) {
		$parts['title'] = $term->name;
	}

	return $parts;
}
add_filter( 'document_title_parts', 'mdb_choose_document_title' );

// ---------------------------------------------------------------------------
// Free-samples tree detection
// ---------------------------------------------------------------------------

/**
 * Returns true if $term is the "free-samples" category or any of its
 * descendants, so the template knows which view to render.
 *
 * @param \WP_Term $term
 * @return bool
 */
function mdb_is_free_samples_cat( \WP_Term $term ): bool {
	if ( 'free-samples' === $term->slug ) {
		return true;
	}
	foreach ( get_ancestors( $term->term_id, 'product_cat', 'taxonomy' ) as $ancestor_id ) {
		$ancestor = get_term( $ancestor_id, 'product_cat' );
		if ( $ancestor && ! is_wp_error( $ancestor ) && 'free-samples' === $ancestor->slug ) {
			return true;
		}
	}
	return false;
}

// ---------------------------------------------------------------------------
// Enqueue Product Customizer widget assets on non-free-samples choose pages
// ---------------------------------------------------------------------------

/**
 * The MDB Product Customizer widget CSS/JS is normally loaded only via the
 * Elementor frontend hook.  On our custom /choose/ pages we need to enqueue
 * it directly so the widget HTML we output in the template works correctly.
 */
function mdb_choose_enqueue_product_customizer(): void {
	$cat_slug = get_query_var( 'mdb_choose_cat' );
	if ( ! $cat_slug ) {
		return;
	}

	$term = get_term_by( 'slug', sanitize_title( $cat_slug ), 'product_cat' );
	if ( ! $term || is_wp_error( $term ) || mdb_is_free_samples_cat( $term ) ) {
		return;
	}

	$plugin_dir = WP_PLUGIN_DIR . '/mdb-custom-widgets';
	$plugin_url = plugins_url( '', WP_PLUGIN_DIR . '/mdb-custom-widgets/mdb-custom-widgets.php' );

	if ( ! is_dir( $plugin_dir ) ) {
		return;
	}

	// Shared widget base styles / JS.
	wp_enqueue_style(
		'mdb-custom-widgets',
		$plugin_url . '/assets/css/widgets.css',
		[],
		file_exists( "$plugin_dir/assets/css/widgets.css" ) ? filemtime( "$plugin_dir/assets/css/widgets.css" ) : null
	);
	wp_enqueue_style(
		'mdb-product-customizer-widget',
		$plugin_url . '/assets/css/product-customizer-widget.css',
		[ 'mdb-custom-widgets' ],
		file_exists( "$plugin_dir/assets/css/product-customizer-widget.css" ) ? filemtime( "$plugin_dir/assets/css/product-customizer-widget.css" ) : null
	);

	wp_enqueue_script(
		'mdb-custom-widgets',
		$plugin_url . '/assets/js/widgets.js',
		[ 'jquery' ],
		file_exists( "$plugin_dir/assets/js/widgets.js" ) ? filemtime( "$plugin_dir/assets/js/widgets.js" ) : null,
		true
	);
	wp_enqueue_script(
		'mdb-product-customizer-widget',
		$plugin_url . '/assets/js/product-customizer-widget.js',
		[ 'jquery', 'mdb-custom-widgets' ],
		file_exists( "$plugin_dir/assets/js/product-customizer-widget.js" ) ? filemtime( "$plugin_dir/assets/js/product-customizer-widget.js" ) : null,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'mdb_choose_enqueue_product_customizer' );
