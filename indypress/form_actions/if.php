<?php
/* That's "meta": this call an action if a condition is met, another otherwise
	condition is specified by "if", and can be: "logged","mobile"
	if positive, the configuration contained in "then" will be loaded;
	else, the configuration contained in "else" will be loaded
*/
add_action('indypress_action_init_if', 'indypress_action_if_init', 10, 1);
function indypress_action_if_init ($args) {
	switch($args['if']) {
		case 'logged':
			$condition = is_user_logged_in();
			break;
		case 'mobile':
			$condition = is_mobile();
			break;
		default:
			return;
			break;
	}

	if( $condition )
		$sub_args = $args['then'];
	else
		$sub_args = $args['else'];
	do_action('indypress_action_init_' . $sub_args['type'], $sub_args);
}

