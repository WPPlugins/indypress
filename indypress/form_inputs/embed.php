<?php
add_action('indypress_input_init_embed', 'indypress_input_embed_init', 10, 2);
function indypress_input_embed_init ($args, $number) {
	new indypress_input_embed( $args, $number );
}
class indypress_input_embed {
	/* Handles an embed: it is not a true "field" of the form; it's an utility to
			embed urls and insert into tinymce editor
		Args:
		'editor': it's different than in "upload": it MUST BE given the tinymce name
	 */
	function indypress_input_embed ( $args, $number ) {
		if(!isset($args['editor']))
			$args['editor'] = 0;
		$this->args = $args;
		add_filter( 'indypress_input_form_embed_' . $number, array( &$this, 'form' ), 10, 2 );
		add_action('indypress_inputs_styles', array($this, 'scripts'));
		add_filter( 'indypress_form_post', array( &$this, 'submitted' ), 10, 2 );
	}
	function scripts() {
		global $indypress_url;
		$args = $this->args;
		wp_enqueue_script('indyembed', $indypress_url . 'form_inputs/embed.js',
			array('jquery', 
			'jquery-ui-core',
			'jquery-ui-dialog'),
			false, true );
		wp_enqueue_style('jquery-ui-smoothness', $indypress_url . 'css/smoothness/jquery-ui-1.8.16.custom.css');
		wp_localize_script('indyembed', 'embed_params',
			array(
				'editor' => $args['editor'],
				'imageurl' => $indypress_url . 'images/videoplayer.png'
		) );
	}
	function form( $previous, $submitted ) {
		//this outputs html
		$args = $this->args;

		$form = <<<EOHTML
<a id="embed-button">Embed video<br/>from external sites</a>
EOHTML;
		return $previous . $form;
	}
	function submitted( $submitted ) {
		function replace_embeds( $content ) {
			//FIXME: just doesnt work
			$pattern = '#<img[^<>]*data-indy-embed=."([^"]*)."[^<>]*/>#i';

			$new_content = $content;

			$res = preg_match_all( $pattern, $content, $matches, PREG_SET_ORDER );
			if($res === FALSE ) //error
				return $content;
			foreach( $matches as $m ) {
					$url = htmlspecialchars_decode($m[1]); //don't know why (maybe because it's a data-* attribute?), but it is escaped, so that & becomes &amp; etcetera
					$new_content = str_replace( $m[0], '[embed]' . $url . '[/embed]', $new_content );
			}
			return $new_content;
		}
		$args = $this->args;
		$submitted[$args['editor']] = replace_embeds($submitted[$args['editor']]);
		return $submitted;
	}
}

