<?php
// Fetched and eval'd by blueprint-paste.json — never served as a URL.
if ( isset( $_SERVER['SCRIPT_FILENAME'] ) && @realpath( $_SERVER['SCRIPT_FILENAME'] ) === __FILE__ ) {
	http_response_code( 403 );
	exit;
}

/**
 * Paste-a-page boot: a blank site, one empty front page, nothing else.
 * The blueprint lands on it with the Paste HTML door open. The four
 * paintings join the media library so a pasted page can pick them.
 */
require_once '/wordpress/wp-load.php';
wp_set_current_user( 1 );
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
$dir = WP_PLUGIN_DIR . '/gogh/demo-assets/';
foreach ( array( 'starry-night.jpg', 'sunflowers.jpg', 'wheat-field.jpg', 'almond-blossom.jpg' ) as $f ) {
	try {
		$tmp = wp_tempnam( $f );
		copy( $dir . $f, $tmp );
		media_handle_sideload( array( 'name' => $f, 'tmp_name' => $tmp ), 0 );
	} catch ( \Throwable $e ) {}
}
// the sample post and page step aside — this site begins with nothing
try { wp_trash_post( 1 ); wp_trash_post( 2 ); } catch ( \Throwable $e ) {}
$id = wp_insert_post( array(
	'post_type'    => 'page',
	'post_status'  => 'publish',
	'post_title'   => 'Start here',
	'post_name'    => 'start',
	'post_content' => '',
) );
if ( $id && ! is_wp_error( $id ) ) {
	update_post_meta( $id, '_wp_page_template', 'page-no-title' );
	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $id );
}
update_option( 'blogname', 'A page from nothing' );
update_option( 'blogdescription', 'Paste it. Make it yours.' );
