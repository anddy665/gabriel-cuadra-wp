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
 * Functions for plugin Premium Addons for Elementor
 * Plugin URL: https://wordpress.org/plugins/premium-addons-for-elementor/
 **/


/**
 * Update Carousel Slider Post IDs In Post Meta
 *
 * Find & Replace Post IDs
 * Add Slashes For JSON Content
 *
 * @param array $jedi_post_ids - Array of post IDs.
 **/
function jswj_jedi_premium_carousel_slider_update_postmeta( $jedi_post_ids ) {
	if( ! is_plugin_active( 'premium-addons-for-elementor/premium-addons-for-elementor.php' ) ) { return; }

	# Check Each Post For Carousel Slider
	# Pattern: "premium_carousel_slider_content":"["8410","8888"]"
	foreach( $jedi_post_ids as $jedi_post_id ) {

		$post_meta = get_post_meta( $jedi_post_id, '_elementor_data', true );

		# Update Slider Content IDs
		$search_string = '"premium_carousel_slider_content":';
		$search_start_pos = strpos( $post_meta, $search_string );

		# Protect against infinite loops
		$escape_hatch = 0;

		# Keep checking content for search string
		while( false !== $search_start_pos ) {

			# Get array of post IDs from post meta
			$search_start_pos += strlen( $search_string );
			$search_end_pos = strpos( $post_meta, ']', $search_start_pos ) + 1;
			$slider_id_string = substr( $post_meta, $search_start_pos, $search_end_pos - $search_start_pos );

			# Loop through Post IDs, replace with imported Post IDs
			$slider_ids = json_decode( $slider_id_string );
			foreach( $slider_ids as $key => $old_post_id ) {
				$slider_ids[ $key ] = strval( $jedi_post_ids[ $old_post_id ] );
			}
			$post_meta = str_replace( $slider_id_string, wp_json_encode( $slider_ids ), $post_meta );

			# Reset starting search position to search remaining content
			$search_start_pos = strpos( $post_meta, $search_string, $search_end_pos );

			# Protect against infinite loops
			$escape_hatch++;
			if( $escape_hatch > 100 ) { break; }
		} # END while

		# Update Repeater Item IDs
		# Pattern: "premium_carousel_repeater_item":"8410"
		$search_string = '"premium_carousel_repeater_item":"';
		$search_start_pos = strpos( $post_meta, $search_string );

		# Protect against infinite loops
		$escape_hatch = 0;

		# Keep checking content for search string
		while( false !== $search_start_pos ) {

			# Get array of post IDs from post meta
			$search_start_pos += strlen( $search_string );
			$search_end_pos = strpos( $post_meta, '"', $search_start_pos );
			$old_repeater_item = substr( $post_meta, $search_start_pos, $search_end_pos - $search_start_pos );
			$new_repeater_item = $jedi_post_ids[ $old_repeater_item ];

			$post_meta = str_replace(
				$search_string . $old_repeater_item . '"',
				$search_string . $new_repeater_item . '"',
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

	# Check Each Post For Popup Codes
	# Pattern: {"popup":"8294"}
	# Pattern: %7B%22popup%22%3A%228294%22%7D
	foreach( $jedi_post_ids as $jedi_post_id ) {

		$post_meta = get_post_meta( $jedi_post_id, '_elementor_data', true );
		$search_string = '%7B%22popup%22%3A%22';
		$search_start_pos = strpos( $post_meta, $search_string );

		# Protect against infinite loops
		$escape_hatch = 0;

		# Keep checking content for search string
		while( false !== $search_start_pos ) {

			# Get array of post IDs from post meta
			$search_start_pos += strlen( $search_string );
			if( $search_start_pos > strlen( $post_meta ) ) { break; }

			$search_end_pos = strpos( $post_meta, '%22%7D', $search_start_pos );
			$popup_id_string = substr( $post_meta, $search_start_pos, $search_end_pos - $search_start_pos );

			if( ! isset( $jedi_post_ids[ $popup_id_string ] ) ) { continue; }

			$post_meta = str_replace( $popup_id_string, $jedi_post_ids[ $popup_id_string ], $post_meta );

			# Reset starting search position to search remaining content
			$search_start_pos = strpos( $post_meta, $search_string, $search_end_pos );

			# Protect against infinite loops
			$escape_hatch++;
			if( $escape_hatch > 100 ) { break; }
		} # END while

		# Add Slashes For JSON Content
		$post_meta = wp_slash( $post_meta );

		update_post_meta( $jedi_post_id, '_elementor_data', $post_meta );

	} # END foreach $jedi_post_ids

} # END jswj_jedi_premium_carousel_slider_update_postmeta()
add_action( 'jedi_after_post_import', 'jswj_jedi_premium_carousel_slider_update_postmeta' );
