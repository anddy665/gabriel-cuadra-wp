<?php

namespace Etn\Core\Modules\Eventin_Ai;


defined( 'ABSPATH' ) || die();

class Eventin_AI {

	use \Etn\Traits\Singleton;

    /**
     * Initialize the class
     *
     * @return void
     */
	public function init() {
		if ( is_admin() ) {
			\Etn\Core\Modules\Eventin_Ai\Admin\Admin::instance()->init();
		}
		
		// Initialize frontend functionality
		add_action( 'wp_enqueue_scripts', [ $this, 'eventin_ai_frontend_scripts' ] );
	}
	
	/**
	 * Enqueue frontend scripts for Eventin AI
	 * 
	 * @return void
	 */
	public function eventin_ai_frontend_scripts() {
		// Only enqueue on pages that might need Eventin AI functionality
		if ( $this->should_enqueue_frontend_scripts() ) {
			// Enqueue Eventin AI CSS styles
			wp_enqueue_style( 'etn-ai-frontend-css', \Wpeventin::plugin_url() . 'build/css/index-ai-style.css', [], \Wpeventin::version() );
			
			// Enqueue Eventin AI JavaScript
			wp_enqueue_script( 'etn-ai-frontend-js', \Wpeventin::core_url() . 'modules/eventin-ai/assets/js/admin.js', [ 'jquery', 'wp-hooks' ], \Wpeventin::version(), true );
			
			$eventin_ai_local_data = [
				'evnetin_ai_active'  => class_exists( 'EventinAI' ) ? true : false,
				'evnetin_pro_active' => class_exists( 'Wpeventin_Pro' ) ? true : false,
			];
			wp_localize_script( 'etn-ai-frontend-js', 'eventin_ai_local_data', $eventin_ai_local_data );
		}
	}
	
	/**
	 * Check if frontend scripts should be enqueued
	 * 
	 * @return bool
	 */
	private function should_enqueue_frontend_scripts() {
		// Check if we're on a Dokan vendor dashboard
		if ( function_exists( 'dokan_is_seller_dashboard' ) && dokan_is_seller_dashboard() ) {
			return true;
		}
		
		// Check if we're on a page with Eventin dashboard shortcode
		global $post;
		
		if ( $post && has_shortcode( $post->post_content, 'etn_pro_dashboard' ) ) {
			return true;
		}
		
		// Check if we're on a page with any Eventin shortcodes
		if ( $post && (
			has_shortcode( $post->post_content, 'etn_pro_dashboard' )
		) ) {
			return true;
		}
		
		return false;
	}
}
