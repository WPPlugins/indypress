<?php

class indypress_publication_settings {

	// HOOK TO LOAD THIS CLASS
	function indypress_publication_settings() {
		add_action( 'admin_menu', array( $this, 'menu' ) );
	}

	function menu() {
		add_submenu_page( 'indypress', 'IndyPress Publication' . __('Settings', 'indypress'), __('Publication', 'indypress'), 'administrator', 'indypress_publication_settings', array( $this, 'settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	function check_enable_plugin( $input ) {
		if( get_option( 'indypress_publication_page' ) && get_option( 'indypress_author' ) )
			return $input;
	}

	function register_settings() {
		add_settings_section( 'indypress_publication_main', __('Main settings', 'indypress'), array( $this, 'empty_section' ), 'indypress_publication_settings' );

		register_setting( 'indypress_settings', 'indypress_enable_publication', array( $this, 'check_enable_plugin' ) );
		add_settings_field( 'indypress_enable_publication', __('Enable open publication', 'indypress'), array( &$this, 'setting_enable_publication' ), 'indypress_publication_settings', 'indypress_publication_main' );
		register_setting( 'indypress_settings', 'indypress_author' );
		add_settings_field( 'indypress_author', __('Author', 'indypress'), array( $this, 'setting_author' ), 'indypress_publication_settings', 'indypress_publication_main' );
		register_setting( 'indypress_settings', 'indypress_publication_page' );
		add_settings_field( 'indypress_publication_page', __('Publication page', 'indypress'), array( $this, 'setting_publication_page' ), 'indypress_publication_settings', 'indypress_publication_main' );

		add_settings_section( 'indypress_publication_form_features', __('Form features', 'indypress'), array( $this, 'empty_section' ), 'indypress_publication_settings' );

		register_setting( 'indypress_settings', 'indypress_preview' );
		add_settings_field( 'indypress_preview', __('Enable preview', 'indypress'), array( $this, 'setting_preview' ), 'indypress_publication_settings', 'indypress_publication_form_features' );

	}

	function load_settings() {
		$this->enable_publication = get_option( 'indypress_enable_publication' );

		$this->publication_page = get_option( 'indypress_publication_page' );
		$this->author = get_option( 'indypress_author' );

		$this->preview = get_option( 'indypress_preview' );

		return 1;
	}

	function setting_boolean( $option ) {
?>
	<input type="checkbox" name="<?php echo $option; ?>" <?php if( get_option( $option ) ) echo ' checked="checked"'; ?>">
<?php
	}

	// MAIN
	function setting_enable_publication() {
		$this->setting_boolean( 'indypress_enable_publication' );
	}
	function setting_author() {
		global $wpdb;
?>
	<select name="indypress_author">
		<option></option>
		<?php

		$rows = $wpdb->get_results("SELECT ID, user_nicename FROM " . $wpdb->users);
		foreach( $rows as $row ) {
			?>
		<option value="<?php echo $row->ID; ?>"<?php if( $row->ID == get_option( 'indypress_author' ) ) echo ' selected="selected"'; ?>><?php echo $row->user_nicename; ?></option>
			<?php
		}
		?>
	</select>
<?php
	}
	function setting_publication_page() {
?>
	<select name="indypress_publication_page">
		<option></option>
			<?php

		$rows = get_pages();
		foreach( $rows as $row ) {
			?>
		<option value="<?php echo $row->ID; ?>"<?php if( $row->ID == $this->publication_page ) echo ' selected="selected"'; ?>><?php echo $row->post_title; ?><?php //echo $row->name; ?></option>
			<?php
		}
		?>
	</select>
<?php
	}

	// CATEGORIES
	function setting_list_of_terms( $option ) {
	  $option_array = get_option( $option );
	  if( !$option_array ) $option_array = array();
?>
	<select name="<?php echo $option; ?>[]" multiple="multiple" style="height:150px; width:150px;">
<?php
	  foreach( get_taxonomies('', 'objects') as $tax_o ) {
		$tax = $tax_o->name;
		if( 'post_tag' == $tax ||  'link_category' == $tax || 'nav_menu' == $tax || 'post_format' == $tax )
		  continue;
		echo '<optgroup label="' . $tax_o->label . '">';
		foreach( get_terms( $tax, array('hide_empty' => false) ) as $row ) {
?>
		  <option value="<?php echo $row->term_id; ?>"<?php if( in_array( $row->term_id, $option_array ) ) echo ' selected="selected"'; ?>><?php echo $row->name; ?></option>
<?php
		}
		echo '</optgroup>';
	  }
?>
	</select>

<?php
	}

	function setting_preview() {
		$this->setting_boolean( 'indypress_preview' );
	}

	function empty_section() {
	}

	function settings_page() {
		global $wpdb;

		if ( !current_user_can( 'administrator' ) )
			wp_die( __( 'You do not have sufficient permissions to access this page.' , 'indypress') );

		$this->load_settings();

		?>
		<div class="wrap">

			<h2>IndyPress Settings: Publication</h2>

			<form name="indypress_form" method="post" action="options.php">

				<?php settings_fields( 'indypress_settings' ); ?>
				<?php do_settings_sections( 'indypress_publication_settings' ); ?>
				<p class="submit">
					<input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes', 'indypress'); ?>" />
				</p>
			</form>
		</div>
<?php
		return;
	}

}

?>
