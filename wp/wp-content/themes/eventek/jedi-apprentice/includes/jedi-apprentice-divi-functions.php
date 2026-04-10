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
 * Import The Divi Theme Options
 **/
function jswj_jedi_import_divi_options() {
	jswj_verify_ajax_nonce_and_capability( 'jswj_import_process_nonce', 'jedi_ajax_nonce' );
	if( ! jswj_is_divi() ) {
		echo wp_json_encode( array( 1, 'Divi Not Installed, Skipping Options Import' ) );
		wp_die();
	}

	$jedi_import_options = get_option( 'jedi_import_options' );
	$jedi_apprentice_settings = get_option( 'jedi_apprentice_settings' );
	$track_import = get_option( 'jedi_track_import' );
	$jedi_imported_media = $track_import['imported_media'];
	$jedi_post_ids = $track_import['imported_posts'];

	$import_data = jswj_get_jedi_import_data();
	$divi_options = $import_data['options'];

	if( ! function_exists( 'et_update_option' ) ) {
		echo wp_json_encode( array( 0, 'Unable To Import Theme Options - Divi Theme Not Detected' ) );
	}

	/**
	 * Loops through Divi Options
	 * Uses Divi's function to update values from import
	 **/
	foreach( $divi_options as $divi_key => $jedi_import_option ) {
		et_update_option( $divi_key, $jedi_import_option );
	}

	# Update Image URLs in Divi Options
	if( ! empty( $jedi_imported_media['urls'] ) ) {
		$divi_logo = et_get_option( 'divi_logo' );
		$divi_favicon = et_get_option( 'divi_favicon' );
		$divi_rss_url = et_get_option( 'divi_rss_url' );

		# Loops through imported Image URLs
		foreach( $jedi_imported_media['urls'] as $jedi_url ) {
			if( $divi_logo === $jedi_url['oldURL'] ) { $divi_logo = $jedi_url['newURL']; }
			if( $divi_rss_url === $jedi_url['oldURL'] ) { $divi_rss_url = $jedi_url['newURL']; }
		}

		# Uses Divi's function to update values from import
		et_update_option( 'divi_logo', $divi_logo );
		et_update_option( 'divi_rss_url', $divi_rss_url );
	}

	jswj_jedi_log( 'Divi Theme Settings & Customizer Options Imported' );

	echo wp_json_encode( array( 1, 'Divi Theme Settings & Customizer Options Imported' ) );
	wp_die();

} # END jswj_jedi_import_divi_options()
add_action( 'wp_ajax_jswj_jedi_import_divi_options', 'jswj_jedi_import_divi_options' );

/**
 * Checks Child Theme PHP Files for Divi global module shortcodes
 * Replaces exported Post IDs with imported Post IDs
 **/
function jswj_update_phpfile_shortcodes() {
	if( ! jswj_is_divi() ) { return; }

	$jedi_apprentice_settings = get_option( 'jedi_apprentice_settings' );
	$jedi_apprentice_settings = get_option( 'jedi_apprentice_settings' );
	$child_theme_style = $jedi_apprentice_settings['installer_style'];
	$track_import = get_option( 'jedi_track_import' );
	$jedi_post_ids = $track_import['imported_posts'];

	# Update PHP File Shortcodes
	if( $child_theme_style ) {

		# Get list of Child Theme PHP Files
		$php_files = glob( get_stylesheet_directory() . '/*.php' );
		jswj_jedi_log( 'Child Theme PHP Files' );

		# Loop through PHP Files
		foreach( $php_files as $php_file ) {
			$php_file_contents = file_get_contents( $php_file );
			$php_file_sanitized = str_replace( WP_CONTENT_DIR, '', $php_file );

			jswj_jedi_log( 'Looking into PHP File: ' . $php_file_sanitized . PHP_EOL );

			$file_changes = 0;

			# Loop through post IDs to replace shortcode references to each ID
			foreach( $jedi_post_ids as $old_id => $jedi_post_id ) {
				$old_id = (int) $old_id;
				$jedi_post_id = (int) $jedi_post_id;

				# continue if not found
				if ( ! preg_match( '/global_module="' . $old_id . '"/smi', $php_file_contents ) ) {
					continue;
				}

				$file_changes++;

				$old_shortcode = 'global_module="' . $old_id . '"';
				$new_shortcode = 'global_module="' . $jedi_post_id . '"';
				$php_file_contents = str_replace( $old_shortcode, $new_shortcode, $php_file_contents );
			}

			# move on and don't even re-save this file if there weren't even any changes.
			if ( ! $file_changes ) {
				jswj_jedi_log( 'No changes to PHP File: ' . $php_file_sanitized . PHP_EOL );
				continue;
			}

			# Write PHP File & check for errors
			$safe_jedi = file_put_contents( $php_file, $php_file_contents );
			if( false === $safe_jedi ) {
				jswj_jedi_log( 'Error writing footer.php file', $php_file_sanitized );
			}

			jswj_jedi_log( 'PHP File: ' . $php_file_sanitized . PHP_EOL . '# of changes made: ' . $file_changes );

		} # END foreach( $php_files )

	} # END IF ChildTheme

} # END jswj_update_phpfile_shortcodes()
add_action( 'jedi_after_post_import', 'jswj_update_phpfile_shortcodes', 102 );


/**
 * Update Global Module IDs In Posts
 *
 * Checks each post for global module shortcodes & global parent shortcodes
 * Replaces exported Post IDs with imported Post IDs
 **/
function jswj_update_divi_global_module_shortcodes() {
	if( ! jswj_is_divi() ) { return; }

	$jedi_apprentice_settings = get_option( 'jedi_apprentice_settings' );
	$track_import = get_option( 'jedi_track_import' );
	$jedi_post_ids = $track_import['imported_posts'];

	foreach( $jedi_post_ids as $jedi_post_id ) {
		$dgm_post = get_post( $jedi_post_id );

		# Global Module - Search & Replace
		$hay_global = strpos( $dgm_post->post_content, 'global_module=' );
		while( false !== $hay_global ) {

			$dgm_post_id_start = strpos( $dgm_post->post_content, 'global_module="', $hay_global ) + 15;
			$dgm_post_id_end = strpos( $dgm_post->post_content, '"', $dgm_post_id_start );
			if( $dgm_post_id_start !== $dgm_post_id_end ) {

				$dgm_post_id = substr(
					$dgm_post->post_content,
					$dgm_post_id_start,
					$dgm_post_id_end - $dgm_post_id_start 
				);

				if( isset( $jedi_post_ids[ $dgm_post_id ] ) ) {
					$old_shortcode = 'global_module="' . $dgm_post_id . '"';
					$new_shortcode = 'global_module="' . $jedi_post_ids[ $dgm_post_id ] . '"';
					$dgm_post->post_content = str_replace( $old_shortcode, $new_shortcode, $dgm_post->post_content );

					jswj_jedi_log( 
						'Global Module Update: ',
						'[ ' . $dgm_post_id . ' ]--->[ ' . $jedi_post_ids[ $dgm_post_id ] . ' ]' 
					);
				} # END If

			} # END If

			# Search Rest Of Post?
			$hay_global = strpos( $dgm_post->post_content, 'global_module=', $dgm_post_id_end + 1 );

		} # END while

		# Global Parent - Search & Replace
		$hay_global = strpos( $dgm_post->post_content, 'global_parent=' );
		while( false !== $hay_global ) {

			$dgm_post_id_start = strpos( $dgm_post->post_content, 'global_parent="', $hay_global ) + 15;
			$dgm_post_id_end = strpos( $dgm_post->post_content, '"', $dgm_post_id_start );
			if( $dgm_post_id_start !== $dgm_post_id_end ) {

				$dgm_post_id = substr(
					$dgm_post->post_content,
					$dgm_post_id_start,
					$dgm_post_id_end - $dgm_post_id_start 
				);

				if( isset( $jedi_post_ids[ $dgm_post_id ] ) ) {
					$old_shortcode = 'global_parent="' . $dgm_post_id . '"';
					$new_shortcode = 'global_parent="' . $jedi_post_ids[ $dgm_post_id ] . '"';

					$dgm_post->post_content = str_replace( $old_shortcode, $new_shortcode, $dgm_post->post_content );

					jswj_jedi_log( 
						'Global Parent Update: ',
						'[ ' . $dgm_post_id . ' ]--->[ ' . $jedi_post_ids[ $dgm_post_id ] . ' ]' 
					);
				}
			}

			# Search Rest Of Post?
			$hay_global = strpos( $dgm_post->post_content, 'global_parent=', $dgm_post_id_end + 1 );

		} # END while

		wp_update_post( $dgm_post );

	} # END foreach $jedi_post_ids

} # END jswj_update_divi_global_module_shortcodes()
add_action( 'jedi_after_post_import', 'jswj_update_divi_global_module_shortcodes', 101 );


/**
 * Set Divi Libary Item Terms
 **/
function jswj_divi_after_posts_import() {
	if( ! jswj_is_divi() ) { return; }

	$jedi_apprentice_settings = get_option( 'jedi_apprentice_settings' );
	$track_import = get_option( 'jedi_track_import' );
	$jedi_post_ids = $track_import['imported_posts'];
	$jedi_category_ids = $track_import['imported_categories'];
	$categories_posts = $track_import['categories_posts'];
	$import_data = jswj_get_jedi_import_data();

	# Import and assign the terms for Divi Library Items
	if( isset( $import_data['layout_terms'] ) ) {
		$layout_posts = $import_data['layout_terms'];

		foreach( $layout_posts as $post_id => $layout_post ) {
			foreach( $layout_post as $term_name => $layout_post_term ) {
				wp_set_post_terms( $jedi_post_ids[ $post_id ], $layout_post_term, $term_name );
			}
		}
	}

} # END jswj_divi_after_posts_import()
add_action( 'jedi_after_post_import', 'jswj_divi_after_posts_import', 100 );


/**
 * Update Divi Post Content
 * 
 * @param string $post_content - Post Content.
 **/
function jswj_divi_postcontent_filter( $post_content ) {
	if( ! jswj_is_divi() ) { return $post_content; }

	$jedi_apprentice_settings = get_option( 'jedi_apprentice_settings' );
	$track_import = get_option( 'jedi_track_import' );
	$jedi_category_ids = $track_import['imported_categories'];

	# If Media were imported, load imported data
	$process_media = false;
	if( count( $track_import['imported_media']['ids'] ) > 0 ) {
		$process_media = true;
		$jedi_imported_media = $track_import['imported_media'];
		$jedi_update_image_urls = $jedi_imported_media['urls'];
		$jedi_update_image_ids = $jedi_imported_media['ids'];
	}

	# Update Divi Global Module IDs Referenced In Post Content
	foreach( $jedi_category_ids as $key => $jedi_category_id ) {
		$post_content = str_replace(
			'include_categories="' . $key . '"',
			'include_categories="' . $jedi_category_id . '"',
			$post_content 
		);
	}

	# Update Categories Referenced In Content (ie: Portfolio Modules)
	foreach( $jedi_category_ids as $key => $jedi_category_id ) {
		$post_content = str_replace(
			'include_categories="' . $key . '"',
			'include_categories="' . $jedi_category_id . '"',
			$post_content 
		);
	}

	# Update Image IDs Referenced In Gallery Module
	if( $process_media ) {
		$begin_search = 0;
		$prevent_forever_loop = 0;
		while( strpos( $post_content, 'gallery_ids="', $begin_search ) ) {
			$gallery_module = array();
			$gallery_module['start'] = strpos( $post_content, 'gallery_ids="', $begin_search );
			$gallery_module['end'] = strpos( $post_content, '"', $gallery_module['start'] + 14 );

			$module_old_ids = substr(
				$post_content,
				$gallery_module['start'],
				$gallery_module['end'] - $gallery_module['start']
			);
			$module_new_ids = $module_old_ids;
			foreach( $jedi_update_image_ids as $old_id => $new_id ) {
				if( is_wp_error( $new_id ) ) { continue; }
				$module_new_ids = str_replace( $old_id, $new_id, $module_new_ids );
			}
			$post_content = str_replace( $module_old_ids, $module_new_ids, $post_content );

			$begin_search = $gallery_module['end'] + 1;

			$prevent_forever_loop++;
			if( $prevent_forever_loop > 100 ) { break; }
		} #END while
	} #END if process_media

	return $post_content;

} # END jswj_divi_postcontent_filter()
add_filter( 'jedi_modify_post_content', 'jswj_divi_postcontent_filter', 1, 1 );

/**
 * Update Fullwidth Menu Modules With New Menu IDs
 *
 * Checks each post for fullwidth menu module shortcodes
 * Replaces exported Menu IDs with imported Menu IDs
 **/
function jswj_update_divi_fullwidth_menu_modules() {
	if( ! jswj_is_divi() ) { return; }

	$jedi_apprentice_settings = get_option( 'jedi_apprentice_settings' );
	$track_import = get_option( 'jedi_track_import' );
	$jedi_post_ids = $track_import['imported_posts'];
	$jedi_menu_ids = $track_import['imported_menu_ids'];

	foreach( $jedi_post_ids as $jedi_post_id ) {
		$fwm_post = get_post( $jedi_post_id );

		# Fullwidth Menu Module - Search & Replace Menu IDs
		$hay_fullwidth_module = strpos( $fwm_post->post_content, '[et_pb_fullwidth_menu ' );
		while( false !== $hay_fullwidth_module ) {

			$fwm_menu_id_start = strpos( $fwm_post->post_content, 'menu_id="', $hay_fullwidth_module ) + 9;
			$fwm_menu_id_end = strpos( $fwm_post->post_content, '"', $fwm_menu_id_start );
			if( $fwm_menu_id_start !== $fwm_menu_id_end ) {

				$fwm_menu_id = substr(
					$fwm_post->post_content,
					$fwm_menu_id_start,
					$fwm_menu_id_end - $fwm_menu_id_start
				);

				if( isset( $jedi_menu_ids[ $fwm_menu_id ] ) ) {
					$fwm_post->post_content = substr_replace(
						$fwm_post->post_content,
						$jedi_menu_ids[ $fwm_menu_id ],
						$fwm_menu_id_start,
						strlen( $fwm_menu_id )
					);
					jswj_jedi_log( 
						'Fullwidth Menu Module Update: ',
						'[ ' . $fwm_menu_id . ' ]--->[ ' . $jedi_menu_ids[ $fwm_menu_id ] . ' ]' 
					);
				}
			} # END If

			# Search Rest Of Post?
			$hay_fullwidth_module = strpos(
				$fwm_post->post_content,
				'[et_pb_fullwidth_menu ',
				$fwm_menu_id_end + 1
			);

		} # END while

		wp_update_post( $fwm_post );

	} # END foreach $jedi_post_ids

} # END jswj_update_divi_fullwidth_menu_modules()
add_action( 'jedi_after_import', 'jswj_update_divi_fullwidth_menu_modules' );

/**
 * Update Menu Modules With New Menu IDs
 *
 * Checks each post for menu module shortcodes
 * Replaces exported Menu IDs with imported Menu IDs
 **/
function jswj_update_divi_menu_modules() {
	if( ! jswj_is_divi() ) { return; }

	$jedi_apprentice_settings = get_option( 'jedi_apprentice_settings' );
	$track_import = get_option( 'jedi_track_import' );
	$jedi_post_ids = $track_import['imported_posts'];
	$jedi_menu_ids = $track_import['imported_menu_ids'];

	foreach( $jedi_post_ids as $jedi_post_id ) {
		$menu_module_post = get_post( $jedi_post_id );

		$infinte_count = 0;

		# Menu Module - Search & Replace Menu IDs
		$hay_menu_module = strpos( $menu_module_post->post_content, '[et_pb_menu ' );
		while( false !== $hay_menu_module ) {

			$infinte_count++;
			if( $infinte_count > 10 ) { break; }

			$menu_module_id_start = strpos( $menu_module_post->post_content, 'menu_id="', $hay_menu_module ) + 9;

			# Break if Menu ID is not set within the shortcode,
			if( $menu_module_id_start < $hay_menu_module ) {
				$hay_menu_module = false;
				continue;
			}

			$menu_module_id_end = strpos( $menu_module_post->post_content, '"', $menu_module_id_start );

			if( $menu_module_id_start !== $menu_module_id_end ) {

				$menu_module_id = substr(
					$menu_module_post->post_content,
					$menu_module_id_start,
					$menu_module_id_end - $menu_module_id_start
				);

				if( isset( $jedi_menu_ids[ $menu_module_id ] ) ) {
					$menu_module_post->post_content = substr_replace(
						$menu_module_post->post_content,
						$jedi_menu_ids[ $menu_module_id ],
						$menu_module_id_start,
						strlen( $menu_module_id )
					);
					jswj_jedi_log( 
						'Menu Module Update: ',
						'[ ' . $menu_module_id . ' ]--->[ ' . $jedi_menu_ids[ $menu_module_id ] . ' ]' 
					);
				}
			} # END If

			# Search Rest Of Post?
			$hay_menu_module = strpos(
				$menu_module_post->post_content,
				'[et_pb_menu ',
				$menu_module_id_end + 1
			);

		} # END while

		wp_update_post( $menu_module_post );

	} # END foreach $jedi_post_ids

} # END jswj_update_divi_menu_modules()
add_action( 'jedi_after_import', 'jswj_update_divi_menu_modules' );
