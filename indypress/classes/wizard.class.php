<?php
	/* This handles automatic creation of user/page on plugin activation (unless the user already has some selected)
		It is not the typical UI wizard: it's invisible!
		DOES:
			* user generation
				- random password
			* set user for indypress
			* page creation
				- text with "open publishing is temporarily disabled" and translation
			* set page for indypress
		WILL DO:
			* user generation
				- "empty" role
	 */


	/**
	 * indypress_wizard 
	 * 
	 * should be instantiated on register_activation_hook
	 * @author boyska <piuttosto@logorroici.org>
	 */
	class indypress_wizard {
		function indypress_wizard() {
			if( !is_int( get_option('indypress_author') ) )
				$this->create_user();
			if( !is_int( get_option('indypress_publication_page') ) )
				$this->create_page();
			update_option( 'indypress_enable_publication' , true );
		}
		function create_role() {
			//TODO: create role
		}
		function create_user() {
			$this->create_role();
			$pass = '';
			for ($i = 0; $i < 20; $i++) {
					$pass .= chr(rand(32, 126));
			} //generate a strong password
			//TODO: add with wp_insert_user, so role and nicename can be specified
			$user_id = wp_create_user('anonymous', $pass, 'doesnot@exists.urg');
			if(!is_int($user_id)) //Error: user already exist
				return;
			update_option( 'indypress_author', $user_id );
		}
		function create_page() {
			global $wpdb;
			$post_id = $wpdb->get_var(
				//the join is needed just to check for deleted pages with meta still in db
				"SELECT p.ID FROM
					$wpdb->postmeta m , $wpdb->posts p
				WHERE
					meta_key = 'indypress_publication_page'
					AND p.ID = m.post_id
				LIMIT 1
				");
			if($post_id === null) {
				//use wp_insert_post
				$post = array();
				$post['post_title'] = 'Publish';
				$post['post_author'] = get_option('indypress_author');
				$post['post_type'] = 'page';
				$post['post_name'] = 'publish';
				$post['post_status'] = 'publish';
				$post['post_content'] = 'Open publishing is temporarily disabled';

				$post_id = wp_insert_post($post);
				if( $post_id == 0 )
					return;
				add_post_meta( $post_id, 'indypress_publication_page', true, true );
			}
			update_option( 'indypress_publication_page', $post_id );
		}
	}

