<?php

function get_indy_publish_page_id() {
		return get_option('indypress_publication_page');
}
/**
 * get_indy_publish_permalink
 *
 * Especially useful for theme developers
 * 
 * @access public
 * @return string Permalink for publish page
 */
function get_indy_publish_permalink() {
		return get_permalink(get_indy_publish_page_id());
}

function get_indypress_publication_terms( $deprecated_reverse = false ) {
	$slugs = get_option( 'indypress_formlist' );
	$slugs = apply_filters( 'get_indypress_publication_terms', $slugs );

	return $slugs;
}

function indypress_get_form_slug() {
	//TODO: implemente indypress_get_form_slug for custom $url
	if( !isset($_GET['indypress']) )
		return null;
	$current_slug = $_GET['indypress'];
	return $current_slug;
}

?>
