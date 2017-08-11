<?php
/*
	This module allows integration with LiveBlogging plugin:
	http://wordpress.org/extend/plugins/live-blogging/
*/
function sanitize_is_array( $input ) {
	if( !is_array( $input ) )
		return array();
	return $input;
}

class indypress_liveblogging_settings {

	// HOOK TO LOAD THIS CLASS
	function indypress_liveblogging_settings() {
		add_action( 'admin_menu', array( $this, 'menu' ) );
	}

	function menu() {
		add_submenu_page( 'indypress', 'IndyPress LiveBlogging' . __('Settings', 'indypress'), 'LiveBlogging', 'administrator', 'indypress_liveblogging_settings', array( $this, 'settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	function register_settings() {
		register_setting( 'indypress_liveblogging', 'indypress_active_liveblogs', 'sanitize_is_array' );
		register_setting( 'indypress_liveblogging', 'indypress_premoderate_liveblogs', 'sanitize_is_array' );
		add_settings_section( 'indypress_liveblogging_main', __('Main', 'indypress'), array( $this, 'main_section' ), 'indypress_liveblogging_settings' );
		add_settings_field( 'indypress_active_liveblogs', __('Active Liveblogs', 'indypress'), array( $this, 'setting_active_liveblogs' ), 'indypress_liveblogging_settings', 'indypress_liveblogging_main' );
		add_settings_field( 'indypress_premoderate_liveblogs', __('Liveblogs on which publication is premoderated', 'indypress'), array( $this, 'setting_premoderate_liveblogs' ), 'indypress_liveblogging_settings', 'indypress_liveblogging_main' );
	}

	function setting_active_liveblogs() {
?>
					<select name="indypress_active_liveblogs[]" multiple="multiple" style="height:150px; width:150px;">
							<?php

						$rows = get_terms( 'liveblog' );
						foreach( $rows as $row ) {
							?>
						<option value="<?php echo $row->term_id; ?>"<?php if( in_array( $row->term_id, $this->active_liveblogs ) ) echo ' selected="selected"'; ?>><?php echo get_the_title( $row->slug ); ?></option>
							<?php
						}
						?>
					</select>
<?php
	}
	function setting_premoderate_liveblogs() {
?>
					<select name="indypress_premoderate_liveblogs[]" multiple="multiple" style="height:150px; width:150px;">
							<?php
						$rows = get_terms( 'liveblog');
						foreach( $rows as $row ) {
							?>
						<option value="<?php echo $row->term_id; ?>"<?php if( in_array( $row->term_id, $this->premoderate_liveblogs ) ) echo ' selected="selected"'; ?>><?php echo get_the_title( $row->slug ); ?></option>
							<?php
						}
						?>
					</select>
<?php
	}

	function load_settings() {
		$this->active_liveblogs = sanitize_is_array( get_option( 'indypress_active_liveblogs' ) );
		$this->premoderate_liveblogs = sanitize_is_array( get_option( 'indypress_premoderate_liveblogs' ) );
	}

	function main_section() {
	}
	function settings_page() {
		if ( !current_user_can( 'administrator' ) )
			wp_die( __( 'You do not have sufficient permissions to access this page.' , 'indypress') );
		$this->load_settings();
		?>
	<div class="wrap">
		<div class="icon32" id="icon-options-general"><br></div>
		<h2>IndyPress: LiveBlogging settings</h2>
		Some optional text here explaining the overall purpose of the options and what they relate to etc.
		<form action="options.php" method="post">
		<?php settings_fields( 'indypress_liveblogging' ); ?>
		<?php do_settings_sections( 'indypress_liveblogging_settings' ); ?>
		<p class="submit">
			<input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes', 'indypress'); ?>" />
		</p>
		</form>
	</div>
<?php
	}
}

?>
