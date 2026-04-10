<?php
/**
 * Easy Demo Import
 *
 * This is the main driver file for importing the demo content.
 *
 * @package    jedi-master
 * @author     Jerry Simmons <jerry@ferventsolutions.com>
 * @copyright  2021 Jerry Simmons
 * @license    GPL-2.0+
 **/

if( ! defined( 'ABSPATH' ) ) { exit; }

if( defined( 'JEDI_APPRENTICE_PATH' ) ) {
	if( function_exists( 'jswj_jedi_log' ) ) {
		jswj_jedi_log( 'Aborting Import Initialization because JEDI_APPRENTICE_PATH is already defined' );
	}
	return;
}

/**
 * Require WordPress Dependencies
 **/
require_once ABSPATH . 'wp-admin/includes/image.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
require_once ABSPATH . 'wp-admin/includes/media.php';
require_once ABSPATH . 'wp-admin/includes/class-pclzip.php';


/**
 * Define Paths - Includes trailing slash
 **/
define( 'JEDI_APPRENTICE_PATH', plugin_dir_path( __FILE__ ) );
define( 'JEDI_APPRENTICE_URL', jswj_get_jedi_apprentice_url() );

/**
 * Load Required Dependencies
 **/
if( file_exists( JEDI_APPRENTICE_PATH . 'includes' ) ) {
	foreach( glob( JEDI_APPRENTICE_PATH . 'includes/*.php' ) as $required ) {
		require_once $required;
	}
}

/**
 * Load JEDI Apprentice Addons
 **/
if( file_exists( JEDI_APPRENTICE_PATH . 'apprentice-addons' ) ) {
	foreach( glob( JEDI_APPRENTICE_PATH . 'apprentice-addons/*.php' ) as $addon ) {
		require_once $addon;
	}
}


/**
 * Initialize JEDI Installer Settings
 *
 * Runs when database value is not set, or when plugin is activated
 **/
register_activation_hook( __FILE__, 'train_jedi_apprentice' );
$jedi_apprentice_settings = get_option( 'jedi_apprentice_settings' );
if( false === $jedi_apprentice_settings ) { train_jedi_apprentice(); }

# Catch switch to another JEDI theme, and reload settings from file
if( JEDI_APPRENTICE_PATH !== get_option( 'jedi_apprentice_path' ) ) { train_jedi_apprentice(); }

/**
 * Initialize JEDI Apprentice
 *
 * @return void
 */
function train_jedi_apprentice() {
	jswj_jedi_log( 'Operating System: ' . PHP_OS . ', PHP Version: ' . PHP_VERSION );

	$jedi_settings = jswj_get_jedi_import_data();

	$jedi_apprentice_settings = $jedi_settings['jedi_settings'];
	$jedi_apprentice_settings['jedi_apprentice_path'] = plugin_dir_path( __FILE__ );
	$jedi_apprentice_settings['jedi_apprentice_url'] = jswj_get_jedi_apprentice_url();

	if( ! empty( $jedi_settings['wp_options'] ) ) {
		$jedi_apprentice_settings['include_wp_options'] = 1;
	}

	update_option( 'jedi_apprentice_settings', $jedi_apprentice_settings );
	update_option( 'jedi_import_options', $jedi_apprentice_settings );
	update_option( 'jedi_apprentice_path', JEDI_APPRENTICE_PATH );
	delete_option( 'jedi_status' );

	jswj_jedi_log( 'Initialize Installer Settings', $jedi_apprentice_settings );
} # END train_jedi_apprentice()


/**
 * Gets The URL To Jedi Apprentice Folder
 *
 * Tests if standard WP function works first, then tries an alternate method
 *
 * @return URL string
 **/
function jswj_get_jedi_apprentice_url() {

	$installer_slug = basename( dirname( __FILE__, 2 ) );

	if( strpos( __FILE__, get_stylesheet_directory() ) !== false ) {
		$url = get_stylesheet_directory_uri() . '/jedi-apprentice/';
	} else {
		$url = plugins_url() . '/' . $installer_slug . '/jedi-apprentice/';
	}

	return $url;
} # END jswj_get_jedi_apprentice_url()

/**
 * Load JEDI Apprentice Admin Menu
 **/
$jedi_apprentice_admin = new JEDI_Apprentice_Admin();
