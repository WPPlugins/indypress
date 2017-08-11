<?php
add_action('indypress_input_init_upload', 'indypress_input_upload_init', 10, 2);
function indypress_input_upload_init ($args, $number) {
	new indypress_input_upload( $args, $number );
}
class indypress_input_upload {
	/* Handles an upload: it is not a true "field" of the form; it's an utility to
			upload images and insert into tinymce editor
		Args:
			'editor': default to 0, so that the first tinymce is used. If you have more than one, give its name
	 */
	function indypress_input_upload ( $args, $number ) {
		if(!isset($args['editor']))
			$args['editor'] = 0;
		$this->args = $args;
		add_filter( 'indypress_input_form_upload_' . $number, array( &$this, 'form' ), 10, 2 );
		add_action('indypress_inputs_styles', array($this, 'scripts'));
	}
	function scripts() {
		global $indypress_url;
		$args = $this->args;
		wp_enqueue_script('indyupload', $indypress_url . 'form_inputs/upload.js',
			array('jquery', 
			'jquery-ui-core',
			'jquery-ui-dialog',
			'jquery-form'),
			false, true );
		wp_enqueue_style('jquery-ui-smoothness', $indypress_url . 'css/smoothness/jquery-ui-1.8.16.custom.css');
		wp_localize_script('indyupload', 'params',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'editor' => $args['editor']
		) );
	}
	function form( $previous, $submitted ) {
		//this outputs html
		$args = $this->args;

		$form = <<<EOHTML
<a id="upload-button">Upload<br/>images</a>
<div id="post_attachments" class="invisible">
	<h3>Attachments</h3>
	<ul></ul>
</div>
EOHTML;
		return $previous . $form;
	}
}

