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
 * Update Ditty News Ticker Shortcodes in Post Content
 *
 * @uses JEDI Hook jedi_after_post_import
 * @param array $jedi_post_ids An array of imported Post IDs.
 **/
function jswj_update_ditty_news_ticker_shortcodes( $jedi_post_ids ) {
	if( ! is_plugin_active( 'ditty-news-ticker/ditty-news-ticker.php' ) ) { return; }

	foreach( $jedi_post_ids as $jedi_post_id ) {
		$search_post = get_post( $jedi_post_id );
		if( false !== strpos( $search_post->post_content, 'ditty_news_ticker id' ) ) {
			$ditty_news_ticker_id_start = strpos( $search_post->post_content, 'ditty_news_ticker id="' ) + 22;
			$ditty_news_ticker_id_end = strpos( $search_post->post_content, '"]', $ditty_news_ticker_id_start );
			$ditty_news_ticker_id = substr( $search_post->post_content, $ditty_news_ticker_id_start, $ditty_news_ticker_id_end - $ditty_news_ticker_id_start );
			if( '' !== $ditty_news_ticker_id && isset( $jedi_post_ids[ $ditty_news_ticker_id ] ) ) {
				$old_shortcode = '[ditty_news_ticker id="' . $ditty_news_ticker_id . '"]';
				$new_shortcode = '[ditty_news_ticker id="' . $jedi_post_ids[ $ditty_news_ticker_id ] . '"]';
				$search_post->post_content = str_replace( $old_shortcode, $new_shortcode, $search_post->post_content );
				wp_update_post( $search_post );
			}
		}
	}
}
add_action( 'jedi_after_post_import', 'jswj_update_ditty_news_ticker_shortcodes' );
