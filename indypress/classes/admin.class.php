<?php

class indypress_admin {

	// HOOK TO LOAD THIS CLASS
	function indypress_admin() {
		add_action( 'admin_menu', array( $this, 'menu' ) );
		add_action( 'plugin_action_links', array( $this, 'pluginpage_link' ), 10, 2 );
		//add_action( 'init', array( $this, 'register_my_comment_type' ) );
	}

	function register_settings() {
		add_settings_section( 'indypress_admin_main', __('Main', 'indypress'), array( $this, 'main_section' ), 'indypress_admin_settings' );
	}

	function settings_page() {
		if ( !current_user_can( 'administrator' ) )
			wp_die( __( 'You do not have sufficient permissions to access this page.' , 'indypress') );
		?>
	<div class="wrap">
		<div class="icon32" id="icon-options-general"><br></div>
		<h2>IndyPress: Admin settings</h2>
		This will configurate the interface for administrators (dashboard).
		<form action="options.php" method="post">
		<?php settings_fields( 'indypress_admin' ); ?>
		<?php do_settings_sections( 'indypress_admin_settings' ); ?>
		<p class="submit">
			<input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes', 'indypress'); ?>" />
		</p>
		</form>
	</div>
<?php
	}

	function main_section() {
		return;
	}

	function indypress_admin_init() {
	}

	function pluginpage_link($links, $file) {
		global $indypress_path;
		if ($file == basename(dirname($indypress_path)) . '/indypress.php'){
			$links[] = '<a href="?page=indypress">'.__('Status','indypress')."</a>";
		}
		return $links;
	}

	function menu() {
		add_menu_page( 'IndyPress Plugin', 'IndyPress', 'administrator', 'indypress', array( $this, 'main_page' ), NULL, 3 );
		if(apply_filters('indypress_settings_adminpage', false)) //it is empty by default: this will make it easy to hide
			add_submenu_page( 'indypress', 'IndyPress Admin' . __( 'Settings' , 'indypress'), 'Admin', 'administrator', 'indypress_admin_settings', array( $this, 'settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );

		if( ! get_option( 'indypress_add_new_event' ) ) {
			global $submenu;
			unset( $submenu['edit.php?post_type=indypress_event'][10] ); //hide the "Add New event" button, but if you enter the url it is still accessible
		}
	}
	
	function main_page() {
		if ( !current_user_can( 'administrator' ) )
			wp_die( __( 'You do not have sufficient permissions to access this page.' , 'indypress') );
		?>
		<div class="wrap">
			<h2>IndyPress Status</h2>
			<h3>Status</h3>
			<h4>Base configuration</h4>
<?php if(!get_option('indypress_author', false) || !get_option('indypress_publication_page', false) || !get_option('indypress_enable_publication', false)): ?>
	<p>Some configuration is missing:
	<ol>
<?php if(!get_option('indypress_author', false)): ?>
	<li>Anonymous user has not been chosen: create one and choose it!</li>
<?php endif; if(!get_option('indypress_publication_page', false)): ?>
	<li>Publication page has not been chosen: do it!</li>
<?php endif; if(!get_option('indypress_enable_publication', false)): ?>
	<li>Publication is currently disabled: enable it!</li>
<?php endif; ?>
	</ol>
	You can do this on <a href="?page=indypress_publication_settings">Indypress-&gt;Publication</a></p>
	<h4>Forms configuration</h4>
<?php else: ?>
	<p>Basic settings are fine: great!</p>
<?php endif; //TODO: add a form for wizard here ?>
			<p>There are <?php echo count(get_indypress_publication_terms()); ?> forms enabled.
<?php if(!count(get_indypress_publication_terms())): ?>
	To quickly setup one, activate the <em>Indypress Base configuration</em> plugin.<br/>
	<em>OR</em> (the more advanced way), add one on <a href="?page=indypress_newform_settings">Indypress-&gt;Add a form</a> page, then configure it on the Forms page<p>
<?php endif; ?>
			
			<h3>Plugins</h3>
			<p>For additional functionalities, you can enable:
			<ul>
			<li>IndypressEvent: provides support for events (date, location, SEO, and more)<?php if(function_exists('the_event_information')) echo '<strong>(already activated)</strong>'; ?></li>
			<li>Indypress Reserved: limit some forms to certain user</li>
			</ul>
			Go to <a href="plugins.php?plugin_status=inactive">plugin page</a> and enable them.</p>

			<h3>About</h3>
			<p><a href="https://code.autistici.org/trac/indypress/wiki/DocToc">Plugin documentation: get started!</a></p>
			<p>Developed by p(A)skao, boyska</p>
		</div>
		<?php
		//TODO: report a summary about the current status of the plugin, with links to documentation
	}

}

?>
