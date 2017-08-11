<?php
class indypress_form_settings {
	function indypress_form_settings() {
		add_action( 'admin_menu', array( $this, 'menu' ) );
	}

	function menu() {
		add_submenu_page( 'indypress', 'IndyPress Forms' . __('Settings', 'indypress'), __('Forms', 'indypress'), 'administrator', 'indypress_form_settings', array( $this, 'settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	function register_settings() {
		foreach(get_option( 'indypress_formlist' ) as $slug) { //we don't care about forms added by plugins
			add_settings_section( 'indypress_form_' .  $slug, 'Form ' . $slug, create_function('', ''), 'indypress_form_settings' );

			register_setting('indypress_form', 'indypress_forms_' . $slug . '_enabled' );
			add_settings_field(
				'indypress_forms_' . $slug . '_title',
				'Enabled',
				array( $this, 'setting_boolean' ),
				'indypress_form_settings',
				'indypress_form_' . $slug,
				'indypress_forms_' . $slug . '_title' );
			register_setting('indypress_form', 'indypress_forms_' . $slug . '_title' );
			add_settings_field(
				'indypress_forms_' . $slug . '_title',
				'Title',
				array( $this, 'setting_line' ),
				'indypress_form_settings',
				'indypress_form_' . $slug,
				'indypress_forms_' . $slug . '_title' );
			register_setting('indypress_form', 'indypress_forms_' . $slug . '_fields' );
			add_settings_field(
				'indypress_forms_' . $slug . '_fields',
				'Fields',
				array( $this, 'setting_textarea' ),
				'indypress_form_settings',
				'indypress_form_' . $slug,
				'indypress_forms_' . $slug . '_fields' );
			register_setting('indypress_form', 'indypress_forms_' . $slug . '_actions' );
			add_settings_field(
				'indypress_forms_' . $slug . '_actions',
				'Actions',
				array( $this, 'setting_textarea' ),
				'indypress_form_settings',
				'indypress_form_' . $slug,
				'indypress_forms_' . $slug . '_actions' );
		}
	}

	function settings_page() {
		if ( !current_user_can( 'administrator' ) )
			wp_die( __( 'You do not have sufficient permissions to access this page.' , 'indypress') );
		?>
		<div class="wrap">
			<h2>IndyPress Settings: Form configuration </h2>
			<p>Configuring may sound difficult: that's why we have
			<a href="https://code.autistici.org/trac/indypress/wiki/FormPresets">a list of presets</a> ready
			for copy&paste!</p>
<?php
		$plugin_added = array_diff( get_indypress_publication_terms(), get_option( 'indypress_formlist' ) );
		if( $plugin_added )
			echo 'These forms have been added by plugin, so they are NOT configurable here, but will appear in your publication page: ' . implode( ',', $plugin_added );
?>
			<form name="indypress_form" method="post" action="options.php">

				<?php settings_fields( 'indypress_form' ); ?>
				<?php do_settings_sections( 'indypress_form_settings' ); ?>
				<p class="submit">
					<input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes', 'indypress'); ?>" />
				</p>
			</form>
		</div>
<?php
		return;
	}

	function setting_textarea( $option ) {
		/**
		 * Indents a flat JSON string to make it more human-readable.
		 *
		 * @param string $json The original JSON string to process.
		 *
		 * @return string Indented version of the original JSON string.
		 */

		$value = $this->pretty_print(get_option( $option ));
?>
	<textarea name="<?php echo $option; ?>"><?php echo $value;
?></textarea>
<?php
	}
	function setting_line( $option ) {
?>
		<input type="text" name="<?php echo $option; ?>" value="<?php echo get_option( $option ); ?>" />
<?php
	}
	function indent($json) {

		$result      = '';
		$pos         = 0;
		$strLen      = strlen($json);
		$indentStr   = '  ';
		$newLine     = "\n";
		$prevChar    = '';
		$outOfQuotes = true;

		for ($i=0; $i<=$strLen; $i++) {

			// Grab the next character in the string.
			$char = substr($json, $i, 1);

			// Are we inside a quoted string?
			if ($char == '"' && $prevChar != '\\') {
				$outOfQuotes = !$outOfQuotes;
			
			// If this character is the end of an element, 
			// output a new line and indent the next line.
			} else if(($char == '}' || $char == ']') && $outOfQuotes) {
				$result .= $newLine;
				$pos --;
				for ($j=0; $j<$pos; $j++) {
					$result .= $indentStr;
				}
			}
			
			// Add the character to the result string.
			$result .= $char;

			// If the last character was the beginning of an element, 
			// output a new line and indent the next line.
			if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
				$result .= $newLine;
				if ($char == '{' || $char == '[') {
					$pos ++;
				}
				
				for ($j = 0; $j < $pos; $j++) {
					$result .= $indentStr;
				}
			}
			
			$prevChar = $char;
		}

		return $result;
	} //end indent
	function pretty_print( $json ) {

		if(defined('JSON_PRETTY_PRINT'))
			return json_encode(json_decode($json, TRUE),JSON_PRETTY_PRINT);
		return $this->indent( json_encode( json_decode($json, TRUE) ) );
	}
}
