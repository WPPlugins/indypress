<?php
add_action('indypress_input_init_select', 'indypress_input_select_init', 10, 2);
function indypress_input_select_init ($args, $number) {
	new indypress_input_select( $args, $number );
}
class indypress_input_select {
	/* Handles a select input.
		Arguments:
		'name': the name of the fields.
		'entries' :	list of boxes: it's a id=>extra_dict array, where extra_dict is an array with "extra" information: checked, label, etc
		'ids_are_terms' (optional): if this is true, ids are considered to be terms,
			and the label, when missing from the entries, is retrieved accordingly
		'entries_from_tax': (optional) array of taxonomy from which the entries have to be taken (or just a single taxonomy)
	 */
	function indypress_input_select ( $args, $number ) {
		$this->entries = $args['entries'];
		if(isset($args['entries_from_tax'])) {
			$args['ids_are_terms'] = true;
			foreach((array)$args['entries_from_tax'] as $tax)
				foreach( get_categories(array('taxonomy' => $tax)) as $term )
					$this->entries[$term->term_id] = array();
		}
		$this->args = $args;
		add_filter( 'indypress_input_form_select_' . $number, array( &$this, 'form' ) );
//        add_filter( 'indypress_input_publication_' . $args['action_publish'], array( &$this, 'change_field' ), 10, 2 );
		add_filter( 'indypress_form_validation_field_' . $args['name'], array( &$this, 'validation' ), 10, 2 );
		add_filter( 'indypress_form_validation_all', array( &$this, 'validation_all' ), 10, 2 );
	}
	function form( $previous ) {
		//this outputs html
		$args = $this->args;
		$form = $previous;
		$name = $args['name'];

		$form .= '<select name="' . $name . '">';
		$form .= '<option></option>';

		foreach( $this->entries as $id => $attrs ) {
			if( in_array( 'label', $attrs ) )
				$label = $attrs['label'];
			elseif( $args['ids_are_terms'] )
				$label = get_term_by( 'id', $id, get_taxonomy_from_term($id) )->name;
			else
				$label = $id;
			
			$form .= "\n\t\t<option value=\"$id\">";
			
			$form .= $label . '</option>' . PHP_EOL;
		}
		$form .= '</select>';
		return $form;
	}
	function validation( $errors, $value ) {
		//rationale: value must be in $args['entries']
		$args = $this->args;
		if(!in_array($value, array_keys($this->entries) ))
			$errors[] = $args['name'];
		return $errors;
	}
	function validation_all( $errors, $submitted ) {
		$args = $this->args;
		if(!isset($submitted[$args['name']]))
			$errors[] = $args['name'];
		return $errors;
	}
}

