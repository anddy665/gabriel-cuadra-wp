<?php
/**
 * Easy Demo Import Pro - Apprentice Plugins
 *
 * @package    jedi-master
 * @author     Jerry Simmons <jerry@ferventsolutions.com>
 * @copyright  2021 Jerry Simmons
 * @license    GPL-2.0+
 **/

if ( ! defined( 'ABSPATH' ) ) { exit; }

	/**
	 * Action: List Recommended Plugins
	 * Add Checkboxes For Install & Activate Options
	 * 
	 * @param array $selected_plugins - Selected Plugins.
	 **/
	function jswj_jedi_list_recommended_plugins( $selected_plugins ) {

		$active_plugins = get_option( 'active_plugins' );
		$installed_plugins = get_plugins();

		echo '<div class="jedi_available_plugins">';
			echo '<table class="jedi_suggest_plugins_table">';
				echo '<td class="jedi_plugin_heading" colspan="3"><h3>Recommended Plugin(s) From The WordPress Plugin Repository:</h3></td>';

				foreach( $selected_plugins as $plugin_slug ) {

					$get_plugin_repo_data = array(
						'action'  => 'plugin_information',
						'request' => serialize(
							(object) array( 
								'slug'   => $plugin_slug,
								'fields' => array( 'description' => true ),
							)
						),
					);
					$repo_data = wp_remote_post(
						'http://api.wordpress.org/plugins/info/1.0/',
						array( 'body' => $get_plugin_repo_data )
					);
					if( is_wp_error( $repo_data ) ) {
						jswj_jedi_log( 'Error Connecting To WordPress Plugin Repository', $repo_data );
						echo '<tr class="row1"><td class="jedi_plugin_repo_error_td">';
							echo '<h4>Error Connecting To WordPress Plugin Repository</h4>';
							echo '</td></tr>';
						continue;
					}

					if( 'N;' != $repo_data['body'] ) {
						$plugin_repo_data = unserialize( $repo_data['body'] );

						$is_plugin_installed = false;
						foreach( $installed_plugins as $plugin_file => $installed_plugin ) {
							$plugin_path_info = pathinfo( WP_PLUGIN_DIR . '/' . $plugin_file );
							$active_plugin_slug = basename( $plugin_path_info['dirname'] );
							if( $active_plugin_slug === $plugin_slug ) {
								$is_plugin_installed = true;
								$installed_plugin_file = $plugin_file;
							}
						}
						$is_plugin_activated = false;
						foreach( $active_plugins as $active_plugin ) {
							$plugin_path_info = pathinfo( WP_PLUGIN_DIR . '/' . $active_plugin );
							$active_plugin_slug = basename( $plugin_path_info['dirname'] );
							if( $active_plugin_slug === $plugin_slug ) {
								$is_plugin_activated = true;
							}
						}
					} else { continue; }

					echo '<tr class="row1"><td class="jedi_plugin_name_td">';
						echo '<h4>' . esc_html( $plugin_repo_data->name ) . '</h4>';

						if( $is_plugin_installed ) {
							echo "<input disabled type='checkbox' name='plugin_installed' value='1'"
								. checked( 1, $is_plugin_installed, false ) . ' /> Installed';
						} else {
							echo "<input type='checkbox' name='install_plugin_" . esc_attr( $plugin_repo_data->slug ) . "' value='" . esc_attr( $plugin_repo_data->download_link ) . "' "
								. checked( 1, 1, false ) . ' /> Install';
						}
						echo '<br>';

						if( $is_plugin_activated ) {
							echo "<input disabled type='checkbox' name='plugin_activated' value='1'"
								. checked( 1, $is_plugin_installed, false ) . ' /> Activated';
						} else {
							if( ! $is_plugin_installed ) {
								echo "<input type='checkbox' name='activate_plugin_" . esc_attr( $plugin_repo_data->slug ) . "' value='" . esc_attr( $plugin_repo_data->slug ) . "' "
									. checked( 1, 1, false ) . ' /> Activate';
							} else {
								echo "<input type='checkbox' name='activate_plugin_" . esc_attr( $plugin_repo_data->slug ) . "' value='" . esc_attr( $installed_plugin_file ) . "' "
									. checked( 1, 1, false ) . ' /> Activate';
							}
						}

					echo '</td>';
					echo '<td class="jedi_plugin_description_td">';
						echo esc_html( substr( wp_strip_all_tags( $plugin_repo_data->description ), 0, 150 ) ) . '... <br>'; 
						echo '<a href="' . esc_attr( $plugin_repo_data->homepage ) . '" target="_blank">Visit Plugin Homepage For More Information</a>';

					echo '</td>';

					echo '<td class="jedi_plugin_info_td">';
						echo 'Author: <a href="' . esc_attr( $plugin_repo_data->author_profile ) . '" target="_blank">' . esc_html( wp_strip_all_tags( $plugin_repo_data->author ) ) . '</a><br>';
						echo 'Downloads: ' . number_format( $plugin_repo_data->downloaded ) . '<br>';
						echo 'Plugin Rating: ' . esc_attr( $plugin_repo_data->rating ) . '% ('
							. esc_attr( $plugin_repo_data->num_ratings ) . ' ratings)<br>';
						echo 'Support Threads: ' . esc_html( $plugin_repo_data->support_threads )
							. ' (' . esc_html( $plugin_repo_data->support_threads_resolved ) . ' resolved)<br>';
						echo 'Current Version: ' . esc_html( $plugin_repo_data->version )
							. ' (Updated: ' . esc_html( substr( $plugin_repo_data->last_updated, 0, 10 ) ) . ')<br>';
					echo '</td>';
				echo '</tr>';
				} // END foreach selected_plugins
			echo '</table>';
		echo '</div>';

	} # END list_recommended_plugins()
	add_action( 'jedi_list_recommended_plugins', 'jswj_jedi_list_recommended_plugins', 0, 1 );


	/**
	 * Action: List Recommended Plugins
	 * Add Checkboxes For Install & Activate Options
	 **/


	/**
	 * Action: List Recommended Plugins not found in the WordPress Plugin Repository
	 * Add Checkboxes For Install & Activate Options
	 * 
	 * @param array $nonrepo_plugins - Non Repo Plugins.
	 **/
	function jswj_list_recommended_nonrepo_plugins( $nonrepo_plugins ) {

		$active_plugins = get_option( 'active_plugins' );
		$installed_plugins = get_plugins();

		echo '<div class="jedi_available_plugins">';
			echo '<table class="jedi_suggest_plugins_table">';
				echo '<td class="jedi_plugin_heading" colspan="3"><h3><br>Other Recommended Plugin(s):</h3></td>';

				foreach( $nonrepo_plugins as $plugin_slug => $nonrepo_plugin ) {
					$is_plugin_installed = false;
					foreach( $installed_plugins as $plugin_file => $installed_plugin ) {
						$plugin_path_info = pathinfo( WP_PLUGIN_DIR . '/' . $plugin_file );
						$active_plugin_slug = basename( $plugin_path_info['dirname'] );
						if( $active_plugin_slug === $plugin_slug ) {
							$is_plugin_installed = true;
							$installed_plugin_file = $plugin_file;
						}
					}
					$is_plugin_activated = false;
					foreach( $active_plugins as $active_plugin ) {
						$plugin_path_info = pathinfo( WP_PLUGIN_DIR . '/' . $active_plugin );
						$active_plugin_slug = basename( $plugin_path_info['dirname'] );
						if( $active_plugin_slug === $plugin_slug ) {
							$is_plugin_activated = true;
						}
					}

					echo '<tr class="row1"><td class="jedi_plugin_name_td">';
						echo '<h4>' . esc_html( $nonrepo_plugin['Name'] ) . '</h4>';

						if( $is_plugin_installed ) {
							echo "<input disabled type='checkbox' name='plugin_installed' value='1'"
								. checked( 1, $is_plugin_installed, false ) . ' /> Installed';
						} else {
							echo "<input type='checkbox' name='install_plugin_" . esc_attr( $plugin_slug ) . "' value='nonrepo' "
								. checked( 1, 1, false ) . ' /> Install';
						}
						echo '<br>';

						if( $is_plugin_activated ) {
							echo "<input disabled type='checkbox' name='plugin_activated' value='1'"
								. checked( 1, $is_plugin_installed, false ) . ' /> Activated';
						} else {
							if( ! $is_plugin_installed ) {
								echo "<input type='checkbox' name='activate_plugin_" . esc_attr( $plugin_slug ) . "' value='" . esc_attr( $plugin_slug ) . "' "
									. checked( 1, 1, false ) . ' /> Activate';
							} else {
								echo "<input type='checkbox' name='activate_plugin_" . esc_attr( $plugin_slug ) . "' value='" . esc_attr( $installed_plugin_file ) . "' "
									. checked( 1, 1, false ) . ' /> Activate';
							}
						}
					echo '</td>';

					echo '<td class="jedi_plugin_description_td">';
						echo wp_kses_post( $nonrepo_plugin['Description'] );
					echo '</td>';

					echo '<td class="jedi_plugin_info_td">';
						echo 'Version: ' . esc_html( $nonrepo_plugin['Version'] ) . '<br>';
						echo 'Plugin URL: <a href="' . esc_url( $nonrepo_plugin['PluginURI'] ) . '" target="_blank">'
							. esc_url( $nonrepo_plugin['PluginURI'] ) . '</a><br>';
					echo '</td>';
				echo '</tr>';
				} // END foreach selected_plugins
			echo '</table>';
		echo '</div>';

	} // END list_recommended_nonrepo_plugins()
	add_action( 'jedi_list_recommended_nonrepo_plugins', 'jswj_list_recommended_nonrepo_plugins', 0, 1 );



/**
 * Install Plugins Driver
 **/
function jswj_jedi_install_plugins() {
	jswj_verify_ajax_nonce_and_capability( 'jswj_import_process_nonce', 'jedi_ajax_nonce' );

	$jedi_import_options = get_option( ' jedi_import_options' );
	$install_plugins = $jedi_import_options['install_plugins'];
	$install_nonrepo_plugins = $jedi_import_options['install_nonrepo_plugins'];
	$activate_plugins = $jedi_import_options['activate_plugins'];

	$installed_repo = 0;
	$installed_nonrepo = 0;

	if( 0 < count( $install_plugins ) ) {
		$installed_repo = jswj_jedi_install_repo_plugins();
		jswj_jedi_log( 'Repository Plugins Installed', $installed_repo );
	}
	if( 0 < count( $install_nonrepo_plugins ) ) {
		$installed_nonrepo = jswj_jedi_install_nonrepo_plugins();
		jswj_jedi_log( 'Non-Repository Plugins Installed', $installed_nonrepo );
	}

	jswj_ajax_response( array( 1, $installed_repo + $installed_nonrepo . ' Plugins Installed' ) );
} # END jswj_jedi_install_activate_plugins()
add_action( 'wp_ajax_jswj_jedi_install_plugins', 'jswj_jedi_install_plugins' );

/**
 * Install Selected Repository Plugins
 *
 * @uses PclZip()
 * @uses wp_filesystem
 * @uses get_plugins()
 * @uses wp_cache_get() and wp_cache_set()
 **/
function jswj_jedi_install_repo_plugins() {

	$jedi_import_options = get_option( 'jedi_import_options' );
	$install_plugins = $jedi_import_options['install_plugins'];
	$report_installed = 0;

	require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
	require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
	$direct_filesystem = new WP_Filesystem_Direct( new StdClass() );

	foreach( $install_plugins as $slug => $install_plugin ) {
		$install_plugin = trim( $install_plugin );

		$temp_zip = WP_PLUGIN_DIR . '/' . basename( $install_plugin );

		$downloaded_zip = download_url( $install_plugin );
		$safe_jedi = $direct_filesystem->copy(
			$downloaded_zip,
			WP_PLUGIN_DIR . '/' . basename( $install_plugin )
		);
		if( ! $safe_jedi ) {
			jswj_jedi_log( 'Plugin Install Failed', $install_plugin );
			continue;
		}
		unset( $downloaded_zip );

		/**
		 * Unzip Plugin File
		 **/
		$unzip_plugin = new PclZip( $temp_zip );
		if( $unzip_plugin->extract( PCLZIP_OPT_PATH, WP_PLUGIN_DIR ) === 0 ) {
			die( 'Error : ' . esc_html( $unzip_plugin->errorInfo( true ) ) );
		}

		/**
		 * Remove Temporary Zip File
		 **/
		global $wp_filesystem;
		if( empty( $wp_filesystem ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			if( ! WP_Filesystem() ) {
				jswj_jedi_order_66( 'Failed to initialize WP Filesystem.' );
			}
		}
		$wp_filesystem->delete( $temp_zip );

		$plugin_data = get_plugins( '/' . $slug );
		$plugin_file = $slug . '/' . key( $plugin_data );

		# Update WP Plugin Data Cache
		$cache_plugins = wp_cache_get( 'plugins', 'plugins' );
		if ( ! empty( $cache_plugins ) ) {
			$cache_plugins[''][ $plugin_file ] = $plugin_data;
			wp_cache_set( 'plugins', $cache_plugins, 'plugins' );
		}

		# Update JEDI Import Setting With Plugin File
		if( isset( $jedi_import_options['activate_plugins'][ $slug ] ) ) {
			$jedi_import_options['activate_plugins'][ $slug ] = $plugin_file;
			update_option( 'jedi_import_options', $jedi_import_options );
		}

		$report_installed++;
		jswj_jedi_log( 'Plugin Installed', $plugin_file );
	}

	return $report_installed;
} // END jedi_install_repo_plugins()


/**
 * Install Selected Plugins Not Found In The Repository
 *
 * @uses wp_filesystem
 * @uses get_plugins()
 * @uses wp_cache_get() and wp_cache_set()
 **/
function jswj_jedi_install_nonrepo_plugins() {

	$jedi_import_options = get_option( 'jedi_import_options' );
	$install_plugins = $jedi_import_options['install_nonrepo_plugins'];
	$report_installed = 0;

	$plugin_import_path = JEDI_APPRENTICE_PATH . 'demo-data/anyplugin/';

	foreach( $install_plugins as $install_plugin ) {

		$plugin_install_path = WP_PLUGIN_DIR . '/' . $install_plugin . '/';
		# Check if plugin exists to avoid overwrite
		if( file_exists( substr( $plugin_install_path, 0, - 1 ) ) ) {
			continue;
		}

		# Create Plugin Folder
		if( ! wp_mkdir_p( $plugin_install_path ) ) {
			jswj_jedi_order_66( 'Unable to create Plugin Folder: ' . $plugin_install_path );
		}

		# Duplicate The Plugin Folder
		$safe_jedi = copy_dir( $plugin_import_path . $install_plugin . '/', $plugin_install_path );
		if( is_wp_error( $safe_jedi ) ) {
			jswj_jedi_order_66( 'Failed to copy Plugin folder', $safe_jedi );
		}

		$plugin_data = get_plugins( '/' . $install_plugin );
		$plugin_file = $install_plugin . '/' . key( $plugin_data );

		# Update WP Plugin Data Cache
		$cache_plugins = wp_cache_get( 'plugins', 'plugins' );
		if ( ! empty( $cache_plugins ) ) {
			$cache_plugins[''][ $plugin_file ] = $plugin_data;
			wp_cache_set( 'plugins', $cache_plugins, 'plugins' );
		}

		# Update JEDI Import Setting With Plugin File
		if( isset( $jedi_import_options['activate_plugins'][ $install_plugin ] ) ) {
			$jedi_import_options['activate_plugins'][ $install_plugin ] = $plugin_file;
			update_option( 'jedi_import_options', $jedi_import_options );
		}

		$report_installed++;
		jswj_jedi_log( 'Plugin Installed', $plugin_file );
	}

	return $report_installed;
} # END jedi_install_nonrepo_plugins()


/**
 * Activate Selected Plugins
 *
 * @uses activate_plugins()
 **/
function jswj_jedi_activate_plugins() {
	jswj_verify_ajax_nonce_and_capability( 'jswj_import_process_nonce', 'jedi_ajax_nonce' );

	$jedi_import_options = get_option( 'jedi_import_options' );
	$activate_plugins = $jedi_import_options['activate_plugins'];
	$report_activated = 0;

	ob_start();
	$safe_jedi = activate_plugins( $activate_plugins, '', is_network_admin(), false );
	ob_end_clean();

	if( ! is_wp_error( $safe_jedi ) ) {
		jswj_jedi_log( count( $activate_plugins ) . ' Plugins Activated' );
		jswj_ajax_response( array( 1, count( $activate_plugins ) . ' Plugins Activated' ) );
	} else {
		jswj_jedi_log( 'Error Activating Plugins', serialize( $safe_jedi ) );
		jswj_ajax_response( array( 0, 'Error Activating Plugins' ) );
	}

	wp_die();
} # END jswj_jedi_activate_plugins()
add_action( 'wp_ajax_jswj_jedi_activate_plugins', 'jswj_jedi_activate_plugins' );
