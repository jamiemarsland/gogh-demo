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
update_option( 'blogname', 'The Yellow House' );
