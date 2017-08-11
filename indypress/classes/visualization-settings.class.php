<?php

	class Indypress_VisualizationSettings
	{
		//Settings related to visitors-output
		function Indypress_VisualizationSettings() {
			add_action( 'admin_menu', array( $this, 'menu' ) );
		}

		function menu() {
			add_submenu_page( 'indypress', 'IndyPress Visualization' . __( 'Settings' , 'indypress'), __('Visualization', 'indypress'), 'administrator', 'indypress_visualization_settings', array( $this, 'settings_page' ) );
			add_action( 'admin_init', array( $this, 'register_settings' ) );
		}

		function register_settings() {
			add_settings_section( 'indypress_visualization_theme', __('Visualization: theme', 'indypress'), array( $this, 'empty_section' ), 'indypress_visualization_settings' );
			register_setting( 'indypress_visualization', 'indypress_thumb_attachment' );
			add_settings_field( 'indypress_thumb_attachment', __('Show attached image as thumbnail', 'indypress'), array( $this, 'settings_thumb_attachment' ), 'indypress_visualization_settings', 'indypress_visualization_theme' );


			add_settings_section( 'indypress_visualization_comments',	__('Comment visualization ', 'indypress'), array( $this, 'empty_section' ), 'indypress_visualization_settings' );

			register_setting( 'indypress_visualization', 'indypress_promoted_in_content' );
			add_settings_field( 'indypress_promoted_in_content', __('Show promoted comments (It works whitout theme configuration, must be disabled if you want to configure your theme manually)', 'indypress'), array( $this, 'settings_promoted_in_content' ), 'indypress_visualization_settings', 'indypress_visualization_comments' );

			add_settings_section( 'indypress_visualization_seo', __('SEO', 'indypress'), array( $this, 'empty_section' ), 'indypress_visualization_settings' );
			register_setting( 'indypress_visualization', 'indypress_hidden_noindex' );
			add_settings_field( 'indypress_hidden_noindex', __('Do not allow to index hidden posts', 'indypress'), array( $this, 'settings_hidden_noindex' ), 'indypress_visualization_settings', 'indypress_visualization_seo' );
			register_setting( 'indypress_visualization', 'indypress_hidden_nofollow' );
			add_settings_field( 'indypress_hidden_nofollow', __('Do not allow to follow links from hidden posts', 'indypress'), array( $this, 'settings_hidden_nofollow' ), 'indypress_visualization_settings', 'indypress_visualization_seo' );
			register_setting( 'indypress_visualization', 'indypress_premoderate_noindex' );
			add_settings_field( 'indypress_premoderate_noindex', __('Do not allow to index premoderate posts', 'indypress'), array( $this, 'settings_premoderate_noindex' ), 'indypress_visualization_settings', 'indypress_visualization_seo' );
			register_setting( 'indypress_visualization', 'indypress_premoderate_nofollow' );
			add_settings_field( 'indypress_premoderate_nofollow', __('Do not allow to follow links from premoderate posts', 'indypress'), array( $this, 'settings_premoderate_nofollow' ), 'indypress_visualization_settings', 'indypress_visualization_seo' );


			add_settings_section( 'indypress_visualization_admin', __('Admin actions', 'indypress'), array( $this, 'empty_section' ), 'indypress_visualization_settings' );
			register_setting( 'indypress_visualization', 'indypress_ajax_status' );
			add_settings_field( 'indypress_ajax_status', __('Make hide/promote actions AJAX', 'indypress'), array( $this, 'settings_ajax_status' ), 'indypress_visualization_settings', 'indypress_visualization_admin' );
		}

		function empty_section() {
		}

	function setting_boolean( $option ) {
?>
	<input type="checkbox" name="<?php echo $option; ?>" <?php if( get_option( $option ) ) echo ' checked="checked"'; ?>">
<?php
	}
	function settings_ajax_status() {
		$this->setting_boolean( 'indypress_ajax_status' );
	}
	function settings_thumb_attachment() {
		$this->setting_boolean( 'indypress_thumb_attachment' );
	}
	function settings_premoderate_noindex() {
		$this->setting_boolean( 'indypress_premoderate_noindex' );
	}
	function settings_premoderate_nofollow() {
		$this->setting_boolean( 'indypress_premoderate_nofollow' );
	}
	function settings_hidden_noindex() {
		$this->setting_boolean( 'indypress_hidden_noindex' );
	}
	function settings_hidden_nofollow() {
		$this->setting_boolean( 'indypress_hidden_nofollow' );
	}
	function settings_promoted_in_content() {
		$this->setting_boolean( 'indypress_promoted_in_content' );
	}
	function settings_page() {
			global $wpdb;

			if ( !current_user_can( 'administrator' ) )
				wp_die( __( 'You do not have sufficient permissions to access this page.' , 'indypress') );

			?>
			<div class="wrap">

				<h2>IndyPress Settings: Visualization</h2>

				<form name="indypress_form" method="post" action="options.php">

					<?php settings_fields( 'indypress_visualization' ); ?>
					<?php do_settings_sections( 'indypress_visualization_settings' ); ?>
					<p class="submit">
						<input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes' , 'indypress'); ?>" />
					</p>
				</form>
			</div>
	<?php
			return;
	}

	}
?>
