<?php

class indypress_publication_admin {

	function indypress_publication_admin() {
		add_action( 'wp_ajax_tag-autocomplete', array( $this, 'complete' ) );
		add_action( 'wp_ajax_nopriv_tag-autocomplete', array( $this, 'complete' ) );

		add_action('manage_posts_custom_column', array( &$this, 'anon_user_column' ), 10, 2);
		add_filter('manage_posts_columns', array( &$this, 'add_columns' ));
	}

	function complete() {

		global $wpdb;

		if ( isset( $_GET['tax'] ) ) {
			$taxonomy = sanitize_key( $_GET['tax'] );
			$tax = get_taxonomy( $taxonomy );
			if ( ! $tax )
				die( '0' );
		} else {
			die('0');
		}

		$s = stripslashes( $_GET['q'] );

		if ( false !== strpos( $s, ',' ) ) {
			$s = explode( ',', $s );
			$s = $s[count( $s ) - 1];
		}

		$s = trim( $s );

		if ( strlen( $s ) < 2 )
			die(); // require 2 chars for matching

		$results = $wpdb->get_col( $wpdb->prepare( "SELECT t.name FROM $wpdb->term_taxonomy AS tt INNER JOIN $wpdb->terms AS t ON tt.term_id = t.term_id WHERE tt.taxonomy = %s AND t.name LIKE (%s)", $taxonomy, '%' . like_escape( $s ) . '%' ) );
		echo join( $results, "\n" );

		die;
	}


	function add_columns( $columns ) {
		$columns['anonuser'] = 'Anonymous name';
		return $columns;
	}
	function anon_user_column( $column_name, $post_id ) {
		if( $column_name == 'anonuser' )
			echo get_post_meta( $post_id, 'post_author_name', TRUE );
	}
}

?>
