<?php
// Defense in depth: this file is meant to be FETCHED and eval'd by the
// demo blueprint, never executed as a URL. When hit directly over HTTP
// its own path is the requested script; refuse that. (It is also
// excluded from the distributed plugin zip via .distignore.)
if ( isset( $_SERVER['SCRIPT_FILENAME'] ) && @realpath( $_SERVER['SCRIPT_FILENAME'] ) === __FILE__ ) {
	http_response_code( 403 );
	exit;
}

require_once '/wordpress/wp-load.php';
wp_set_current_user( 1 );
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
$dir = WP_PLUGIN_DIR . '/gogh/demo-assets/';
// a blueprint may boot a DIFFERENT design (blueprint-photographer defines
// GOGH_DEMO_STARTER, and GOGH_DEMO_MEDIA for the pictures its media library
// should hold): that design's own pages and posts, none of the Yellow House's
$starter = defined( 'GOGH_DEMO_STARTER' ) ? sanitize_key( GOGH_DEMO_STARTER ) : 'yellow-house';
if ( defined( 'GOGH_SITE_DEF_JSON' ) ) {
	// a site from a definition (see gogh_site_def_boot): pages, posts, menu,
	// chrome and palette now; the editor draws the sections on first load
	try { wp_trash_post( 1 ); } catch ( \Throwable $e ) {}
	$def = json_decode( (string) GOGH_SITE_DEF_JSON, true );
	if ( is_array( $def ) && function_exists( 'gogh_site_def_boot' ) ) {
		try { gogh_site_def_boot( $def ); } catch ( \Throwable $e ) {}
	}
} elseif ( 'yellow-house' !== $starter ) {
	try { wp_trash_post( 1 ); } catch ( \Throwable $e ) {}
	// a shop design boots with WooCommerce already active: its own pages
	// must exist before the design seats them in the menu, and a demo
	// never shows the setup wizard, the task list or a coming-soon veil
	if ( class_exists( 'WooCommerce' ) ) {
		try {
			if ( class_exists( 'WC_Install' ) ) {
				WC_Install::create_pages();
			}
			delete_transient( '_wc_activation_redirect' );
			update_option( 'woocommerce_onboarding_profile', array( 'skipped' => true ) );
			update_option( 'woocommerce_task_list_hidden', 'yes' );
			update_option( 'woocommerce_coming_soon', 'no' );
			update_option( 'woocommerce_currency', defined( 'GOGH_DEMO_CURRENCY' ) ? sanitize_text_field( GOGH_DEMO_CURRENCY ) : 'GBP' );
			update_option( 'woocommerce_default_country', 'GB' );
		} catch ( \Throwable $e ) {}
	}
	try {
		$req = new WP_REST_Request( 'POST', '/gogh/v1/starter' );
		$req->set_param( 'slug', $starter );
		rest_do_request( $req );
	} catch ( \Throwable $e ) {}
	if ( defined( 'GOGH_DEMO_MEDIA' ) && is_array( GOGH_DEMO_MEDIA ) ) {
		foreach ( GOGH_DEMO_MEDIA as $murl ) {
			try { media_sideload_image( esc_url_raw( $murl ), 0, null, 'id' ); } catch ( \Throwable $e ) {}
		}
	}
	if ( defined( 'GOGH_DEMO_TITLE' ) ) {
		update_option( 'blogname', sanitize_text_field( GOGH_DEMO_TITLE ) );
	}
} else {
	// the four paintings live in the media library too — the Collection page
	// invites visitors to swap images, so the library must not be empty
	foreach ( array( 'starry-night.jpg', 'sunflowers.jpg', 'wheat-field.jpg', 'almond-blossom.jpg' ) as $f ) {
		try {
			$tmp = wp_tempnam( $f );
			copy( $dir . $f, $tmp );
			media_handle_sideload( array( 'name' => $f, 'tmp_name' => $tmp ), 0 );
		} catch ( \Throwable $e ) {}
	}
	// the demo site IS the Yellow House starter — same code path users get
	try {
		$req = new WP_REST_Request( 'POST', '/gogh/v1/starter' );
		$req->set_param( 'slug', 'yellow-house' );
		rest_do_request( $req );
	} catch ( \Throwable $e ) {}
	// the painted bloom as the site logo
	try {
		$tmp = wp_tempnam( 'bloom.png' );
		copy( $dir . 'yellow-house-logo.png', $tmp );
		$lid = media_handle_sideload( array( 'name' => 'yellow-house-bloom.png', 'tmp_name' => $tmp ), 0, 'The Yellow House bloom' );
		if ( ! is_wp_error( $lid ) ) {
			update_option( 'site_logo', $lid );
		}
	} catch ( \Throwable $e ) {}
	// two notes so the blog breathes, and the sample post steps aside
	try { wp_trash_post( 1 ); } catch ( \Throwable $e ) {}
	wp_insert_post( array(
		'post_type'    => 'post',
		'post_status'  => 'publish',
		'post_title'   => 'Why Almond Blossom was a gift',
		'post_content' => '<!-- wp:paragraph --><p>Vincent painted it for his newborn nephew — branches against a spring sky, hope in paint. It hangs in the gift room, it is not for sale, and it never will be.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>These posts were written with gogh, and they survive every site redesign — switch the whole design in Site style and they simply re-dress.</p><!-- /wp:paragraph -->',
	) );
	wp_insert_post( array(
		'post_type'    => 'post',
		'post_status'  => 'publish',
		'post_title'   => 'The wheatfield arrived on a Tuesday',
		'post_content' => '<!-- wp:paragraph --><p>Crated, insured, and heavier than any sky has a right to be. It took four of us to hang it and one long evening to stop staring. Room two, straight ahead.</p><!-- /wp:paragraph -->',
	) );
	// a reference post with real sections, so the Manual look has contents
	// to build: how to care for a print, the way a gallery would tell you
	$guide = wp_insert_post( array(
		'post_type'    => 'post',
		'post_status'  => 'publish',
		'post_title'   => 'Caring for a print',
		'post_content' =>
			'<!-- wp:paragraph --><p>A giclée print will outlive you if you let it. Most of what shortens its life happens in the first week: the wrong frame, the wrong wall, a damp hallway. This is everything we tell people at the counter, written down.</p><!-- /wp:paragraph -->' .
			'<!-- wp:heading --><h2 class="wp-block-heading">Unpacking</h2><!-- /wp:heading -->' .
			'<!-- wp:paragraph --><p>Prints travel flat between two boards. Open the parcel on a clean table, not the floor, and lift the print by its edges. The margins are there to be handled; the image is not.</p><!-- /wp:paragraph -->' .
			'<!-- wp:list --><ul class="wp-block-list"><!-- wp:list-item --><li>Wash and dry your hands, or wear cotton gloves.</li><!-- /wp:list-item --><!-- wp:list-item --><li>Keep the boards. They are the best storage the print will ever have.</li><!-- /wp:list-item --><!-- wp:list-item --><li>Leave the tissue on until the frame is ready.</li><!-- /wp:list-item --></ul><!-- /wp:list -->' .
			'<!-- wp:heading --><h2 class="wp-block-heading">Framing</h2><!-- /wp:heading -->' .
			'<!-- wp:paragraph --><p>A frame does two jobs: it keeps the glass off the paper and it keeps the air out. Ask for a mount (a window of card between print and glass) and for acid-free backing.</p><!-- /wp:paragraph -->' .
			'<!-- wp:heading {"level":3} --><h3 class="wp-block-heading">Glass or acrylic</h3><!-- /wp:heading -->' .
			'<!-- wp:paragraph --><p>Glass is cheaper and scratches less. Acrylic is lighter, safer over a bed, and the UV-filtering kind is worth it in a bright room. Either way, the print must not touch it.</p><!-- /wp:paragraph -->' .
			'<!-- wp:heading {"level":3} --><h3 class="wp-block-heading">Sizes we print</h3><!-- /wp:heading -->' .
			'<!-- wp:table --><figure class="wp-block-table"><table><thead><tr><th>Print</th><th>Image</th><th>Paper</th><th>Standard frame</th></tr></thead><tbody><tr><td>Small</td><td>30 × 24 cm</td><td>40 × 30 cm</td><td>40 × 30 cm</td></tr><tr><td>Medium</td><td>40 × 30 cm</td><td>50 × 40 cm</td><td>50 × 40 cm</td></tr><tr><td>Large</td><td>60 × 45 cm</td><td>70 × 50 cm</td><td>70 × 50 cm</td></tr></tbody></table></figure><!-- /wp:table -->' .
			'<!-- wp:paragraph --><p>Paper sizes include a margin on every side. Frame to the paper size and let the mount do the cropping.</p><!-- /wp:paragraph -->' .
			'<!-- wp:heading --><h2 class="wp-block-heading">Hanging</h2><!-- /wp:heading -->' .
			'<!-- wp:paragraph --><p>Out of direct sun, away from radiators, never in a bathroom. The centre of the image at about 145 cm from the floor is where most eyes land.</p><!-- /wp:paragraph -->' .
			'<!-- wp:quote --><blockquote class="wp-block-quote"><!-- wp:paragraph --><p>If you can feel the wall is cold or damp with your palm, the print will feel it too. Pick another wall.</p><!-- /wp:paragraph --></blockquote><!-- /wp:quote -->' .
			'<!-- wp:heading --><h2 class="wp-block-heading">Cleaning</h2><!-- /wp:heading -->' .
			'<!-- wp:paragraph --><p>Dust the glass with a dry microfibre cloth. Nothing wet near the frame, and never spray anything at it. If the print itself needs attention, bring it back to us.</p><!-- /wp:paragraph -->' .
			'<!-- wp:heading --><h2 class="wp-block-heading">If something goes wrong</h2><!-- /wp:heading -->' .
			'<!-- wp:paragraph --><p>A ripple in the paper is usually damp and usually recoverable if caught early. A fade is not. Write to us with a photo and the print number from the back, and we will tell you honestly what can be done.</p><!-- /wp:paragraph -->',
	) );
	if ( $guide && ! is_wp_error( $guide ) ) {
		update_post_meta( $guide, '_gogh_post_style', 'manual' ); // opens in the Manual look
	}
	update_option( 'blogname', 'The Yellow House' );
}
// a blueprint may ask for one of gogh's other headers (blueprint-classic
// defines GOGH_DEMO_HEADER before fetching this boot): the pattern goes in
// wearing the same menu the starter just built
if ( defined( 'GOGH_DEMO_HEADER' ) ) {
	$pfile = WP_PLUGIN_DIR . '/gogh/patterns/' . sanitize_file_name( GOGH_DEMO_HEADER ) . '.html';
	$part  = get_block_template( get_stylesheet() . '//header', 'wp_template_part' );
	if ( $part && ! empty( $part->wp_id ) && is_readable( $pfile ) && preg_match( '/"ref":(\d+)/', (string) $part->content, $mm ) ) {
		$praw = file_get_contents( $pfile );
		$ref  = (int) $mm[1];
		if ( preg_match( '/wp:navigation[^>]*"ref":\d+/', $praw ) ) {
			$next = preg_replace( '/("ref":)\d+/', '${1}' . $ref, $praw, 1 );
		} elseif ( preg_match( '/<!--\s+wp:navigation\s+\{/', $praw ) ) {
			$next = preg_replace( '/(<!--\s+wp:navigation\s+\{)/', '${1}"ref":' . $ref . ',', $praw, 1 );
		} else {
			$next = preg_replace( '/(<!--\s+wp:navigation)(\s+-->)/', '${1} {"ref":' . $ref . '}${2}', $praw, 1 );
		}
		if ( $next ) {
			wp_update_post( array( 'ID' => $part->wp_id, 'post_content' => $next ) );
		}
	}
}
