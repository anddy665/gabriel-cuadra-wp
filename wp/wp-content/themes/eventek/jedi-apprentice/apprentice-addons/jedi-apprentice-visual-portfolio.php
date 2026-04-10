<?php
/**
 * Easy Demo Import Pro
 *
 * Visual Portfolio Plugin Shortcodes Addon
 * https://wordpress.org/plugins/visual-portfolio/
 *
 * Updates the Visual Portfolio Shortcodes With Imported Post IDs
 * Does not import plugin settings values
 *
 * @package    jedi-master
 * @author     Jerry Simmons <jerry@ferventsolutions.com>
 * @copyright  2021 Jerry Simmons
 * @license    GPL-2.0+
 **/

if( ! defined( 'ABSPATH' ) ) { exit; }

/**
 * Update Visual Portfolio Shortcodes in Post Content
 *
 * Example Shortcode: [visual_portfolio id="415"]
 *
 * @uses JEDI Hook jedi_after_post_import
 * @param array $jedi_post_ids An array of imported Post IDs.
 **/
function jswj_update_visual_portfolio_shortcodes( $jedi_post_ids ) {

	if( ! is_plugin_active( 'visual-portfolio/class-visual-portfolio.php' ) ) { return; }

	foreach( $jedi_post_ids as $jedi_post_id ) {

		$the_post = get_post( $jedi_post_id );
		if( false !== strpos( $the_post->post_content, '[visual_portfolio id' ) ) {
			$vp_form_id_start = strpos( $the_post->post_content, '[visual_portfolio id="' ) + 22;
			$vp_form_id_end = strpos( $the_post->post_content, '"', $vp_form_id_start + 1 );
			$vp_form_id = substr( $the_post->post_content, $vp_form_id_start, $vp_form_id_end - $vp_form_id_start );
			$old_shortcode = '[visual_portfolio id="' . $vp_form_id . '"';
			$new_shortcode = '[visual_portfolio id="' . $jedi_post_ids[ $vp_form_id ] . '"';
			$the_post->post_content = str_replace( $old_shortcode, $new_shortcode, $the_post->post_content );
			wp_update_post( $the_post );
		}
	} # END foreach $jedi_post_ids
}
add_action( 'jedi_after_post_import', 'jswj_update_visual_portfolio_shortcodes' );
