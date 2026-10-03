<?php
// Defense in depth: this file is meant to be FETCHED and eval'd by the
// demo blueprint, never executed as a URL. When hit directly over HTTP
// its own path is the requested script; refuse that. (It is also
// excluded from the distributed plugin zip via .distignore.)
if ( isset( $_SERVER['SCRIPT_FILENAME'] ) && @realpath( $_SERVER['SCRIPT_FILENAME'] ) === __FILE__ ) {
	http_response_code( 403 );
	exit;
}

/**
 * Woo demo boot — runs AFTER demo-boot.php on the gogh × WooCommerce
 * blueprint. The Yellow House gains a shop: the paintings become products,
 * Shop joins the nav, and the teaching copy explains whose page is whose.
 *
 * Fetched fresh from the repo at boot (the blueprint is CDN-cached; this
 * file is not) — edits here go live everywhere within minutes.
 */
require_once '/wordpress/wp-load.php';
wp_set_current_user( 1 ); // unfiltered_html: KSES must not strip our markup

if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

// the starter apply trashed every page — including Woo's own. Recreate the
// missing Shop / Cart / Checkout / My account set.
try {
	if ( class_exists( 'WC_Install' ) ) {
		WC_Install::create_pages();
	}
} catch ( \Throwable $e ) {}

// no setup wizard in a demo
try {
	delete_transient( '_wc_activation_redirect' );
	update_option( 'woocommerce_onboarding_profile', array( 'skipped' => true ) );
	update_option( 'woocommerce_task_list_hidden', 'yes' );
	update_option( 'woocommerce_currency', 'GBP' );
} catch ( \Throwable $e ) {}

// the paintings are already in the media library (demo-boot sideloads them)
function gogh_woo_att( $needle ) {
	$q = get_posts( array(
		'post_type'      => 'attachment',
		'post_status'    => 'inherit',
		's'              => $needle,
		'posts_per_page' => 1,
	) );
	return $q ? $q[0]->ID : 0;
}

$catalogue = array(
	array(
		'name'  => 'The Starry Night — giclée print',
		'price' => '45',
		'img'   => 'starry-night',
		'short' => 'The village sleeps, the sky does not. Museum-grade print, 50×40cm.',
		'desc'  => 'This product page is pure WooCommerce — gogh does not touch it. Everything AROUND the shop (the home page, the collection, the stories) was painted with gogh. That is the point: gogh plays inside real WordPress, real plugins and all.',
	),
	array(
		'name'  => 'Sunflowers — giclée print',
		'price' => '25',
		'sale'  => '19', // two prints on sale, so an "On sale" rail has something to show
		'img'   => 'sunflowers',
		'short' => 'Fifteen suns in a jar. The print that pays the rent, 40×30cm.',
		'desc'  => 'Try this: open the home page, click Edit with gogh, and drag this painting anywhere. Then come back here — the shop keeps working. Two worlds, one site.',
	),
	array(
		'name'  => 'Wheat Field with Cypresses — giclée print',
		'price' => '30',
		'sale'  => '24',
		'img'   => 'wheat-field',
		'short' => 'Wind you can see. Painted from the asylum window, 50×40cm.',
		'desc'  => 'The products in this shop were seeded by the demo — add your own from the WordPress admin, and build the pages that sell them with gogh.',
	),
	array(
		'name'  => 'Almond Blossom — giclée print',
		'price' => '35',
		'img'   => 'almond-blossom',
		'short' => 'Painted for a newborn nephew — branches against a spring sky, 40×30cm.',
		'desc'  => 'The original hangs in the gift room and is not for sale — it never will be. The prints are for everyone.',
	),
);

try {
	foreach ( $catalogue as $item ) {
		$existing = get_posts( array( 'post_type' => 'product', 'title' => $item['name'], 'posts_per_page' => 1 ) );
		if ( $existing ) {
			continue;
		}
		$p = new WC_Product_Simple();
		$p->set_name( $item['name'] );
		if ( '0' !== $item['price'] ) {
			$p->set_regular_price( $item['price'] );
		}
		if ( ! empty( $item['sale'] ) ) {
			$p->set_sale_price( $item['sale'] );
		}
		$p->set_short_description( $item['short'] );
		$p->set_description( $item['desc'] );
		$p->set_stock_status( isset( $item['stock'] ) ? $item['stock'] : 'instock' );
		$att = gogh_woo_att( $item['img'] );
		if ( $att ) {
			$p->set_image_id( $att );
		}
		$p->save();
	}
} catch ( \Throwable $e ) {}

// Shop joins the site nav (the starter built a wp_navigation without it)
try {
	$shop_id = function_exists( 'wc_get_page_id' ) ? wc_get_page_id( 'shop' ) : 0;
	if ( $shop_id > 0 ) {
		$navs = get_posts( array( 'post_type' => 'wp_navigation', 'posts_per_page' => 1, 'post_status' => 'publish' ) );
		if ( $navs && false === strpos( $navs[0]->post_content, 'Shop' ) ) {
			wp_update_post( array(
				'ID'           => $navs[0]->ID,
				'post_content' => $navs[0]->post_content .
					'<!-- wp:navigation-link {"label":"Shop","type":"page","kind":"post-type","id":' . (int) $shop_id . ',"url":"' . esc_url_raw( get_permalink( $shop_id ) ) . '"} /-->',
			) );
		}
	}
} catch ( \Throwable $e ) {}
