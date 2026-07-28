<?php
/**
 * Hello Biz Child Theme functions and definitions
 *
 * @package HelloBizChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Disable Hello Biz's generic base theme stylesheet.
 *
 * We keep the parent header/footer layer, but the broad button/form resets in
 * hello-biz/assets/css/theme.css should not leak into this child theme.
 */
function mdb_theme_hide_hello_biz_base_styles( $hide ) {
	return true;
}
add_filter( 'hello-plus-theme/settings/hello_theme', 'mdb_theme_hide_hello_biz_base_styles' );

/**
 * Detect whether the current request is the Uni CPO builder view.
 *
 * @return bool
 */
function mdb_is_cpo_builder_request() {
	if ( ! isset( $_GET['cpo_options'] ) ) {
		return false;
	}

	$flag = sanitize_text_field( wp_unslash( $_GET['cpo_options'] ) );

	return '1' === $flag;
}

/**
 * Enqueue child theme styles
 */
function hello_biz_child_enqueue_styles() {
	if ( mdb_is_cpo_builder_request() ) {
		return;
	}

	// Build dependency list — always after the parent header/footer layer and after every
	// WooCommerce stylesheet that is actually registered on this request.
	// This guarantees our overrides cascade on top of WC defaults regardless
	// of which WC style handles are present (classic vs. block themes, etc.).
	$dependencies = array();

	if ( wp_style_is( 'hello-biz-header-footer', 'registered' ) || wp_style_is( 'hello-biz-header-footer', 'enqueued' ) ) {
		$dependencies[] = 'hello-biz-header-footer';
	}
	
	if ( class_exists( 'WooCommerce' ) ) {
		$wc_handles = array(
			'woocommerce-general',
			'woocommerce-layout',
			'woocommerce-smallscreen',
			'woocommerce-inline',
			'wc-blocks-style',
			'wc-blocks-vendors-style',
			'wc-blocks-packages-style',
		);
		foreach ( $wc_handles as $handle ) {
			if ( wp_style_is( $handle, 'registered' ) || wp_style_is( $handle, 'enqueued' ) ) {
				$dependencies[] = $handle;
			}
		}
	}
	
	// Enqueue child theme stylesheet
	wp_enqueue_style( 
		'hello-biz-child-style',
		get_stylesheet_directory_uri() . '/assets/css/main.css',
		$dependencies,
		file_exists( get_stylesheet_directory() . '/assets/css/main.css' ) ? filemtime( get_stylesheet_directory() . '/assets/css/main.css' ) : wp_get_theme()->get( 'Version' )
	);
}
add_action( 'wp_enqueue_scripts', 'hello_biz_child_enqueue_styles', 99 );

/**
 * Enqueue JavaScript files
 */
function hello_biz_child_enqueue_scripts() {
	if ( mdb_is_cpo_builder_request() ) {
		return;
	}

	// Enqueue custom JavaScript
	$custom_js_path = get_stylesheet_directory() . '/assets/js/custom.js';
	wp_enqueue_script(
		'mdb-theme-custom',
		get_stylesheet_directory_uri() . '/assets/js/custom.js',
		array( 'jquery' ),
		file_exists( $custom_js_path ) ? filemtime( $custom_js_path ) : wp_get_theme()->get('Version'),
		true
	);

	// Configurator — single product wizard. Only loaded on product pages so
	// it does not add weight to any other page.
	if ( is_singular( 'product' ) ) {
		$configurator_path = get_stylesheet_directory() . '/assets/js/configurator.js';
		wp_enqueue_script(
			'mdb-configurator',
			get_stylesheet_directory_uri() . '/assets/js/configurator.js',
			// uni-cpo-frontend must be listed so WP outputs it before our script,
			// guaranteeing unicpoAllOptions is defined when our ready handler runs.
			array( 'jquery', 'uni-cpo-frontend' ),
			file_exists( $configurator_path ) ? filemtime( $configurator_path ) : wp_get_theme()->get( 'Version' ),
			true
		);

		// Export per-option image-shape data so the configurator JS can add
		// mdb-shape--circle to the appropriate module wrappers.
		mdb_inline_cpo_shapes();
	}

	// Pass AJAX URL to front-end JS.
	wp_localize_script( 'mdb-theme-custom', 'mdbAjax', array(
		'url' => admin_url( 'admin-ajax.php' ),
	) );
}
add_action( 'wp_enqueue_scripts', 'hello_biz_child_enqueue_scripts' );

/**
 * Route Uni CPO calculated price updates into the custom configurator sidebar.
 *
 * Uni CPO reads this selector during its own JS init and caches the matched
 * element, so this must be set in PHP (before frontend.js runs) rather than
 * overridden later in custom JavaScript.
 *
 * @param array|string $selectors Existing selectors from Uni CPO.
 * @return array
 */
function mdb_uni_cpo_price_selector( $selectors ) {
	if ( mdb_is_cpo_builder_request() ) {
		return $selectors;
	}

	if ( ! is_singular( 'product' ) ) {
		return is_array( $selectors ) ? $selectors : array();
	}

	if ( is_string( $selectors ) ) {
		$selectors = array_map( 'trim', explode( ',', $selectors ) );
	}

	if ( ! is_array( $selectors ) ) {
		$selectors = array();
	}

	array_unshift( $selectors, '#mdb-total-price' );

	$selectors = array_values( array_unique( array_filter( $selectors, function ( $el ) {
		return is_string( $el ) && '' !== trim( $el );
	} ) ) );

	return $selectors;
}
add_filter( 'uni_cpo_price_selector', 'mdb_uni_cpo_price_selector' );

/**
 * Walk a CPO builder content array and return a slug => cpo_geom_radio map.
 *
 * @param array  $nodes    Top-level content array or recursive child array.
 * @param array  $shapes   Accumulated map (pass by reference via return).
 * @param string $var_slug CPO variable prefix (e.g. "uni_cpo_").
 * @return array
 */
function mdb_walk_cpo_nodes( array $nodes, array $shapes, $var_slug ) {
	foreach ( $nodes as $node ) {
		if ( ! is_array( $node ) ) {
			continue;
		}
		if ( ! empty( $node['columns'] ) && is_array( $node['columns'] ) ) {
			$shapes = mdb_walk_cpo_nodes( $node['columns'], $shapes, $var_slug );
		}
		if ( ! empty( $node['modules'] ) && is_array( $node['modules'] ) ) {
			$shapes = mdb_walk_cpo_nodes( $node['modules'], $shapes, $var_slug );
		}
		if ( isset( $node['obj_type'] ) && 'option' === $node['obj_type'] ) {
			$raw_slug = isset( $node['settings']['cpo_general']['main']['cpo_slug'] )
				? (string) $node['settings']['cpo_general']['main']['cpo_slug']
				: '';
			$geom = isset( $node['settings']['cpo_general']['main']['cpo_geom_radio'] )
				? (string) $node['settings']['cpo_general']['main']['cpo_geom_radio']
				: '';
			if ( '' !== $raw_slug && '' !== $geom ) {
				$shapes[ $var_slug . $raw_slug ] = $geom;
			}
		}
	}
	return $shapes;
}

/**
 * Inline mdbCpoShapes as a JS variable before configurator.js runs.
 * Maps each CPO option slug to its cpo_geom_radio value ('circle'|'square').
 */
function mdb_inline_cpo_shapes() {
	if ( ! class_exists( 'UniCpo' ) || ! function_exists( 'UniCpo' ) ) {
		return;
	}

	$post_id = get_the_ID();
	if ( ! $post_id ) {
		return;
	}

	$raw_content = get_post_meta( $post_id, '_cpo_content', true );
	if ( empty( $raw_content ) ) {
		return;
	}

	$decoded = base64_decode( (string) $raw_content, true );
	if ( false === $decoded ) {
		return;
	}

	$content = maybe_unserialize( $decoded );
	if ( ! is_array( $content ) ) {
		return;
	}

	$var_slug = UniCpo()->get_var_slug();
	$shapes   = mdb_walk_cpo_nodes( $content, array(), $var_slug );

	// Only keep non-circle entries — circles are CPO's default so no JS class
	// is needed for them. This keeps the inline payload as small as possible.
	$shapes = array_filter( $shapes, function( $geom ) {
		return 'circle' !== $geom;
	} );

	if ( empty( $shapes ) ) {
		return;
	}

	wp_add_inline_script(
		'mdb-configurator',
		'var mdbCpoShapes = ' . wp_json_encode( $shapes ) . ';',
		'before'
	);
}

/**
 * Enqueue admin styles and scripts
 */
function hello_biz_child_admin_enqueue() {
	// Enqueue admin CSS
	$admin_css_path = get_stylesheet_directory() . '/assets/css/admin.css';
	wp_enqueue_style(
		'mdb-theme-admin',
		get_stylesheet_directory_uri() . '/assets/css/admin.css',
		array(),
		file_exists( $admin_css_path ) ? filemtime( $admin_css_path ) : wp_get_theme()->get('Version')
	);
	
	// Enqueue admin JavaScript
	$admin_js_path = get_stylesheet_directory() . '/assets/js/admin.js';
	wp_enqueue_script(
		'mdb-theme-admin-js',
		get_stylesheet_directory_uri() . '/assets/js/admin.js',
		array( 'jquery' ),
		file_exists( $admin_js_path ) ? filemtime( $admin_js_path ) : wp_get_theme()->get('Version'),
		true
	);
}
add_action( 'admin_enqueue_scripts', 'hello_biz_child_admin_enqueue' );

/**
 * Add your custom functions below this line
 */

/**
 * Add WooCommerce theme support
 */
function mdb_theme_woocommerce_setup() {
    add_theme_support( 'woocommerce' );
    add_theme_support( 'wc-product-gallery-zoom' );
    add_theme_support( 'wc-product-gallery-lightbox' );
    add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'mdb_theme_woocommerce_setup' );

/**
 * Include custom checkout progress functionality
 */
require_once get_stylesheet_directory() . '/inc/checkout-progress.php';

/**
 * AJAX login & registration handlers
 */
require_once get_stylesheet_directory() . '/inc/ajax-auth.php';

/**
 * /choose/{category}/ URL routing
 */
require_once get_stylesheet_directory() . '/inc/choose-category.php';

/**
 * Custom header: Customizer options + front-end template
 */
require_once get_stylesheet_directory() . '/inc/header-options.php';
require_once get_stylesheet_directory() . '/inc/custom-header.php';

// ---------------------------------------------------------------------------
// Single product — "back to selection" breadcrumb
// ---------------------------------------------------------------------------

/**
 * Resolve the best /choose/ URL for a product.
 *
 * Priority:
 *   1. Deepest assigned product_cat that is NOT in the free-samples tree and
 *      has a parent (i.e. a real sub-category), so we land on the most specific
 *      choose page.
 *   2. Any top-level product_cat that is not free-samples.
 *   3. Fallback to /choose/ root.
 *
 * @param int $product_id
 * @return array{url:string, label:string}
 */
function mdb_product_choose_back_link( int $product_id ): array {
	$samples_ids = function_exists( 'mdb_get_samples_term_ids' ) ? mdb_get_samples_term_ids() : array();

	$terms = get_the_terms( $product_id, 'product_cat' );

	if ( empty( $terms ) || is_wp_error( $terms ) ) {
		return array( 'url' => home_url( '/choose/' ), 'label' => __( 'Return to Product Selection', 'mdb-theme' ) );
	}

	// Filter out free-samples tree terms.
	$terms = array_filter( $terms, function ( $t ) use ( $samples_ids ) {
		return ! in_array( $t->term_id, $samples_ids, true );
	} );

	if ( empty( $terms ) ) {
		return array( 'url' => home_url( '/choose/' ), 'label' => __( 'Return to Product Selection', 'mdb-theme' ) );
	}

	// Prefer child categories (parent !== 0) so we point to the most specific page.
	$children = array_filter( $terms, fn( $t ) => $t->parent !== 0 );
	$chosen   = ! empty( $children ) ? reset( $children ) : reset( $terms );

	$url   = home_url( '/choose/' . $chosen->slug . '/' );
	$label = sprintf(
		/* translators: %s: category name */
		__( 'Return to %s Selection', 'mdb-theme' ),
		$chosen->name
	);

	return array( 'url' => $url, 'label' => $label );
}

/**
 * Output the back-link breadcrumb above the product summary.
 * Hooked late enough that WooCommerce's own breadcrumb (priority 20) has already run.
 */
function mdb_single_product_back_link(): void {
	if ( ! is_singular( 'product' ) ) {
		return;
	}

	$link = mdb_product_choose_back_link( get_the_ID() );
	printf(
		'<div class="mdb-product-breadcrumb"><a href="%s"><span class="icon" aria-hidden="true">←</span> %s</a></div>',
		esc_url( $link['url'] ),
		esc_html( $link['label'] )
	);
}
add_action( 'woocommerce_before_single_product_summary', 'mdb_single_product_back_link', 5 );

// ---------------------------------------------------------------------------
// Related products — compact grid (4 columns, 4 products)
// ---------------------------------------------------------------------------

add_filter( 'woocommerce_output_related_products_args', function ( $args ) {
	$args['posts_per_page'] = 4;
	$args['columns']        = 4;
	return $args;
} );

/**
 * Exclude "Samples" (free-samples) and all its descendants from the shop/archives.
 * Covers: product posts, WooCommerce-specific product loops, and category tiles.
 */

// Helper: returns all term IDs in the free-samples tree (cached per request).
// Uses direct DB queries to avoid triggering get_terms filters (which would cause recursion).
function mdb_get_samples_term_ids() {
	static $ids = null;
	if ( null !== $ids ) {
		return $ids;
	}

	global $wpdb;

	// Look up the root term directly — no get_terms API involved.
	$root_id = (int) $wpdb->get_var( $wpdb->prepare(
		"SELECT t.term_id
		 FROM {$wpdb->terms} t
		 INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
		 WHERE t.slug = %s AND tt.taxonomy = %s
		 LIMIT 1",
		'free-samples',
		'product_cat'
	) );

	if ( ! $root_id ) {
		$ids = array();
		return $ids;
	}

	// Collect all descendants iteratively via the term_taxonomy table.
	$ids      = array( $root_id );
	$to_check = array( $root_id );

	while ( ! empty( $to_check ) ) {
		$placeholders = implode( ',', array_fill( 0, count( $to_check ), '%d' ) );
		$query_args   = array_merge( array( 'product_cat' ), $to_check );

		// phpcs:ignore WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
		$children = $wpdb->get_col( $wpdb->prepare(
			"SELECT term_id FROM {$wpdb->term_taxonomy}
			 WHERE taxonomy = %s AND parent IN ($placeholders)",
			$query_args
		) );

		$children = array_map( 'intval', $children );
		$new      = array_diff( $children, $ids );

		if ( empty( $new ) ) {
			break;
		}

		$ids      = array_merge( $ids, $new );
		$to_check = $new;
	}

	return $ids;
}

// Helper: returns product IDs that belong to the free-samples tree (cached per request).
function mdb_get_samples_product_ids() {
	static $product_ids = null;
	if ( null !== $product_ids ) {
		return $product_ids;
	}

	$term_ids = mdb_get_samples_term_ids();
	if ( empty( $term_ids ) ) {
		$product_ids = array();
		return $product_ids;
	}

	$product_ids = get_posts( array(
		'post_type'      => 'product',
		'post_status'    => 'any',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'tax_query'      => array( array(
			'taxonomy' => 'product_cat',
			'field'    => 'term_id',
			'terms'    => $term_ids,
			'operator' => 'IN',
		) ),
	) );

	return $product_ids;
}

// 1. Exclude products from the main query (shop, archives, search).
add_action( 'pre_get_posts', function ( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( ! ( $query->is_shop() || $query->is_product_category() || $query->is_product_tag() || $query->is_search() ) ) {
		return;
	}

	$excluded_ids = mdb_get_samples_product_ids();
	if ( empty( $excluded_ids ) ) {
		return;
	}

	$existing = (array) $query->get( 'post__not_in' );
	$query->set( 'post__not_in', array_unique( array_merge( $existing, $excluded_ids ) ) );
} );

// 2. Also exclude from WooCommerce's own product loop query (widget loops, shortcodes, etc.).
add_action( 'woocommerce_product_query', function ( $q ) {
	$excluded_ids = mdb_get_samples_product_ids();
	if ( empty( $excluded_ids ) ) {
		return;
	}

	$existing = (array) $q->get( 'post__not_in' );
	$q->set( 'post__not_in', array_unique( array_merge( $existing, $excluded_ids ) ) );
} );

// 3. Hide the free-samples category tiles from the shop/archive category display.
add_filter( 'woocommerce_product_subcategories_args', function ( $args ) {
	$term_ids = mdb_get_samples_term_ids();
	if ( empty( $term_ids ) ) {
		return $args;
	}
	$existing        = isset( $args['exclude'] ) ? (array) $args['exclude'] : array();
	$args['exclude'] = array_unique( array_merge( $existing, $term_ids ) );
	return $args;
} );

// 4. Exclude from any get_terms call (sidebar widgets, nav, etc.).
// Safe because mdb_get_samples_term_ids() has a re-entry guard that returns [] when called recursively.
add_filter( 'get_terms_args', function ( $args, $taxonomies ) {
	if ( is_admin() ) {
		return $args;
	}
	// Don't interfere on /choose/ pages — get_term_by() calls get_terms()
	// internally, so excluding terms here would cause the template to 404.
	if ( get_query_var( 'mdb_choose_cat' ) ) {
		return $args;
	}
	if ( ! in_array( 'product_cat', (array) $taxonomies, true ) ) {
		return $args;
	}
	$term_ids = mdb_get_samples_term_ids();
	if ( empty( $term_ids ) ) {
		return $args;
	}
	$existing        = isset( $args['exclude'] ) ? (array) $args['exclude'] : array();
	$args['exclude'] = array_unique( array_merge( $existing, $term_ids ) );
	return $args;
}, 10, 2 );


