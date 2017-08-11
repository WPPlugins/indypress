<?php
	function get_comment_hide_links($postid=null, $commentid=null) {
		if( $postid === null )
			$postid = get_the_ID();
		if( $commentid === null )
			$commentid = get_comment_ID();
		if( !get_option('indypress_ajax_status') ) {
			$hide_link .= ' | <a href="' . admin_url( 'admin-ajax.php' ) . '?action=change_comment_status&status=hidden&comment_id=' . get_comment_ID() . '&nonce=' . wp_create_nonce('indypress-change-comment-status') . '&indypress_header=' . $_SERVER['REQUEST_URI'] . '">' . __('Hide', 'indypress') . '</a>';
			$unhide_link .= ' | <a href="' . admin_url( 'admin-ajax.php' ) . '?action=change_comment_status&status=normal&comment_id=' . get_comment_ID() . '&nonce=' . wp_create_nonce('indypress-change-comment-status') . '&indypress_header=' . $_SERVER['REQUEST_URI'] . '">' . __('Normal', 'indypress') . '</a>';		
			$promote_link .= ' | <a href="' . admin_url( 'admin-ajax.php' ) . '?action=change_comment_status&status=promoted&comment_id=' . get_comment_ID() . '&nonce=' . wp_create_nonce('indypress-change-comment-status') . '&indypress_header=' . $_SERVER['REQUEST_URI'] . '">' . __('promote', 'indypress') . '</a>';		
		}
		else {
			$hide_link = '<a class="hide-comment-ajax" comment_id="' . get_comment_ID() . '">' . __('Hide', 'indypress') . '</a>';
			$unhide_link = '<a class="normal-comment-ajax" comment_id="' . get_comment_ID() . '">' . __('Normal', 'indypress') . '</a>';
			$promote_link = '<a class="promoted-comment-ajax" comment_id="' . get_comment_ID() . '">' . __('Promote', 'indypress') . '</a>';
		}
		return array(
			'hide' => $hide_link,
			'normal' => $unhide_link,
			'promote' => $promote_link
		);
	}
	function get_post_hide_links($postid=null, $ajax=true) {
		if( $postid === null )
			$postid = get_the_ID();
		if( $ajax ) {
			$hide_link = '<a class="hide-post-ajax" post_id="' . $postid . '">' . __('Hide', 'indypress') . '</a>';
			$normal_link = '<a class="normal-post-ajax" post_id="' . $postid . '">' . __('Promote', 'indypress') . '</a>';
			$premoderate_link = '<a class="premoderate-post-ajax" post_id="' . $postid . '">' . __('Premoderate', 'indypress') . '</a>';
		}
		return array(
			'hide' => $hide_link,
			'normal' => $normal_link,
			'promote' => $premoderate_link
		);

	}

