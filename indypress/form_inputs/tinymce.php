<?php
add_action('indypress_input_init_tinymce', 'indypress_input_tinymce_init', 10, 2);
function indypress_input_tinymce_init ($args, $number) {
	new indypress_input_tinymce( $args, $number );
}
class indypress_input_tinymce {
	function indypress_input_tinymce ( $args, $number ) {
		$default = array(
			'name' => 'post_content',
			'disable_if_mobile' => true,
			'embedly_hack' => false
		);
		$args = array_merge($default, $args);
		$this->args = $args;
		add_filter( 'indypress_input_form_tinymce_' . $number, array( &$this, 'form' ), 10, 2 );
		//VERY DIRTY HACK!
		if($args['embedly_hack'] && is_user_logged_in() && function_exists('embedly_footer_widgets'))
			add_action('wp_print_scripts', 'embedly_footer_widgets');
	}
	function form( $previous, $submitted ) {
		//this outputs html
		//TODO: required should be optional
		$args = $this->args;
		$form = $previous;
		$form .= $this->load_visual_editor( $submitted );

		return $form;
	}
	function filtering_richedit( $value ) {
			return true;
	}

	// Load TinyMCE
	function load_visual_editor( $submitted ) {
		//TODO: make it work with wp3.3
		$args = $this->args;

		// Add filter to force rich editing
		global $indypress_url;
		add_filter( 'get_user_option_rich_editing', array( $this, 'filtering_richedit' ), 10, 1 );
		$enabled = true;
		if($args['disable_if_mobile'] && is_mobile())
			$enabled = false;

		ob_start();

		// Load tinyMCE
		if(function_exists('wp_editor')) { //wordpress >= 3.3
			if($enabled)
				$tinymce = array('theme_advanced_buttons1' => 'bold,italic,underline,separator,bullist,numlist,justifyleft,justifycenter,justifyright,justifyfull,undo,redo,link,unlink,separator,removeformat,separator,fullscreen,wp_adv' );
			else $tinymce = false;
			wp_editor( esc_attr( stripslashes( $submitted[$args['name']] ) ), $args['name'],
				array(
					'tinymce' => $tinymce,
					'quicktags' => $enabled
				));
		}
		else { //wordpress <= 3.2
			echo '<textarea name="' . $args['name'] . '" class="post_content" id="post_content" style="width:98%; height:200px">' . esc_attr( stripslashes( $submitted[$args['name']] ) ) . '</textarea>';
			if($enabled) { //if not $enabled, the textarea will do its job
				require_once( ABSPATH . 'wp-admin/includes/post.php' );
				wp_tiny_mce( true,
					array(
						'mode' => 'textareas',
						'editor_selector' => 'post_content',
						'theme_advanced_buttons1' => "bold,italic,underline,|,justifyleft,justifycenter,justifyright, justifyfull,|,bullist,numlist,|,undo,redo,|,link,unlink,|,removeformat",
						'theme' => 'advanced',
						'plugins' => 'spellchecker',
						'content_css' => $indypress_url . 'css/publication_visual_editor.css',
						'width' => '100%', 'height' => '400',
						'language' => 'it',
						'paste_auto_remove_styles' => 'true',
						'paste_auto_remove_spans' => 'true',
						'paste_auto_cleanup_on_paste' => 'true',
						'skin' => 'default'
					) );
			}
		}

		$outputted = ob_get_contents();
		ob_end_clean();

		// Delete filter to force rich editing
		remove_filter( 'get_user_option_rich_editing', array( $this, 'filtering_richedit' ) );


		return $outputted;
	}

}

