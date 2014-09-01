<?php

class A2importerAdmin {
	const NONCE = 'a2importer-update-key';

	private static $initiated = false;
	private static $notices = array();

	public static function init() {
		if ( ! self::$initiated ) {
			print_r('here we are');
			self::init_hooks();
		}
	}

	public static function init_hooks() {
		self::$initiated = true;
		print_r('here we are initiating');

		add_action( 'admin_init', array( 'A2importerAdmin', 'admin_init' ) );
		add_action( 'admin_menu', array( 'A2importerAdmin', 'admin_menu' ), 5 ); # Priority 5, so it's called before Jetpack's admin_menu.
	}

	public static function admin_init() {
		load_plugin_textdomain( 'a2importer' );
	}

	public static function admin_menu() {
		add_submenu_page('tools.php', 'A2importer','A2 import tool', 'manage_options', 'a2importer-key-config', array( 'A2importerAdmin', 'display_page' ) );

	}

	public static function admin_head() {
		if ( !current_user_can( 'manage_options' ) )
			return;
	}

	public static function display_page() {
		include(A2IMPORTER__PLUGIN_DIR . '/templates/admin-start.php' );
	}
}
