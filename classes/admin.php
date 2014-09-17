<?php

class A2importerAdmin {
	const NONCE = 'a2importer-update-key';

	private static $initiated = false;
	private static $_settings_key = 'a2-import-settings';
	private static $_log_key = 'a2-import-log';
	private static $_post_list_key = 'a2-imported-posts-list';
	private static $_tabs = array();

	public static function init() {
		if (!self::$initiated) {
			self::init_hooks();
			load_plugin_textdomain('a2importer');
		}
	}

	public static function init_hooks() {
		self::$initiated = true;
		add_action('admin_init', array('A2importerAdmin', 'register_plugin_settings'));
		add_action('admin_init', array('A2importerAdmin', 'register_plugin_import_log'));
		add_action('admin_init', array('A2importerAdmin', 'register_plugin_post_list'));
		add_action('admin_init', array('A2importerAdmin', 'register_plugin_optiones'));
		add_action('admin_init', array('A2importerAdmin', 'create_log_file'));
		add_action('admin_menu', array('A2importerAdmin', 'admin_menu'), 5);
	}

	public static function admin_menu() {
		add_submenu_page('tools.php', 'A2importer','A2 import tool', 'manage_options', 'a2importer', array( 'A2importerAdmin', 'display_page' ) );
	}

	public static function register_plugin_optiones() {
		add_option( 'a2iported_posts', '');

		add_option( 'a2idb-name', '');
		add_option( 'a2idb-host', '');
		add_option( 'a2idb-user', '');
		add_option( 'a2idb-pass', '');
		register_setting( 'a2idb', 'a2idb-name' );
		register_setting( 'a2idb', 'a2idb-host' );
		register_setting( 'a2idb', 'a2idb-user' );
		register_setting( 'a2idb', 'a2idb-pass' );

		add_option( 'a2itime-period', 'overall');
		add_option( 'a2itime-from', '');
		add_option( 'a2itime-to', '');
		register_setting( 'a2itime', 'a2itime-period' );
		register_setting( 'a2itime', 'a2itime-from' );
		register_setting( 'a2itime', 'a2itime-to' );

		add_option( 'a2cron-set', '0');
		add_option( 'a2icron-period', '');
		register_setting( 'a2icron', 'a2cron-set' );
		register_setting( 'a2icron', 'a2cron-period' );
	}

	public static function register_plugin_settings() {
		self::$_tabs[self::$_settings_key] = 'Settings';
	}

	public static function register_plugin_import_log() {
		self::$_tabs[self::$_log_key] = 'Import Log';
	}

	public static function register_plugin_post_list() {
		self::$_tabs[self::$_post_list_key] = 'All imported posts';
	}

	public static function admin_head() {
		if ( !current_user_can( 'manage_options' ) )
			return;
	}

	public static function display_page() {
		$current_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : self::$_settings_key;
		echo '<div class="wrap"><h2>A2 Import tool</h2>';
		echo '<h2 class="nav-tab-wrapper">';
		foreach ( self::$_tabs as $tab_key => $tab_caption ) {
			$active = $current_tab == $tab_key ? 'nav-tab-active' : '';
			echo '<a class="nav-tab ' . $active . '" href="?page=a2importer&tab=' . $tab_key . '">' . $tab_caption . '</a>';
		}
		echo '</h2>';
		require (A2IMPORTER__PLUGIN_DIR . 'templates/' . $current_tab . '.php');
		echo '</div>';
	}

	public static function create_log_file() {
		$upload_dir = wp_upload_dir();
		$path = $upload_dir['basedir'] . '/a2import-tool/import.log';
		if (!file_exists($upload_dir['basedir'] . '/a2import-tool')) {
			mkdir($upload_dir['basedir'] . '/a2import-tool', 0777, true);
		}
		$log = fopen($path, 'a') or die("Can't create import log file");
		fclose($log);
	}
}
