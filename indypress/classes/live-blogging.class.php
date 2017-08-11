<?php

class indypress_liveblogging {

	function indypress_liveblogging() {

		$this->load_settings();

		// ADD FILTER TO PUBLICATION TERMS
		add_filter( 'get_indypress_publication_terms', array( $this, 'get_terms' ) );
	}

	function load_settings() {
		$this->publication_page = get_option( 'indypress_publication_page' );
		$this->active_liveblogs = get_option( 'indypress_active_liveblogs', array() );
	}
	
	function get_terms( $allow_terms ) {
		$active_liveblogs_terms = array();

		if( $this->active_liveblogs ) {
			foreach( $this->active_liveblogs as $term_id )
				$active_liveblogs_terms[] = array( 'ID' => $term_id, 'url' => '?page_id=' . $this->publication_page . '&indypress_lb=' . $term_id, 'name' => get_the_title( get_term( $term_id, get_taxonomy_from_term( $term_id ) )->name ) );
			$allow_terms = array_merge( $allow_terms, $active_liveblogs_terms );
		}

		return $allow_terms;
	}

}

?>
