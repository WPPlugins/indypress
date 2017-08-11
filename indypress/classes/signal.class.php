<?php

class indypress_signal {

	// HOOK TO LOAD THIS CLASS
	function indypress_signal() {
		global $indypress_path;

		// REQUIRE API
		require_once( $indypress_path . '/api/signal.php' );

		// REGISTER SIGNAL POST STATUS
		add_action( 'init', array( $this, 'register_signal_post_status' ) );

		// LOAD CSS
		add_filter( 'the_posts', array( $this, 'scripts_and_styles' ) );

		// LOAD JS
		add_action( 'wp_head', array( $this , 'js' ) );

		// LOAD FORMS
		add_action( 'indypress_signal_post', array( $this, 'signal_post') );
		add_action( 'indypress_signal_comment', array( $this, 'signal_comment') );

		// REPLACE COMMENTS TEMPLATE
//		add_filter( "comments_template", "repalce_comments_template" );

	}
/*
	function replace_comments_template() {
		return TEMPLATEPATH . '/indypress-comments.php';
	}
*/
	function register_signal_post_status() {

		register_post_status( 'signal', array(
			'label'       => _x( 'Signalled', 'post' , 'indypress'),
			'public'      => true,
			'show_in_admin_status_list' => true,
			'label_count' => _n_noop( 'Signalled <span class="count">(%s)</span>', 'Signalled <span class="count">(%s)</span>' , 'indypress'),

		) );

	}

	function scripts_and_styles( $posts ) {
		global $indypress_relative_path;
		wp_register_style( 'indypress_signal', $indypress_relative_path . 'css/signal.css' );
		wp_enqueue_style( 'indypress_signal' );
		return $posts;
	}

	function js( ) {

		$return = '
		<script type="text/javascript">
			//<![CDATA[
			function indypress_signal(id) {
				var signal = document.getElementById(id);
				if(signal.style.display == "block") {
					signal.style.display = "none";
				} else {
					signal.style.display = "block";
				}
			}
			//]]>
		</script>';

		echo $return;
	}

	function signal_post() {
		global $post;

		// CHECKING THE FORM
		if( $_POST["email"]==NULL && $_POST["indypress_signal_type"]=="post" && is_numeric( $_POST["indypress_signal"] ) ) {

			// UPDATING DATABASE
			$signal_number = get_post_meta( $post->ID, 'indypress_signal', TRUE );
			$new_signal_number = $signal_number+1;
			
			if( get_post_status( $post ->ID ) == 'publish' )
				wp_update_post( array( 'ID' => $post->ID, 'post_status' => 'signal' ) );

			if( $signal_number )
				if( ( get_post_status( $post->ID ) != 'hide' ) && update_post_meta( $post->ID, 'indypress_signal', "$new_signal_number" ) )
					echo '<div id="indypress_signal" class="indypress_signal">' . __( 'You marked this post as out of policy. Thanks for your contribute!', 'indypress' ) . '</div>';
				else
					echo '<div id="indypress_signal" class="indypress_signal">' . __( 'It has been impossible to mark this post.', 'indypress' ) . '</div>';
			elseif( ( get_post_status( $post->ID ) != 'hide' ) && add_post_meta( $post->ID, 'indypress_signal', '1', FALSE ) )
				echo '<div id="indypress_signal" class="indypress_signal">' . __( 'You marked this post as out of policy. Thanks for your contribute!', 'indypress' ) . '</div>';
			else
				echo '<div id="indypress_signal" class="indypress_signal">' . __( 'It has been not possible to mark this post.', 'indypress' ) . '</div>';
		} else {
			if( get_post_status( $post ->ID ) == 'publish' )
				echo $this->form( $post->ID );
		}

	}

	function signal_comment() {
		global $comment;

		// CHECKING THE FORM
		if( $_POST["email"]==NULL && $_POST["indypress_signal_type"]=='comment' && is_numeric( $_POST["indypress_signal"] ) && $comment->comment_ID == $_POST["indypress_signal"] ) {

			// UPDATING DATABASE
			$signal_number = get_comment_meta( $comment->comment_ID, 'indypress_signal', TRUE );
			$new_signal_number = $signal_number+1;
			if( $signal_number )
				if( update_comment_meta( $comment->comment_ID, 'indypress_signal', "$new_signal_number" ) )
					echo '<div id="indypress_signal">' . __( 'You marked this post as out of policy. Thanks for your contribute!', 'indypress' ) . '</div>';
				else
					echo '<div id="indypress_signal">' . __( 'It has been impossible to mark this post.', 'indypress' ) . '</div>';
			elseif( add_comment_meta( $comment->comment_ID, 'indypress_signal', '1', FALSE ) )
				echo '<div id="indypress_signal">' . __( 'You marked this post as out of policy. Thanks for your contribute!', 'indypress' ) . '</div>';
			else
				echo '<div id="indypress_signal">' . __( 'It has been impossible to mark this post.', 'indypress' ) . '</div>';
		} else {
			echo $this->form( $comment->comment_ID , 'comment' );
		}

	}

	function form( $id, $type='post' ) {
		global $post;

		$return .= ' | <a href="javascript:void(0)" onclick="javascript:indypress_signal(\'indypress_signal_' . $type . '_' . $id . '\')">' . __( 'Report as out of policy', 'indypress' ) . '</a>
		<div id="indypress_signal_' . $type . '_' . $id . '" class="nodisplay indypress_signal">
		<p>' . __( 'If you think this post is out of policy or spam, mark il pushing on "Mark post!" key.', 'indypress' ) . '</p>
		<form action="' . $_SERVER['REQUEST_URI'] . '#indypress_signal" method="post">
			<label class="nodisplay" for="email">' . __( 'Antispam.. leave as is!', 'indypress' ) . '</label><input class="nodisplay" type="text" name="email">
			<input type="hidden" name="indypress_signal" value="' .  $id . '">
			<input type="hidden" name="indypress_signal_type" value="' . esc_attr($type) . '">
			<input type="submit" value="' . __( 'Mark post!', 'indypress' ) . '">
		</form>
		</div>';
		
		return $return;
	}

}

?>
