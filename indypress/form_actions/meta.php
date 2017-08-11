<?php
add_action('indypress_action_init_meta', 'indypress_action_meta_init', 10, 1);
function indypress_action_meta_init ($args) {
	new indypress_action_meta( $args );
}
class indypress_action_meta {
	/* This will add a meta to your post: you must supply an $args which contains
			'meta_field': the field you want to create/update
			'submit_field': the field to take the data from (usually, it's the "name" of some input)
			//TODO: allow filtering it
			//TODO: allow to customize uniqueness
	 */
	function indypress_action_meta ($args) {
		$this->args = $args;
		add_action('indypress_form_action_post', array($this, 'add_meta'), 10, 2);
	}
	function add_meta( $post_id, $submitted) {
		$args = $this->args;
		if(isset($submitted[$args['submit_field']]))
			add_post_meta( $post_id, $args['meta_field'], $submitted[$args['submit_field']], true );
	}
}

