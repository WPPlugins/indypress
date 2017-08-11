<?php

class indypress_signal_admin {

	// HOOK TO LOAD THIS CLASS
	function indypress_signal_admin() {

		// REGISTER HIDE POST STATUS
		add_action( 'init', array( $this, 'register_signal_post_status' ) );

		// ADD LINK TO POSTS LIST
		add_filter( 'post_row_actions', array( $this, 'post_link_admin' ), 10, 2 );

		// ADD ACTION TO HIDE/UNHIDE A POST
		add_action( 'init', array( $this, 'unsignal_post' ) );

		// ADD ACTION TO HIDE/UNHIDE A POST
		add_action( 'init', array( $this, 'unsignal_comment' ) );

		// ADD COMMENTS PAGE
		add_action( 'admin_menu', array( $this, 'menu' ) );

	}

	function menu() {
		add_comments_page( __( 'IndyPress signalled comments', 'indypress' ), __( 'Signalled as out of policy', 'indypress' ), 'administrator', 'indypress_signal_comment', array( $this, 'signal_page_comment' ) );
	}

	// POSTS
	function register_signal_post_status() {

		register_post_status( 'signal', array(
			'label'       => _x( 'Signalled and published', 'post' , 'indypress'),
			'public'      => true,
			'show_in_admin_status_list' => true,
			'label_count' => _n_noop( 'Signalled and published <span class="count">(%s)</span>', 'Signalled and published <span class="count">(%s)</span>' , 'indypress'),
		) );

	}

	function post_link_admin($actions) {
		global $post;
		
		if( $post->post_status == 'signal' )
			$actions['unsignal'] = '<a href="edit.php?action=unsignal_post&id=' . $post->ID . '" title="' . __('Ignore report', 'indypress' ) . '">' . __( 'Ignore report', 'indypress' ) . '</a>';
		
		return $actions;
	}

	function hide_post() {
		if( isset( $_GET['action'] ) && is_numeric( $_GET['id'] ) ) {
			global $wpdb;
			
			if( $_GET['action'] == 'hide_post' )
				$type = 'hide';
			else if( $_GET['action'] == 'unhide_post' )
				$type = 'publish';
			
			$pid = $_GET['id'];
			$query = "UPDATE $wpdb->posts SET post_status = '$type' WHERE ID = $pid LIMIT 1";
			if ( $wpdb->query( $query ) )
				return '<div id="message" class="updated below-h2"><p>' . __( 'Article setted as ', 'indypress' ) . $type . '</p></div>';
			else
				return '<div id="message" class="updated below-h2"><p>' . __( 'Article hidden', 'indypress' ) . '</p></div>';
		}
	}

	function unsignal_post( $id ) {
		if( isset( $_GET['action'] ) && isset( $_GET['id'] ) && is_numeric( $_GET['id'] ) ) {
			global $wpdb;

			if( $_GET['action'] == 'unsignal_post' ) {
				delete_post_meta( $_GET['id'], 'indypress_signal' );
				wp_update_post( array( 'ID' => $_GET['id'], 'post_status' => 'publish' ) );
			}
		}
	}

	function unsignal_comment( $id ) {
		if( isset( $_GET['action'] ) && isset( $_GET['id'] ) && is_numeric( $_GET['id'] ) ) {
			global $wpdb;

			if( $_GET['action'] == 'unsignal_comment' ) {
				delete_comment_meta( $_GET['id'], 'indypress_signal' );
			}
		}
	}

	// SHOW ALL SIGNALED COMMENT
	function signal_page_comment() {
		global $wpdb;

		require_once( 'pagination.class.php' );
		$pagination = new pagination();

		if ( !current_user_can( 'administrator' ) )
			wp_die( __( 'You do not have sufficient permissions to access this page.' , 'indypress' ) );

		$query = "SELECT * FROM $wpdb->comments AS p INNER JOIN $wpdb->commentmeta as pm ON p.comment_ID=pm.comment_id WHERE pm.meta_key='indypress_signal' AND p.comment_type!='hidden' ORDER BY comment_date DESC";
		$query2 = $query . ' LIMIT ' . ($pagination->per_page * ( $pagination->page_num - 1 )) . ", " . $pagination->per_page;

		?>
		<div class="wrap">
			<h2><?php _e( 'Comments reported as out of policy' ) ?></h2>
			
			<div class="tablenav">
				<div class="tablenav-pages">
					<?php $pagination->paging( $query, 'edit-comments.php', 'indypress_signal_comment' ) ; ?>
				</div>
			</div>
			<table class="widefat">
			<thead>
				<tr>
					<th><?php _e( 'Author', 'indypress' ); ?></th>
					<th><?php _e( 'Comment', 'indypress' ); ?></th>
					<th><?php _e( 'Report', 'indypress' ); ?></th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th><?php _e( 'Author', 'indypress' ); ?></th>
					<th><?php _e( 'Comment', 'indypress' ); ?></th>
					<th><?php _e( 'Report', 'indypress' ); ?></th>
				</tr>
			</tfoot>
			<tbody>
				<?php

				$results = $wpdb->get_results( $query2 );
				if( $results ) {
					foreach( $results as $result ) {
						?>
				<tr>
						<td>
						<?php if( $result->comment_author ) echo $result->comment_author; else _e("Anonymous", 'indypress'); ?>
					</td>
					<td>
					<?php
					
					$ptime = date('G', strtotime( $result->comment_date ) );
					if ( ( abs(time() - $ptime) ) < 86400 )
						$ptime = sprintf( __( '%s ago', 'indypress' ), human_time_diff( $ptime ) );
					else
						$ptime = mysql2date( __( 'Y/m/d \a\t g:i A', 'indypress' ), $result->comment_date );
					?>
						<a href="<?php echo get_permalink( $result->comment_post_ID ); ?>"><?php echo $ptime; ?></a><br /><?php echo $result->comment_content; ?>
						<div class="row-actions">
							<span class="edit"><a href="edit-comments.php?page=indypress_hide_comments&action=hide_comment&id=<?php echo $result->comment_ID ?>" title="' . __('Hide comment') . '">' . __('Hide') . '</a></span> |
							<span class="edit"><a href="edit-comments.php?page=indypress_signal_comment&action=unsignal_comment&id=<?php echo $result->comment_ID ?>" title="' . __('Ignore reports') . '">' . __('Ignore reports') . '</a></span>
						</div>
					</td>
					<td><?php echo $result->meta_value; ?></td>
				</tr>
						<?php
					}
				} else {
					?>
				<tr>
					<td colspan=2><?php _e('There are no comments signalled as out of policy.') ?></td>
				</tr>
					<?php
				}
				?>
			</tbody>
			</table>
		</div>
		<?php
	}

}

?>
