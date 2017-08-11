<?php
add_action('indypress_input_init_autocomplete', 'indypress_input_autocomplete_init', 10, 2);
function indypress_input_autocomplete_init ($args, $number) {
	new indypress_input_autocomplete( $args, $number );
}
class indypress_input_autocomplete {
	/* Handles an autocomplete input: it is similar to a line input, but has autocomplete
		It's meant for tags and similar
	 */
	function indypress_input_autocomplete ( $args, $number ) {
		$this->args = $args;
		add_filter( 'indypress_input_form_autocomplete_' . $number, array( &$this, 'form' ) );
		add_filter( 'indypress_form_post', array( &$this, 'submitted' ) );
	}
	function form( $previous ) {
		//this outputs html
		//TODO: autocomplete :)
		$args = $this->args;
		return '
			<input type="text" name="' . $args['name'] . '" value="' . esc_attr( stripslashes( $args['value'] ) ) . '" size="50" maxlength="200" class="' . $args['classes'] . '" title="' . $args['title'] . '" />';
	}
	function submitted( $submitted ) {
		$args = $this->args;
		$previous = $submitted[ $args['name'] ];
		if( is_string( $previous ) )
			$submitted[ $args['name'] ] = explode( ',', $previous );
		return $submitted;
	}

}

