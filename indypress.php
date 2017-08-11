<?php

/* 
Plugin Name: IndyPress
Plugin URI: http://code.autistici.org/p/indypress
Description: Make your WordPress as an Indypendent Media Center
Author: p(A)skao
Version: 1.0.0
Author URI: paskao@hacari.org
License: GPL2
Domain Path: ./languages/
*/

// CONFIG
$indypress_url = plugins_url( '', __FILE__ ) . '/indypress/';
$indypress_relative_path = '/wp-content/plugins/' . str_replace( basename( __FILE__ ), "", plugin_basename( __FILE__ )) . 'indypress/';
$indypress_path = ABSPATH . 'wp-content/plugins/' . str_replace( basename( __FILE__ ), "", plugin_basename( __FILE__ )) . 'indypress/';

require_once( $indypress_path . 'config.php' );
require_once( $indypress_path . 'api/publication.php' );


/* --- Modified by Cap --- Start section*/
load_plugin_textdomain('indypress', '', 'indypress/languages');
/* --- Modified by Cap --- End section*/

require_once( $indypress_path . 'classes/wizard.class.php' );
register_activation_hook( __FILE__, 'do_wizard' );

/* Publication inputs: TODO: move it to publication_common or something like that */
	include_once( $indypress_path . 'form_inputs/line.php');
	include_once( $indypress_path . 'form_inputs/html.php');
	include_once( $indypress_path . 'form_inputs/tinymce.php');
	include_once( $indypress_path . 'form_inputs/autocomplete.php');
	include_once( $indypress_path . 'form_inputs/if.php');
	include_once( $indypress_path . 'form_inputs/checkboxes.php');
	include_once( $indypress_path . 'form_inputs/select.php');
	include_once( $indypress_path . 'form_inputs/upload.php');
	include_once( $indypress_path . 'form_inputs/embed.php');
	include_once( $indypress_path . 'form_actions/meta.php');
	include_once( $indypress_path . 'form_actions/pre_field.php');
	include_once( $indypress_path . 'form_actions/if.php');
	include_once( $indypress_path . 'form_actions/filter.php');
/* Publication inputs end */

require_once( $indypress_path . 'classes/upload.class.php' );
$indypress_upload = new indypress_upload();

// ADMIN PANEL MENU
if( is_admin() ) {
	// LOAD ADMIN COMMON
	require_once( $indypress_path . 'classes/admin.class.php' );
	$indypress_admin = new indypress_admin();
	// INDYPRESS ADMIN HOOK
	add_action( 'indypress_admin_init', array( $indypress_admin, 'indypress_admin' ) );

	// LOAD PUBLICATION SETTINGS
	require_once( $indypress_path . 'classes/publication_settings.class.php' );
	$indypress_publication_settings = new indypress_publication_settings();
	add_action( 'indypress_admin_init', array( $indypress_publication_settings, 'indypress_publication_settings' ) );
	require_once( $indypress_path . 'classes/form_settings.php' );
	$indypress_form_settings = new indypress_form_settings();
	require_once( $indypress_path . 'classes/newform_settings.php' );
	$indypress_newform_settings = new indypress_newform_settings();

	// LOAD PUBLICATION ADMIN&AJAX
	require_once( $indypress_path . 'classes/publication_admin.class.php' );
	$indypress_publication_admin = new indypress_publication_admin();
	add_action( 'indypress_admin_init', array( $indypress_publication_admin, 'indypress_publication_admin' ) );

	// LOAD HIDING POST
	require_once( $indypress_path . 'classes/hide_admin.class.php' );
	$indypress_hide_admin = new indypress_hide_admin();
	add_action( 'indypress_admin_init', array( $indypress_hide_admin, 'indypress_hide_admin' ) );

	// LOAD SIGNALING POSTS AND COMMENTS
	require_once( $indypress_path . 'classes/signal_admin.class.php' );
	$indypress_signal_admin = new indypress_signal_admin();
	add_action( 'indypress_admin_init', array( $indypress_signal_admin, 'indypress_signal_admin' ) );

	//LOAD LIVEBLOGGING SETTINGS
//    require_once( $indypress_path . 'classes/live-blogging_settings.class.php' );
//    $indypress_liveblogging_settings = new indypress_liveblogging_settings();
//    add_action( 'indypress_admin_init', array( $indypress_liveblogging_settings, 'indypress_liveblogging_settings' ) );

	require_once( $indypress_path . 'classes/visualization-settings.class.php' );
	$indypress_visualization_settings = new Indypress_VisualizationSettings();
	add_action( 'indypress_admin_init', array( $indypress_visualization_settings, 'Indypress_VisualizationSettings' ) );


} else {

	// INDYPRESS HOOK
	do_action( 'indypress_init' );

	// LOAD OPEN PUBLICATION
	$enable_publication = get_option( 'indypress_enable_publication' );
	if( $enable_publication ) {
		require_once( $indypress_path . 'classes/publication.class.php' );
		$indypress_publication = new indypress_publication();
		require_once( $indypress_path . 'classes/publication_form.class.php' );
		$indypress_publication_form = new indypress_publication_form();
		add_action( 'indypress_init', array( $indypress_publication, 'indypress_publication' ) );
	}

//    require_once( $indypress_path . 'classes/live-blogging.class.php' );
//    $indypress_liveblogging = new indypress_liveblogging();
	
	// LOAD RIGHT VISUALIZATION OF AUTHOR POST
	require_once( $indypress_path . 'classes/publication_theme.class.php' );
	$indypress_publication_theme = new indypress_publication_theme();
	add_action( 'indypress_init', array( $indypress_publication_theme, 'indypress_publication_theme' ) );

	// LOAD SIGNALING POSTS AND COMMENTS
	require_once( $indypress_path . 'classes/signal.class.php' );
	$indypress_signal = new indypress_signal();
	add_action( 'indypress_init', array( $indypress_signal, 'indypress_signal' ) );

	// LOAD HIDING POSTS AND COMMENTS
	require_once( $indypress_path . 'classes/hide_admin.class.php' );
	$indypress_hide_admin = new indypress_hide_admin();
	add_action( 'init', array( &$indypress_hide_admin, 'register_hide_post_status' ) );
	require_once( $indypress_path . 'classes/hide.class.php' );
	$indypress_hide = new indypress_hide();
	add_action( 'indypress_init', array( $indypress_hide, 'indypress_hide' ) );

}

	function do_wizard() {
		$indypress_wizard = new indypress_wizard();
	}

?>
