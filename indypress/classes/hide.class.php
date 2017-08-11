<?php

class indypress_hide {

	function indypress_hide() {
		global $indypress_path;

		// REQUIRE API
		require_once( $indypress_path . '/api/hide.php' );
		
		// ADD LINK TO HIDE POST INTO THEME
		add_filter( 'edit_post_link', array( $this, 'hide_post_link' ) );
		// and in adminbar
		add_action( 'admin_bar_menu', array( $this, 'admin_bar_hide' ), 91 ); //priority determines position

		// ADD "nofollow" TO HIDDEN/PREMODERATED POSTS
		add_action( 'wp_head', array( $this, 'nofollow' ) );
		// ADD HIDE/PROMOTE LINK TO COMMENTS
		add_filter( 'comment_reply_link', array( $this, 'status_link' ) );
		// SET COMMENTS NUMBER
		add_filter( 'get_comments_number', array( $this, 'get_no_hidden_comment_count' ) );

		// SHOW UNPUBLISHED SINGLE POSTS
		add_filter( 'the_posts', array( $this, 'show_premoderate' ) );
		// show promoted comments inside posts
		add_filter( 'the_content', array( $this, 'show_promoted_comments' ) );
		// add ajax-hiding js
		add_action( 'wp_enqueue_scripts', array( $this, 'ajax_js' ) );

		// block comments on hidden posts
		add_action( 'comments_open', array( &$this,'close_comment'), 10, 2 );
	}

	function ajax_js() {
		// Current javascript can safely provided to non-admin, too. It's just useless
		if( !is_user_logged_in() )
			return;
		global $indypress_url;
		wp_enqueue_script( 'hide-ajax', $indypress_url . '/includes/hide_ajax.js', array( 'jquery' ) );
		wp_localize_script( 'hide-ajax', 'args', array(	
			'post_nonce' => wp_create_nonce('indypress-change-post-status'),
			'comment_nonce' => wp_create_nonce('indypress-change-comment-status'),
			'url' => admin_url( 'admin-ajax.php' ) ) );
	}

	function nofollow( $all_posts ) {
		global $wp_query;

		if( '0' == get_option( 'blog_public' ) )
			return;
		if( ( is_hidden_post() && get_option( 'indypress_hidden_noindex' ) ) || 
			( is_premoderate_post() && get_option( 'indypress_premoderate_noindex' ) ) )
			$values[] = 'noindex';
		if( ( is_hidden_post() && get_option( 'indypress_hidden_nofollow' ) ) || 
			( is_premoderate_post() && get_option( 'indypress_premoderate_nofollow' ) ) )
			$values[] = 'nofollow';
		if( $values )
			echo '<meta name="robots" content="' . implode( ',', $values )  . '" />';
	}

	function show_premoderate( $all_posts ) {
		/* it's usually impossible to view a single, non-published post.
			This filter the_posts to solve this */
		global $wpdb;
		global $wp_query;
		if( empty( $all_posts ) && !is_page() && $wp_query->query_vars['name'] ) {
			$query = $wpdb->prepare( "
				SELECT wposts.* FROM $wpdb->posts wposts, $wpdb->postmeta wpostmeta
				WHERE wposts.post_name = '%s'
				LIMIT 1
				", $wp_query->query_vars['name'] );

			$results = $wpdb->get_results( $query );
			//getting $results someway
			if( !empty( $results ) )
				$all_posts = $results;
		}
		return $all_posts;
	}

	function hide_post_link( $link ) {
		//TODO: finer permission handling
		global $post;
		require_once( 'hide_admin.class.php' );
		require_once( 'hide_common.class.php' );
		$i = $post->ID;
		extract( get_post_hide_links( $i ) );
			
		if(get_option( 'indypress_ajax_status' ) ) {
			if( $post->post_status == 'hide' )
				$link .= "<span id=\"post-hide-$i\" class=\"post-hide\">$normal</span>";
			elseif( $post->post_status == 'premoderate')
				$link .= "<span id=\"post-hide-$i\" class=\"post-hide\">$normal | $hide</span>";
			else
				$link .= "<span id=\"post-hide-$i\" class=\"post-hide\">$hide</span>";
			return $link;
		}
		if( $post->post_status == 'hide' && indy_can_change_post_status( $post->ID, 'normal_post' ) )
			$link .= ' | <a href="' . indypress_get_hide_post_href( $post->ID, 'normal' ) . '&indypress_header=' . $_SERVER['REQUEST_URI'] . '" title="' . __('UnHide this post', 'indypress') . '">' . __('UnHide', 'indypress') . '</a>';		
		else{
			if ( indy_can_change_post_status( $post->ID, 'hide' ) )
				$link .= ' | <a href="' . indypress_get_hide_post_href( $post->ID, 'hide' ) . '&indypress_header=' . $_SERVER['REQUEST_URI'] . '" title="' . __('Hide this post', 'indypress') . '">' . __('Hide', 'indypress') . '</a>';	
			if( $post->post_status == 'premoderate' && indy_can_change_post_status( $post->ID, 'publish' ) ) 
				$link .= ' | <a href="' . indypress_get_hide_post_href( $post->ID, 'normal' ) . '&indypress_header=' . $_SERVER['REQUEST_URI'] . '" title="' . __('Promote this post', 'indypress') . '">' . __('Promote', 'indypress') . '</a>';		
		}

		return $link;
	}

	function admin_bar_hide() {
		if ( !is_single() )
			return;

		global $post;
		if( !indy_can_change_post_status( $post->ID, 'hide' ) )
			return;

		global $wp_admin_bar;
		if( 'hide' != $post->post_status )
			$wp_admin_bar->add_menu( array(
				'id' => 'indy_hide',
				'title' => 'Hide',
				'href' => indypress_get_hide_post_href( $post->ID, 'hide' ) . '&indypress_header=' . $_SERVER['REQUEST_URI'] . '" title="Hide this post">Hide'
			) );
		if( 'publish' != $post->post_status )
			$wp_admin_bar->add_menu( array(
				'id' => 'indy_promote',
				'title' => 'Promote',
				'href' => indypress_get_hide_post_href( $post->ID, 'normal' ) . '&indypress_header=' . $_SERVER['REQUEST_URI'] . '" title="Promote this post">Promote'
			) );
		if( 'premoderate' != $post->post_status )
			$wp_admin_bar->add_menu( array(
				'id' => 'indy_premoderate',
				'title' => 'Premoderate',
				'href' => indypress_get_hide_post_href( $post->ID, 'premoderate' ) . '&indypress_header=' . $_SERVER['REQUEST_URI'] . '" title="Premoderate this post">Premoderate'
			) );

	}

	// HIDE/PROMOTE COMMENT
	function status_link($x) {
		$p = get_the_ID();
		$c = get_comment_ID();
		if ( ( current_user_can( 'moderate_comments' ) ) ) {
			$type = get_comment_type();
			require_once('hide_common.class.php');
			$links = get_comment_hide_links();
			extract( $links );
			if ( 'comment' == $type )
				return $x . " | <span id=\"comment-actions-$c\" class=\"comment-actions\">$hide | $links->promote</span>";
			else if ( 'hidden' == $type )
				return $x . " | <span id=\"comment-actions-$c\" class=\"comment-actions\">$normal</span>";
			else if ( 'promoted' == $type ) 
				return $x . " | <span id=\"comment-actions-$c\" class=\"comment-actions\">$normal</span>";
		} else
			return $x;
	}

	function get_no_hidden_comment_count($x) {
		$p = get_the_ID();
		$h = $this->get_hidden_comment_count();
		if ( current_user_can( 'moderate_comments' ) )
			return $x;
		else 
			return $x-$h;
	}
	
	function get_hidden_comment_count() {
		global $wpdb;
		global $post;

		$p = $post->ID;
		$where = '';
		if ( $p > 0 ) 
			$where = $wpdb->prepare("WHERE comment_post_ID = %d AND comment_type = 'hidden'", $p);			
			$totals = (array) $wpdb->get_results("
				SELECT comment_approved, COUNT( * ) AS hidden
				FROM {$wpdb->comments}
				{$where}
				GROUP BY comment_approved"
				, ARRAY_A);
		if(isset($totals[0]))
			return $totals[0]['hidden'];
	}

	function show_promoted_comments( $content ) {
		global $wp_query;

		if( !get_option( 'indypress_promoted_in_content' ) )
			return $content;
		if( !is_single() )
			return $content;
		$page_id = $wp_query->post->ID;
		$comments = get_comments( array(
			'post_id' => $page_id,
			'type' => 'promoted',
			'order' => 'ASC' //oldest to newest
		) );
		$comment_text = '';
		foreach( $comments as $c ) {
			$comment_text .= '<li class="promoted-contribute">' .  wpautop($c->comment_content) . '</li>';
		}
		if( '' !== $comment_text )
			$comment_text = '<h3>' . __('Contributi:', 'indypress') . '</h3><ul id="promoted-contributes-list">' . $comment_text . '</ul>';
		return $content . $comment_text;
	}

	function close_comment( $open, $post_id ) {
		if( ! $open )
			return $open;
		$status = get_post_status($post_id);
		if( 'hide' == $status )
			return false;
		else
			return $open;
	}
}

