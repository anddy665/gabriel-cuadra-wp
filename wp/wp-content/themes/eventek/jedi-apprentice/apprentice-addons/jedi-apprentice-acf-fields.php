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
 * Advanced Custom Fields - Update Postmeta To Match New Post & Media IDs
 **/
function jswj_update_acf_postmeta() {

	# Bail If Advanced Custom Fields Is Not Active
	if( !is_plugin_active( 'advanced-custom-fields/acf.php' ) ) { return; }

	$jedi_apprentice_settings = get_option( 'jedi_apprentice_settings' );
	$track_import = get_option( 'jedi_track_import' );
	$jedi_post_ids = $track_import['imported_posts'];
	$jedi_category_ids = $track_import['imported_categories'];
	$categories_posts = $track_import['categories_posts'];
	$import_data = jswj_get_jedi_import_data();


	# If Media were imported, load imported data
	$process_media = false;
	if( count( $track_import['imported_media']['ids'] ) > 0 ) {
		$process_media = true;
		$jedi_imported_media = $track_import['imported_media'];
		$jedi_update_image_urls = $jedi_imported_media['urls'];
		$jedi_update_image_ids = $jedi_imported_media['ids'];
	}

	$acf_image_array = array();

	/**
	 * Work Through Field Groups
	 */
	foreach( $jedi_post_ids as $old_id => $new_id ) {
		$post_type = get_post_type( $new_id );
		if( 'acf-field-group' !== $post_type ) { 
			continue; 
		}
		$acf_post_group = get_post( $new_id );
		$posts = get_posts( array('post_parent'=>strval($new_id)));

		$args = array(
			'posts_per_page' => -1,
			'post_parent'    => $new_id,
			'post_type'      => 'acf-field',
		);
	
		$children = get_children( $args );

		foreach( $children as $child ) {
			$child_post_content = unserialize( $child->post_content );
			$group_parent_slug = $child->post_excerpt . '_';
			if( 'group' === $child_post_content['type'] ) {
				$child_args = array(
					'posts_per_page' => -1,
					'post_parent'    => $child->ID,
					'post_type'      => 'acf-field',
				);
				$kidskids = get_children( $child_args );

				foreach( $kidskids as $kidskid ) {
					$kidskid_post_content = unserialize( $kidskid->post_content );
					if( 'image' === $kidskid_post_content['type'] ) {
						$postmeta_slug = $group_parent_slug;
						$postmeta_slug .= $kidskid->post_excerpt;
						$acf_image_array[] = $postmeta_slug;
					}

				}

			}
		}
	} # END foreach jedi_post_ids


	/**
	 * Build Image Array
	 **/
	foreach( $jedi_post_ids as $old_id => $new_id ) {
		$post_type = get_post_type( $new_id );
		if( 'acf-field' !== $post_type ) { 
			continue; 
		}

		$acf_post = get_post( $new_id );
		$acf_post_content = unserialize( $acf_post->post_content );
		$acf_field_slug = $acf_post->post_excerpt;

		if( $process_media ) {
			if( 'image' === $acf_post_content['type'] ) {
				$acf_image_array[] = $acf_field_slug;
			} else if( 'group'  === $acf_post_content['type'] ) {

			}
		}
	} # END foreach jedi_post_ids

	$acf_image_unique_array = array_unique( $acf_image_array );

	foreach( $jedi_post_ids as $old_id => $new_id ) {
		if( $process_media ) {
			$meta_data = get_post_meta( $new_id );

			foreach( $meta_data as $meta_slug => $meta_value ) {
				foreach( $acf_image_unique_array as $acf_image_slug ) {
					if( false !== strpos( $meta_slug, $acf_image_slug ) ) {
						if( is_array( $meta_value ) ) {
							foreach( $meta_value as $single_meta_value ) {
								if( !isset( $jedi_update_image_ids[$single_meta_value] ) ) { continue; }
								update_post_meta(
									$new_id,
									$acf_image_slug,
									$jedi_update_image_ids[$single_meta_value],
									$single_meta_value
								);
							}
						} else {
							if( isset( $jedi_update_image_ids[$meta_value] ) ) { 
								update_post_meta(
									$new_id,
									$acf_image_slug,
									$jedi_update_image_ids[$meta_value]
								);
							}
						}
					}
				}
			}
		}
	} # END foreach jedi_post_ids


	jswj_jedi_log( 'ACF Field Postmeta Updated' );

} # END jswj_update_acf_postmeta()
add_action( 'jedi_after_post_import', 'jswj_update_acf_postmeta', 101 );
