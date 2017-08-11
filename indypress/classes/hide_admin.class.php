<?php
/* ***
	This module deals with both 'hide' and 'premoderate' statuses
 * ***/

function indy_can_change_post_status( $post_id, $target ) {
		//TODO: better permission handling
		return current_user_can( 'administrator' ) || current_user_can('indypress_change_status');
}
function indy_can_change_comment_status( $post_id, $target ) {
		//TODO: better permission handling
		return current_user_can( 'moderate_comments' );
}
class indypress_hide_admin {

	// HOOK TO LOAD THIS CLASS
	function indypress_hide_admin() {

		// REGISTER HIDE POST STATUS
		add_action( 'init', array( &$this, 'register_hide_post_status' ) );
		// add ajax-hiding js
		add_action( 'init', array( &$this, 'ajax_js' ) );

		// ADD LINK TO POSTS LIST
		add_filter( 'post_row_actions', array( $this, 'post_link_admin' ), 10, 2 );

		// ADD LINK TO COMMENTS LIST
		add_filter( 'comment_row_actions', array( $this, 'hide_comment_link_admin' ), 10, 2 );

		// ADD ACTION TO HIDE/UNHIDE POSTS AND COMMENTS
		add_action( 'admin_init', array( $this, 'get_signal' ) );

		// ADD HIDE COMMENTS PAGE
		add_action( 'admin_menu', array( $this, 'menu' ) );

		// Manage AJAX
		add_action( 'wp_ajax_change_post_status', array( $this, 'ajax_hide_post' ) );
		add_action( 'wp_ajax_change_comment_status', array( $this, 'ajax_hide_comment' ) );

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

	function register_hide_post_status() {

		register_post_status( 'hide', array(
			'label'       => _x( 'Nascosto', 'post' , 'indypress'),
			'public'      => false,
			'private'      => true, //if this is not given, commenting will fail
			'show_in_admin_status_list' => true,
			'label_count' => _n_noop( 'Nascosti <span class="count">(%s)</span>', 'Nascosti <span class="count">(%s)</span>' , 'indypress'),
			'exclude_from_search' => true,
		) );

		register_post_status('premoderate', array(
			'label'       => _x('Premoderate', 'post', 'indypress'),
			'public'      => false,
			'private'      => true, //if this is not given, commenting will fail
			'show_in_admin_status_list' => true,
			'label_count' => _n_noop('Premoderate <span class="count">(%s)</span>', 'Premoderate <span class="count">(%s)</span>', 'indypress'),
			'exclude_from_search' => true,
		) );

	}

	function init_hide() {
		$current_user = wp_get_current_user();
		if( $current_user->user_level >=7 && isset( $_GET['indypress_header'] ) ) {
			$this->hide();
			header("location: " . $_GET['indypress_header'] . "#comment-" . $_GET['id']);
		}
	}

	function get_signal() {
		if( !isset( $_GET['action'] ) )
			return;
		if( $_GET['action'] == 'approve_post' ) {
			$this->hide_post();
			if( isset( $_GET['indypress_header'] ) )
				header( 'Location: ' . $_GET['indypress_header'] );
		}
	}
		function ajax_hide_post() {
			if( !wp_verify_nonce( $_REQUEST['nonce'], 'indypress-change-post-status' ) || !indy_can_change_post_status( $_POST['post_id'], $_POST['status'] ) )  {
				header( 'HTTP/1.1 401 Unauthorized' );
				die();
			}
			if( !is_numeric($_REQUEST['post_id']) ) {
				header( 'HTTP/1.1 400 Bad Request' );
				die();
			}
			if( $_REQUEST['status'] == 'hide' || $_REQUEST['status'] == 'normal' || $_REQUEST['status'] == 'premoderate' )
				$status = $_REQUEST['status'];
			else {
				header( 'HTTP/1.1 400 Bad Request' );
				die();
			}
			if( $this->hide_post( $_REQUEST['post_id'], $status ) == -1 )
				header( 'HTTP/1.1 400 Bad Request' );
			else {
			  echo json_encode(array(0, 'OK'));
			  if( isset( $_REQUEST['indypress_header'] ) )
				header( 'Location: ' . $_REQUEST['indypress_header'] );
			}
			die();
		}
		function ajax_hide_comment() {
			if( !wp_verify_nonce( $_REQUEST['nonce'], 'indypress-change-comment-status' ) || ! indy_can_change_comment_status( $_POST['comment_id'], $_POST['status'] ) )  {
				header( 'HTTP/1.1 401 Unauthorized' );
				die();
			}
			if( $_REQUEST['status'] == 'hidden' )
				$action = 'hide_comment';
			else if( $_REQUEST['status'] == 'normal' )
				$action = 'unhide_comment';
			else if( $_REQUEST['status'] == 'promoted' )
				$action = 'promote_comment'; 
			else {
				header( 'HTTP/1.1 400 Bad Request' );
				echo json_encode(array(-1, 'not valid action "' . $_REQUEST['status'] . '"'));
				die();
			}

			if( $this->hide_comment( $_REQUEST['comment_id'], $action ) == -1 ) {
				header( 'HTTP/1.1 400 Bad Request' );
				echo json_encode(array(-1, 'hiding errors'));
			}
			else {
				echo json_encode(array(0, 'OK'));
				if( isset( $_REQUEST['indypress_header'] ) )
				  header( 'Location: ' . $_REQUEST['indypress_header'] . '#comment-' . $_GET['id'] );
			}
			die();
		}

		function post_link_admin($actions) {
			global $post;
			
			$i = $post->ID;
			require_once('hide_common.class.php');
			extract( get_post_hide_links( $i ) );
			if( $post->post_status == 'hide' )
				$actions["indypresshide"] = "<span id=\"post-hide-$i\" class=\"post-hide\">$normal</span>";
			elseif( $post->post_status == 'premoderate')
				$actions["indypresshide"] = "<span id=\"post-hide-$i\" class=\"post-hide\">$normal | $hide</span>";
			else
				$actions["indypresshide"] = "<span id=\"post-hide-$i\" class=\"post-hide\">$hide</span>";
			
			return $actions;
		}

		function hide_post( $post_id=-1, $action=-1) {
			if( $post_id === -1 )
				$post_id = $_GET['id'];
			if( $action === -1 )
				$action = $_GET['action'];
			if( isset( $action ) && is_numeric( $post_id ) ) {
				global $wpdb;
				
				if( 'normal' == $action )
					$type = 'publish';
				elseif( $action == 'hide' || $action == 'premoderate' )
					$type = $action;
				else
					return '-1';
				
				$query = "UPDATE $wpdb->posts SET post_status = '$type' WHERE ID = $post_id LIMIT 1";
				$res = $wpdb->query( $query );
	 
				if ( $res === FALSE )
					return '-1'; //errors
				if( $res > 0 )
					return 0; //all as expected
				else
					return 1; //ok, but it was already hidden

			}
			return -1;
		}

	function menu() {
		add_comments_page( 'IndyPress Hidden comments', __('Hidden', 'indypress'), 'administrator', 'indypress_hide_comments', array( $this, 'hide_comments_page' ) );
	}

	// COMMENTS
	function hide_comment( $comment_id=-1, $action=-1) {			
		if( $comment_id === -1 )
			$comment_id = $_GET['id'];
		if( $action === -1 && isset( $_GET['action'] ) )
			$action = $_GET['action'];

		if ( $action >= 0 && is_numeric( $comment_id ) ) {
			global $wpdb;

			if ( $action == 'hide_comment' )
				$type = 'hidden';
			else if ( $action == 'unhide_comment' )
				$type = '';
			else if ( 'promote_comment' == $action )
				$type = 'promoted';

			$query = "UPDATE $wpdb->comments SET `comment_type` = '".$type."' WHERE `comment_ID` = $comment_id LIMIT 1";
		  $res = $wpdb->query( $query );

		  if ( $res === FALSE )
			  return '-1'; //errors
		  if( $res > 0 )
			  return 0; //all as expected
		  else
			  return 1; //ok, but it was already hidden
		}
		
	}
	
	function hide_comment_link_admin($actions) {
		global $comment;
		require_once('hide_common.class.php');
		$links = get_comment_hide_links();
		extract($links);
		
		$type = $comment->comment_type;
		$c = $comment->comment_ID;
		if ( 'comment' == $type || '' == $type )
			$actions['indypresshide'] = "<span id=\"comment-actions-$c\" class=\"comment-actions\">$hide | $promote</span>";
		else if ( 'hidden' == $type )
			$actions['indypresshide'] = "<span id=\"comment-actions-$c\" class=\"comment-actions\">$normal</span>";
		else if ( 'promoted' == $type ) 
			$actions['indypresshide'] = "<span id=\"comment-actions-$c\" class=\"comment-actions\">$normal</span>";
		else
			print_r("type is ($type)");
		return $actions;
	}
	
	function hide_comments_page() {
		global $wpdb;

		require_once( 'pagination.class.php' );
		$pagination = new pagination();
		
		if ( !current_user_can( 'moderate_comments' ) )
			wp_die( __( 'You do not have sufficient permissions to access this page.' , 'indypress') );

		$query = "SELECT * FROM $wpdb->comments WHERE comment_type='hidden' ORDER BY comment_date DESC";
		$query2 = $query . ' LIMIT ' . ($pagination->per_page * ( $pagination->page_num - 1 )) . ", " . $pagination->per_page;

		?>
		<div class="wrap">
			<h2>Hidden comments</h2>
			
			<div class="tablenav">
				<div class="tablenav-pages">
					<?php $pagination->paging( $query, 'edit-comments.php', 'indypress_hide_comments' ); ?>
				</div>
			</div>
			<table class="widefat">
			<thead>
				<tr>
					<th><?php _e("Author", 'indypress'); ?></th>
					<th><?php _e("Comment", 'indypress'); ?></th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th><?php _e("Author", 'indypress'); ?></th>
					<th><?php _e("Comment", 'indypress'); ?></th>
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
						$ptime = sprintf( __('%s ago', 'indypress'), human_time_diff( $ptime ) );
					else
						$ptime = mysql2date(__('Y/m/d \a\t g:i A', 'indypress'), $result->comment_date );
					?>
						<a href="<?php echo get_permalink( $result->comment_post_ID ); ?>" title="<?php echo $result->comment_ID; ?>"><?php echo $ptime; ?></a><br /><?php echo $result->comment_content; ?>
						<div class="row-actions">
							<span class="edit"><a href="edit-comments.php?page=indypress_hide_comments&action=unhide_comment&id=<?php echo $result->comment_ID; ?>">' . __('UnHide') . '</a></span>
						</div>
					</td>
				</tr>
						<?php
					}
				} else {
					?>
				<tr>
					<td colspan=2>There aren't hidden comments.</td>
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

