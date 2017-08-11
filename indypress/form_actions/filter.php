<?php
add_action('indypress_action_init_filter', 'indypress_action_filter_init', 10, 1);
function indypress_action_filter_init ($args) {
	new indypress_action_filter( $args );
}
class indypress_action_filter {
/*	This will filter a field submitted by the user; it's important to do security checks, for example
			'submit_field': the field to filter
			'filter': function name
	 */
	function indypress_action_filter ($args) {
		$this->args = $args;
		add_action('indypress_form_post', array($this, 'filter'), 10, 2);
	}
	function filter( $submitted) {
		$args = $this->args;
		$data = $submitted[$args['submit_field']];
		$new = $args['filter']($submitted[$args['submit_field']]);
		if(isset($submitted[$args['submit_field']]))
			$submitted[$args['submit_field']] = $new;
		return $submitted;
	}
}


