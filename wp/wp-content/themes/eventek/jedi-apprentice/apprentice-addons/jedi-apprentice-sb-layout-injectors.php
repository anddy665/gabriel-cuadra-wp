<?php
/**
 * Easy Demo Import Pro - Sean Barton's Injector Setting Functions
 *
 * @package    jedi-master
 * @author     Jerry Simmons <jerry@ferventsolutions.com>
 * @copyright  2021 Jerry Simmons
 * @license    GPL-2.0+
 **/

if ( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Update Sean Barton's Layout Injector Plugin Settings With Post IDs
 *
 * @uses JEDI Hook jedi_after_post_import
 * @param array $jedi_post_ids An array of imported Post IDs.
 **/
function jswj_update_sb_layout_injector_settings( $jedi_post_ids ) {

	$import_data = jswj_get_jedi_import_data();
	$track_import = get_option( 'jedi_track_import' );

	if( ! isset( $import_data['sean_barton_injector_settings'] ) ) { return; }

	$jedi_master_sb_injector_export = $import_data['sean_barton_injector_settings'];

	foreach( $jedi_master_sb_injector_export as $key => $option ) {
		if( is_serialized( $option ) ) { $option = unserialize( $option ); }
		switch( $key ) {

			# Single Post ID Updates
			case 'sb_et_cpt_li_post_layout':
			case 'sb_et_cpt_li_post_archive_layout':
			case 'sb_et_cpt_li_project_layout':
			case 'sb_et_cpt_li_project_archive_layout':
			case 'sb_et_cpt_li_woocarousel_layout':
			case 'sb_divi_fe_pre-footer':
			case 'sb_et_search_li_layout':
			case 'sb_et_search_li_pt_post_layout':
			case 'sb_et_search_li_pt_page_layout':
			case 'sb_et_search_li_pt_product_layout':
			case 'sb_et_search_li_pt_project_layout':
			case 'sb_et_search_li_pt_woocarousel_layout':
			case 'sb_et_woo_li_product_page':
			case 'sb_et_woo_li_product_cat_archive_general':
			case 'sb_et_woo_li_product_tag_archive_general':
			case 'sb_et_woo_li_shop_archive_page':
				if( isset( $jedi_post_ids[ $option ] ) ) { $option = $jedi_post_ids[ $option ]; }
				break;

			# CPT Taxonomies
			case 'sb_et_cpt_li_post_taxonomies':
			case 'sb_et_cpt_li_page_taxonomies':
			case 'sb_et_cpt_li_project_taxonomies':
			case 'sb_et_cpt_li_woocarousel_taxonomies':
				if( ! is_array( $option ) ) { break; }
				if( isset( $option['category'] ) ) {
					if( is_serialized( $option['category'] ) ) {
						$option = unserialize( $option['category'] );
					}
					foreach( $option['category'] as $category => $category_layout ) {
						if( isset( $jedi_post_ids[ $category_layout ] ) ) {
							$option['category'][ $category ] = $category_layout;
						}
					}
				}
				break;

			# Taxonomy Array Of Post ID Updates
			case 'sb_et_tax_li':
				if( ! is_array( $option ) ) { break; }

				if( isset( $option['category']['archive'] ) &&
					isset( $jedi_post_ids[ $option['category']['archive'] ] ) ) {
					$option['category']['archive'] = $jedi_post_ids[ $option['category']['archive'] ];
				}
				if( isset( $option['category']['archive']['terms'] ) ) {
					if( is_serialized( $option['category']['archive']['terms'] ) ) {
						$option = unserialize( $option['category']['archive']['terms'] );
					}
					foreach( $option['category']['archive']['terms'] as $term_key => $term_post ) {
						if( isset( $jedi_post_ids[ $term_post ] ) ) {
							$option['category']['archive']['terms'][ $term_key ] = $term_post;
						}
					}
				}

				if( isset( $option['author_archives']['archive'] ) &&
					isset( $jedi_post_ids[ $option['author_archives']['archive'] ] ) ) {
					$option['author_archives']['archive'] = $jedi_post_ids[ $option['author_archives']['archive'] ];
				}
				if( isset( $option['author_archives']['archive']['terms'] ) ) {
					if( is_serialized( $option['author_archives']['archive']['terms'] ) ) {
						$option = unserialize( $option['author_archives']['archive']['terms'] );
					}
					foreach( $option['author_archives']['archive']['terms'] as $term_key => $term_post ) {
						if( isset( $jedi_post_ids[ $term_post ] ) ) {
							$option['author_archives']['archive']['terms'][ $term_key ] = $term_post;
						}
					}
				}

				if( isset( $option['date_archives']['archive'] ) &&
					isset( $jedi_post_ids[ $option['date_archives']['archive'] ] ) ) {
					$option['date_archives']['archive'] = $jedi_post_ids[ $option['date_archives']['archive'] ];
				}
				if( isset( $option['date_archives']['archive']['terms'] ) ) {
					if( is_serialized( $option['date_archives']['archive']['terms'] ) ) {
						$option = unserialize( $option['date_archives']['archive']['terms'] );
					}
					foreach( $option['date_archives']['archive']['terms'] as $term_key => $term_post ) {
						if( isset( $jedi_post_ids[ $term_post ] ) ) {
							$option['date_archives']['archive']['terms'][ $term_key ] = $term_post;
						}
					}
				}

				break;

			# Array Of Post ID Updates
			case 'sb_et_woo_li_product_cat':
			case 'sb_et_woo_li_product_tag':
			case 'sb_et_woo_li_product_cat_archive':
			case 'sb_et_woo_li_product_tag_archive':
				if( ! is_array( $option ) ) { break; }

				foreach( $option as $term_key => $term_post ) {
					if( isset( $jedi_post_ids[ $term_post ] ) ) {
						$option[ $term_key ] = $jedi_post_ids[ $term_post ];
					}
				}
				break;
		}
		update_option( $key, $option );
		jswj_jedi_log( 'Updating SB Layout Injector Setting: ' . $key, wp_json_encode( $option ) );
	}

} # END jswj_update_sb_layout_injector_settings()
add_action( 'jedi_after_post_import', 'jswj_update_sb_layout_injector_settings' );

/**
 * Update Loop Selections In Library Layouts
 *
 * @uses JEDI Hook jedi_after_post_import
 * @param array $jedi_post_ids An array of imported Post IDs.
 **/
function jswj_update_sb_layout_loop_shortcodes( $jedi_post_ids ) {

	foreach( $jedi_post_ids as $jedi_post_id ) {
		$sb_loop_post = get_post( $jedi_post_id );
		if( false !== strpos( $sb_loop_post->post_content, '[et_pb_' ) &&
			false !== strpos( $sb_loop_post->post_content, 'loop_layout="' ) ) {
			$sb_loop_id_start = strpos( $sb_loop_post->post_content, 'loop_layout="' ) + 13;
			$sb_loop_id_end = strpos( $sb_loop_post->post_content, '"', $sb_loop_id_start );
			$sb_loop_id = substr( $sb_loop_post->post_content, $sb_loop_id_start, $sb_loop_id_end - $sb_loop_id_start );

			if( empty( $sb_loop_id ) ) { continue; }

			$old_loop_attr = 'loop_layout="' . $sb_loop_id . '"';
			$new_loop_attr = 'loop_layout="' . $jedi_post_ids[ $sb_loop_id ] . '"';
			$sb_loop_post->post_content = str_replace( $old_loop_attr, $new_loop_attr, $sb_loop_post->post_content );
			wp_update_post( $sb_loop_post );
			jswj_jedi_log( 
				'Updating SB Layout Injector Post: ' . $jedi_post_id,
				$old_loop_attr . ' - ' . $new_loop_attr 
			);
		}
	}
}
add_action( 'jedi_after_post_import', 'jswj_update_sb_layout_loop_shortcodes' );


/**
 * Update Sean Barton's Layout Injector Plugin Post Meta
 *
 * @uses JEDI Hook jedi_after_post_import
 * @param array $jedi_post_ids An array of imported Post IDs.
 **/
function jswj_update_sb_layout_injector_postmeta( $jedi_post_ids ) {

	$import_data = jswj_get_jedi_import_data();
	$postmeta = $import_data['postmeta'];
	$track_import = get_option( 'jedi_track_import' );

	foreach( $jedi_post_ids as $old_id => $new_id ) {

		# Get WooCommerce Gallery Media IDs
		$meta_value = get_post_meta( $new_id, 'sb_divi_fe_layout_overrides', true );
		if( empty( $meta_value ) ) { continue; }

		if( is_serialized( $meta_value ) ) {
			$meta_value = unserialize( $meta_value );
		}
		$original_meta_value = $meta_value;
		foreach( $meta_value as $key => $value ) {
			if( isset( $jedi_post_ids[ $value ] ) ) {
				$meta_value[ $key ] = $jedi_post_ids[ $value ];
			}
		}

		if( $original_meta_value !== $meta_value ) {
			jswj_jedi_log( 
				'Updating SB Layout Injector Meta: sb_divi_fe_layout_overrides',
				wp_json_encode( $meta_value )
			);
			update_post_meta( $new_id, 'sb_divi_fe_layout_overrides', $meta_value );
		}
	} # END foreach jedi_post_ids

} # END jswj_update_sb_layout_injector_postmeta()
add_action( 'jedi_after_post_import', 'jswj_update_sb_layout_injector_postmeta' );
