<?php
/**
 * @package A2importer
 */
/*
Plugin Name: A2importer
Plugin URI: http://a2importer.com/
Description: A2Importer is a tool to import blog posts from one WP site to another
Version: 0.0.8
Author: Maxim Sergeev
Author URI: http://automattic.com/wordpress-plugins/
License: GPLv2 or later
Text Domain: a2importer
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

define( 'A2IMPORTER_VERSION', '0.0.8' );
define( 'A2IMPORTER__MINIMUM_WP_VERSION', '3.1' );
define( 'A2IMPORTER__PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'A2IMPORTER__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

register_activation_hook( __FILE__, array( 'A2importer', 'plugin_activation' ) );
register_deactivation_hook( __FILE__, array( 'A2importer', 'plugin_deactivation' ) );

function run_import_process() {
	if( $Importer = new CustomPostsImporter() ) {
		die('ok');
	} else {
		die(0);
	}
}

add_action( 'wp_ajax_on_start_import', 'run_import_process' );

require_once( A2IMPORTER__PLUGIN_DIR . '/classes/importer.php' );

if ( is_admin() ) {
	require_once( A2IMPORTER__PLUGIN_DIR . '/classes/admin.php' );
	add_action( 'init', array( 'A2importerAdmin', 'init' ) );
}