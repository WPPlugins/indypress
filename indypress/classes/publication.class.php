<?php

class indypress_publication {

	// HOOK TO LOAD THIS CLASS
	function indypress_publication() {

		global $indypress_path;

		// REQUIRE API
		require_once( $indypress_path . '/api/publication.php' );
		require_once( $indypress_path . '/api/term.php' );

		$this->load_settings();

		// ENABLE SESSION
		add_action( 'init', array( $this, 'enable_session') );

		// REPLACE CONTENT PAGE WITH INDYPRESS SCRIPT
		add_filter( 'the_content', array( $this, 'publication' ) );

		add_action( 'indypress_clean_old_drafts', array( $this, 'clean_old_drafts' ) );

		// WP add -2, -3, etc. if a post with same name exist, but only if they
		// have same type. This leads to conflicts between posts, events and
		// liveblog entries. This avoid these problems
		add_filter( 'name_save_pre', array( $this, 'fix_names' ) );

		add_action( 'indypress_inputs_initialize', array($this, 'inputs_initialize') );
		add_action( 'indypress_actions_initialize', array($this, 'actions_initialize') );

		add_action( 'wp_print_styles', array($this, 'styles') );
	}
	function styles() {
		global $wp_query;
		$this->this_page_id = $wp_query->post->ID;

		// IF PLUGIN MUST BE SHOWN IN THIS POST/PAGE
		if( $this->this_page_id == $this->publication_page && isset($_GET['indypress']) && !isset($_POST['azione']) ) {
			do_action('indypress_inputs_initialize');
			do_action('indypress_inputs_styles');
		}
	}

	// ENABLING AND CLEARING SESSION
	function enable_session() {
		if ( !session_id() )
			session_start();
	}

	function clear_session() {
		unset( $_SESSION['indypress_attachment'] );
	}

	// LOAD PUBLICATIONS SETTINGS
	function load_settings() {
		$this->author = get_option( 'indypress_author' );
		$this->publication_page = get_option( 'indypress_publication_page' );
	}

	function publication( $content ) {

		global $wp_query, $indypress_publication_form;

		$this->this_page_id = $wp_query->post->ID;

		// IF PLUGIN MUST BE SHOWN IN THIS POST/PAGE
		if( $this->this_page_id != $this->publication_page) {
			$this->clear_session();
			return $content;
		}
		if( !isset($_GET['indypress']) )
			return $this->publication_terms();
		if( !in_array($_GET['indypress'], get_indypress_publication_terms() ) )
			die( 'Non-existent term (or you don\'t have proper permissions)' );
		// GET INDYPRESS PAGE
		$current_slug = $this->current_slug = indypress_get_form_slug();

		if( $_POST && !( isset( $_POST['azione'] ) && $_POST['azione'] == 'preview' ) ) { // publish
			$result = $this->publish( $_POST );
			if( is_numeric( $result ) ) {
				$return = '<h2>' . __('Post published.', 'indypress') . '</h2>';
				$return .= __('View', 'indypress') . '<a href="' . get_permalink( $result ) . '">' . __('post', 'indypress') . '</a>';
				return $return;
			}
			elseif( is_array( $result ) ) {
				$return = '<h2>' . __('Attention! Following field are required', 'indypress') . '</h2>';
				$return .= implode('<br/>', $result);
				$return .= $indypress_publication_form->form( $_POST );
				return $return;
			}
		} elseif( isset ( $current_slug ) ) {
			if( $_POST && $_POST['azione'] == 'preview' ) { // preview
				$result = $this->publish( $_POST, 'draft' );
				if( is_numeric( $result ) ) {
					wp_schedule_single_event( time() + 3600, 'indypress_clean_old_drafts', array( $result ) );
					print_r( wp_next_scheduled( 'indypress_clean_old_drafts' ) );
					$return .= __('View', 'indypress') . '<a href="' . get_permalink( $result ) . '">' . __('preview', 'indypress') . '</a>';
				}
				else {
					$return .= '<h2>' . __('Warning! Following fields are required', 'indypress') . '</h2>';
					foreach( $result as $error )
						$return .= $error . '<br>';
				}
				$return .= $indypress_publication_form->form( $_POST );
				return $return;
			}
			//display the form

				return $indypress_publication_form->form($args);
			} else
				return $this->publication_terms();
		return $content;
	}

	// PRINT THE PUBLICATION CATEGORIES LIST
	function publication_terms( $reverse_sort=false ) {
		$this->clear_session();

		$slugs = get_indypress_publication_terms();

		$return = "";

		foreach( $slugs as $slug ) {
			$url = '?page_id=' . $this->publication_page . '&indypress=' . $slug;
			$displayname = get_option('indypress_forms_' . $slug . '_title', '');
			if(!$displayname)
				$displayname = ucfirst($slug);
			$displayname = apply_filters('indypress_publication_term_title_' . $slug,  $displayname);
			$return .= '<a href="' . $url . '">' . $displayname . '</a><br />';
		}
		
		return $return;
	}

	/**
	 * validate 
	 * 
	 * @param array $submitted something like $_POST, or slightly different
	 * @return array the list of errors, or an empty one on success
	 */
	function validate( $submitted ) {
		//TODO: good way of giving human readable error messages
		$errors = array();

		foreach($submitted as $field => $value) {
			$errors = apply_filters('indypress_form_validation_field_' . $field, $errors, $value);
		}
		$errors = apply_filters('indypress_form_validation_all', $errors, $submitted);

		return $errors;
	}
	function publish( $args, $status='default' ) {

		global $wpdb, $indypress_url;

		// CHECK POST VARS
		$error = false;

		do_action('indypress_inputs_initialize'); //inputs could setup validation
		$error_return = $this->validate($args);
		if(!empty($error_return))
			$error = true;
		// MANDATORY VARS
		if( empty( $args['post_content'] ) ) {
			$error = true;
			$error_return[] = __('Text body', 'indypress');
		}

		do_action('indypress_actions_initialize');
		do_action('indypress_inputs_initialize');
		//before even trying to insert things, we'll filter $_POST
		//this is needed for anti-injection, or composite values: for example tags is usually a string with commas
		//a filter could convert it in an array()
		$submitted = apply_filters('indypress_form_post', $_POST);
		//from now on, using $_POST is BANNED! only $submitted is allowed

		// IF CHECK POST VARS IS OK, TRY TO INSERT POST INTO DATABASE
		if( !$error ) {
			// This (will) be a two-phase thing:
			// - first, data will be collected (using filters), to create the post: title, content, author, terms, tags
			// - then, metadata, attachments, whatever, will be created

			// AUTHOR NAME
			if( is_user_logged_in() ) {
				$current_user = wp_get_current_user();
				$author = $current_user->ID;
			} else
				$author = $this->author;

			// ALL FIELDS SAVED INTO AN ARRAY
			$cats = apply_filters('indypress_form_action_pre_terms', array(), $submitted);
			$my_post = array(
				'post_title' => apply_filters('indypress_form_action_pre_title', $submitted['post_title'], $submitted),
				'post_author' => apply_filters('indypress_form_action_pre_author', $author, $submitted),
				'post_content' => apply_filters('indypress_form_action_pre_content', $submitted['post_content'], $submitted),
				'post_category' => $cats,
				'post_type' => apply_filters('indypress_form_action_pre_type', 'post', $submitted),
				'post_status' => apply_filters('indypress_form_action_pre_status', 'publish', $submitted),
				'tags_input' => apply_filters('indypress_form_action_pre_tags', $submitted['tags_input'], $submitted)
			);


			// INSERT POST INTO DATABASE
			$id_new_post = wp_insert_post( $my_post );
			do_action('indypress_form_action_post', $id_new_post, $submitted);
			if( $id_new_post ) {
				foreach( $cats as $term ) { //why is it needed? but it truly is
					$tax = get_taxonomy_from_term( $term );
					wp_set_object_terms( $id_new_post, (int)$term, $tax, true );
				}

				// CHANGE AUDIO AND VIDEO PLAYER IMAGE WITH THE FLASH OBJECT
				$new_post = get_post( $id_new_post );
				$new_content = $new_post->post_content;

				// UPDATE CONTENT
				if( $new_content != $new_post->post_content )
					wp_update_post( array( 'ID' => $id_new_post, 'post_content' => $new_content ) );

				// UPDATE ATTACHMENTS
				if( $_SESSION['indypress_attachment'] ) {

					// ATTACH FILE TO POST
					foreach( $_SESSION['indypress_attachment'] as $attachment ) {
						$query = "UPDATE $wpdb->posts SET post_parent=$id_new_post WHERE guid='".$attachment['url']."'";
						if( !$wpdb->query( $query ) ) { $error = true; $error_return[] = __('System error 4') . $query; }
					}

				}
			}
			else
				return -1;

			if( $error )
				return $error_return;
			else
				return $id_new_post;
		} else
			return $error_return;

	}

	function clean_old_drafts( $id=null ) {
		global $wpdb;

		$querystr = "SELECT DISTINCT a.post_parent AS id
			FROM $wpdb->posts AS a
				LEFT JOIN $wpdb->posts AS b ON a.post_parent = b.ID
			WHERE b.ID is NULL AND a.post_parent != 0";

		$ids = $wpdb->get_col( $querystr );

		foreach( $ids as $a )
			wp_delete_post( $a, true );

		if( is_numeric( $id ) )
			return wp_delete_post( $id, true );
	}


	function inputs_initialize( $args = array() ) {
		static $already_done = 0;
		if($already_done != 0) {
			return;
		}
		$already_done++;

		$i = 1;
		foreach(indypress_input_get() as $field) {
			do_action('indypress_input_init_' . $field['type'], $field, $i);
			$i = $i + 1;
		}
	}
	function actions_initialize( $args = array() ) {
		static $already_done = 0;
		if($already_done != 0) {
			return;
		}
		$already_done++;

		foreach(indypress_actions_get() as $field) {
			do_action('indypress_action_init_' . $field['type'], $field);
		}
	}
}
/**
 * indypress_input_get : retrieve the list of fields for form $slug, or for the current page if $slug is null. At the moment $formpage is ignored
 * @param string $slug optional: the page we want to know the fields
 * @access public
 * @return array the list of fields
 */
function indypress_input_get( $slug=null ) {
	$fields = array();
	if( $slug === null)
		$slug = indypress_get_form_slug();
	$opt = json_decode(get_option('indypress_forms_' . $slug . '_fields'), TRUE/*array*/);
	if(is_array($opt))
		$fields = $opt;
	return apply_filters('indypress_input_fields', $fields);
}

/**
 * indypress_actions_get : retrieve the list of fields for form $slug, or for the current page if $slug is null. At the moment $slug is ignored
 * @param string $slug the page we want to know the fields
 * @access public
 * @return array the list of fields
 */
function indypress_actions_get( $slug=null ) {
	$actions = array();
	if( $slug === null)
		$slug = indypress_get_form_slug();
	$opt = json_decode(get_option('indypress_forms_' . $slug . '_actions'), TRUE/*array*/);
	if(is_array($opt))
		$actions = $opt;
	return apply_filters('indypress_input_actions', $actions);
}
?>
