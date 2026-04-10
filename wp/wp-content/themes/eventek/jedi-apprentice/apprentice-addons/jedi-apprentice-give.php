<?php
/**
 * Easy Demo Import Pro - Give Plugin
 *
 * @package    jedi-master
 * @author     Jerry Simmons <jerry@ferventsolutions.com>
 * @copyright  2021 Jerry Simmons
 * @license    GPL-2.0+
 **/

if( ! defined( 'ABSPATH' ) ) { exit; }


/**
 * Update Give Form Shortcodes in Post Content
 *
 * @uses JEDI Hook jedi_after_post_import
 * @param array $jedi_post_ids An array of imported Post IDs.
 **/
function jswj_update_giveform_shortcodes( $jedi_post_ids ) {
	if( ! is_plugin_active( 'give/give.php' ) ) { return; }

	foreach( $jedi_post_ids as $jedi_post_id ) {
		$give_post = get_post( $jedi_post_id );
		if( false !== strpos( $give_post->post_content, 'give_form id' ) ) {
			$give_form_id_start = strpos( $give_post->post_content, 'give_form id="' ) + 14;
			$give_form_id_end = strpos( $give_post->post_content, '"]', $give_form_id_start );
			$give_form_id = substr( $give_post->post_content, $give_form_id_start, $give_form_id_end - $give_form_id_start );
			if( '' !== $give_form_id && isset( $jedi_post_ids[ $give_form_id ] ) ) {
				$old_shortcode = '[give_form id="' . $give_form_id . '"]';
				$new_shortcode = '[give_form id="' . $jedi_post_ids[ $give_form_id ] . '"]';
				$give_post->post_content = str_replace( $old_shortcode, $new_shortcode, $give_post->post_content );
				wp_update_post( $give_post );
			}
		}
	}
}
add_action( 'jedi_after_post_import', 'jswj_update_giveform_shortcodes' );
