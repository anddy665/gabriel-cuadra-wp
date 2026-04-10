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
 * Sync Theme Builder Post IDs
 **/
function jswj_import_divi_theme_builder_data() {
	jswj_verify_ajax_nonce_and_capability( 'jswj_import_process_nonce', 'jedi_ajax_nonce' );

	if( ! jswj_is_divi() ) {
		echo wp_json_encode( array( 1, 'Divi Not Installed, Skipping Theme Builder Import' ) );
		wp_die();
	}

	$jedi_apprentice_settings = get_option( 'jedi_apprentice_settings' );
	$track_import = get_option( 'jedi_track_import' );
	$jedi_post_ids = $track_import['imported_posts'];
	$jedi_category_ids = $track_import['imported_categories'];
	$categories_posts = $track_import['categories_posts'];
	$import_data = jswj_get_jedi_import_data();

	/**
	 * Check if Theme Builder Post already exists
	 * If it does, use that for imported theme builder content
	 * If it doesn't, use the one from imported content
	 **/
	$theme_builder_post_id = et_theme_builder_get_theme_builder_post_id( 'publish', false );
	if( 0 === $theme_builder_post_id ) {
		$imported_theme_builder_post_id = $import_data['divi_theme_builder']['theme_builder_post_id'];

		if( isset( $jedi_post_ids[ $imported_theme_builder_post_id ] ) ) {
			# Use Imported Theme Builder Post
			$theme_builder_post_id = $import_data['divi_theme_builder']['theme_builder_post_id'];

		} else {

			# Catch Error When No Theme Builder Post On Site, Or In Import Content
			jswj_jedi_log(
				'Error Identifying Theme Builder Post: ',
				wp_json_encode(
					array(
						'theme_builder_post_id'          => $theme_builder_post_id,
						'imported_theme_builder_post_id' => $imported_theme_builder_post_id,
					)
				)
			);
			return false;

		}
	} # END if theme_builder_post_id

	/**
	 * Add Imported Templates To Theme Builder
	 **/
	$imported_theme_builder_template_ids = $import_data['divi_theme_builder']['theme_builder_template_ids'];
	foreach( $imported_theme_builder_template_ids as $template_id ) {
		add_post_meta(
			$theme_builder_post_id,
			'_et_template',
			$jedi_post_ids[ $template_id ]
		);
	}

	$theme_builder_post_types = array(
		ET_THEME_BUILDER_TEMPLATE_POST_TYPE,
		ET_THEME_BUILDER_HEADER_LAYOUT_POST_TYPE,
		ET_THEME_BUILDER_BODY_LAYOUT_POST_TYPE,
		ET_THEME_BUILDER_FOOTER_LAYOUT_POST_TYPE,
	);
	$theme_builder_layout_meta_slugs = array(
		'_et_header_layout_id',
		'_et_body_layout_id',
		'_et_footer_layout_id',
	);

	/**
	 * Loop Through Imported Posts To Update Theme Builder Meta Data
	 **/
	foreach( $jedi_post_ids as $old_id => $new_id ) {
		$post_type = get_post_type( $new_id );

		# Update Layout IDs
		if( in_array( $post_type, $theme_builder_post_types, true ) ) {
			foreach( $theme_builder_layout_meta_slugs as $meta_slug ) {

				if( metadata_exists( 'post', $new_id, $meta_slug ) ) {
					$meta_value = get_post_meta( $new_id, $meta_slug, true );

					if( isset( $jedi_post_ids[ $meta_value ] ) ) {
						update_post_meta(
							$new_id,
							$meta_slug,
							$jedi_post_ids[ $meta_value ],
							$meta_value
						);
					}
				}
			}

			/**
			 * Make Sure Old Post IDs Are Not Attached To Theme Builder Templates
			 **/
			if( 'et_template' === $post_type ) {
				delete_post_meta( $theme_builder_post_id, '_et_template', $old_id );
			}
		}

		# Update Meta _et_use_on
		if( metadata_exists( 'post', $new_id, '_et_use_on' ) ) {
			$use_on = get_post_meta( $new_id, '_et_use_on', false );

			$useon_post_ids = $track_import['imported_posts'];
			$useon_category_ids = $track_import['imported_categories'];

			delete_post_meta( $new_id, '_et_use_on' );
			foreach ( $use_on as $condition ) {
				foreach( $useon_post_ids as $useon_old_id => $useon_new_id ) {
					# Pages
					if( strpos( $condition, 'page:id:' . $useon_old_id ) !== false ) {
						$condition = str_replace( 'page:id:' . $useon_old_id, 'page:id:' . $useon_new_id, $condition );
					}

					# Posts
					if( strpos( $condition, 'post:id:' . $useon_old_id ) !== false ) {
						$condition = str_replace( 'post:id:' . $useon_old_id, 'post:id:' . $useon_new_id, $condition );
					}

					# Children
					if( strpos( $condition, 'children:id:' . $useon_old_id ) !== false ) {
						$condition = str_replace( 'children:id:' . $useon_old_id, 'children:id:' . $useon_new_id, $condition );
					}

					# Single Custom Post Type
					if( strpos( $condition, 'singular:post_type' ) !== false
						&& strpos( $condition, 'id:' . $useon_old_id ) !== false ) {
							$condition = str_replace( 'id:' . $useon_old_id, 'id:' . $useon_new_id, $condition );
					}
				}

				foreach( $useon_category_ids as $useon_old_cat_id => $useon_new_cat_id ) {
					if( strpos( $condition, 'term:id:' . $useon_old_cat_id ) !== false ) {
						$condition = str_replace( 'term:id:' . $useon_old_cat_id, 'term:id:' . $useon_new_cat_id, $condition );
					}
				}

				add_post_meta( $new_id, '_et_use_on', $condition );
			} # END foreach use_on
		} # END _et_use_on

		# Update Meta _et_exclude_from
		if( metadata_exists( 'post', $new_id, '_et_exclude_from' ) ) {
			$exclude_from = get_post_meta( $new_id, '_et_exclude_from', false );

			$excludefrom_post_ids = $track_import['imported_posts'];
			$excludefrom_category_ids = $track_import['imported_categories'];

			delete_post_meta( $new_id, '_et_exclude_from' );
			foreach ( $exclude_from as $condition ) {
				foreach( $excludefrom_post_ids as $excludefrom_old_id => $excludefrom_new_id ) {
					# Pages
					if( strpos( $condition, 'page:id:' . $excludefrom_old_id ) !== false ) {
						$condition = str_replace( 'page:id:' . $excludefrom_old_id, 'page:id:' . $excludefrom_new_id, $condition );
					}

					# Posts
					if( strpos( $condition, 'post:id:' . $excludefrom_old_id ) !== false ) {
						$condition = str_replace( 'post:id:' . $excludefrom_old_id, 'post:id:' . $excludefrom_new_id, $condition );
					}

					# Children
					if( strpos( $condition, 'children:id:' . $excludefrom_old_id ) !== false ) {
						$condition = str_replace( 'children:id:' . $excludefrom_old_id, 'children:id:' . $excludefrom_new_id, $condition );
					}
				}
				foreach( $excludefrom_category_ids as $excludefrom_old_cat_id => $excludefrom_new_cat_id ) {
					if( strpos( $condition, 'term:id:' . $excludefrom_old_cat_id ) !== false ) {
						$condition = str_replace( 'term:id:' . $excludefrom_old_cat_id, 'term:id:' . $excludefrom_new_cat_id, $condition );
					}
				}

				add_post_meta( $new_id, '_et_exclude_from', $condition );
			}# END foreach exclude_from
		} # END _et_exclude_from
	} # END foreach jedi_post_ids

	# Clean Up
	et_theme_builder_trash_draft_and_unused_posts();

	jswj_jedi_log( 'Divi Theme Builder Content Imported' );

	echo wp_json_encode( array( 1, 'Divi Theme Builder Content Imported' ) );
	wp_die();

} # END jswj_import_divi_theme_builder_data()
add_action( 'wp_ajax_jswj_jedi_import_divi_theme_builder_data', 'jswj_import_divi_theme_builder_data', 101 );
