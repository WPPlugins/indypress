<?php
add_action('indypress_action_init_pre', 'indypress_action_pre_init', 10, 1);
function indypress_action_pre_init ($args) {
	new indypress_action_pre( $args );
}
class indypress_action_pre {
	/* This will change a "fundamental" field of your post
			'post_field': the field you want to create/update
				'submit_field': the field to take the data from (usually, it's the "name" of some input)
				OR
				'static': a static value
			'operation': can be 
				'set' (default): the value is changed to what the user says
				'array_push': if the previous value was an array, add an element to it
				'array_merge': add an array to an array
	 */
	function indypress_action_pre ($args) {
		$this->args = $args;
		add_filter('indypress_form_action_pre_' . $args['post_field'], array($this, 'change_field'), 10 , 2 );
	}
	function change_field( $previous, $data) {
		$args = $this->args;
		if(isset($args['static']))
			$new = $args['static'];
		else
			$new = $data[$args['submit_field']];
		if(!isset($args['operation']))
			$args['operation'] = 'set';
		switch($args['operation']) {
			case 'array_merge':
				$previous = array_merge($previous, $new);
				break;
			case 'array_push':
				array_push($previous, $new);
				break;
			default:
			case 'set':
				$previous = $new;
				break;
		}
		return $previous;
	}
}
