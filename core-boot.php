<?php
/**
 * Elliot Grey, in core blocks. The photographer site the gogh user test
 * runs on, rebuilt for Twenty Twenty-Five with nothing but core: the same
 * pages, words, pictures and menu, so a Site Editor test compares like
 * with like. Run once on a fresh site: `wp eval-file core-boot.php`, or the
 * blueprint's runPHP step.
 */
if ( ! defined( 'ABSPATH' ) ) { require dirname( __FILE__ ) . '/wp-load.php'; }
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/image.php';
wp_set_current_user( 1 );

// ---- the pictures: the same six the gogh test site carries ----
$MEDIA = array(
	'hero'    => 'https://images.unsplash.com/photo-1516483638261-f4dbaf036963?auto=format&fit=crop&w=1800&q=80',
	'tall'    => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=1200&q=80',
	'light'   => 'https://images.unsplash.com/photo-1523217582562-09d0def993a6?auto=format&fit=crop&w=1400&q=80',
	'coast'   => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=1600&q=80',
	'portrait'=> 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=1200&q=80',
	'villa'   => 'https://images.unsplash.com/photo-1518005020951-eccb494ad742?auto=format&fit=crop&w=1200&q=80',
);
$ids = array(); $urls = array();
foreach ( $MEDIA as $k => $u ) {
	// run twice, the pictures are not fetched twice
	$had = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'title' => 'Elliot Grey — ' . $k, 'numberposts' => 1, 'fields' => 'ids' ) );
	if ( $had ) { $ids[ $k ] = (int) $had[0]; $urls[ $k ] = wp_get_attachment_url( $had[0] ); continue; }
	// Unsplash addresses carry no file extension, which media_sideload_image
	// refuses; fetch the bytes and hand them over under a .jpg name instead
	$id = 0;
	try {
		$tmp = download_url( esc_url_raw( $u ), 60 );
		if ( ! is_wp_error( $tmp ) ) {
			$id = media_handle_sideload( array( 'name' => 'elliot-grey-' . $k . '.jpg', 'tmp_name' => $tmp ), 0, 'Elliot Grey — ' . $k );
			if ( is_wp_error( $id ) ) { @unlink( $tmp ); $id = 0; }
		}
	} catch ( \Throwable $e ) { $id = 0; }
	if ( ! $id ) { $ids[ $k ] = 0; $urls[ $k ] = $u; continue; }
	$ids[ $k ] = (int) $id; $urls[ $k ] = wp_get_attachment_url( $id );
}
$img = function ( $k, $ratio = '' ) use ( $ids, $urls ) {
	$id = $ids[ $k ]; $u = $urls[ $k ];
	// a cropped picture carries its ratio on the img too: the block's
	// attributes alone do not draw it on the front end
	$attrs = ( $id ? '"id":' . $id . ',' : '' ) . '"sizeSlug":"large","linkDestination":"none"' . ( $ratio ? ',"aspectRatio":"' . $ratio . '","scale":"cover"' : '' );
	// exactly what the image block saves, or the editor flags the block as invalid
	$style = $ratio ? ' style="aspect-ratio:' . $ratio . ';object-fit:cover"' : '';
	return '<!-- wp:image {' . $attrs . '} --><figure class="wp-block-image size-large"><img src="' . esc_url( $u ) . '" alt=""' . $style . ( $id ? ' class="wp-image-' . $id . '"' : '' ) . '/></figure><!-- /wp:image -->';
};

// ---- the site ----
update_option( 'blogname', 'Elliot Grey' );
update_option( 'blogdescription', 'People · Places · Quiet moments' );
update_option( 'timezone_string', 'Europe/London' );

// ---- pages ----
$kick = function ( $t ) { return '<!-- wp:paragraph {"style":{"typography":{"letterSpacing":"0.2em","textTransform":"uppercase","fontSize":"0.8rem"}},"textColor":"accent-4"} --><p class="has-accent-4-color has-text-color" style="letter-spacing:0.2em;text-transform:uppercase;font-size:0.8rem">' . esc_html( $t ) . '</p><!-- /wp:paragraph -->'; };

$home =
	'<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|50","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}}},"layout":{"type":"constrained"}} --><div class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--60);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--50)">' .
	'<!-- wp:columns {"verticalAlignment":"center","align":"wide"} --><div class="wp-block-columns alignwide are-vertically-aligned-center">' .
	'<!-- wp:column {"verticalAlignment":"center","width":"62%"} --><div class="wp-block-column is-vertically-aligned-center" style="flex-basis:62%">' .
	$kick( 'People · Places · Quiet moments' ) .
	'<!-- wp:heading {"level":1,"style":{"typography":{"fontSize":"3.4rem","lineHeight":"1.1","letterSpacing":"-0.02em"}}} --><h1 class="wp-block-heading" style="font-size:3.4rem;letter-spacing:-0.02em;line-height:1.1">The art of paying attention.</h1><!-- /wp:heading -->' .
	'</div><!-- /wp:column -->' .
	'<!-- wp:column {"verticalAlignment":"center","width":"38%"} --><div class="wp-block-column is-vertically-aligned-center" style="flex-basis:38%">' .
	'<!-- wp:paragraph --><p>Photography for the moments that deserve to stay.</p><!-- /wp:paragraph -->' .
	'<!-- wp:paragraph --><p><a href="/portfolio/">Explore portfolio ↗</a></p><!-- /wp:paragraph -->' .
	'</div><!-- /wp:column --></div><!-- /wp:columns --></div><!-- /wp:group -->' .

	'<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"0","bottom":"var:preset|spacing|60","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}}},"layout":{"type":"constrained"}} --><div class="wp-block-group alignfull" style="padding-top:0;padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--50)">' .
	'<!-- wp:columns {"align":"wide"} --><div class="wp-block-columns alignwide">' .
	'<!-- wp:column {"width":"53%"} --><div class="wp-block-column" style="flex-basis:53%">' . $img( 'hero', '4/3' ) . $kick( '01 / Somewhere slower' ) . '</div><!-- /wp:column -->' .
	'<!-- wp:column {"width":"25%"} --><div class="wp-block-column" style="flex-basis:25%">' . $img( 'tall', '3/4' ) . $kick( '02 / Human nature' ) . '</div><!-- /wp:column -->' .
	'<!-- wp:column {"width":"22%"} --><div class="wp-block-column" style="flex-basis:22%">' . $img( 'light', '3/4' ) . $kick( '03 / Light & space' ) . '</div><!-- /wp:column -->' .
	'</div><!-- /wp:columns --></div><!-- /wp:group -->' .

	'<!-- wp:group {"align":"full","backgroundColor":"accent-5","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}}},"layout":{"type":"constrained"}} --><div class="wp-block-group alignfull has-accent-5-background-color has-background" style="padding-top:var(--wp--preset--spacing--60);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--50)">' .
	'<!-- wp:heading {"align":"wide","style":{"typography":{"fontSize":"2.2rem"}}} --><h2 class="wp-block-heading alignwide" style="font-size:2.2rem">What I make</h2><!-- /wp:heading -->' .
	'<!-- wp:paragraph {"align":"wide"} --><p class="alignwide">Three kinds of work, one way of looking.</p><!-- /wp:paragraph -->' .
	'<!-- wp:columns {"align":"wide","style":{"spacing":{"margin":{"top":"var:preset|spacing|40"}}}} --><div class="wp-block-columns alignwide" style="margin-top:var(--wp--preset--spacing--40)">';
$cards = array(
	array( 'People', 'Portraits', 'People as they are, in the light they live in. Families, makers, couples, and the odd dog.', 'Book a session ↗', '/contact/' ),
	array( 'Places', 'Spaces', 'Houses, studios, shops and coastlines: the rooms and views that hold a story.', 'See the places ↗', '/portfolio/' ),
	array( 'Work', 'Commissions', 'Editorial stories, brand work and the occasional wedding that feels like a long lunch.', 'Start a conversation ↗', '/contact/' ),
);
foreach ( $cards as $c ) {
	$home .= '<!-- wp:column --><div class="wp-block-column">' .
		'<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|40","bottom":"var:preset|spacing|40","left":"var:preset|spacing|40","right":"var:preset|spacing|40"}},"border":{"radius":"12px"}},"backgroundColor":"base","layout":{"type":"constrained"}} --><div class="wp-block-group has-base-background-color has-background" style="border-radius:12px;padding-top:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--40)">' .
		$kick( $c[0] ) .
		'<!-- wp:heading {"level":3} --><h3 class="wp-block-heading">' . esc_html( $c[1] ) . '</h3><!-- /wp:heading -->' .
		'<!-- wp:paragraph --><p>' . esc_html( $c[2] ) . '</p><!-- /wp:paragraph -->' .
		'<!-- wp:paragraph --><p><a href="' . esc_attr( $c[4] ) . '">' . esc_html( $c[3] ) . '</a></p><!-- /wp:paragraph -->' .
		'</div><!-- /wp:group --></div><!-- /wp:column -->';
}
$home .= '</div><!-- /wp:columns --></div><!-- /wp:group -->';

$about =
	'<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}}},"layout":{"type":"constrained"}} --><div class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--60);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--50)">' .
	'<!-- wp:columns {"verticalAlignment":"center","align":"wide"} --><div class="wp-block-columns alignwide are-vertically-aligned-center">' .
	'<!-- wp:column {"verticalAlignment":"center","width":"44%"} --><div class="wp-block-column is-vertically-aligned-center" style="flex-basis:44%">' . $img( 'portrait', '3/4' ) . '</div><!-- /wp:column -->' .
	'<!-- wp:column {"verticalAlignment":"center","width":"56%"} --><div class="wp-block-column is-vertically-aligned-center" style="flex-basis:56%">' .
	$kick( 'About' ) .
	'<!-- wp:heading {"style":{"typography":{"fontSize":"2.4rem"}}} --><h2 class="wp-block-heading" style="font-size:2.4rem">Slow looking, honest light.</h2><!-- /wp:heading -->' .
	'<!-- wp:paragraph --><p>I’m Elliot Grey, a photographer working between the coast and the city. I make portraits, places and the quiet in between, and I would rather wait for the light than fake it.</p><!-- /wp:paragraph -->' .
	'<!-- wp:paragraph --><p>Available for commissions, editorial work and the occasional wedding that feels like a long lunch.</p><!-- /wp:paragraph -->' .
	'<!-- wp:buttons --><div class="wp-block-buttons"><!-- wp:button --><div class="wp-block-button"><a class="wp-block-button__link wp-element-button" href="/contact/">Get in touch</a></div><!-- /wp:button --></div><!-- /wp:buttons -->' .
	'</div><!-- /wp:column --></div><!-- /wp:columns --></div><!-- /wp:group -->';

$portfolio =
	'<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}}},"layout":{"type":"constrained"}} --><div class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--60);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--50)">' .
	'<!-- wp:paragraph {"align":"center","style":{"typography":{"letterSpacing":"0.2em","textTransform":"uppercase","fontSize":"0.8rem"}}} --><p class="has-text-align-center" style="letter-spacing:0.2em;text-transform:uppercase;font-size:0.8rem">People · Places · Quiet moments</p><!-- /wp:paragraph -->' .
	'<!-- wp:heading {"textAlign":"center","level":1} --><h1 class="wp-block-heading has-text-align-center">Portfolio</h1><!-- /wp:heading -->' .
	'<!-- wp:gallery {"columns":3,"linkTo":"none","align":"wide","sizeSlug":"large"} --><figure class="wp-block-gallery alignwide has-nested-images columns-3 is-cropped">' .
	$img( 'hero' ) . $img( 'tall' ) . $img( 'light' ) . $img( 'coast' ) . $img( 'portrait' ) . $img( 'villa' ) .
	'</figure><!-- /wp:gallery --></div><!-- /wp:group -->';

$contact =
	'<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|60","bottom":"var:preset|spacing|60","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}}},"layout":{"type":"constrained","contentSize":"720px"}} --><div class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--60);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--50)">' .
	'<!-- wp:paragraph {"align":"center","style":{"typography":{"letterSpacing":"0.2em","textTransform":"uppercase","fontSize":"0.8rem"}}} --><p class="has-text-align-center" style="letter-spacing:0.2em;text-transform:uppercase;font-size:0.8rem">Say hello</p><!-- /wp:paragraph -->' .
	'<!-- wp:heading {"textAlign":"center","level":1,"style":{"typography":{"fontSize":"2.6rem"}}} --><h1 class="wp-block-heading has-text-align-center" style="font-size:2.6rem">Let’s make something worth keeping.</h1><!-- /wp:heading -->' .
	'<!-- wp:paragraph {"align":"center"} --><p class="has-text-align-center">Tell me a little about the moment, the people and the place. I reply within two days.</p><!-- /wp:paragraph -->' .
	'<!-- wp:paragraph {"align":"center"} --><p class="has-text-align-center"><a href="mailto:hello@elliotgrey.example">hello@elliotgrey.example</a></p><!-- /wp:paragraph -->' .
	'</div><!-- /wp:group -->';

$journal = '<!-- wp:paragraph --><p>Notes from the road, the studio and the hour before dinner.</p><!-- /wp:paragraph -->';

$mk = function ( $slug, $title, $content, $extra = array() ) {
	$ex = get_page_by_path( $slug );
	$args = array_merge( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_name' => $slug, 'post_title' => $title, 'post_content' => $content, 'post_author' => 1 ), $extra );
	if ( $ex ) { $args['ID'] = $ex->ID; return wp_update_post( $args ); }
	return wp_insert_post( $args );
};
$p_home = $mk( 'home', 'Home', $home );
$p_about = $mk( 'about', 'About', $about );
$p_port = $mk( 'portfolio', 'Portfolio', $portfolio );
$p_journal = $mk( 'journal', 'Journal', $journal );
$p_contact = $mk( 'contact', 'Contact', $contact );
foreach ( array( $p_home, $p_about, $p_port, $p_contact ) as $pid ) { update_post_meta( $pid, '_wp_page_template', 'page-no-title' ); }
update_option( 'show_on_front', 'page' );
update_option( 'page_on_front', $p_home );
update_option( 'page_for_posts', $p_journal );
// the sample page and post go
foreach ( array( 'sample-page' ) as $s ) { $x = get_page_by_path( $s ); if ( $x ) wp_delete_post( $x->ID, true ); }
$hello = get_page_by_path( 'hello-world', OBJECT, 'post' ); if ( $hello ) wp_delete_post( $hello->ID, true );

// ---- posts ----
$cat = term_exists( 'Journal', 'category' ); if ( ! $cat ) { $cat = wp_insert_term( 'Journal', 'category' ); }
$cat_id = is_array( $cat ) ? (int) $cat['term_id'] : (int) $cat;
$posts = array(
	array( 'the-hour-before-dinner', 'The hour before dinner', 'coast', '<!-- wp:paragraph --><p>There is an hour, most evenings, when the light gives up trying and just settles. Kitchens fill. Someone opens a window. It is the best hour for people and the worst for hurrying.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>I shoot most of my family work then. Not because it is pretty, though it is, but because nobody is performing.</p><!-- /wp:paragraph -->' ),
	array( 'why-i-shoot-portraits-outdoors', 'Why I shoot portraits outdoors', 'tall', '<!-- wp:paragraph --><p>A studio is a room where a person waits to be looked at. A field, a doorway, a bus stop: places where they were already standing before I arrived.</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>The second kind of picture is harder to make and easier to keep.</p><!-- /wp:paragraph -->' ),
	array( 'a-note-on-waiting', 'A note on waiting', 'villa', '<!-- wp:paragraph --><p>Most of the job is waiting. Waiting for the cloud, the laugh, the dog to sit. The camera is a way of paying attention with your whole body.</p><!-- /wp:paragraph -->' ),
	array( 'print-it-small', 'Print it small', 'light', '<!-- wp:paragraph --><p>Big prints are for walls. Small prints are for hands, and hands are where photographs live. Print the ones you love at the size of a postcard and put them where you make tea.</p><!-- /wp:paragraph -->' ),
);
$day = 0;
foreach ( $posts as $p ) {
	$ex = get_page_by_path( $p[0], OBJECT, 'post' );
	$args = array( 'post_type' => 'post', 'post_status' => 'publish', 'post_name' => $p[0], 'post_title' => $p[1], 'post_content' => $p[3], 'post_author' => 1, 'post_category' => array( $cat_id ), 'post_date' => gmdate( 'Y-m-d H:i:s', time() - ( 7 + $day * 9 ) * DAY_IN_SECONDS ) );
	$day++;
	if ( $ex ) { $args['ID'] = $ex->ID; $id = wp_update_post( $args ); } else { $id = wp_insert_post( $args ); }
	if ( $id && ! is_wp_error( $id ) && ! empty( $ids[ $p[2] ] ) ) set_post_thumbnail( $id, $ids[ $p[2] ] );
}

// ---- the menu: a navigation block the header template part already uses ----
$nav_content = '';
foreach ( array( array( 'Home', $p_home ), array( 'About', $p_about ), array( 'Portfolio', $p_port ), array( 'Journal', $p_journal ), array( 'Contact', $p_contact ) ) as $m ) {
	$nav_content .= '<!-- wp:navigation-link {"label":"' . esc_attr( $m[0] ) . '","type":"page","id":' . (int) $m[1] . ',"url":"' . esc_url( get_permalink( $m[1] ) ) . '","kind":"post-type"} /-->';
}
$navs = get_posts( array( 'post_type' => 'wp_navigation', 'post_status' => 'publish', 'numberposts' => 1 ) );
if ( $navs ) { wp_update_post( array( 'ID' => $navs[0]->ID, 'post_title' => 'Main', 'post_content' => $nav_content ) ); $nav_id = $navs[0]->ID; }
else { $nav_id = wp_insert_post( array( 'post_type' => 'wp_navigation', 'post_status' => 'publish', 'post_title' => 'Main', 'post_content' => $nav_content, 'post_author' => 1 ) ); }

// ---- the look: the gogh photographer starter's own palette (it names TT5's
// Sunrise but paints over it: warm paper, near-black ink, one bronze accent),
// so both test sites wear the same colours ----
if ( class_exists( 'WP_Theme_JSON_Resolver' ) ) {
	$gs_id = WP_Theme_JSON_Resolver::get_user_global_styles_post_id();
	if ( $gs_id ) {
		$data = array(
			'version' => 3,
			'isGlobalStylesUserThemeJSON' => true,
			'settings' => array( 'color' => array( 'palette' => array(
				array( 'slug' => 'base', 'name' => 'Base', 'color' => '#F6F4EE' ),
				array( 'slug' => 'contrast', 'name' => 'Contrast', 'color' => '#171614' ),
				array( 'slug' => 'accent-1', 'name' => 'Accent 1', 'color' => '#8C7A5B' ),
				array( 'slug' => 'accent-2', 'name' => 'Accent 2', 'color' => '#171614' ),
				array( 'slug' => 'accent-3', 'name' => 'Accent 3', 'color' => '#E9E4D8' ),
				array( 'slug' => 'accent-4', 'name' => 'Accent 4', 'color' => '#8C7A5B' ),
				array( 'slug' => 'accent-5', 'name' => 'Accent 5', 'color' => '#EDE9E0' ),
				array( 'slug' => 'accent-6', 'name' => 'Accent 6', 'color' => '#8C7A5B33' ),
			) ) ),
			'styles' => array(
				'color' => array( 'background' => 'var:preset|color|base', 'text' => 'var:preset|color|contrast' ),
				'elements' => array( 'button' => array( 'color' => array( 'background' => 'var:preset|color|contrast', 'text' => 'var:preset|color|base' ) ) ),
			),
		);
		wp_update_post( array( 'ID' => $gs_id, 'post_content' => wp_json_encode( $data ) ) );
	}
}
// ---- the footer: Twenty Twenty-Five ships placeholder link columns (Blog,
// FAQs, Shop, Themes…) that read as a real site's menu; a quiet one instead ----
$footer = '<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|50","bottom":"var:preset|spacing|50","left":"var:preset|spacing|50","right":"var:preset|spacing|50"}}},"layout":{"type":"constrained"}} --><div class="wp-block-group alignfull" style="padding-top:var(--wp--preset--spacing--50);padding-right:var(--wp--preset--spacing--50);padding-bottom:var(--wp--preset--spacing--50);padding-left:var(--wp--preset--spacing--50)">' .
	'<!-- wp:group {"align":"wide","layout":{"type":"flex","justifyContent":"space-between","flexWrap":"wrap"}} --><div class="wp-block-group alignwide">' .
	'<!-- wp:group {"layout":{"type":"flex","orientation":"vertical"}} --><div class="wp-block-group"><!-- wp:site-title {"level":0} /--><!-- wp:site-tagline /--></div><!-- /wp:group -->' .
	'<!-- wp:paragraph {"style":{"typography":{"fontSize":"0.9rem"}}} --><p style="font-size:0.9rem">© Elliot Grey · <a href="/contact/">Say hello</a></p><!-- /wp:paragraph -->' .
	'</div><!-- /wp:group --></div><!-- /wp:group -->';
$theme_slug = get_stylesheet();
$term = get_term_by( 'slug', $theme_slug, 'wp_theme' );
if ( ! $term ) { $t = wp_insert_term( $theme_slug, 'wp_theme' ); $term_id = is_array( $t ) ? (int) $t['term_id'] : 0; } else { $term_id = (int) $term->term_id; }
$existing = get_posts( array( 'post_type' => 'wp_template_part', 'name' => 'footer', 'post_status' => 'publish', 'numberposts' => 1, 'tax_query' => array( array( 'taxonomy' => 'wp_theme', 'field' => 'slug', 'terms' => $theme_slug ) ) ) );
if ( $existing ) { wp_update_post( array( 'ID' => $existing[0]->ID, 'post_content' => $footer ) ); $fp = $existing[0]->ID; }
else {
	$fp = wp_insert_post( array( 'post_type' => 'wp_template_part', 'post_status' => 'publish', 'post_name' => 'footer', 'post_title' => 'Footer', 'post_content' => $footer, 'post_author' => 1 ) );
	if ( $fp && $term_id ) { wp_set_post_terms( $fp, array( $term_id ), 'wp_theme' ); wp_set_post_terms( $fp, array( 'footer' ), 'wp_template_part_area' ); }
}
// ---- the Journal: the theme's blog template says "Blog"; this site calls it Journal ----
$home_tpl = '<!-- wp:template-part {"slug":"header"} /-->' .
	'<!-- wp:group {"tagName":"main","style":{"spacing":{"margin":{"top":"var:preset|spacing|60"}}},"layout":{"type":"constrained"}} --><main class="wp-block-group" style="margin-top:var(--wp--preset--spacing--60)">' .
	'<!-- wp:heading {"level":1,"style":{"typography":{"fontSize":"2.6rem"}}} --><h1 class="wp-block-heading" style="font-size:2.6rem">Journal</h1><!-- /wp:heading -->' .
	'<!-- wp:paragraph --><p>Notes from the road, the studio and the hour before dinner.</p><!-- /wp:paragraph -->' .
	'<!-- wp:pattern {"slug":"twentytwentyfive/template-query-loop"} /-->' .
	'</main><!-- /wp:group -->' .
	'<!-- wp:template-part {"slug":"footer"} /-->';
$ex_tpl = get_posts( array( 'post_type' => 'wp_template', 'name' => 'home', 'post_status' => 'publish', 'numberposts' => 1, 'tax_query' => array( array( 'taxonomy' => 'wp_theme', 'field' => 'slug', 'terms' => $theme_slug ) ) ) );
if ( $ex_tpl ) { wp_update_post( array( 'ID' => $ex_tpl[0]->ID, 'post_content' => $home_tpl ) ); }
else {
	$tp = wp_insert_post( array( 'post_type' => 'wp_template', 'post_status' => 'publish', 'post_name' => 'home', 'post_title' => 'Journal', 'post_content' => $home_tpl, 'post_author' => 1 ) );
	if ( $tp && $term_id ) { wp_set_post_terms( $tp, array( $term_id ), 'wp_theme' ); }
}
// ---- no welcome tours: the tester sees the editor, not a guide about it ----
$prefs = get_user_meta( 1, 'wp_persisted_preferences', true );
if ( ! is_array( $prefs ) ) { $prefs = array(); }
$prefs['core/edit-post'] = array_merge( isset( $prefs['core/edit-post'] ) && is_array( $prefs['core/edit-post'] ) ? $prefs['core/edit-post'] : array(), array( 'welcomeGuide' => false, 'welcomeGuideTemplate' => false ) );
$prefs['core/edit-site'] = array_merge( isset( $prefs['core/edit-site'] ) && is_array( $prefs['core/edit-site'] ) ? $prefs['core/edit-site'] : array(), array( 'welcomeGuide' => false, 'welcomeGuideStyles' => false, 'welcomeGuidePage' => false, 'welcomeGuideTemplate' => false ) );
$prefs['core'] = array_merge( isset( $prefs['core'] ) && is_array( $prefs['core'] ) ? $prefs['core'] : array(), array( 'welcomeGuide' => false ) );
$prefs['_modified'] = gmdate( 'c' );
update_user_meta( 1, 'wp_persisted_preferences', $prefs );
flush_rewrite_rules();
echo "Elliot Grey in core blocks: home={$p_home} about={$p_about} portfolio={$p_port} journal={$p_journal} contact={$p_contact} nav={$nav_id} media=" . implode( ',', $ids ) . "\n";
