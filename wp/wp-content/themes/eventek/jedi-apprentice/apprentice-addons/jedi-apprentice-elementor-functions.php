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
 * Filter Postmeta Content For Elementor
 *
 * Find & Replace Image IDs
 * Add Slashes For JSON Content
 *
 * @param string $postmeta - String / Array.
 *
 * @return String / Array
 **/
function jswj_jedi_elementor_filter_postmeta( $postmeta ) {
	if( ! jswj_is_elementor() ) { return $postmeta; }

	# Find & Replace Image IDs
	$track_import = get_option( 'jedi_track_import' );
	$jedi_imported_media_ids = $track_import['imported_media']['ids'];
	$jedi_imported_media_urls = $track_import['imported_media']['urls'];

	foreach( $jedi_imported_media_ids as $old_media_id => $new_media_id ) {

		# Patterns
		# "image":{"url":"...","id":###}
		# "background_image":{"url":"...","id":###}
		# "bg_image":{"url":"...","id":###}
		# "wp_gallery":[{"id":###,"url":"..."},{"id":###,"url":"..."}]
		$postmeta = str_replace(
			'"id":' . $old_media_id . '}',
			'"id":' . $new_media_id . '}',
			$postmeta 
		);
		$postmeta = str_replace(
			'"id":' . $old_media_id . ',',
			'"id":' . $new_media_id . ',',
			$postmeta
		);

	} # END foreach jedi_imported_media_ids

	foreach( $jedi_imported_media_urls as $media_url_array ) {
		$old_media_url = $media_url_array['oldURL'];
		$new_media_url = $media_url_array['newURL'];

		# Patterns
		# "image":{"url":"...","id":###}
		# "background_image":{"url":"...","id":###}
		# "bg_image":{"url":"...","id":###}
		# "wp_gallery":[{"id":###,"url":"..."},{"id":###,"url":"..."}]
		$postmeta = str_replace(
			'"url":' . $old_media_url . '}',
			'"url":' . $new_media_url . '}',
			$postmeta
		);
		$postmeta = str_replace(
			'"url":' . $old_media_url . ',',
			'"url":' . $new_media_url . ',',
			$postmeta
		);

	} # END foreach jedi_imported_media_ids

	# Add Slashes For JSON Content
	$postmeta = wp_slash( $postmeta );

	return $postmeta;
} # END jswj_jedi_elementor_filter_postmeta()
add_filter( 'jedi_filter_postmeta_content', 'jswj_jedi_elementor_filter_postmeta' );

/**
 * Import The Elementor Plugin Options
 **/
function jswj_jedi_import_elementor_options() {
	jswj_verify_ajax_nonce_and_capability( 'jswj_import_process_nonce', 'jedi_ajax_nonce' );

	if( ! jswj_is_elementor() ) {
		jswj_ajax_response( array( 1, 'Unable To Import Options, Elementor Not Installed' ) );
	}

	$jedi_import_options = get_option( 'jedi_import_options' );
	$jedi_apprentice_settings = get_option( 'jedi_apprentice_settings' );
	$track_import = get_option( 'jedi_track_import' );
	$jedi_imported_media = $track_import['imported_media'];
	$jedi_post_ids = $track_import['imported_posts'];

	$import_data = jswj_get_jedi_import_data();
	$elementor_options = $import_data['elementor_options'];

	/**
	 * Loops through Elementor Options
	 **/
	foreach( $elementor_options as $option_key => $jedi_import_option ) {
		$jedi_import_option = apply_filters( 'jedi_elementor_options_import_filter', $jedi_import_option, $option_key );
		update_option( $option_key, $jedi_import_option );
	}

	# Import Fonts
	jswj_jedi_import_elementor_fonts();

	# Import CSS Files
	jswj_jedi_import_elementor_css();

	jswj_jedi_log( 'Elementor Options & Settings Imported' );

	jswj_ajax_response( array( 1, 'Elementor Options & Settings Imported' ) );

} # END jswj_jedi_import_elementor_options()
add_action( 'wp_ajax_jswj_jedi_import_elementor_options', 'jswj_jedi_import_elementor_options' );

/**
 * Import Elementor CSS Files From Export Folder
 *
 * Copies CSS Files To Upload Directory
 * Rename & Replace CSS Files With Updated Post IDs
 **/
function jswj_jedi_import_elementor_css() {
	if( ! jswj_is_elementor() ) { return; }

	$jedi_apprentice_settings = get_option( 'jedi_apprentice_settings' );

	$track_import = get_option( 'jedi_track_import' );
	$jedi_post_ids = $track_import['imported_posts'];

	$css_import_path = JEDI_APPRENTICE_PATH . 'demo-data/elementor/css';
	if( ! file_exists( $css_import_path ) ) { return; }

	$upload_dir = wp_get_upload_dir();
	$upload_css_dir = $upload_dir['basedir'] . '/elementor/css';

	# Initialize the WP filesystem
	global $wp_filesystem;
	if( empty( $wp_filesystem ) ) {
		require_once ABSPATH . '/wp-admin/includes/file.php';
		if( ! WP_Filesystem() ) {
			jswj_jedi_order_66( 'Failed to initialize WP Filesystem.' );
		}
	}

	# Create Upload CSS Folder If Necessary
	if( ! wp_mkdir_p( $upload_css_dir ) ) {
		jswj_jedi_order_66( 'Unable to create Plugin Folder: ' . $upload_css_dir );
	}

	# Duplicate The CSS Folder
	$safe_jedi = copy_dir( $css_import_path, $upload_css_dir );
	if( is_wp_error( $safe_jedi ) ) {
		jswj_jedi_order_66( 'Failed to copy Plugin folder', wp_json_encode( $safe_jedi ) );
	}

	# Loop Through CSS Files To Find & Update Post IDs
	$css_files = glob( $upload_css_dir . '/*.css' );
	foreach( $css_files as $css_file ) {
		foreach( $jedi_post_ids as $old_id => $new_id ) {

			# If File Matches Post ID
			if( strpos( $css_file, '-' . strval( $old_id ) . '.css' ) !== false ) {

				jswj_jedi_log( 'Importing CSS File', $css_file );

				# Create New CSS File Name
				$new_css_file = str_replace(
					'-' . strval( $old_id ) . '.css',
					'-' . strval( $new_id ) . '.css',
					$css_file
				);

				# Create New CSS File Content
				$css_file_content = str_replace(
					'.elementor-' . strval( $old_id ) . ' ',
					'.elementor-' . strval( $new_id ) . ' ',
					file_get_contents( $css_file )
				);

				# Replace Popup IDs In File Content
				# Pattern: #elementor-popup-modal-###
				$css_file_content = str_replace(
					'#elementor-popup-modal-' . strval( $old_id ),
					'#elementor-popup-modal-' . strval( $new_id ),
					$css_file_content
				);

				# Save New CSS File
				file_put_contents( $new_css_file, $css_file_content );

				jswj_jedi_log( 'New CSS File', $new_css_file );

				# Delete CSS File With Original ID
				unlink( $css_file );
			}
		}
	}

	# Loop Through CSS Files To Find & Update Media & Font File URLs
	$css_files = glob( $upload_css_dir . '/*.css' );
	foreach( $css_files as $css_file ) {
		$css_file_content = file_get_contents( $css_file );

		# If Media were imported, load imported data
		# Update Image URLs In Content
		if( count( $track_import['imported_media']['ids'] ) > 0 ) {
			$jedi_imported_media = $track_import['imported_media'];
			$jedi_update_image_urls = $jedi_imported_media['urls'];
			$jedi_update_image_ids = $jedi_imported_media['ids'];

			foreach( $jedi_update_image_urls as $jedi_update_image_url ) {
				$css_file_content = str_replace(
					$jedi_update_image_url['oldURL'],
					$jedi_update_image_url['newURL'],
					$css_file_content
				);
			}
		}

		# If Fonts were imported, load imported data
		# Update Image URLs In Content
		if( isset( $track_import['imported_fonts'] ) && count( $track_import['imported_fonts'] ) > 0 ) {
			foreach( $track_import['imported_fonts'] as  $old_font_url => $new_font_url ) {
				$css_file_content = str_replace(
					$old_font_url,
					$new_font_url,
					$css_file_content
				);
			}
		}

		# Save Modified File
		file_put_contents( $css_file, $css_file_content );

	} # END foreach $css_files

} # END jswj_jedi_import_elementor_css()


/**
 * Import Elementor Font Files From Export Folder
 *
 * Copies Font Files To Upload Directory
 **/
function jswj_jedi_import_elementor_fonts() {
	if( ! jswj_is_elementor() ) { return; }

	$jedi_apprentice_settings = get_option( 'jedi_apprentice_settings' );

	$track_import = get_option( 'jedi_track_import' );
	$track_import['imported_fonts'] = array();

	$font_import_path = JEDI_APPRENTICE_PATH . 'demo-data/elementor/fonts';
	if( ! file_exists( $font_import_path ) ) { return; }

	# Get Upload Directory Data
	$upload_dir = wp_get_upload_dir();
	$upload_font_dir = $upload_dir['path'] . '/';
	$upload_font_url = $upload_dir['url'] . '/';

	$import_data = jswj_get_jedi_import_data();
	$font_files_to_import = $import_data['elementor_options']['fonts'];

	# Initialize the WP filesystem
	global $wp_filesystem;
	if( empty( $wp_filesystem ) ) {
		require_once ABSPATH . '/wp-admin/includes/file.php';
		if( ! WP_Filesystem() ) {
			jswj_jedi_order_66( 'Failed to initialize WP Filesystem.' );
		}
	}

	# Duplicate The Font Folder To Current Upload Folder
	$safe_jedi = copy_dir( $font_import_path, $upload_font_dir );
	if( is_wp_error( $safe_jedi ) ) {
		jswj_jedi_order_66( 'Failed to copy Plugin folder', wp_json_encode( $safe_jedi ) );
	}

	# Get The Font Database Records
	$font_files_args = array(
		'post_type'      => 'elementor_font',
		'posts_per_page' => -1,
	);
	$font_files_query = new WP_Query( $font_files_args );
	$font_records = $font_files_query->posts;

	$font_types = array( 'woff', 'woff2', 'ttf', 'svg', 'eot' );

	# Loop Through Font Records
	# Check each one to see if local file exists
	# If local file exists, copy font file to export folder
	foreach( $font_records as $font_record ) {

		# Get Font Record Metadata
		$font_record_metadata = get_post_meta( $font_record->ID, 'elementor_font_face', true );
		$font_file_record_metadata = get_post_meta( $font_record->ID, 'elementor_font_files', true );

		foreach( $font_files_to_import as $file_url => $font_file_to_import ) {
			if( ! file_exists( $upload_font_dir . basename( $file_url ) ) ) { continue; }

			# Update Font Face Metadata
			$font_record_metadata = str_replace(
				$file_url,
				$upload_font_url . basename( $file_url ),
				$font_record_metadata
			);

			# Update Font File Metadata
			foreach( $font_types as $font_type ) {
				if( ! isset( $font_file_record_metadata[0][ $font_type ] ) ) { continue; }

				$font_file_record_metadata[0][ $font_type ] = str_replace(
					$file_url,
					$upload_font_url . basename( $file_url ),
					$font_file_record_metadata[0][ $font_type ]
				);
			}

			$track_import['imported_fonts'][ $file_url ] = $upload_font_url . basename( $file_url );

		} # END foreach $font_files_to_import

		update_post_meta( $font_record->ID, 'elementor_font_face', $font_record_metadata );
		update_post_meta( $font_record->ID, 'elementor_font_files', $font_file_record_metadata );

	} # END foreach $font_records

	update_option( 'jedi_track_import', $track_import );

} # END jswj_jedi_import_elementor_fonts()


/**
 * Update Elementor Pro Theme Builder Options With Updated Post IDs
 * 
 * @param string $jedi_import_option - Option Content.
 * @param string $option_key - Option Key.
 **/
function jswj_elementor_pro_option_filter( $jedi_import_option, $option_key ) {
	if( ! jswj_is_elementor() ) { return; }

	if( 'elementor_pro_theme_builder_conditions' !== $option_key ) { return $jedi_import_option; }
	if( ! is_array( $jedi_import_option ) ) { return $jedi_import_option; }

	$track_import = get_option( 'jedi_track_import' );
	$jedi_imported_media = $track_import['imported_media'];
	$jedi_post_ids = $track_import['imported_posts'];

	foreach( $jedi_import_option as $theme_element_slug => $theme_element_items ) {
		foreach( $theme_element_items as $element_id => $element_content ) {
			if( isset( $jedi_post_ids[ $element_id ] ) ) {

				# Create New Element With Updated Post ID
				$new_id = $jedi_post_ids[ $element_id ];

				foreach( $element_content as $single_key => $single_element ) {

					# Single Page
					$search_string = 'include/singular/page/';
					if( strpos( $single_element, $search_string ) !== false ) {
						$old_post_id = substr( $single_element, strlen( $search_string ) );
						if( isset( $jedi_post_ids[ $old_post_id ] ) ) {
							$single_element = str_replace( $old_post_id, $jedi_post_ids[ $old_post_id ], $single_element );
							$element_content[ $single_key ] = $single_element;
						}
					}

					# Single Post
					$search_string = 'include/singular/post/';
					if( strpos( $single_element, $search_string ) !== false ) {
						$old_post_id = substr( $single_element, strlen( $search_string ) );
						if( isset( $jedi_post_ids[ $old_post_id ] ) ) {
							$single_element = str_replace( $old_post_id, $jedi_post_ids[ $old_post_id ], $single_element );
							$element_content[ $single_key ] = $single_element;
						}
					}
				} # END foreach $element_content

				$jedi_import_option[ $theme_element_slug ][ $new_id ] = $element_content;

				# Delete Old Element
				unset( $jedi_import_option[ $theme_element_slug ][ $element_id ] );
			}
		}
	}

	return $jedi_import_option;
} # END jswj_elementor_pro_option_filter()
add_filter( 'jedi_elementor_options_import_filter', 'jswj_elementor_pro_option_filter', 10, 2 );



/**
 * Update Post Content For Elementor Elements
 *
 * Style: .elementor-####
 *
 * @uses JEDI Hook jedi_after_post_import
 * @param array $jedi_post_ids An array of imported Post IDs.
 **/
function jswj_update_elementor_postcontent( $jedi_post_ids ) {
	if( ! jswj_is_elementor() ) { return; }

	jswj_jedi_log( 'Executing jswj_update_elementor_postcontent() ', wp_json_encode( $jedi_post_ids ) );

	$search_post_ids = $jedi_post_ids;
	$search_within_post_ids = $jedi_post_ids;

	foreach( $search_post_ids as $key => $search_post_id ) {

		$the_post = get_post( $search_post_id );

		$update_count = 0;

		foreach( $search_within_post_ids as $old_search_post_id => $new_search_post_id ) {

			$old_elementor_string = '.elementor-' . $old_search_post_id . ' ';
			$new_elementor_string = '.elementor-' . $new_search_post_id . ' ';

			$count = 0;
			$the_post->post_content = str_replace(
				$old_elementor_string,
				$new_elementor_string,
				$the_post->post_content,
				$count
			);
			$update_count = $update_count + $count;

		}

		if( $update_count > 0 ) {
			$update_post = wp_update_post( $the_post );
			jswj_jedi_log(
				'Updating post #' . $search_post_id . ' for elementor content',
				'Replacements: ' . $update_count
			);
		}
	} # END foreach $jedi_post_ids
} # END jswj_update_elementor_postcontent()
add_action( 'jedi_after_post_import', 'jswj_update_elementor_postcontent' );




/**
 * Update Post Meta
 *
 * Find & Replace Post IDs
 * Add Slashes For JSON Content
 *
 * @param array $jedi_post_ids - Array of post IDs.
 **/
function jswj_jedi_elementor_postmeta( $jedi_post_ids ) {
	if( ! jswj_is_elementor() ) { return; }

	# Check Each Post For Template IDs
	# Pattern: "template_id":"8435"
	foreach( $jedi_post_ids as $jedi_post_id ) {

		$post_meta = get_post_meta( $jedi_post_id, '_elementor_data', true );

		# Update Template Item IDs
		# Pattern: "template_id":"8435"
		$search_string = '"template_id":"';
		$search_start_pos = strpos( $post_meta, $search_string );

		# Protect against infinite loops
		$escape_hatch = 0;

		# Keep checking content for search string
		while( false !== $search_start_pos ) {

			# Get array of post IDs from post meta
			$search_start_pos += strlen( $search_string );
			$search_end_pos = strpos( $post_meta, '"', $search_start_pos );
			$old_repeater_item = substr( $post_meta, $search_start_pos, $search_end_pos - $search_start_pos );
			if( isset( $jedi_post_ids[ $old_repeater_item ] ) ) {
				$new_repeater_item = $jedi_post_ids[ $old_repeater_item ];

				$post_meta = str_replace(
					$search_string . $old_repeater_item . '"',
					$search_string . $new_repeater_item . '"',
					$post_meta
				);
			}

			# Reset starting search position to search remaining content
			$search_start_pos = strpos( $post_meta, $search_string, $search_end_pos );

			# Protect against infinite loops
			$escape_hatch++;
			if( $escape_hatch > 100 ) { break; }
		} # END while

		# Add Slashes For JSON Content
		$post_meta = wp_slash( $post_meta );

		update_post_meta( $jedi_post_id, '_elementor_data', $post_meta );

	} # END foreach jedi_post_ids

	# Check Each Post For Query Post IDs
	# Pattern: "posts_posts_ids":["7727","7726","7725"]
	foreach( $jedi_post_ids as $jedi_post_id ) {

		$post_meta = get_post_meta( $jedi_post_id, '_elementor_data', true );

		# Update Post IDs
		# Pattern: "posts_posts_ids":["7727","7726","7725"]
		$search_string = '"posts_posts_ids":';
		$search_start_pos = strpos( $post_meta, $search_string );

		# Protect against infinite loops
		$escape_hatch = 0;

		# Keep checking content for search string
		while( false !== $search_start_pos ) {

			# Get array of post IDs from post meta
			$search_start_pos += strlen( $search_string );
			$search_end_pos = strpos( $post_meta, ']', $search_start_pos ) + 1;

			$old_post_ids_json = substr( $post_meta, $search_start_pos, $search_end_pos - $search_start_pos );

			$post_ids_object = json_decode( $old_post_ids_json );
			if( count( $post_ids_object ) > 0 ) {
				foreach( $post_ids_object as $key => $this_post_id ) {
					if( isset( $jedi_post_ids[ $this_post_id ] ) ) {
						$post_ids_object[ $key ] = $jedi_post_ids[ $this_post_id ];
					}
				}
			}
			$new_post_ids_json = wp_json_encode( $post_ids_object );

			$post_meta = str_replace(
				$search_string . $old_post_ids_json,
				$search_string . $new_post_ids_json,
				$post_meta
			);

			# Reset starting search position to search remaining content
			$search_start_pos = strpos( $post_meta, $search_string, $search_end_pos );

			# Protect against infinite loops
			$escape_hatch++;
			if( $escape_hatch > 100 ) { break; }
		} # END while

		# Add Slashes For JSON Content
		$post_meta = wp_slash( $post_meta );

		update_post_meta( $jedi_post_id, '_elementor_data', $post_meta );

	} # END foreach jedi_post_ids
} # END jswj_jedi_elementor_postmeta()
add_action( 'jedi_after_post_import', 'jswj_jedi_elementor_postmeta' );




/*** ELEMENTOR SHORTCODES ***/

/**
 * Update Elementor Shortcodes in Post Content
 *
 * Example Shortcode: [elementor-template id="415"]
 *
 * @uses JEDI Hook jedi_after_post_import
 * @param array $jedi_post_ids An array of imported Post IDs.
 **/
function jswj_update_elementor_shortcodes( $jedi_post_ids ) {
	if( ! jswj_is_elementor() ) { return; }

	jswj_jedi_log( 'Executing jswj_update_elementor_shortcodes() ', wp_json_encode( $jedi_post_ids ) );

	foreach( $jedi_post_ids as $jedi_post_id ) {

		$the_post = get_post( $jedi_post_id );
		if( false !== strpos( $the_post->post_content, '[elementor-template id' ) ) {
			$the_shortcode_id_start = strpos( $the_post->post_content, '[elementor-template id="' ) + 24;
			$the_shortcode_id_end = strpos( $the_post->post_content, '"', $the_shortcode_id_start + 1 );
			$the_shortcode_id = substr( $the_post->post_content, $the_shortcode_id_start, $the_shortcode_id_end - $the_shortcode_id_start );
			$old_shortcode = '[elementor-template id="' . $the_shortcode_id . '"';
			$new_shortcode = '[elementor-template id="' . $jedi_post_ids[ $the_shortcode_id ] . '"';
			$the_post->post_content = str_replace( $old_shortcode, $new_shortcode, $the_post->post_content );

			jswj_jedi_log( 
				'Updating Elementor Shortcode: ',
				wp_json_encode( 
					array( 
						$jedi_post_id, 
						$the_shortcode_id, 
						$jedi_post_ids[ $the_shortcode_id ],
					) 
				) 
			);

			wp_update_post( $the_post );
		}
	} # END foreach $jedi_post_ids
}
add_action( 'jedi_after_post_import', 'jswj_update_elementor_shortcodes' );


/**
 * Set Elementor Libary Item Terms
 **/
function jswj_import_elementor_library_terms() {
	if( ! jswj_is_elementor() ) { return; }

	$jedi_apprentice_settings = get_option( 'jedi_apprentice_settings' );
	$track_import = get_option( 'jedi_track_import' );
	$jedi_post_ids = $track_import['imported_posts'];
	$jedi_category_ids = $track_import['imported_categories'];
	$categories_posts = $track_import['categories_posts'];
	$import_data = jswj_get_jedi_import_data();

	# Import and assign the terms for Elementor Library Items
	if( isset( $import_data['elementor_options']['elementor_library_terms'] ) ) {
		$elementor_library_terms = $import_data['elementor_options']['elementor_library_terms'];

		# Loop Through Library Item Posts
		foreach( $elementor_library_terms as $old_post_id => $post_terms ) {

			# Loop Through Library Item Post Terms
			foreach( $post_terms as $post_term_slug => $post_term ) {
				wp_set_post_terms( $jedi_post_ids[ $old_post_id ], $post_term_slug, 'elementor_library_type', false );
			}
		} # END foreach $elementor_library_terms

	}

} # END jswj_import_elementor_library_terms()
add_action( 'jedi_after_post_import', 'jswj_import_elementor_library_terms', 100 );
