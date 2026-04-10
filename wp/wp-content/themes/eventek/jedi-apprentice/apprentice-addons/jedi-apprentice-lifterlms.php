<?php
/**
 * Easy Demo Import Pro - WooCommerce Functions
 *
 * @package    jedi-master
 * @author     Jerry Simmons <jerry@ferventsolutions.com>
 * @copyright  2021 Jerry Simmons
 * @license    GPL-2.0+
 **/

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Update Lifter LMS Postmeta With Updated Post IDs
 *
 * @uses JEDI Hook jedi_after_post_import
 * @param array $jedi_post_ids An array of imported Post IDs.
 **/
function jswj_update_lifter_postmeta( $jedi_post_ids ) {

	if( ! is_plugin_active( 'lifterlms/lifterlms.php' ) ) { return; }

	$import_data = jswj_get_jedi_import_data();
	$postmeta = $import_data['postmeta'];
	$track_import = get_option( 'jedi_track_import' );
	$jedi_update_image_ids = $track_import['imported_media']['ids'];

	foreach( $jedi_post_ids as $old_id => $new_id ) {

		# Update Lifter LMS Redirect Info
		$redirect_type = get_post_meta( $new_id, '_llms_restriction_redirect_type', true );
		if( 'page' === $redirect_type ) {
			$redirect_page = intval( get_post_meta( $new_id, '_llms_redirect_page_id', true ) );
			$new_redirect_page = $jedi_post_ids[ $redirect_page ];
			update_post_meta( $new_id, '_llms_redirect_page_id', $new_redirect_page );
			jswj_jedi_log( 'Updating Lifter LMS Redirect Page For Post #' . $new_id, $new_redirect_page );
		}

		# Update Lifter LMS Sales Page Info
		$content_type = get_post_meta( $new_id, '_llms_sales_page_content_type', true );
		if( 'page' === $content_type ) {
			$sales_page = intval( get_post_meta( $new_id, '_llms_sales_page_content_page_id', true ) );
			$new_sales_page = $jedi_post_ids[ $sales_page ];
			update_post_meta( $new_id, '_llms_sales_page_content_page_id', $new_sales_page );
			jswj_jedi_log( 'Updating Lifter LMS Sales Page For Post #' . $new_id, $new_sales_page );
		}

		# Update Lifter LMS Restricted Levels Info
		$restricted_levels = get_post_meta( $new_id, '_llms_restricted_levels', true );
		if( ! empty( $restricted_levels ) ) {
			if( is_array( $restricted_levels ) ) {
				$restricted_levels_array = $restricted_levels;
			} elseif( is_serialized( $restricted_levels ) ) {
				$restricted_levels_array = unserialize( $restricted_levels );
			} else {
				$restricted_levels_array = array();
			}
			foreach( $restricted_levels_array as $key => $restricted_level ) {
				$restricted_levels_array[ $key ] = $jedi_post_ids[ $restricted_level ];
			}
			$new_restricted_levels = serialize( $restricted_levels_array );
			update_post_meta( $new_id, '_llms_restricted_levels', $restricted_levels_array );
			jswj_jedi_log( 'Updating Lifter LMS Restricted Levels For Post #' . $new_id, $new_restricted_levels );
		}

		# SIMPLE POST ID CONVERSIONS
		$id_conversions = array(
			'_llms_parent_id',
			'_llms_parent_course',
			'_llms_parent_section',
			'_llms_order_id',
			'_llms_lesson_id',
			'_llms_product_id',
			'_llms_engagement',
			'_llms_engagement_trigger_post',
			'_llms_generated_from_id',
			'_llms_quiz',
			'_llms_prerequisite',
		);
		foreach( $id_conversions as $meta_key ) {
			$original_id = intval( get_post_meta( $new_id, $meta_key, true ) );
			if( $original_id > 0 ) {
				$new_post_id = $jedi_post_ids[ $original_id ];
				update_post_meta( $new_id, $meta_key, $new_post_id );
				jswj_jedi_log( 
					'Updating Lifter LMS Meta [' . $meta_key . '] For Post #' . $new_id, 
					$new_post_id 
				);
			}
		}

		# Media Updates If Media Was Imported
		if( count( $jedi_update_image_ids ) > 0 ) {
			$original_image_id = intval( get_post_meta( $new_id, '_llms_achievement_image', true ) );
			if( $original_image_id > 0 ) {
				$new_image_id = $jedi_update_image_ids[ $original_image_id ];
				update_post_meta( $new_id, '_llms_achievement_image', $new_image_id );
				jswj_jedi_log( 
					'Updating Lifter LMS Meta [_llms_achievement_image] For Post #' . $new_id, 
					$new_image_id
				);
			}
		}

		# Quiz Choice Updates For Image Questions
		$llms_choices = get_post_meta( $new_id, '', false );
		foreach( $llms_choices as $meta_key => $meta_key_value ) {
			if( false !== strpos( $meta_key, '_llms_choice_' ) ) {
				foreach( $meta_key_value as $key => $choice ) {
					$choice = unserialize( $choice );
					if( 'image' === $choice['choice_type'] ) {
						$choice['choice']['id'] = $jedi_update_image_ids[ $choice['choice']['id'] ];
						$choice['choice']['src'] = wp_get_attachment_url( $choice['choice']['src'] );
					}
				}
				update_post_meta( $new_id, $meta_key, $choice );
				jswj_jedi_log(
					'Updating Lifter LMS Meta [' . $meta_key . '] For Post #' . $new_id,
					$choice['choice']['id'] 
				);
			}
		}

		# Possible Future Updates
			# _llms_image - Array...

	} # END foreach jedi_post_ids

} # END jswj_update_lifter_postmeta()
add_action( 'jedi_after_post_import', 'jswj_update_lifter_postmeta' );

