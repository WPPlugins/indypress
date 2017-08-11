<?php

class indypress_publication_form {

	// HOOK TO LOAD THIS CLASS
	function indypress_publication_form() {

		$this->load_settings();

		// Add javascript and css files
		add_filter( 'the_posts', array( $this, 'scripts_and_styles' ) );

	}

	function load_settings() {

		$this->publication_page = get_option( 'indypress_publication_page' );

		$this->preview = get_option( 'indypress_preview' );

	}

	// LOAD WP CORE SCRIPTS AND STYLES
	function scripts_and_styles( $posts ) {
		global $wp_query;

		//TODO: they should be loaded by appropriate plugins
		if( ( isset( $_GET['indypress'] ) && is_numeric( $_GET['indypress'] ) ) || ( isset( $_GET['indypress_lb'] ) && is_numeric( $_GET['indypress_lb'] ) ) ) {

			if( !get_option( 'indypress_disable_publication_css' ) ) {
				wp_enqueue_style( 'indypress_publication' );
			}
		}

		return $posts;
	}

	// PRINT PUBLICATION FORM
	function form( $args=NULL ) {
		// Printin form	
		$form = '

	<form action="' . $_SERVER['REQUEST_URI'] . '" name="post" id="post" method="post" enctype="multipart/form-data">

	<div id="post-body">
	<div id="post-body-content">';

		do_action('indypress_inputs_initialize');
		$i = 1;
		foreach(indypress_input_get() as $field) {
			$form .= apply_filters('indypress_input_form_' . $field['type'] . '_' . $i, '', $args);
			$i = $i + 1;
		}

		// Show publish and preview button
		$form .= '
		<br><br>
		<input type="submit" name="azione" value="' . __( 'Publish' ) . '" title="' . __( 'Send' ) . '">';
		if( $this->preview )
		$form .= '
		<input type="submit" name="azione" value="' . __( 'preview' ) . '" title="' . __( 'Preview' ) . '">';
		$form .= '
		</div></div>';

		// Print javascripts
		do_action( 'indypress_publication_form_javascript' );

		return apply_filters( 'indypress_publication_form', $form );

	}

}

?>
