<?php
add_action('indypress_input_init_checkboxes', 'indypress_input_checkboxes_init', 10, 2);
function indypress_input_checkboxes_init ($args, $number) {
	new indypress_input_checkboxes( $args, $number );
}
class indypress_input_checkboxes {
	/* Handles a checkboxes input.
		Arguments:
		'name': the name of the fields. "[]" shall not be present
		'entries' :	list of boxes: it's a id=>extra_dict array, where extra_dict is an array with "extra" information: checked, label, title, etc

		'ids_are_terms' (optional): if this is true, ids are considered to be terms,
			and the label, when missing from the entries, is retrieved accordingly
		'entries_from_tax': (optional) array of taxonomy from which the entries have to be taken (or just a single taxonomy)
	 */
	function indypress_input_checkboxes ( $args, $number ) {
		$this->entries = $args['entries'];
		if(isset($args['entries_from_tax'])) {
			$args['ids_are_terms'] = true;
			foreach((array)$args['entries_from_tax'] as $tax)
				foreach( get_categories(array('taxonomy' => $tax)) as $term )
					$this->entries[$term->term_id] = array();
		}
		$this->args = $args;

		add_filter( 'indypress_input_form_checkboxes_' . $number, array( &$this, 'form' ) );
		add_filter( 'indypress_form_validation_field_' . $args['name'], array( &$this, 'validation' ), 10, 2 );
		add_filter( 'indypress_form_validation_all', array( &$this, 'validation_all' ), 10, 2 );
	}
	function form( $previous ) {
		//this outputs html
		$args = $this->args;
		$form = $previous;

		$name = $args['name'];
		$i = 0;
		foreach( $this->entries as $id => $attrs ) {
			if( in_array( 'label', $attrs ) )
				$label = $attrs['label'];
			elseif( $args['ids_are_terms'] )
				$label = get_term_by( 'id', $id, get_taxonomy_from_term($id) )->name;
			else
				$label = $id;
			if(isset($attrs['title']))
				$title = $attrs['title'];
			else
				$title = '';
			
			$form .= "\n\t\t<label for=\"$name$i\"><input type=\"checkbox\" id=\"$name$i\" name=\"{$name}[]\" value=" . $id;
			
			//TODO: buggy now. needs to acceed $_POST
			if( !empty( $args[$name] ) )
				if( in_array( 'checked', $attrs ) && $attrs['checked'] )
					$form .= ' checked="checked"';

			$form .= ' title="' . $title . '">' . $label . '</label><br />';
			$i = $i + 1;
		}
		return $form;
	}
	function validation( $errors, $value ) {
		//rationale: each item in value must be in $args['entries']
		$args = $this->args;
		foreach($value as $item) {
			if(!in_array($item, array_keys($this->entries) ))
				$errors[] = $args['name'] . 'is not a valid value';
		}
		if(isset($args['max']))
			if(count($value) > $args['max'])
				$errors[] = $args['name'] . 'too many selected: maximum is ' . $args['max'];
		return $errors;
	}
	function validation_all( $errors, $submitted ) {
		$args = $this->args;
		if(!isset($submitted[$args['name']]))
			$errors[] = $args['name'];
		return $errors;
	}
}

