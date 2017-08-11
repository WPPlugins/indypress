<?php

function is_hidden_post() {
	global $post;
	if( is_single() && $post->post_status == 'hide' )
		return true;
	return false;
}

function is_premoderate_post() {
	global $post;
	if( is_single() && $post->post_status == 'premoderate' )
		return true;
	return false;
}

/**
 * indypress_get_hide_post_href 
 * 
 * @param integer $pid  Post ID
 * @param string $status target status: hide/premoderated/normal
 * @access public
 * @return string Complete link
 */
function indypress_get_hide_post_href( $pid, $status) {
  global $post;
  return admin_url( 'admin-ajax.php' ) . '?action=change_post_status&status=' . $status . '&post_id=' . $pid . 
	'&nonce=' . wp_create_nonce('indypress-change-post-status');
}

?>
