<?php
/**
 * Easy Demo Import
 *
 * Functions used by both Master & Apprentice
 *
 * @package    jedi-master
 * @author     Jerry Simmons <jerry@ferventsolutions.com>
 * @copyright  2021 Jerry Simmons
 * @license    GPL-2.0+
 **/

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Writes logging information to file
 *
 * @param string $log_trigger - Provides information about what calls the function.
 * @param mixed  $log_data - Optional. Provides extra data about the logged event.
 **/
function jswj_jedi_log( $log_trigger, $log_data = '' ) {

	$jedi_log = get_option( 'jswj_jedi_log' );
	if( false === $jedi_log ) {
		$jedi_log = array();
	} else {
		$jedi_log = json_decode( $jedi_log );
	}

	$entries = count( $jedi_log );
	if( $entries > 500 ) {
		$jedi_log = array_slice( $jedi_log, $entries - 500 );
	}

	# Convert Log Data As Needed
	if( is_wp_error( $log_data ) ) {
		$log_data = $log_data->get_error_message();
	} elseif( is_array( $log_data ) || is_object( $log_data ) ) {
		$log_data = wp_json_encode( $log_data );
	}

	# Prepare Log Entry
	$log_entry = array(
		'time'    => gmdate( 'Y-m-d H:i:s' ),
		'trigger' => $log_trigger,
		'data'    => $log_data,
	);
	$jedi_log[] = $log_entry;

	update_option( 'jswj_jedi_log', wp_json_encode( $jedi_log ) );

} # END jswj_jedi_log()

/**
 * Display JEDI Log In HTML Table
 *
 * @return void
 **/
function jswj_display_jedi_log() {
	$jedi_log = get_option( 'jswj_jedi_log' );
	if( false === $jedi_log ) {
		echo 'No log data exists';
	} else {
		$jedi_log = json_decode( $jedi_log );
		if( is_array( $jedi_log ) ) {
			echo '<table class="jedi_admin_log">';
			foreach( $jedi_log as $log_entry ) {
				echo '<tr>';
					echo '<td style="width:25%">' . esc_html( $log_entry->time ) . '</td>';
					echo '<td style="width:25%">' . esc_html( $log_entry->trigger ) . '</td>';
					echo '<td style="width:50%">' . esc_html( $log_entry->data ) . '</td>';
				echo '</tr>';
			}
			echo '</table>';
		} else {
			echo '<p>Invalid Log Data</p>';
			echo wp_json_encode( $jedi_log );
		}
	}
} # END jswj_display_jedi_log()

/**
 * Log Errors That Cause A JEDI Process To Die
 * Then display error message to user
 *
 * @param string $error_message Optional. The error message.
 * @param mixed  $error_data Optional. Provides extra data about the error.
 **/
function jswj_jedi_order_66( $error_message = '', $error_data = '' ) {
	jswj_jedi_log( 'order_66', $error_message );

	if( is_wp_error( $error_data ) ) {
		jswj_jedi_log( 'Error Codes', $error_data->errors );
		jswj_jedi_log( 'Error Data', $error_data->error_data );
	} else {
		jswj_jedi_log( 'Error Data: ', $error_data );
	}

	wp_die( esc_attr( $error_message ) );
}

/**
 * Get System Info For Diagnosing Purposes
 *
 * @return array $system_info
 **/
function jswj_jedi_get_system_info() {
	if( ! jswj_is_user_capable() ) { return; }

	$system_info = array();

	# OS Info
	$system_info['php_os'] = PHP_OS;
	$system_info['php_version'] = PHP_VERSION;

	# INI Settings
	$system_info['allow_url_fopen'] = ini_get( 'allow_url_fopen' );
	$system_info['max_execution_time'] = ini_get( 'max_execution_time' );
	$system_info['max_file_uploads'] = ini_get( 'max_file_uploads' );
	$system_info['memory_limit'] = ini_get( 'memory_limit' );

	# WordPress Info
	$system_info['wp_version'] = get_bloginfo( 'version' );
	$system_info['wp_url'] = get_bloginfo( 'url' );

	# Theme Info
	$wp_theme = wp_get_theme();
	if( ! is_multisite() ) {
		if( is_child_theme() ) {
			if( $wp_theme->parent() !== false ) {
				$system_info['wp_theme_name'] = $wp_theme->parent()->get( 'Name' );
				$system_info['wp_theme_version'] = $wp_theme->parent()->get( 'Version' );
			}
			$system_info['wp_childtheme_name'] = $wp_theme->get( 'Name' );
			$system_info['wp_childtheme_version'] = $wp_theme->get( 'Version' );
		} else {
			$system_info['wp_theme_name'] = $wp_theme->get( 'Name' );
			$system_info['wp_theme_version'] = $wp_theme->get( 'Version' );
		}
	} else {
		$system_info['is_multisite'] = is_multisite();
	}

	if( ! function_exists( 'get_plugins' ) ) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}
	$system_info['installed_plugins'] = get_plugins();
	$system_info['active_plugins'] = get_option( 'active_plugins' );

	return $system_info;
} # END jswj_jedi_get_system_info()


/**
 * Systems Check Before Export
 *
 * @return string $return_html
 */
function jswj_jedi_systems_check() {
	if( ! jswj_is_user_capable() ) { return; }

	$warnings = array();

	if( floatval( PHP_VERSION ) < 5.6 ) {
		$warnings[] = 'PHP Version Is Lower Than Recommended: ' . PHP_VERSION;
	}
	if( intval( ini_get( 'memory_limit' ) ) < 64 ) {
		$warnings[] = 'Memory Limit Is Lower Than Recommended: ' . ini_get( 'memory_limit' );
	}
	if( intval( ini_get( 'max_execution_time' ) ) < 30 ) {
		$warnings[] = 'Max Execution Time Is Lower Than Recommended: ' . ini_get( 'max_execution_time' );
	}

	if( class_exists( 'JEDI_Master_Admin' ) ) {
		$missing_media_files = jswj_export_check_media_database();
		$count = count( $missing_media_files );
		if( $count > 0 ) {
			$warnings[] = '<strong>Media Library Mismatch Notice:</strong> ' . $count . ' item(s) in the Media Library are missing the corresponding files. Recommended Action - Clean up media library before exporting. Specific file details can be found in the log file found in the <a href="' . admin_url() . 'admin.php?page=jedi_master_menu_support">Support page</a>.';

			foreach( $missing_media_files as $media_file ) {
				jswj_jedi_log( 'Media Library Mismatch - Missing File: ', $media_file );
			}
		}
	}

	if( class_exists( 'JEDI_Apprentice_Admin' ) ) {
		$jedi_apprentice_settings = get_option( 'jedi_apprentice_settings' );

		#Check Access To WordPress Repository
		if( isset( $jedi_apprentice_settings['selected_plugins'] ) ) {
			$get_plugin_repo_data = array(
				'action'  => 'plugin_information',
				// phpcs:ignore
				'request' => serialize(
					(object) array(
						'slug'   => 'akismet',
						'fields' => array( 'description' => true ),
					)
				),
			);
			$repo_data = wp_remote_post(
				'http://api.wordpress.org/plugins/info/1.0/',
				array( 'body' => $get_plugin_repo_data )
			);
			if( is_wp_error( $repo_data ) ) {
				$warnings[] = 'Unable To Connect To The WordPress Plugin Repository. Recommended Plugins May Not Install.';
			}
		}
	}

	if( empty( $warnings ) ) { return; }

	$return_html = '<div class="jedi_systems_check">';
		$return_html .= '<h4>Easy Demo Import Systems Check</h4>';
		foreach( $warnings as $warning ) {
			$return_html .= '<p class="jedi_systems_check_warning">' . $warning . '</p>';
		}

	$return_html .= '</div>';

	return $return_html;
} # END jswj_jedi_systems_check()




/**
 * Gets all the media information and stores it in the export array
 **/
function jswj_export_check_media_database() {

	# Export The Media Database
	$query_images_args = array(
		'post_type'      => 'attachment',
		'post_mime_type' => 'image',
		'post_status'    => 'inherit',
		'posts_per_page' => -1,
	);
	$query_images = new WP_Query( $query_images_args );

	$missing_media_files = array();

	# Updates the GUID with the current URL - Helps imported images
	foreach( $query_images->posts as $image ) {
		$image_path = get_attached_file( $image->ID );
		if( ! file_exists( $image_path ) ) {
			$missing_media_files[] = $image_path;
		}
	}

	return $missing_media_files;

} # END jswj_export_check_media_database()

/**
 * Resume Import Form
 *
 * @return void
 */
function jswj_jedi_resume_import_form() {
	echo '<div class="catch_incomplete_import_form">';
		echo '<h2>The Previous Import Did Not Complete Successfully</h2>';
		echo '<p>Would you like to try again?</p><br><br>';

		echo '<div class="jedi_import_options_container">';
			echo '<form action="admin.php?page=jedi_apprentice_menu" method="POST">';
				wp_nonce_field( 'jedi_apprentice_menu_resume', '_jedi_apprentice_nonce' );
				echo '<input type="hidden" value="true" name="jedi_previous_import_buttons" />';
				submit_button( 'Resume Previous Import', 'primary', 'resume_previous_import', false );
				echo esc_attr( str_repeat( '&nbsp;', 20 ) );
				submit_button( 'Cancel Previous Import', 'delete', 'cancel_previous_import', false );
			echo '</form>';
		echo '</div>';

	echo '</div>';
} # END resume_import_form()


/**
 * Convert hex color to RGBA and darken the color
 *
 * @param [type] $hex_string - Hex Color String.
 * @param [type] $darker - Darken By Percent.
 *
 * @return false / string
 **/
function jswj_hex_shades( $hex_string, $darker ) {
	$hex_string = preg_replace( '/[^0-9A-Fa-f]/', '', $hex_string ); # Gets a proper hex string
	$rgb_array = array();
	if( strlen( $hex_string ) === 6 ) {
		$rgb_array['red'] = dechex( hexdec( substr( $hex_string, 0, 2 ) ) * ( 1 - $darker ) );
		$rgb_array['green'] = dechex( hexdec( substr( $hex_string, 2, 2 ) ) * ( 1 - $darker ) );
		$rgb_array['blue'] = dechex( hexdec( substr( $hex_string, 4, 2 ) ) * ( 1 - $darker ) );
		$rgb_array['red'] = strlen( $rgb_array['red'] ) >= 2 ? $rgb_array['red'] : '0' . $rgb_array['red'];
		$rgb_array['green'] = strlen( $rgb_array['green'] ) >= 2 ? $rgb_array['green'] : '0' . $rgb_array['green'];
		$rgb_array['blue'] = strlen( $rgb_array['blue'] ) >= 2 ? $rgb_array['blue'] : '0' . $rgb_array['blue'];
	} else {
		return false; # Invalid hex color code
	}
	return '#' . $rgb_array['red'] . $rgb_array['green'] . $rgb_array['blue'];
}

/**
 * Write JSON Response & Die
 *
 * @param array $json_response - Content To Write.
 *
 * @return void
 **/
function jswj_ajax_response( $json_response = array() ) {
	jswj_jedi_log( 'jswj_ajax_response', $json_response );
	echo 'jswj_response_start' . wp_json_encode( $json_response ) . 'jswj_response_end';
	wp_die();
}


add_action( 'wp_ajax_jswj_ajax_log', 'jswj_ajax_log' );
/**
 * Update JEDI Log From AJAX Call
 *
 * @return void
 **/
function jswj_ajax_log() {
	jswj_verify_ajax_nonce_and_capability( 'jediExportProcessNonce', 'jedi_ajax_nonce' );

	if( ! jswj_is_user_capable() ) { return; }

	// phpcs:disable WordPress.Security.NonceVerification.Missing
	if( ! isset( $_POST['logText'] ) ) { return; }
	$log_text = sanitize_text_field( $_POST['logText'] );
	// phpcs:enable WordPress.Security.NonceVerification.Missing

	jswj_jedi_log( 'jswj_ajax_log', $log_text );
} # END jswj_ajax_log()


/**
 * Process Exclude Post IDs Field
 *
 * @param string  $excluded_id_list - List Of IDs From Field.
 * @param boolean $return_array - Optional Return Format.
 *
 * @return array / string
 **/
function jswj_jedi_process_post_id_list( $excluded_id_list = '', $return_array = false ) {
	if( empty( $excluded_id_list ) ) { return ''; }

	# Remove any whitespace
	$excluded_id_list = str_replace( ' ', '', $excluded_id_list );

	# Remove trailing comma(s)
	if ( substr( $excluded_id_list, -1 ) === ',' ) {
		$excluded_id_list = rtrim( $excluded_id_list, ',' );
	}

	$validate_posts = array();
	$validate_posts = explode( ',', $excluded_id_list );

	foreach( $validate_posts as $key => $validate_post ) {
		if( false === get_post_status( $validate_post ) ) {
			unset( $validate_posts[ $key ] );
		}
	}

	if( ! empty( $validate_posts ) ) {
		if( true === $return_array ) {
			$excluded_id_list = $validate_posts;
		} else {
			$excluded_id_list = implode( ',', $validate_posts );
		}
	} else {
		$excluded_id_list = '';
	}

	return $excluded_id_list;
} # END jedi_process_post_id_list()


/**
 * Process Include WP Option By Slug Field
 *
 * @param string  $option_slug_list - List Of Slugs From Field.
 * @param boolean $return_array - Optional Return Format.
 *
 * @return array / string
 **/
function jswj_jedi_process_option_slug_list( $option_slug_list = '', $return_array = false ) {

	if( empty( $option_slug_list ) ) { return ''; }

	# Remove trailing comma(s)
	if ( substr( $option_slug_list, -1 ) === ',' ) {
		$option_slug_list = rtrim( $option_slug_list, ',' );
	}

	$validate_options = explode( ',', $option_slug_list );

	# Loop Through WP Options, Remove Any That Do Not Exist In The wp_options Table
	foreach( $validate_options as $key => $validate_option ) {
		if( false === get_option( $validate_option ) ) {
			unset( $validate_options[ $key ] );
		}
	}

	if( ! empty( $validate_options ) ) {
		if( true === $return_array ) {
			$option_slug_list = $validate_options;
		} else {
			$option_slug_list = implode( ',', $validate_options );
		}
	} else {
		if( true === $return_array ) {
			$option_slug_list = array();
		} else {
			$option_slug_list = '';
		}
	}

	return $option_slug_list;
} # END jedi_process_post_id_list()


/**
 * Check if Divi Theme or Plugin is active
 **/
function jswj_is_divi() {
	$is_divi = false;
	if( function_exists( 'et_setup_theme' ) ) { $is_divi = true; }
	if( defined( 'ET_BUILDER_THEME' ) ) { $is_divi = true; }
	if( defined( 'ET_BUILDER_PLUGIN_DIR' ) ) { $is_divi = true; }

	return $is_divi;
}


/**
 * Check if Elementor Plugin is active
 **/
function jswj_is_elementor() {
	$is_elementor = false;
	if( defined( 'ELEMENTOR_VERSION' ) ) { $is_elementor = true; }
	if( defined( 'ELEMENTOR_PRO_VERSION' ) ) { $is_elementor = true; }

	return $is_elementor;
}

/**
 * Check if Elementor Pro Plugin is active
 **/
function jswj_is_elementor_pro() {
	$is_elementor_pro = false;
	if( defined( 'ELEMENTOR_PRO_VERSION' ) ) { $is_elementor_pro = true; }

	return $is_elementor_pro;
}

/**
 * Check if WooCommerce Plugin is active
 */
function jswj_is_woocommerce() {
	return is_plugin_active( 'woocommerce/woocommerce.php' );
}

/**
 * Check If Current User Has Import Capability
 *
 * @return bool
 **/
function jswj_is_user_capable() {
	if( is_user_logged_in() && current_user_can( 'import' ) ) {
		return true;
	} else if( wp_doing_ajax() ) {
		wp_die( 'Invalid User Action' );
		return false;
	} else {
		return false;
	}
} # END jswj_is_user_capable()

/**
 * Verify/Check For Valid Nonce & User Capability
 *
 * Returns true or will die() on fail.
 *
 * @param string $nonce_action nonce action name.
 * @param string $nonce_id nonce query arg name.
 *
 * @return bool true or die on fail
 **/
function jswj_verify_nonce_and_capability( $nonce_action, $nonce_id ) {
	if( isset( $_POST[ $nonce_id ] )
		&& check_admin_referer( $nonce_action, $nonce_id ) !== false
		&& jswj_is_user_capable()
		) {
			return true;
	} else {
		wp_die( 'Invalid User Action' );
		return false;
	}
} # END jswj_verify_nonce_and_capability()


/**
 * Verify/Check For Valid AJAX Nonce & User Capability
 *
 * Returns true or will die() on fail.
 *
 * @param string $nonce_action nonce action name.
 * @param string $nonce_id nonce query arg name.
 *
 * @return bool true or die on fail
 **/
function jswj_verify_ajax_nonce_and_capability( $nonce_action, $nonce_id ) {
	if( check_ajax_referer( $nonce_action, $nonce_id, false ) !== false
		&& jswj_is_user_capable()
		) {
			return true;
	} else {
		jswj_ajax_response( array( 'ajax_error' => 'Invalid User Action' ) );
		wp_die();
	}
} # END jswj_verify_ajax_nonce_and_capability()
