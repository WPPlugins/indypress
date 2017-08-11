<?php
add_action('indypress_input_init_html', 'indypress_input_html_init', 10, 2);
function indypress_input_html_init ($args, $number) {
	new indypress_input_html( $args, $number );
}
class indypress_input_html {
/*
		This just adds output, really simple
 */
	function indypress_input_html ( $args, $number ) {
		$this->args = $args;
		add_filter( 'indypress_input_form_html_' . $number, array( &$this, 'form' ) );
	}
	function form( $previous ) {
		return $this->args['content'];
	}
}

