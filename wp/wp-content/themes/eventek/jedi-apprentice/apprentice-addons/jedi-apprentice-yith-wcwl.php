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
 * Restore Yith WooCommerce Wishlist Settings
 * Update Post IDs Where Applicable
 *
 * @uses JEDI Hook jedi_after_post_import
 **/
function jswj_import_yith_wcwl_settings() {
	# Bail If WooCommerce Is Not Active
	if( !jswj_is_woocommerce() ) { return; }

	# Bail If Yith WooCommerce Wishlist Is Not Active
	if( !is_plugin_active( 'yith-woocommerce-wishlist/init.php' ) ) { return; }

	$import_data = jswj_get_jedi_import_data();
	$track_import = get_option( 'jedi_track_import' );
	$jedi_post_ids = $track_import['imported_posts'];

	if( ! isset( $import_data['yith_wcwl_settings'] ) ) { return; }

	$yith_wcwl_settings = $import_data['yith_wcwl_settings'];

	foreach( $yith_wcwl_settings as $key => $option ) {
		switch( $key ) {

			# Single Post ID Updates
			case 'yith_wcwl_wishlist_page_id':
				if( isset( $jedi_post_ids[ $option ] ) ) { 
					$option = $jedi_post_ids[ $option ]; 
				}
				break;
		}
		update_option( $key, $option );
		jswj_jedi_log( 'Updating WooCommerce Setting: ' . $key, serialize( $option ) );
	} # END foreach yith_wcwl_settings

} # END jswj_import_yith_wcwl_settings()
add_action( 'jedi_import_plugin_settings', 'jswj_import_yith_wcwl_settings', 100 );
