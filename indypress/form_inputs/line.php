<?php
add_action('indypress_input_init_line', 'indypress_input_line_init', 10, 2);
function indypress_input_line_init ($args, $number) {
	new indypress_input_line( $args, $number );
}
class indypress_input_line {
	/* Handles a line input.
		Args:
		'required' (optional): if true, it's required (both as html5 "soft" validation and php one)
	 */
	function indypress_input_line ( $args, $number ) {
		$this->args = $args;
		add_filter( 'indypress_input_form_line_' . $number, array( &$this, 'form' ), 10, 2 );
		add_filter( 'indypress_form_validation_all', array( &$this, 'validation' ), 10, 2 );
	}
	function form( $previous, $submitted ) {
		//this outputs html
		$args = $this->args;

		if(isset($args['value']))
			$value = $args['value'];
		elseif( isset( $submitted[ $args['name'] ] ) )
			$value = $submitted[ $args['name'] ];
		else
			$value = '';
		$previous .= '
			<input type="text" name="' . $args['name'] . '" value="' . esc_attr( stripslashes($value) ) . '" size="50" maxlength="200"';
		if(isset($args['required']) && $args['required'])
			$previous .= ' required ';
		$previous .= 'class="' . $args['classes'] . '" title="' . $args['title'] . '" />';
		return $previous;
	}
	function validation( $errors, $submitted ) {
		$args = $this->args;
		if( !isset($args['required']) || $args['required'] != true )
			return $errors;
		if( !isset( $submitted[ $args['name'] ] ) || empty( $submitted[ $args['name'] ] ) )
			$errors[] = $args['name'];
		return $errors;
	}

}

