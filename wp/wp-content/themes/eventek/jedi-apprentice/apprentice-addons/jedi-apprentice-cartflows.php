<?php
/**
 * Easy Demo Import Pro - Cartflows Import
 *
 * @package    jedi-master
 * @author     Jerry Simmons <jerry@ferventsolutions.com>
 * @copyright  2021 Jerry Simmons
 * @license    GPL-2.0+
 **/

if ( ! defined( 'ABSPATH' ) ) { exit; }


/**
 * Restore Cartflows Settings
 * Update Post IDs Where Applicable
 *
 * @uses JEDI Hook jedi_import_plugin_settings
 **/
function jswj_import_cartflows_settings() {
	# Bail If WooCommerce Is Not Active
	if( !jswj_is_woocommerce() ) { return; }

	# Bail If Cartflows Is Not Active
	if( !is_plugin_active( 'cartflows/cartflows.php' ) ) { return; }

	jswj_jedi_log( 'Importing Cartflows Settings' );

	$import_data = jswj_get_jedi_import_data();

	$track_import = get_option( 'jedi_track_import' );
	$jedi_post_ids = $track_import['imported_posts'];
	$jedi_category_ids = $track_import['imported_categories'];
	$categories_posts = $track_import['categories_posts'];

	# Bail if no Cartflows settings exist in import data
	if( ! isset( $import_data['cartflows_settings'] ) ) { return; }

	$cartflows_settings = $import_data['cartflows_settings'];

	foreach( $cartflows_settings as $key => $option ) {
		if( is_serialized( $option ) ) { $option = unserialize( $option ); }
		switch( $key ) {

			# Single Post ID Updates
			case '_cartflows_common':
				$old_global_checkout = intval( $option['global_checkout'] );
				if( isset( $jedi_post_ids[ $old_global_checkout ] ) ) {
					$new_global_checkout = $jedi_post_ids[ $old_global_checkout ];
					$option['global_checkout'] = strval( $new_global_checkout );
				}
				break;
			case 'cartflows-divi-flows-and-steps-1':
			case 'cartflows-elementor-flows-and-steps-1':
			case 'cartflows-beaver-builder-flows-and-steps-1':
			case 'cartflows-gutenberg-flows-and-steps-1':
				$option = jswj_jedi_update_ids_in_steps( $option );
				break;
		}
		update_option( $key, $option );
		jswj_jedi_log( 'Updating Cartflows Setting: ' . $key );
	} # END foreach yith_wcwl_settings

	# Update Post Meta
	foreach( $jedi_post_ids as $old_post_id => $new_post_id ) {
		if( 'cartflows_step' === get_post_type( $new_post_id ) ) {
			$meta_post_id = get_post_meta( $new_post_id, 'wcf-flow-id', true );
			if( isset( $jedi_post_ids[ $meta_post_id ] ) ) {
				update_post_meta( $new_post_id, 'wcf-flow-id', $jedi_post_ids[ $meta_post_id ], $meta_post_id );
			}
		}
		if( 'cartflows_flow' === get_post_type( $new_post_id ) ) {
			$flow_post_meta = get_post_meta( $new_post_id, 'wcf-steps', true );
			foreach( $flow_post_meta as $meta_index => $meta_array ) {
				$ref_id = $meta_array['id'];
				if( isset( $jedi_post_ids[ $ref_id ] ) ) {
					$flow_post_meta[$meta_index]['id'] = $jedi_post_ids[ $ref_id ];
				}
			}
			update_post_meta( $new_post_id, 'wcf-steps', $flow_post_meta );
		}
	}

} # END jswj_import_cartflows_settings()
add_action( 'jedi_import_plugin_settings', 'jswj_import_cartflows_settings', 100 );


/**
 * Update Post & Term IDs In Cartflows Steps
 *
 * @param array $option
 * @param array $jedi_post_ids
 * @return array
 */
function jswj_jedi_update_ids_in_steps( $option ) {

	$track_import = get_option( 'jedi_track_import' );
	$jedi_post_ids = $track_import['imported_posts'];
	$jedi_category_ids = $track_import['imported_categories'];
	$categories_posts = $track_import['categories_posts'];

	foreach( $option as $index => $flow ) {

		# Update The Flow ID
		if( isset( $jedi_post_ids[ $flow['ID'] ] ) ) {
			$new_flow_id = $jedi_post_ids[ $flow['ID'] ];
			$option[$index]['ID'] = $new_flow_id;
		}

		# Loop Through Steps To Update Step IDs (Posts)
		foreach( $flow['steps'] as $step_index => $step_array ) {
			if( isset( $jedi_post_ids[ $step_array['ID'] ] ) ) {
				$new_step_id = $jedi_post_ids[ $step_array['ID'] ];
				$option[$index]['steps'][$step_index]['ID'] = $new_step_id;
			}
		}

		# Loop Through Setting To Update Step IDs (Terms)
		foreach( $flow['cartflows_flow_page_builder'] as $pb_index => $pb_array ) {
			if( isset( $jedi_category_ids[ $pb_array['term_id'] ] ) ) {
				$new_pb_id = $jedi_category_ids[ $pb_array['term_id'] ];
				$option[$index]['cartflows_flow_page_builder'][$pb_index]['term_id'] = $new_pb_id;
				$option[$index]['cartflows_flow_page_builder'][$pb_index]['term_taxonomy_id'] = $new_pb_id;
			}
		}

		# Loop Through Setting To Update Step IDs (Terms)
		foreach( $flow['cartflows_flow_category'] as $pb_index => $pb_array ) {
			if( isset( $jedi_category_ids[ $pb_array['term_id'] ] ) ) {
				$new_pb_id = $jedi_category_ids[ $pb_array['term_id'] ];
				$option[$index]['cartflows_flow_category'][$pb_index]['term_id'] = $new_pb_id;
				$option[$index]['cartflows_flow_category'][$pb_index]['term_taxonomy_id'] = $new_pb_id;
			}
		}

		# Loop Through Setting To Update Step IDs (Terms)
		foreach( $flow['cartflows_flow_type'] as $pb_index => $pb_array ) {
			if( isset( $jedi_category_ids[ $pb_array['term_id'] ] ) ) {
				$new_pb_id = $jedi_category_ids[ $pb_array['term_id'] ];
				$option[$index]['cartflows_flow_type'][$pb_index]['term_id'] = $new_pb_id;
				$option[$index]['cartflows_flow_type'][$pb_index]['term_taxonomy_id'] = $new_pb_id;
			}
		}

	}

	return $option;
} # END jswj_jedi_update_ids_in_steps()