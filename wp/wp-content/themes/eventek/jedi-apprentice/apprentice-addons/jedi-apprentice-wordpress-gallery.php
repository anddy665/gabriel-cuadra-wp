<?php
/**
 * Easy Demo Import Pro - WordPress Gallery Shortcode
 *
 * @package    jedi-master
 * @author     Jerry Simmons <jerry@ferventsolutions.com>
 * @copyright  2021 Jerry Simmons
 * @license    GPL-2.0+
 **/

if( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Update Media IDs In WordPress Gallery Shortcodes
 * 
 * @param string $post_content - Post Content.
 * 
 * @return string
 **/
function jswj_wp_gallery_images( $post_content ) {
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

	# Update Image IDs Referenced In Gallery Module
	if( $process_media ) {
		$begin_search = 0;
		while( strpos( $post_content, '[gallery ', $begin_search ) ) {

			# Get Gallery Shortcode
			$gallery_module_start = strpos( $post_content, '[gallery ', $begin_search );
			$gallery_module_end = strpos( $post_content, ']', $gallery_module_start ) + 1;
			$shortcode_text = substr(
				$post_content,
				$gallery_module_start,
				$gallery_module_end - $gallery_module_start
			);

			# Parse Shortcode To Get IDs
			$gallery_ids_start = strpos( $post_content, 'ids="', $gallery_module_start ) + 5;
			$gallery_ids_end = strpos( $post_content, '"', $gallery_ids_start + 1 );
			$gallery_ids = substr(
				$post_content,
				$gallery_ids_start,
				$gallery_ids_end - $gallery_ids_start
			);

			# Loop Through Media IDs
			$gallery_id_array = explode( ',', $gallery_ids );
			foreach( $gallery_id_array as $key => $gallery_id ) {
				if( isset( $jedi_update_image_ids[ $gallery_id ] ) ) {
					$gallery_id_array[ $key ] = $jedi_update_image_ids[ $gallery_id ];
				}
			}
			$new_gallery_ids = implode( ',', $gallery_id_array );

			# Update Post Content
			$post_content = str_replace( $gallery_ids, $new_gallery_ids, $post_content );

			# Search The Rest Of The Post
			$begin_search = $gallery_module_end + 1;
		}
	}

	return $post_content;

} # END jswj_divi_postcontent_filter()
add_filter( 'jedi_modify_post_content', 'jswj_wp_gallery_images', 1, 1 );



