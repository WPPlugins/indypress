<?php
class indypress_newform_settings {
	function indypress_newform_settings() {
		add_action( 'admin_menu', array( $this, 'menu' ) );
	}

	function menu() {
		add_submenu_page( 'indypress', 'IndyPress add form' . __('Settings', 'indypress'), __('Add a form', 'indypress'), 'administrator', 'indypress_newform_settings', array( $this, 'settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action('wp_enqueue_scripts', array($this, 'scripts'));
		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-sortable');
	}
	function scripts() {
		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-sortable');
	}

	function register_settings() {
		add_settings_section( 'indypress_newform_main', 'Add a form', create_function('', ''), 'indypress_newform_settings' );
		register_setting('indypress_newform', 'indypress_formlist', array($this, 'sanitize_nonempty_elem'));
		add_settings_field(
			'indypress_formlist',
			'Current forms (drag them to change their order!)',
			array( $this, 'setting_hiddenlist' ),
			'indypress_newform_settings',
			'indypress_newform_main');
		add_settings_field(
			'indypress_formlist[]',
			'Slug of the new form',
			array( $this, 'setting_line' ),
			'indypress_newform_settings',
			'indypress_newform_main',
			'indypress_formlist[]');
	}

	function settings_page() {
		if ( !current_user_can( 'administrator' ) )
			wp_die( __( 'You do not have sufficient permissions to access this page.' , 'indypress') );
		$slugs = get_indypress_publication_terms();
		?>
		<div class="wrap">
			<h2>IndyPress Settings: add a form </h2>
			At the moment <?php echo count($slugs); ?> forms are available:
			<form name="indypress_newform" method="post" action="options.php">

				<?php settings_fields( 'indypress_newform' ); ?>
				<?php do_settings_sections( 'indypress_newform_settings' ); ?>
				<p class="submit">
					<input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes', 'indypress'); ?>" />
				</p>
			</form>
			<script type="text/javascript">
			//TODO: sortable
			jQuery(document).ready(function($) {
				$('#indypress-form-sortable').sortable();
				$('#indypress-form-sortable').disableSelection();
				$('.indypress-form-remove').click(function() {;
					var slug = $(this).data('slug');
					$(':input[name="indypress_formlist[]"][value="' + slug + '"]').remove();
					$(this).parent().remove(); //remove <li>
				});
			});
			</script>
		</div>
<?php
		return;
	}

	function setting_hiddenlist( $option ) {
		echo '<ol id="indypress-form-sortable">';
		foreach( get_indypress_publication_terms() as $value ):
			echo '<li>' . $value . '<a data-slug="' . $value . '" class="indypress-form-remove">remove me</a>';
?>
<input type="hidden" name="indypress_formlist[]" value="<?php echo $value; ?>" /></li>
<?php
		endforeach;
		echo '</ol>';
	}
	function setting_line( $option ) {
?>
		<input type="text" name="<?php echo $option; ?>" value="<?php echo get_option( $option ); ?>" /></li>
<?php
	}

	function sanitize_nonempty_elem( $arr ) {
		$new = array();
		foreach( $arr as $elem )
			if( $elem && !in_array($elem,$new) )
				$new[] = $elem;
		return array_unique($new);
	}
}
