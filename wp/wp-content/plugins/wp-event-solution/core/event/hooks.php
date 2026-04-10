<?php

namespace Etn\Core\Event;

use \Etn\Core\Event\Pages\Event_single_post;

defined( 'ABSPATH' ) || exit;

class Hooks {

	use \Etn\Traits\Singleton;

	public $cpt;
	public $action;
	public $base;
	public $category;
	public $tags;
	public $event;
	public $settings;

	public $actionPost_type = ['etn'];

	public function Init() {

		$this->cpt      = new Cpt();
		$this->category = new Category();
		$this->tags     = new Tags();

		add_action( 'init', [$this, 'create_taxonomy_pages'], 99999 );
		

		// Update woocommerce supported meta data.
		add_action( 'eventin_event_created', [ $this, 'added_woo_supported_meta' ] );
		add_action( 'eventin_event_after_clone', [ $this, 'added_woo_supported_meta' ] );
		add_action( 'eventin_event_updated', [ $this, 'added_woo_supported_meta' ] );

		//upcoming permalink structure
		add_filter('post_type_link', [ $this, 'etn_upcoming_permalink' ], 10, 4);
		
		add_filter( "etn_form_submit_visibility", [$this, "form_submit_visibility"], 10, 2 );
	}

	/**
	 * Added woocommerce supported meta data
	 *
	 * @param   Event_Model  $event
	 *
	 * @return  void
	 */
	public function added_woo_supported_meta($event) {
		$event = new Event_Model( $event );
		
		update_post_meta( $event->id, "_price", 0 );
		update_post_meta( $event->id, "_regular_price", 0 );
		update_post_meta( $event->id, "_sale_price", 0 );
		update_post_meta( $event->id, "_stock", 0 );
	}
	
	public function create_taxonomy_pages(){
		$this->category->create_page();
		$this->tags->create_page();
	}

	

	public function etn_upcoming_permalink($post_link, $post, $leavename, $sample) {
		
		if ($post->post_type == 'etn' && ( $post->post_status == 'upcoming' || $post->post_status == 'expired' ) ) {
			// Modify the permalink structure here
			$post_link = home_url('/etn/' . $post->post_name);
		}
		return $post_link;
	}
	
	
	public function form_submit_visibility( $visible, $post_id ) {
		//get disable option setting from db
		$is_visible           = true;
		$reg_deadline_expired =  \Etn\Core\Event\Helper::instance()->event_registration_deadline( array('single_event_id' => $post_id ) );
		$is_visible           = $reg_deadline_expired? false : true;
		
		return $is_visible;
	}
	
}
