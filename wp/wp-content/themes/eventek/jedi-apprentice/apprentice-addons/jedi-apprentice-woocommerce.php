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
 * Update WooCommerce Product Galleries With New Media IDs
 *
 * @uses JEDI Hook jedi_after_post_import
 * @param array $jedi_post_ids An array of imported Post IDs.
 **/
function jswj_update_woocommerce_galleries( $jedi_post_ids ) {

	$import_data = jswj_get_jedi_import_data();
	$postmeta = $import_data['postmeta'];
	$track_import = get_option( 'jedi_track_import' );

	# Bail If WooCommerce Is Not Active
	if( !jswj_is_woocommerce() ) { return; }

	# Bail If No Media Imported
	if( count( $track_import['imported_media']['ids'] ) === 0 ) { return; }

	$jedi_update_image_ids = $track_import['imported_media']['ids'];

	foreach( $jedi_post_ids as $old_id => $new_id ) {

		# Get WooCommerce Gallery Media IDs
		$meta_value = get_post_meta( $new_id, '_product_image_gallery', true );
		if( empty( $meta_value ) ) { continue; }

		# Loop Through Media IDs
		$gallery_id_array = explode( ',', $meta_value );
		foreach( $gallery_id_array as $key => $gallery_id ) {
			if( isset( $jedi_update_image_ids[ $gallery_id ] ) ) {
				$gallery_id_array[ $key ] = $jedi_update_image_ids[ $gallery_id ];
			}
		}
		$new_gallery_ids = implode( ',', $gallery_id_array );

		jswj_jedi_log( 'Updating WooCommerce Gallery For Product ID #' . $new_id, $new_gallery_ids );
		update_post_meta( $new_id, '_product_image_gallery', $new_gallery_ids );
	} # END foreach jedi_post_ids

} # END jswj_update_woocommerce_galleries()
add_action( 'jedi_after_post_import', 'jswj_update_woocommerce_galleries' );


/**
 * Restore WooCommerce Settings
 * Update Post IDs Where Applicable
 *
 * @uses JEDI Hook jedi_after_post_import
 **/
function jswj_update_woocommerce_settings() {
	# Bail If WooCommerce Is Not Active
	if( !jswj_is_woocommerce() ) { return; }

	$import_data = jswj_get_jedi_import_data();
	$track_import = get_option( 'jedi_track_import' );
	$jedi_post_ids = $track_import['imported_posts'];
	$jedi_update_image_ids = $track_import['imported_media']['ids'];

	if( ! isset( $import_data['woocommerce_settings'] ) ) { return; }

	$jedi_master_woocommerce_export = $import_data['woocommerce_settings'];

	foreach( $jedi_master_woocommerce_export as $key => $option ) {
		switch( $key ) {

			# Single Post ID Updates
			case 'woocommerce_shop_page_id':
			case 'woocommerce_cart_page_id':
			case 'woocommerce_checkout_page_id':
			case 'woocommerce_myaccount_page_id':
			case 'woocommerce_terms_page_id':
				if( isset( $jedi_post_ids[ $option ] ) ) { 
					$option = $jedi_post_ids[ $option ]; 
				}
				break;
			case 'woocommerce_placeholder_image':
				if( isset( $jedi_update_image_ids[ $option ] ) ) { 
					$option = $jedi_update_image_ids[ $option ]; 
				}
				break;
		}
		update_option( $key, $option );
		jswj_jedi_log( 'Updating WooCommerce Setting: ' . $key, serialize( $option ) );
	} # END foreach jedi_master_woocommerce_export

	# Flush Rewrite Rules
	flush_rewrite_rules();

	# Queue Rewrite Flush In WooCommerce
	update_option( 'woocommerce_queue_flush_rewrite_rules', 'yes' );

} # END jswj_update_woocommerce_settings()
add_action( 'jedi_import_plugin_settings', 'jswj_update_woocommerce_settings', 10 );



/**
 * 
 * Product Attributes
 * Step 1 - Import Attributes: After Media Import So That Terms Can Sync Up
 * Step 2 - Import Term Meta: After Category Import
 * 
 */

/**
 * Import WooCommerce Product Attributes
 *
 * @return void
 */
function jswj_jedi_import_wc_product_attribute_taxonomies() {
	# Bail If WooCommerce Is Not Active
	if( !jswj_is_woocommerce() ) { return; }

	$import_data = jswj_get_jedi_import_data();
	$track_import = get_option( 'jedi_track_import' );

	# Bail If No Taxonomies In Import Data
	if( !isset( $import_data['wc_product_attribute_taxonomies'] ) ) { return; }

	$import_attributes = $import_data['wc_product_attribute_taxonomies'];

	foreach( $import_attributes as $old_attribute_id => $import_attribute ) {
		$new_attribute_id = wc_create_attribute(
			array(
				'name'         => $import_attribute->attribute_label,
				'slug'         => $import_attribute->attribute_name,
				'type'         => $import_attribute->attribute_type,
				'order_by'     => $import_attribute->attribute_orderby,
				'has_archives' => false,
			)
		);

		if( is_wp_error( $new_attribute_id ) ) {
			jswj_jedi_log( 'Error Importing Product Attribute Taxonomy', $new_attribute_id->get_error_message() );
		} else {
			jswj_jedi_log( 'Product Attribute Taxonomy Imported: ' . $new_attribute_id, $import_attribute->attribute_name );
			$track_import['imported_product_attributes'][ $old_attribute_id ] = $new_attribute_id;
		}
	} # END foreach import_attributes

	update_option( 'jedi_track_import', $track_import );
} # END jswj_jedi_import_wc_product_attribute_taxonomies()
add_action( 'jedi_after_media_import', 'jswj_jedi_import_wc_product_attribute_taxonomies' );


/**
 * Import WooCommerce Product Attribute Terms & Meta
 *
 * @return void
 */
function jswj_jedi_import_wc_product_attribute_terms_meta() {

	# Bail If WooCommerce Is Not Active
	if( !jswj_is_woocommerce() ) { return; }

	$import_data = jswj_get_jedi_import_data();
	$track_import = get_option( 'jedi_track_import' );
	$jedi_update_image_ids = $track_import['imported_media']['ids'];
	if( isset( $track_import['imported_product_attributes'] ) ) {
		$imported_product_attribute_ids = $track_import['imported_product_attributes'];
	} else {
		# Bail If No Attributes Were Imported
		return;
	}

	# Bail If No Attributes Were Imported
	if( count( $imported_product_attribute_ids ) === 0 ) { return; }

	$wc_product_attribute_data = $import_data['wc_product_attribute_data'];

	foreach( $wc_product_attribute_data as $slug => $attribute_data ) {

		foreach( $attribute_data['terms'] as $terms_index => $term_data ) {
			$old_term_id = $term_data->term_id;
			$new_term_id = $track_import['imported_categories'][ $old_term_id ];

			foreach( $attribute_data['term_meta'][ $terms_index ] as $meta_key => $term_meta_values ) {
				
				foreach( $term_meta_values as $term_meta_value ) {
					if( 'product_attribute_image' === $meta_key ) {
						if( isset( $jedi_update_image_ids[ intval( $term_meta_value ) ] ) ) {
							$term_meta_value = $jedi_update_image_ids[ intval( $term_meta_value ) ];
							jswj_jedi_log( 'Updating product_attribute_image', $term_meta_value );	
						}
					}
					$update_test = update_term_meta( $new_term_id, $meta_key, $term_meta_value );
				}
			}
		}
	}
} # END jswj_jedi_import_wc_product_attribute_terms_meta()
add_action( 'jedi_before_post_import', 'jswj_jedi_import_wc_product_attribute_terms_meta' );
