<?php
/**
 * Easy Demo Import
 *
 * @package    jedi-master
 * @author     Jerry Simmons <jerry@ferventsolutions.com>
 * @copyright  2021 Jerry Simmons
 * @license    GPL-2.0+
 **/

if( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Get Import Data From File
 *
 * @returns array
 **/
function jswj_get_jedi_import_data() {
	if( ! jswj_is_user_capable() ) { return; }

	# Read Import Data from file & store in array
	$jedi_import_file = JEDI_APPRENTICE_PATH . 'demo-data/jedi_data_export.dat';
	if( file_exists( $jedi_import_file ) ) {
		$jedi_apprentice_data_import = unserialize( file_get_contents( $jedi_import_file ) );
		if( false === $jedi_apprentice_data_import ) {
			jswj_jedi_order_66( 'Unable to load import data' );
		}
	} else {
		jswj_jedi_order_66( 'Unable to load settings file', $jedi_import_file );
	}

	return $jedi_apprentice_data_import;
} # END jswj_get_jedi_import_data()


/**
 * Initialize Import Tracking
 **/
function jswj_jedi_tracking_init() {

	$jedi_apprentice_data_import = jswj_get_jedi_import_data();
	$track_import = get_option( 'jedi_track_import' );

	if( false === $track_import || ! isset( $track_import['imported_media'] ) ) {
		$track_import = array(
			'queued_stats'        => array(),
			'imported_stats'      => array(),
			'imported_posts'      => array(),
			'imported_postmeta'   => array(),
			'imported_media'      => array(
				'urls' => array(),
				'ids'  => array(),
			),
			'imported_categories' => array(),
			'categories_posts'    => array(),
			'imported_menu_names' => array(),
			'imported_menu_items' => array(),
			'data'                => $jedi_apprentice_data_import,
			'media'               => $jedi_apprentice_data_import['media'],
		);

		update_option( 'jedi_track_import', $track_import );
	}

	return $track_import;
} # END jswj_jedi_tracking_init()


/**
 * Enqueue Import Script
 *
 * @param string $hook_suffix - WP Admin Page Slug.
 **/
function jswj_prepare_import_script( $hook_suffix ) {
	if( 'toplevel_page_jedi_apprentice_menu' !== $hook_suffix ) { return; }
	set_time_limit( 300 );

	$jedi_apprentice_settings = get_option( 'jedi_apprentice_settings' );
	$jedi_import_options = get_option( 'jedi_import_options' );
	$import_data = jswj_get_jedi_import_data();

	$install_plugins = array();
	$install_nonrepo_plugins = array();
	$activate_plugins = array();

	if( isset( $jedi_import_options['install_plugins'] ) ) {
		$install_plugins = $jedi_import_options['install_plugins'];
	}
	if( isset( $jedi_import_options['install_nonrepo_plugins'] ) ) {
		$install_nonrepo_plugins = $jedi_import_options['install_nonrepo_plugins'];
	}
	if( isset( $jedi_import_options['activate_plugins'] ) ) {
		$activate_plugins = $jedi_import_options['activate_plugins'];
	}

	$import_stats = array();
	$import_stats['plugins'] = array(
		'install'  => count( $install_plugins ) + count( $install_nonrepo_plugins ),
		'activate' => count( $activate_plugins ),
	);
	if( isset( $import_data['posts'] ) ) {
		$import_stats['posts'] = array(
			'current' => 0,
			'total'   => count( $import_data['posts'] ),
		);
	}
	if( isset( $import_data['media'] ) ) {
		$import_stats['media'] = array(
			'current' => 0,
			'total'   => count( $import_data['media'] ),
		);
	}
	if( isset( $import_data['categories'] ) ) {
		$import_stats['categories'] = array(
			'current' => 0,
			'total'   => count( $import_data['categories']['categories'] ),
		);
	}
	if( isset( $import_data['menus'] ) ) {
		$import_stats['menus'] = array(
			'current' => 0,
			'total'   => count( $import_data['menus'][0] ),
		);
	}

	if( $jedi_apprentice_settings['installer_style'] ) {
		wp_register_script(
			'jedi_apprentice_import_script',
			JEDI_APPRENTICE_URL . 'includes/jedi-apprentice-import-functions.js',
			array( 'jquery' ),
			$jedi_apprentice_settings['jedi_master_version'],
			false
		);
	} else {
		wp_register_script(
			'jedi_apprentice_import_script',
			jswj_get_jedi_apprentice_url() . 'includes/jedi-apprentice-import-functions.js',
			array( 'jquery' ),
			$jedi_apprentice_settings['jedi_master_version'],
			false
		);
	}

	wp_localize_script(
		'jedi_apprentice_import_script',
		'jedi_ajax_vars',
		array(
			'jedi_import_options'        => $jedi_import_options,
			'import_stats'               => $import_stats,
			'jswj_import_settings_nonce' => wp_create_nonce( 'jswj_import_settings_nonce' ),
			'jswj_import_process_nonce'  => wp_create_nonce( 'jswj_import_process_nonce' ),
			'max_execution_time'         => ini_get( 'max_execution_time' ),
		)
	);

} # END jswj_prepare_import_script()
add_action( 'admin_enqueue_scripts', 'jswj_prepare_import_script' );

/**
 * AJAX Prepare HTML Import Report
 **/
function jswj_jedi_import_report() {
	// phpcs:ignore WordPress.Security.NonceVerification.Missing
	if( ! isset( $_POST['jedi_import_button'] ) && ! isset( $_POST['resume_previous_import'] ) && ! isset( $_POST['cancel_previous_import'] ) ) {
		return;
	}
	jswj_verify_nonce_and_capability( 'jedi_apprentice_menu', '_jedi_apprentice_nonce' );

	wp_enqueue_script( 'jedi_apprentice_import_script' );

	$jedi_apprentice_settings = get_option( 'jedi_apprentice_settings' );
	$jedi_import_options = get_option( 'jedi_import_options' );

	$install_plugin_count = 0;
	$activate_plugin_count = 0;
	if( isset( $jedi_import_options['install_plugins'] ) ) {
		$install_plugin_count = count( $jedi_import_options['install_plugins'] );
	}
	if( isset( $jedi_import_options['install_nonrepo_plugins'] ) ) {
		$install_plugin_count += count( $jedi_import_options['install_nonrepo_plugins'] );
	}
	if( isset( $jedi_import_options['activate_plugins'] ) ) {
		$activate_plugin_count = count( $jedi_import_options['activate_plugins'] );
	}

	# Gradient Progress Bar
	$accent_color = esc_attr( $jedi_import_options['import_accent_color'] );
	$accent_color2 = jswj_hex_shades( $accent_color, .25 );
	$accent_color3 = jswj_hex_shades( $accent_color, .75 );

	echo '<style>'
		. '.jedi_status::after { background-color: ' . esc_attr( $accent_color ) . '; opacity: .25; } '
		. '.importing_now .jedi_status { '
		. 'background: linear-gradient(-45deg, ' . esc_attr( $accent_color ) . ', ' . esc_attr( $accent_color2 ) . ', ' . esc_attr( $accent_color3 ) . ') '
		. '!important; }'
		. '.importing_now .jedi_import_icon { border-top-color: ' . esc_attr( $accent_color ) . '; }'
		. '.jedi_import_icon { color: ' . esc_attr( $accent_color ) . '; }'
		. '</style>';

	echo '<div class="jedi_import_report_container">';
		echo '<h2>Importing Demo Content...</h2>';

		echo '<p id="jedi_plugins_install" class="jedi_import_report_line" '
			. 'data-install="' . esc_attr( $install_plugin_count ) . '">'
			. '<span class="jedi_import_icon"></span>'
			. '<span class="jedi_label"></span>'
			. '<span class="jedi_stat"></span>'
			. '<span class="jedi_status"></span></p>';
		echo '<p id="jedi_plugins_activate" class="jedi_import_report_line" '
			. 'data-activate="' . esc_attr( $activate_plugin_count ) . '">'
			. '<span class="jedi_import_icon"></span>'
			. '<span class="jedi_label"></span>'
			. '<span class="jedi_stat"></span>'
			. '<span class="jedi_status"></span></p>';
		echo '<p id="jedi_media_import" class="jedi_import_report_line">'
			. '<span class="jedi_import_icon"></span>'
			. '<span class="jedi_label"></span>'
			. '<span class="jedi_stat"></span>'
			. '<span class="jedi_status"></span></p>';
		echo '<p id="jedi_categories_import" class="jedi_import_report_line">'
			. '<span class="jedi_import_icon"></span>'
			. '<span class="jedi_label"></span>'
			. '<span class="jedi_stat"></span>'
			. '<span class="jedi_status"></span></p>';
		echo '<p id="jedi_posts_import" class="jedi_import_report_line">'
			. '<span class="jedi_import_icon"></span>'
			. '<span class="jedi_label"></span>'
			. '<span class="jedi_stat"></span>'
			. '<span class="jedi_status"></span></p>';
		echo '<p id="jedi_homepage_import" class="jedi_import_report_line">'
			. '<span class="jedi_import_icon"></span>'
			. '<span class="jedi_label"></span>'
			. '<span class="jedi_stat"></span>'
			. '<span class="jedi_status"></span></p>';
		echo '<p id="jedi_divioptions_import" class="jedi_import_report_line">'
			. '<span class="jedi_import_icon"></span>'
			. '<span class="jedi_label"></span>'
			. '<span class="jedi_stat"></span>'
			. '<span class="jedi_status"></span></p>';
		echo '<p id="jedi_divi_theme_builder_import" class="jedi_import_report_line">'
			. '<span class="jedi_import_icon"></span>'
			. '<span class="jedi_label"></span>'
			. '<span class="jedi_stat"></span>'
			. '<span class="jedi_status"></span></p>';
		echo '<p id="jedi_elementor_options_import" class="jedi_import_report_line">'
			. '<span class="jedi_import_icon"></span>'
			. '<span class="jedi_label"></span>'
			. '<span class="jedi_stat"></span>'
			. '<span class="jedi_status"></span></p>';
		echo '<p id="jedi_css_import" class="jedi_import_report_line">'
			. '<span class="jedi_import_icon"></span>'
			. '<span class="jedi_label"></span>'
			. '<span class="jedi_stat"></span>'
			. '<span class="jedi_status"></span></p>';
		echo '<p id="jedi_menus_import" class="jedi_import_report_line">'
			. '<span class="jedi_import_icon"></span>'
			. '<span class="jedi_label"></span>'
			. '<span class="jedi_stat"></span>'
			. '<span class="jedi_status"></span></p>';
		echo '<p id="jedi_widgets_import" class="jedi_import_report_line">'
			. '<span class="jedi_import_icon"></span>'
			. '<span class="jedi_label"></span>'
			. '<span class="jedi_stat"></span>'
			. '<span class="jedi_status"></span></p>';
		echo '<p id="jedi_wp_options_import" class="jedi_import_report_line">'
			. '<span class="jedi_import_icon"></span>'
			. '<span class="jedi_label"></span>'
			. '<span class="jedi_stat"></span>'
			. '<span class="jedi_status"></span></p>';
		echo '<h3 id="jedi_import_complete"></h3>';
	echo '</div>';
	echo '<div id="jedi_import_alerts_container">';
	echo '</div>';

} # END jswj_jedi_import_report()
add_action( 'toplevel_page_jedi_apprentice_menu', 'jswj_jedi_import_report', 999 );

/**
 * AJAX Get Import Options
 **/
function jswj_get_import_options() {
	jswj_verify_ajax_nonce_and_capability( 'jswj_import_settings_nonce', 'jedi_ajax_nonce' );

	$jedi_apprentice_settings = get_option( 'jedi_apprentice_settings' );
	$jedi_import_options = get_option( 'jedi_import_options' );
	$jedi_import_options_log = $jedi_import_options;

	if( isset( $jedi_apprentice_settings['extensions'] ) && is_array( $jedi_apprentice_settings['extensions'] ) ) {
		foreach ( $jedi_apprentice_settings['extensions'] as $extension_name => $extension ) {
			if( isset( $extension['extension_path'] ) ) {
				unset( $jedi_apprentice_settings['extensions'][ $extension_name ]['extension_path'] );
			}
			if( isset( $extension['extension_plugin_file'] ) ) {
				unset( $jedi_apprentice_settings['extensions'][ $extension_name ]['extension_plugin_file'] );
			}
		}
	}

	if( isset( $jedi_apprentice_settings['export_paths'] ) ) {
		unset( $jedi_apprentice_settings['export_paths'] );
	}

	if( isset( $jedi_import_options_log['extensions'] ) && is_array( $jedi_import_options_log['extensions'] ) ) {
		foreach ( $jedi_import_options_log['extensions'] as $extension_name => $extension ) {
			if( isset( $extension['extension_path'] ) ) {
				unset( $jedi_import_options_log['extensions'][ $extension_name ]['extension_path'] );
			}
			if( isset( $extension['extension_plugin_file'] ) ) {
				unset( $jedi_import_options_log['extensions'][ $extension_name ]['extension_plugin_file'] );
			}
		}
	}

	if( isset( $jedi_import_options_log['export_paths'] ) ) {
		unset( $jedi_import_options_log['export_paths'] );
	}

	jswj_jedi_log( 'Starting Import Process' );
	jswj_jedi_log( 'jedi_apprentice_settings: ', wp_json_encode( $jedi_apprentice_settings ) );
	jswj_jedi_log( 'jedi_import_options: ', wp_json_encode( $jedi_import_options_log ) );

	jswj_ajax_response( $jedi_import_options );
} # END jswj_get_import_options()
add_action( 'wp_ajax_jswj_get_import_options', 'jswj_get_import_options' );

/**
 * AJAX Import Hook Driver
 **/
function jswj_do_import_hooks() {
	jswj_verify_ajax_nonce_and_capability( 'jswj_import_process_nonce', 'jedi_ajax_nonce' );

	try {

		$hook_name = '';
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		if( isset( $_POST['hook_name'] ) ) {
			$hook_name = sanitize_text_field( $_POST['hook_name'] );
		}
		// phpcs:enable WordPress.Security.NonceVerification.Missing

		if( ! empty( $hook_name ) && has_action( $hook_name ) ) {
			$track_import = get_option( 'jedi_track_import' );

			switch( $hook_name ) {
				case 'jedi_after_media_import':
					do_action( $hook_name, $track_import['imported_media']['ids'] );
					break;
				case 'jedi_after_post_import':
					do_action( $hook_name, $track_import['imported_posts'] );
					break;
				case 'jedi_before_import':
				case 'jedi_before_media_import':
				case 'jedi_before_post_import':
				case 'jedi_after_import':
					do_action( $hook_name );
					break;

				default:
					wp_die();
			}

			jswj_ajax_response( array( 1, 'Action Completed: ' . $hook_name ) );

		} else {
			jswj_ajax_response( array( 0, 'No Action To Do: ' . $hook_name ) );
		}
	} catch( exception $e ) {
		jswj_jedi_log( 'jswj_do_import_hooks() Error', wp_json_encode( $e ) );
	} finally {
		wp_die();
	}
} # END jswj_do_import_hooks()
add_action( 'wp_ajax_jswj_do_import_hooks', 'jswj_do_import_hooks' );

/**
 * AJAX Check To See If Import Completed
 **/
function jswj_after_import_checks() {
	jswj_verify_ajax_nonce_and_capability( 'jswj_import_process_nonce', 'jedi_ajax_nonce' );
	jswj_ajax_response( array( 1, 'Complete Import Verified' ) );

	wp_die();
} # END jswj_after_import_checks()
add_action( 'wp_ajax_jswj_after_import_checks', 'jswj_after_import_checks' );

/**
 * AJAX Trigger Resume Import Form
 **/
function jswj_trigger_resume_import_form() {
	jswj_verify_ajax_nonce_and_capability( 'jswj_import_process_nonce', 'jedi_ajax_nonce' );

	jswj_jedi_resume_import_form();
	wp_die();
}
add_action( 'wp_ajax_jswj_trigger_resume_import_form', 'jswj_trigger_resume_import_form' );

/**
 * Import Media Files Using Batch Size
 **/
function jswj_import_media_batch() {
	jswj_verify_ajax_nonce_and_capability( 'jswj_import_process_nonce', 'jedi_ajax_nonce' );

	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	# Bypass WP's Self-Signed SSL Cert Checks
	add_filter('https_ssl_verify', '__return_false');
	add_filter('https_local_ssl_verify', '__return_false');

	$jedi_import_options = get_option( 'jedi_import_options' );
	$jedi_apprentice_settings = get_option( 'jedi_apprentice_settings' );
	$child_theme_style = $jedi_apprentice_settings['installer_style'];
	$track_import = get_option( 'jedi_track_import' );
	$import_data = jswj_get_jedi_import_data();
	$batch_size = intval( $jedi_apprentice_settings['import_media_batch_size'] );

	$imported_media_count = 0;
	$failed_batch_count = 0;

	// phpcs:disable WordPress.Security.NonceVerification.Missing
	if( isset( $_POST['importedMediaCount'] ) ) {
		$imported_media_count = intval( $_POST['importedMediaCount'] );
	}
	if( isset( $_POST['failedBatchCount'] ) ) {
		$failed_batch_count = intval( $_POST['failedBatchCount'] );
	}
	// phpcs:enable WordPress.Security.NonceVerification.Missing

	# Auto Adjust Batch Size Based On Import Failures
	if( $failed_batch_count > 5 ) { $batch_size = intval( $batch_size * .75 ); }
	if( $failed_batch_count > 7 ) { $batch_size = intval( $batch_size * .5 ); }
	if( $failed_batch_count > 9 ) { $batch_size = 1; }

	$media_batch = array_slice(
		$import_data['media'], # Array
		$imported_media_count, # Offset
		$batch_size,           # Length
		true                   # Preserve Keys
	);

	# Set Correct Paths For Importing Images
	if( $child_theme_style ) {
		$import_media_folder = get_stylesheet_directory_uri() . '/jedi-apprentice/demo-data/media/';
	} else {
		$import_media_folder = JEDI_APPRENTICE_URL . 'demo-data/media/';
	}
	$wp_upload_dir = wp_upload_dir();
	$upload_dir = $wp_upload_dir['path'] . '/';
	$upload_dir_url = $wp_upload_dir['url'] . '/';
	$import_media_dir = JEDI_APPRENTICE_PATH . 'demo-data/media/';

	# Limit Thumbnails For Now, Thumbnails Generated Later With jswj_schedule_thumbnail_creation()
	add_filter(
		'intermediate_image_sizes',
		function() {
			return array( 'thumbnail' );
		},
		999
	);

	# Prevent Thumbnails If Option Is Selected
	if( $jedi_import_options['prevent_thumbnails'] ) {
		add_filter(
			'intermediate_image_sizes',
			function() {
				return array();
			},
			999
		);
	}

	# Prevent Thumbnails If More Than 3 Batches Fail
	if( $failed_batch_count >= 3 ) {
		add_filter(
			'intermediate_image_sizes',
			function() {
				return array();
			},
			999
		);
	}

	foreach( $media_batch as $image_id => $image ) {
		if( isset( $image->importfile ) ) {
			$image_file = $image->importfile;
		} else {
			$image_file = $image->guid;
		}

		# Skip previously imported images
		if( isset( $track_import['imported_media']['ids'][ $image->ID ] ) ) {
			jswj_jedi_log( 'Already Imported Image: [' . $image->ID . '] ' . basename( $image_file ) );
			continue;
		}

		# If File Is In Export Content
		if( file_exists( $import_media_dir . basename( $image_file ) ) ) {

			# Special Handling For SVG Files
			# This is required because media_sideload_image does not use the mime filter
			if( strpos( basename( $image_file ), '.svg' ) !== false ) {

				add_filter( 'upload_mimes', 'jswj_enable_svg_uploads' );

				$source_svg_file = $import_media_dir . basename( $image_file );
				$imported_svg_file = $upload_dir . basename( $image_file );
				$imported_svg_file_url = $upload_dir_url . basename( $image_file );

				$safe_jedi = copy( $source_svg_file, $imported_svg_file );

				# Insert Media If Copy Function Succeeds
				if( false !== $safe_jedi ) {

					$image_args = array(
						'guid'           => $imported_svg_file_url,
						'post_mime_type' => $image->post_mime_type,
						'post_title'     => $image->post_title,
						'post_content'   => '',
						'post_status'    => 'inherit',
					);
					jswj_jedi_log( 'image_args: ', wp_json_encode( $image_args ) );

					$new_id = wp_insert_attachment( $image_args, $imported_svg_file, 0, true );

					# Create Thumbnail & Image Meta Data
					wp_update_attachment_metadata(
						$new_id,
						wp_generate_attachment_metadata( $new_id, $imported_svg_file )
					);
				}
			} else {
				$image_url = $import_media_folder . basename( $image_file );

				# Import Media From File
				$new_id = media_sideload_image(
					$image_url,         # File
					0,                  # Post ID
					$image->post_title, # Title
					'id'                # Return
				);
			}
		} else {
			# If File Is Not In Export Content
			# Import Image From Original Remote URL

			# Make Sure To Use HTTPS URL If Needed
			$image_url = $image->guid;
			if( is_ssl() ) {
				$image_url = str_replace( 'http://', 'https://', $image_url );
			}

			# Import Media From Remote Export Site
			$new_id = media_sideload_image(
				$image_url,         # File
				0,                  # Post ID
				$image->post_title, # Title
				'id'                # Return
			);

		}

		if( is_wp_error( $new_id ) ) {
			jswj_jedi_log( 'Unable to import media file: ' . $image_file, wp_json_encode( $new_id ) );
			continue;
		}

		# Alternate URL - For cases where an image URL is stored in post_content
		$alternate_url = '';
		if( ! empty( $image->post_content ) ) {
			if( esc_url_raw( $image->post_content ) === $image->post_content ) {
				$alternate_url = $image->post_content;
			}
		}

		$track_import['imported_media']['urls'][] = array(
			'oldURL'       => $image->guid,
			'importFile'   => $image_file,
			'alternateURL' => $alternate_url,
			'newURL'       => wp_get_attachment_url( $new_id ),
		);
		$track_import['imported_media']['ids'][ $image->ID ] = $new_id;
		update_option( 'jedi_track_import', $track_import );

		jswj_jedi_log( 'Media Imported: #' . $new_id, basename( $image_file ) . ' -> ' . wp_get_attachment_url( $new_id ) );
	} # END foreach media_batch

	jswj_ajax_response( array( 1, 'Successfully Imported Media Batch' ) );

} # END jswj_import_media_batch()
add_action( 'wp_ajax_jswj_import_media_batch', 'jswj_import_media_batch' );

/**
 * Enable SVG Uploads During Import
 *
 * @param array $mime_types - File Type Array.
 *
 * @return array
 */
function jswj_enable_svg_uploads( $mime_types ) {
	$mime_types['svg'] = 'image/svg+xml';
	return $mime_types;
}

/**
 * Remove Intermediate Image Sizes
 **/
function jswj_remove_intermediate_sizes() {
	return array( 'thumbnail' );
}


/**
 * Schedule Thumbnail Creation For Imported Images
 **/
function jswj_schedule_thumbnail_creation() {
	$jedi_import_options = get_option( 'jedi_import_options' );
	$track_import = get_option( 'jedi_track_import' );
	$jedi_imported_media = $track_import['imported_media'];

	# Prevent Thumbnails If Option Is Selected
	if( $jedi_import_options['prevent_thumbnails'] ) { return; }

	if( ! empty( $jedi_imported_media['ids'] ) ) {
		$count = 0;

		# Loops through imported Image IDs
		foreach( $jedi_imported_media['ids'] as $image_id ) {
			wp_schedule_single_event( time() + 10 + $count, 'jswj_generate_thumbnails', array( $image_id ) );
			$count++;
		}
		jswj_jedi_log( 'Scheduled ' . $count . ' Images For Thumbnail Creation' );
	}
}
add_action( 'jedi_after_import', 'jswj_schedule_thumbnail_creation' );

/**
 * Generate Thumbnails For Images
 *
 * @param string $image_id - WP Image Id.
 **/
function jswj_generate_thumbnails( $image_id ) {
	jswj_jedi_log( 'Generating Thumbnails For Image', $image_id );
	wp_generate_attachment_metadata( $image_id, get_attached_file( $image_id ) );
}
add_action( 'jswj_generate_thumbnails', 'jswj_generate_thumbnails', 10, 1 );


/**
 * Import Posts Using Batch Size
 * 
 * Updated 2021-01 to better handle posts with a lot of postmeta
 * Prevents importing post multiple times when server timeouts occur
 **/
function jswj_import_posts_batch() {
	jswj_verify_ajax_nonce_and_capability( 'jswj_import_process_nonce', 'jedi_ajax_nonce' );

	$jedi_import_options = get_option( 'jedi_import_options' );
	$jedi_apprentice_settings = get_option( 'jedi_apprentice_settings' );
	$child_theme_style = $jedi_apprentice_settings['installer_style'];
	$track_import = get_option( 'jedi_track_import' );

	$import_data = jswj_get_jedi_import_data();
	$postmeta = $import_data['postmeta'];
	$jedi_post_categories = $import_data['categories']['posts'];

	$imported_post_count = 0;
	// phpcs:disable WordPress.Security.NonceVerification.Missing
	if( isset( $_POST['importedPostCount'] ) ) {
		$imported_post_count = intval( $_POST['importedPostCount'] );
	}
	if( isset( $_POST['importErrors'] ) ) {
		jswj_jedi_log( 'importErrors: ', intval( $_POST['importErrors'] ) );
	}
	// phpcs:enable WordPress.Security.NonceVerification.Missing

	# If Media were imported, load imported data
	$process_media = false;
	if( count( $track_import['imported_media']['ids'] ) > 0 ) {
		$process_media = true;
		$jedi_imported_media = $track_import['imported_media'];
		$jedi_update_image_urls = $jedi_imported_media['urls'];
		$jedi_update_image_ids = $jedi_imported_media['ids'];
	}

	# Do Postmeta Filter?
	$filter_postmeta_content = false;
	if( jswj_is_elementor() ) { $filter_postmeta_content = true; }

	$post_batch = array_slice(
		$import_data['posts'],                          # Array
		$imported_post_count,                           # Offset
		$jedi_apprentice_settings['import_batch_size'], # Length
		true                                            # Preserve Keys
	);

	# Loop Through Batch Of Posts - IMPORT POST
	foreach( $post_batch as $post_id => $post ) {
		$old_post_id = $post->ID;
		$post->old_post_id = $post->ID;
		$post->ID = 0;
		$post->import_id = mt_rand( 90000, 99999 );

		# Skip Posts Already Imported
		if( isset( $track_import['imported_posts'][ $old_post_id ] ) 
			&& isset( $track_import['imported_postmeta'][ $old_post_id ] )
			) {
				jswj_jedi_log( 'Already Imported Post & Postmeta: [' . $old_post_id . '] ' . $post->post_title, $track_import['imported_posts'][ $old_post_id ] );
				continue;
		}

		if( ! isset( $track_import['imported_posts'][ $old_post_id ] ) ) {
			# Skip this post of Post Type does not exist
			if( ! post_type_exists( $post->post_type ) && 'et_pb_layout' !== $post->post_type ) {
				jswj_jedi_log(
					'Warning: Post Type Does Not Exist',
					'Post ID: ' . $old_post_id . ' - Post-Type: ' . $post->post_type
				);
				continue;
			}

			# Update Image URLs In Content
			# First checks if any Media were imported
			if( $process_media ) {
				$post->post_content = jswj_replace_image_urls( $post->post_content );
			}

			# Filter: jedi_modify_post_content
			if( has_filter( 'jedi_modify_post_content' ) ) {
				$post->post_content = apply_filters( 'jedi_modify_post_content', $post->post_content );
			}

			# Insert New Post from Import Data
			$new_post_id = wp_insert_post( $post, true );
			if( is_wp_error( $new_post_id ) ) {
				jswj_jedi_log( 'Failed to import post', $new_post_id );
				jswj_ajax_response( array( 0, 'Failed to import post: [' . $old_post_id . '] ' . serialize( $new_post_id ) ) );
			}

			# Store Imported Post IDs
			$track_import['imported_posts'][ $old_post_id ] = $new_post_id;
			update_option( 'jedi_track_import', $track_import );
		} else {
			jswj_jedi_log( 'Already Imported Post: [' . $old_post_id . '] ' . $post->post_title, $track_import['imported_posts'][ $old_post_id ] );
			$new_post_id = $track_import['imported_posts'][ $old_post_id ];
		}
	} # END foreach post_batch POSTS

	# Loop Through Batch Of Posts - IMPORT POSTMETA
	foreach( $post_batch as $post_id => $post ) {
		# Safety Checks - Postmeta imports can bog down the server
		if( !isset( $post->old_post_id ) ) { continue; }
		if( !isset( $track_import['imported_posts'][ $post->old_post_id ] ) ) { continue; }
		if( isset( $track_import['imported_postmeta'][ $post->old_post_id ] ) ) { continue; }

		$old_post_id = $post->old_post_id;
		$new_post_id = $track_import['imported_posts'][ $old_post_id ];

		# Import Post Meta
		if( ! isset( $track_import['imported_postmeta'][ $old_post_id ] ) ) {
			if( isset( $postmeta[ $old_post_id ] ) ) {

				if( is_array( $postmeta[ $old_post_id ] ) ) {
					foreach( $postmeta[ $old_post_id ] as $key => $meta_value ) {
		
						if( null !== $meta_value[0] ) {
							if( is_serialized( $meta_value[0] ) ) {
								$meta_value[0] = unserialize( $meta_value[0] );
							}
		
							$meta_value[0] = jswj_replace_image_urls( $meta_value[0] );
		
							# Filter: jedi_elementor_postmeta_content
							if( $filter_postmeta_content ) {
								if( has_filter( 'jedi_filter_postmeta_content' ) ) {
									$meta_value[0] = apply_filters(
										'jedi_filter_postmeta_content',
										$meta_value[0]
									);
								}
							}
		
							update_post_meta( $new_post_id, $key, $meta_value[0] );
						}
					}

					# Store Post IDs To Track Postmeta Import
					$track_import['imported_postmeta'][ $old_post_id ] = $new_post_id;
					update_option( 'jedi_track_import', $track_import );
				}
			}
		} else {
			jswj_jedi_log( 'Already Imported Postmeta: [' . $old_post_id . '] ' . $post->post_title, $track_import['imported_posts'][ $old_post_id ] );
		}

	} # END foreach post_batch POSTMETA

	jswj_ajax_response( array( 1, 'Successfully Imported Post Batch' ) );

} # END jswj_import_posts_batch()
add_action( 'wp_ajax_jswj_import_posts_batch', 'jswj_import_posts_batch' );


/**
 * Search And Replace Image URLs Within Content
 *
 * @param string $content - Post Content.
 *
 * @return string
 **/
function jswj_replace_image_urls( $content ) {
	# Bail If Object
	if( is_object( $content ) ) { return $content; }

	$track_import = get_option( 'jedi_track_import' );
	$jedi_update_image_urls = $track_import['imported_media']['urls'];
	if( count( $jedi_update_image_urls ) === 0 ) {
		jswj_jedi_log( 'Skipping image url replacement, no images imported.' );
		return $content;
	}

	$replace_count = 0;

	foreach( $jedi_update_image_urls as $jedi_update_image_url ) {

		# Update Media URLs In Post Content - GUID
		$content = str_replace(
			$jedi_update_image_url['oldURL'],
			$jedi_update_image_url['newURL'],
			$content,
			$replace_count
		);

		# Update Alternate Media URLs In Post Content
		if( ! empty( $jedi_update_image_url['alternateURL'] ) ) {
			$content = str_replace(
				$jedi_update_image_url['alternateURL'],
				$jedi_update_image_url['newURL'],
				$content,
				$replace_count
			);
		}

		# Update JSON Encoded Media URLs In Post Content
		$content = str_replace(
			wp_json_encode( $jedi_update_image_url['oldURL'] ),
			wp_json_encode( $jedi_update_image_url['newURL'] ),
			$content,
			$replace_count
		);

		# Update Relative Media URLs In Post Content
		# Such As: /wp-content/uploads/2019/10/file.jpg
		$old_relative_url = '"' . substr( $jedi_update_image_url['oldURL'], strpos( $jedi_update_image_url['oldURL'], '/wp-content/uploads/' ) ) . '"';
		$new_relative_url = '"' . substr( $jedi_update_image_url['newURL'], strpos( $jedi_update_image_url['newURL'], '/wp-content/uploads/' ) ) . '"';
		$content = str_replace( $old_relative_url, $new_relative_url, $content, $replace_count );
		$content = str_replace(
			wp_json_encode( $old_relative_url ),
			wp_json_encode( $new_relative_url ),
			$content,
			$replace_count
		);

		# Update Media Thumbnail URLs In Post Content
		$thumb_url_base = substr(
			$jedi_update_image_url['oldURL'],
			0,
			strrpos( $jedi_update_image_url['oldURL'], '.' )
		);
		$thumb_url_ext = substr(
			$jedi_update_image_url['oldURL'],
			strrpos( $jedi_update_image_url['oldURL'], '.' )
		);
		$thumb_sizes = get_intermediate_image_sizes();
		foreach( $thumb_sizes as $thumb_size ) {
			$thumb_width = get_option( $thumb_size . '_size_w' );
			$thumb_height = get_option( $thumb_size . '_size_h' );
			if( false === $thumb_height || false === $thumb_height ) { continue; }

			$content = str_replace(
				$thumb_url_base . '-' . $thumb_width . 'x' . $thumb_height . $thumb_url_ext,
				$jedi_update_image_url['newURL'],
				$content,
				$replace_count
			);
			$content = str_replace(
				wp_json_encode( $thumb_url_base . '-' . $thumb_width . 'x' . $thumb_height . $thumb_url_ext ),
				wp_json_encode( $jedi_update_image_url['newURL'] ),
				$content,
				$replace_count
			);
		}
	} # END foreach

	return $content;
} # END jswj_replace_image_urls()

/**
 * Reassign Post Parent & Categories
 **/
function jswj_after_posts_import() {
	$jedi_apprentice_settings = get_option( 'jedi_apprentice_settings' );
	$track_import = get_option( 'jedi_track_import' );
	$jedi_post_ids = $track_import['imported_posts'];
	$jedi_category_ids = $track_import['imported_categories'];
	$categories_posts = $track_import['categories_posts'];

	# If Media were imported, Reassign Post Thumbnails
	if( count( $track_import['imported_media']['ids'] ) > 0 ) {
		$process_media = true;
		$jedi_imported_media = $track_import['imported_media'];
		$jedi_update_image_urls = $jedi_imported_media['urls'];
		$jedi_update_image_ids = $jedi_imported_media['ids'];

		foreach( $jedi_post_ids as $old_post_id => $new_post_id ) {
			if( isset( $jedi_update_image_ids[ get_post_thumbnail_id( $new_post_id ) ] ) ) {
				set_post_thumbnail( $new_post_id, $jedi_update_image_ids[ get_post_thumbnail_id( $new_post_id ) ] );
			}
		}
	}

	# Reassign Post Parent With Updated Post IDs
	foreach( $jedi_post_ids as $key => $jedi_post_id ) {
		$post_parent = wp_get_post_parent_id( $jedi_post_id );
		if( $post_parent && isset( $jedi_post_ids[ $post_parent ] ) ) {
			$post_info = array(
				'ID'          => $jedi_post_id,
				'post_parent' => $jedi_post_ids[ $post_parent ],
			);
			wp_update_post( $post_info );
		}
	}

	# Reassign Post Taxonomies Using Updated Post IDs
	foreach( $categories_posts as $old_id => $post_terms_info ) {
		foreach( $post_terms_info['terms'] as $post_term ) {
			if( ! isset( $jedi_post_ids[ $old_id ] ) ) { continue; }
			if( ! isset( $jedi_category_ids[ $post_term['term_id'] ] ) ) { continue; }
			$test = wp_set_post_terms(
				$jedi_post_ids[ $old_id ],                                      # Post ID
				array( intval( $jedi_category_ids[ $post_term['term_id'] ] ) ), # Term ID
				$post_term['taxonomy'],                                         # Taxonomy
				true                                                            # Append?
			);
		}
	}

} # END jswj_after_posts_import()
add_action( 'jedi_after_post_import', 'jswj_after_posts_import' );

/**
 * Imports Each Category & Taxonomy
 **/
function jswj_import_categories() {
	jswj_verify_ajax_nonce_and_capability( 'jswj_import_process_nonce', 'jedi_ajax_nonce' );

	$track_import = get_option( 'jedi_track_import' );
	$import_data = jswj_get_jedi_import_data();
	$import_categories = $import_data['categories']['categories'];
	$categories_posts = $import_data['categories']['posts'];

	$jedi_category_ids = array();

	foreach( $import_categories as $key => $jedi_category ) {
		foreach( $jedi_category as $category ) {
			$new_term = array(
				'description' => $category->description,
				'slug'        => $category->slug,
			);
			$new_cat_id = wp_insert_term( $category->name, $key, $new_term );
			if( true === is_wp_error( $new_cat_id ) ) {
				if( isset( $new_cat_id->error_data['term_exists'] ) ) {
					$jedi_category_ids[ $category->term_id ] = $new_cat_id->error_data['term_exists'];
				}
			} else {
				$jedi_category_ids[ $category->term_id ] = $new_cat_id['term_id'];
			}
		}

		# Assign Updated Category Parent
		foreach( $jedi_category as $category ) {
			if( !isset( $jedi_category_ids[ $category->term_id ] ) 
				|| !isset( $jedi_category_ids[ $category->parent ] )
				) { 
				continue; 
			}

			if( $category->parent > 0 ) {
				$args = array( 'parent' => $jedi_category_ids[ $category->parent ] );
				wp_update_term( $jedi_category_ids[ $category->term_id ], $category->taxonomy, $args );
			}
		}

		# Assign Updated Category Parent
		foreach( $jedi_category as $category ) {
			if( 0 < $category->parent ) {
				$args = array();
				if( isset( $jedi_category_ids[ $category->parent ] ) ) {
					$args = array( 'parent' => $jedi_category_ids[ $category->parent ] );
				}
				if( isset( $jedi_category_ids[ $category->term_id ] ) ) {
					wp_update_term( $jedi_category_ids[ $category->term_id ], $category->taxonomy, $args );
				}
			}
		}
	} # END foreach import_categories

	# Add Categories To Import History
	$track_import['imported_categories'] = $jedi_category_ids;
	$track_import['categories_posts'] = $categories_posts;
	update_option( 'jedi_track_import', $track_import );

	jswj_jedi_log( 'Categories & Taxonomies Imported', serialize( $jedi_category_ids ) );

	jswj_ajax_response( array( 1, 'Categories & Taxonomies Imported: ' . count( $jedi_category_ids ) ) );

} # END import_categories()
add_action( 'wp_ajax_jswj_import_categories', 'jswj_import_categories' );

/**
 * Sets The Homepage
 **/
function jswj_jedi_set_homepage() {
	jswj_verify_ajax_nonce_and_capability( 'jswj_import_process_nonce', 'jedi_ajax_nonce' );

	$jedi_apprentice_settings = get_option( 'jedi_apprentice_settings' );
	$track_import = get_option( 'jedi_track_import' );
	$jedi_post_ids = $track_import['imported_posts'];

	if( $jedi_apprentice_settings['homepage_ID'] || $jedi_apprentice_settings['postspage_id'] ) {
		$log_text = '';
		update_option( 'show_on_front', 'page' );

		# Set Home Page
		if( intval( $jedi_apprentice_settings['homepage_ID'] ) !== 0 ) {
			$new_homepage = $jedi_post_ids[ $jedi_apprentice_settings['homepage_ID'] ];
			update_option( 'page_on_front', $new_homepage );
			$log_text .= 'Homepage Set: ' . get_the_title( $new_homepage ) . ' [Page ID: ' . $new_homepage . ']';
		}

		# Set Posts Page
		if( intval( $jedi_apprentice_settings['postspage_id'] ) !== 0 ) {
			$new_postspage = $jedi_post_ids[ $jedi_apprentice_settings['postspage_id'] ];
			update_option( 'page_for_posts', $new_postspage );
			if( strlen( $log_text ) > 0 ) { $log_text .= ' | '; }
			$log_text .= 'Posts Page Set: ' . get_the_title( $new_postspage ) . ' [Page ID: ' . $new_postspage . ']';
		}

		jswj_jedi_log( 'Pages Set', $log_text );
		jswj_ajax_response( array( 1, $log_text ) );

	} else {
		jswj_ajax_response( array( 0, 'Homepage Not Set' ) );
	}
	wp_die();

} # END jswj_jedi_set_homepage()
add_action( 'wp_ajax_jswj_jedi_set_homepage', 'jswj_jedi_set_homepage' );

/**
 * Import The Additional CSS
 **/
function jswj_jedi_import_css() {
	jswj_verify_ajax_nonce_and_capability( 'jswj_import_process_nonce', 'jedi_ajax_nonce' );

	$jedi_import_options = get_option( 'jedi_import_options' );
	$jedi_apprentice_settings = get_option( 'jedi_apprentice_settings' );
	$track_import = get_option( 'jedi_track_import' );
	$jedi_imported_media = $track_import['imported_media'];
	$jedi_post_ids = $track_import['imported_posts'];

	$import_data = jswj_get_jedi_import_data();
	$import_css = $import_data['css'];

	# Update Image URLs In CSS Content
	# First checks if any Media were imported
	if( count( $jedi_imported_media['ids'] ) > 0 ) {
		$jedi_update_image_urls = $jedi_imported_media['urls'];
		foreach( $jedi_update_image_urls as $jedi_update_image_url ) {

			# Update Media URLs In Post Content
			$import_css = str_replace(
				$jedi_update_image_url['oldURL'],
				$jedi_update_image_url['newURL'],
				$import_css
			);

			# Update Relative Media URLs In Post Content
			# Such As: /wp-content/uploads/2019/10/file.jpg
			$old_relative_url = substr(
				$jedi_update_image_url['oldURL'],
				strpos( $jedi_update_image_url['oldURL'], '/wp-content/uploads/' )
			);
			$new_relative_url = substr(
				$jedi_update_image_url['newURL'],
				strpos( $jedi_update_image_url['newURL'], '/wp-content/uploads/' )
			);
			$import_css = str_replace( $old_relative_url, $new_relative_url, $import_css );

		}
	}

	$custom_css_post = wp_get_custom_css_post();
	if( $custom_css_post ) {

		# Check if Existing CSS Is Identical To Import CSS
		if( preg_replace( '/\s*/m', '', $custom_css_post->post_content ) ===
			preg_replace( '/\s*/m', '', $import_css ) ) {
			jswj_jedi_log( 'Skipping Import Of Identical Customizer CSS' );
			jswj_ajax_response( array( 1, 'Additional CSS Already Imported' ) );
		}

		jswj_jedi_log( 'Customizer CSS Exists, Appending Import CSS' );
		$jedi_import_css = $custom_css_post->post_content . "\n\n" . $import_css;
	} else {
		$jedi_import_css = $import_css;
	}
	wp_update_custom_css_post( $jedi_import_css );

	jswj_jedi_log( 'Additional CSS Imported' );

	jswj_ajax_response( array( 1, 'Additional CSS Imported' ) );

} # END jswj_jedi_import_css()
add_action( 'wp_ajax_jswj_jedi_import_css', 'jswj_jedi_import_css' );

/**
 * Import Menus
 **/
function jswj_jedi_import_menus() {
	jswj_verify_ajax_nonce_and_capability( 'jswj_import_process_nonce', 'jedi_ajax_nonce' );

	$jedi_import_options = get_option( 'jedi_import_options' );
	$jedi_apprentice_settings = get_option( 'jedi_apprentice_settings' );
	$track_import = get_option( 'jedi_track_import' );
	$jedi_imported_media = $track_import['imported_media'];
	$jedi_post_ids = $track_import['imported_posts'];
	$jedi_category_ids = $track_import['imported_categories'];

	# Parse imported menu data
	$import_data = jswj_get_jedi_import_data();
	$jedi_import_menus = $import_data['menus'];
	$jedi_menu_names = $jedi_import_menus[0];
	$jedi_menu_items = $jedi_import_menus[1];
	$jedi_menu_locations = $jedi_import_menus[2];

	# Loop through data to Create Menus
	foreach( $jedi_menu_names as $key => $jedi_menu_name ) {

		$original_name = $jedi_menu_name->name;
		$unique_name = $jedi_menu_name->name;

		if( isset( $track_import['imported_menu_names'][ $original_name ] ) ) {
			jswj_jedi_log( 'Skipping Previusly Imported Menu: ' . $unique_name );
			continue;
		}

		# Create Unique Menu Name If Menu Already Exists
		$make_unique = 1;
		$test_menu = wp_get_nav_menu_object( $unique_name );
		while( false !== $test_menu && $make_unique <= 100 ) {
			$make_unique++;
			$unique_name = $jedi_menu_name->name . ' ' . $make_unique;
			$test_menu = wp_get_nav_menu_object( $unique_name );
		}
		$jedi_menu_names[ $key ]->name = $unique_name;

		# Update Menu Locations With New Unique Name (ie primary-menu)
		foreach( $jedi_menu_locations as $menu_location => $menu_name ) {
			if( $menu_name === $original_name ) {
				$jedi_menu_locations[ $menu_location ] = $unique_name;
			}
		}

		# Create The Menu
		jswj_jedi_log( 'Creating Menu', $unique_name );
		$new_menu_id = wp_create_nav_menu( $unique_name );
		if( is_wp_error( $new_menu_id ) ) {
			jswj_jedi_log( 'Error creating menu: ' . $unique_name, $new_menu_id );
		}
		$track_import['imported_menu_names'][ $original_name ] = $original_name;
		$track_import['imported_menu_ids'][ $jedi_menu_name->term_id ] = $new_menu_id;
	} # END foreach $jedi_menu_names

	# Load Menu Items
	$new_menu_id = array();

	foreach( $jedi_menu_names as $key1 => $jedi_import_menu ) {
		$menu_id = get_term_by( 'name', $jedi_menu_names[ $key1 ]->name, 'nav_menu' );
		if( false === $menu_id ) { continue; }

		foreach( $jedi_menu_items[ $key1 ] as $key2 => $jedi_import_menu_item ) {
			if( isset( $jedi_import_menu_item->_invalid ) ) { continue; }

			# Skip Previously Imported Items
			if( isset( $track_import['imported_menu_items'][ $jedi_import_menu_item->ID ] ) ) {
				jswj_jedi_log( 'Skipping Previusly Imported Menu Item: ' . $jedi_import_menu_item->title );
				continue;
			}

			$skip_this_menu_item = false;

			$item_array = array(
				'menu-item-title'     => $jedi_import_menu_item->title,
				'menu-item-object'    => $jedi_import_menu_item->object,
				'menu-item-type'      => $jedi_import_menu_item->type,
				'menu-item-post-type' => $jedi_import_menu_item->post_type,
				'menu-item-classes'   => implode( ' ', $jedi_import_menu_item->classes ),
				'menu-item-status'    => $jedi_import_menu_item->post_status,
			);

			# Handle Different Menu Item Types
			switch( $jedi_import_menu_item->type ) {
				case 'custom':
					$item_array['menu-item-url'] = $jedi_import_menu_item->url;
					break;

				case 'taxonomy':
					if( isset( $jedi_category_ids[ strval( $jedi_import_menu_item->object_id ) ] ) ) {
						# Try To Sync Taxonomy With New Id
						$new_id = $jedi_category_ids[ $jedi_import_menu_item->object_id ];
						$item_url = get_term_link( $new_id );
						$item_array['menu-item-url'] = $item_url;
						$item_array['menu-item-object-id'] = $new_id;
					} else {
						# If Matching Taxonomy Cannot Be Found, Use First Taxonomy Found
						$possible_taxonomies = get_terms(
							array(
								'taxonomy'   => $jedi_import_menu_item->object,
								'hide_empty' => false,
							)
						);
						if( ! is_wp_error( $possible_taxonomies )
							&& isset( $possible_taxonomies[0] )
							&& is_object( $possible_taxonomies[0] )
							) {
								$new_term_id = $possible_taxonomies[0]->term_id;
								$item_array['menu-item-url'] = get_term_link( $new_term_id );
								$item_array['menu-item-object-id'] = $new_term_id;
						} else {
							$skip_this_menu_item = true;
						}
					}
					break;

				default:
					if( isset( $jedi_post_ids[ $jedi_import_menu_item->object_id ] ) ) {
						$new_id = $jedi_post_ids[ $jedi_import_menu_item->object_id ];
						$item_url = get_page_link( $new_id );
						$item_array['menu-item-url'] = $item_url;
						$item_array['menu-item-object-id'] = $new_id;
					} else {
						$skip_this_menu_item = true;
					}
					break;
			}

			# Handle Child Menu Items
			if ( '0' !== $jedi_import_menu_item->menu_item_parent ) {
				$item_array['menu-item-parent-id'] = intval( $new_menu_id[ $jedi_import_menu_item->menu_item_parent ] );
			}

			# Safety Check
			if( ! is_object( $menu_id ) ) { continue; }
			if( isset( $item_array['menu-item-url'] ) && is_wp_error( $item_array['menu-item-url'] ) ) { continue; }
			if( $skip_this_menu_item ) { continue; }

			# Create Menu Item
			$new_menu_id[ $jedi_import_menu_item->ID ] = wp_update_nav_menu_item(
				$menu_id->term_id, # Menu ID
				0,                 # Menu Item DB ID
				$item_array        # Menu Item Data
			);

			jswj_jedi_log( 'Creating Menu Item', $item_array['menu-item-title'] );

			$track_import['imported_menu_items'][ $jedi_import_menu_item->ID ] = $jedi_import_menu_item->ID;

		} # END foreach $jedi_menu_items
	} # END foreach $jedi_menu_names

	# Set Menu Locations
	$jedi_primary_menu = get_term_by( 'name', $jedi_menu_locations['primary-menu'], 'nav_menu' );
	$jedi_secondary_menu = get_term_by( 'name', $jedi_menu_locations['secondary-menu'], 'nav_menu' );
	$jedi_footer_menu = get_term_by( 'name', $jedi_menu_locations['footer-menu'], 'nav_menu' );
	set_theme_mod(
		'nav_menu_locations',
		array(
			'primary-menu'   => $jedi_primary_menu ? $jedi_primary_menu->term_id : '',
			'secondary-menu' => $jedi_secondary_menu ? $jedi_secondary_menu->term_id : '',
			'footer-menu'    => $jedi_footer_menu ? $jedi_footer_menu->term_id : '',
		)
	);

	jswj_jedi_log( 'Menus Imported: ' . count( $jedi_menu_names ) );
	update_option( 'jedi_track_import', $track_import );

	jswj_ajax_response( array( 1, 'Menus Imported: ' . count( $jedi_menu_names ) ) );

} # END jswj_jedi_import_menus()
add_action( 'wp_ajax_jswj_jedi_import_menus', 'jswj_jedi_import_menus' );

/**
 * Import Widgets
 **/
function jswj_jedi_import_widgets() {
	jswj_verify_ajax_nonce_and_capability( 'jswj_import_process_nonce', 'jedi_ajax_nonce' );

	$import_data = jswj_get_jedi_import_data();
	$jedi_apprentice_widget_import = $import_data['widgets'];
	$track_import = get_option( 'jedi_track_import' );
	$jedi_post_ids = $track_import['imported_posts'];

	update_option( 'sidebars_widgets', $jedi_apprentice_widget_import['sidebars_widgets'] );
	set_theme_mod( 'et_pb_widgets', $jedi_apprentice_widget_import['et_pb_widgets'] );

	$jedi_widget_options = $jedi_apprentice_widget_import['widget_options'];
	if( is_array( $jedi_widget_options ) ) {

		foreach( $jedi_widget_options as $option_key => $jedi_widget_option ) {
			$widget_object = unserialize( $jedi_widget_option );

			# Apply Filters To Update Text Widgets
			if( 'widget_text' === $option_key && is_array( $widget_object ) ) {
				foreach( $widget_object as $key => $widget_text ) {
					if( is_array( $widget_text ) && isset( $widget_text['text'] ) ) {
						$widget_object[ $key ]['text'] = apply_filters( 'jedi_widget_text_filter', $widget_text['text'] );
					}
				}
			}

			# Update Widget Menus With New Menu IDs
			if( 'widget_nav_menu' === $option_key && is_array( $widget_object ) ) {
				foreach( $widget_object as $key => $widget_nav_menu ) {
					if( is_array( $widget_nav_menu )
						&& isset( $widget_nav_menu['nav_menu'] )
						&& isset( $track_import['imported_menu_ids'][ $widget_nav_menu['nav_menu'] ] )
						) {
							$new_menu_id = $track_import['imported_menu_ids'][ $widget_nav_menu['nav_menu'] ];
							$widget_object[ $key ]['nav_menu'] = $new_menu_id;
							jswj_jedi_log(
								'Widget Menu Updated - Old ID ['
								. $widget_nav_menu['nav_menu'] . '] - New ID [' . $new_menu_id . ']'
							);
					}
				}
			}

			# Update Page Widget Items 
			if( 'widget_pages' === $option_key 
				&& is_array( $widget_object )
				) {
					foreach( $widget_object as $widget_object_index => $widget_object_item ) {
						if( is_array( $widget_object_item ) ) {
							$exclude_pages = explode( ',', $widget_object_item['exclude'] );
							$updated_exclude_pages = array();
							foreach( $exclude_pages as $exclude_page ) {
								if( isset( $jedi_post_ids[ trim( $exclude_page ) ] ) ) { 
									$updated_exclude_pages[] = $jedi_post_ids[ trim( $exclude_page ) ];
								}
							}
							$widget_object[ $widget_object_index ]['exclude'] = implode( ',', $updated_exclude_pages );
						}
					}
			}

			update_option( $option_key, $widget_object );
		}
	}

	jswj_jedi_log( 'Widget Data Imported' );

	jswj_ajax_response( array( 1, 'Widget Data Imported' ) );

} # END jswj_jedi_import_widgets()
add_action( 'wp_ajax_jswj_jedi_import_widgets', 'jswj_jedi_import_widgets' );

/**
 * AJAX Import Specified WP Options
 **/
function jswj_jedi_import_wp_options() {
	jswj_verify_ajax_nonce_and_capability( 'jswj_import_process_nonce', 'jedi_ajax_nonce' );

	# Initialize Variables
	$import_data = jswj_get_jedi_import_data();
	if( ! isset( $import_data['wp_options'] ) ) { return; }

	$jedi_wp_options_import = $import_data['wp_options'];
	$wp_option_backup = array();

	# Loop Through WP Options
	foreach( $jedi_wp_options_import as $wp_option => $wp_option_value ) {

		# Add Current Option Value To Backup Array
		$current_option_value = get_option( $wp_option );
		if( false !== $current_option_value ) {
			$wp_option_backup[ $wp_option ] = $current_option_value;
			jswj_jedi_log( 'Saving Backup Of wp_option', $wp_option );
		}

		// TODO, make a blocklist of values you cannot update?

		# Import WP Option
		update_option( $wp_option, $wp_option_value );
		jswj_jedi_log( 'Importing wp_option', $wp_option );
	}

	# Save Backup Array Of WP Options From Before Import
	update_option( 'jedi_wp_options_backup', $wp_option_backup );

	if( has_action( 'jedi_import_plugin_settings' ) ) {
		do_action( 'jedi_import_plugin_settings' );
	}

	jswj_ajax_response( array( 1, 'WP Options Imported' ) );

} # END jswj_jedi_import_wp_options()
add_action( 'wp_ajax_jswj_jedi_import_wp_options', 'jswj_jedi_import_wp_options' );

/**
 * Finish Import
 **/
function jswj_jedi_import_complete() {
	jswj_verify_ajax_nonce_and_capability( 'jswj_import_process_nonce', 'jedi_ajax_nonce' );

	$jedi_apprentice_settings = get_option( 'jedi_apprentice_settings' );

	jswj_jedi_log( 'Import Process Completed: ' . $jedi_apprentice_settings['installer_slug'] );

	update_option( 'jedi_status', 'Imported' );
	update_option( 'jedi_full_import_completed_' . $jedi_apprentice_settings['installer_slug'], 'true' );
	jedi_after_import_remove_demo_content_form();
	wp_die();
} # END jswj_jedi_import_complete()
add_action( 'wp_ajax_jswj_jedi_import_complete', 'jswj_jedi_import_complete' );

/**
 * Remove Demo Content Form
 **/
function jedi_after_import_remove_demo_content_form() {
	$jedi_apprentice_settings = get_option( 'jedi_apprentice_settings' );

	$after_import_message = '';
	if( isset( $jedi_apprentice_settings['custom_documentation']['after_import_message'] ) ) {
		$after_import_message = $jedi_apprentice_settings['custom_documentation']['after_import_message']['text'];
	}

	if( 'show_after_import_message' === $jedi_apprentice_settings['after_import_action']
		|| 'remove_demo_data' === $jedi_apprentice_settings['after_import_action']
		) {
		echo '<div class="jedi_after_import_message_container">';
			echo wp_kses_post( $after_import_message );

			if( 'remove_demo_data' === $jedi_apprentice_settings['after_import_action'] ) {
				echo '<form action="admin.php?page=jedi_apprentice_menu" method="POST" class="jedi_remove_demo_content_container">';
					wp_nonce_field( 'jedi_apprentice_menu_after_import', '_jedi_apprentice_nonce' );

					submit_button(
						'Remove Demo Content & Import Functions',
						'secondary',
						'after_import_remove_demo_content'
					);
				echo '</form>';
			}
		echo '</div>';
	}
}
