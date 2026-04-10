<?php

namespace Etn\Core\Event;

use DateTime;
use Error;
use Etn\Traits\Singleton;

defined( 'ABSPATH' ) || exit;

class Helper {

	use Singleton;

	/**
	 * Return currency symbol
	 */
	public function get_currency() {
		$symbol = class_exists('WooCommerce') ? get_woocommerce_currency_symbol() : '$';
	
		$settings = \Etn\Utils\Helper::get_settings();
		$currency = $settings['etn_settings_country_currency'] ?? '$';
	
		if (!empty($settings['sell_tickets']) && $settings['sell_tickets'] === 'woocommerce') {
			return $symbol;
		}
	
		if (
			(!empty($settings['etn_sells_engine_stripe']) && $settings['etn_sells_engine_stripe'] === 'stripe') ||
			(!empty($settings['paypal_status']) && $settings['paypal_status'] === true)
		) {
			return etn_get_currency_symbol($currency);
		}
	
		return $symbol;
	}

	/**
	 * Return currency symbol with position
	 *
	 * @param string|float $price The price to format
	 * @param object|null $order Optional order object to get currency from
	 * @return string Formatted price with currency symbol
	 */
	public function currency_with_position( $price, $order = null ) {
		$payment_method = $order->payment_method ?? '';

		$currency_symbol = $this->get_currency();

		$currency_position = 'left';
		if ( class_exists( 'WooCommerce' ) ) {
			$currency_position = get_option( 'woocommerce_currency_pos', 'left' );
		}

		// If order is provided and has currency_symbol meta, use it
		// This ensures the correct currency is used for orders paid via SureCart or other gateways
		if ( $payment_method == 'sure_cart' || $payment_method == 'fluentcart' ) {
			$currency_symbol = get_post_meta( $order->id, 'currency_symbol', true );
		}

		if ( $currency_position === 'left_space' ) {
			return sprintf( '%s %s', esc_html( $currency_symbol ), $price );
		} else if ( $currency_position === 'right_space' ) {
			return sprintf( '%s %s', $price, esc_html( $currency_symbol ) );
		} else if ( $currency_position === 'right' ) {
			return sprintf( '%s%s', $price, esc_html( $currency_symbol ) );
		} else {
			return sprintf( '%s%s', esc_html( $currency_symbol ), $price );
		}
	}

	/**
	 * Add recurring tag
	 */
	public function recurring_tag( $data ) {
		if ( ( is_array( $data ) && count( $data ) > 0 ) ) {
			foreach ( $data as $index => $post ) {
				$post_id             = $post->ID;
				$is_recurring_parent = \Etn\Utils\Helper::get_child_events( $post_id );
				if ( $is_recurring_parent ) {
					$post->etn_recurring = true;
				}
			}
		}

		return $data;
	}

	public function get_event_location( $event_id ) {
		$location      = '';
		$location_type = get_post_meta( $event_id, 'etn_event_location_type', true );
		if ( $location_type == 'existing_location' ) {
			$location = get_post_meta( $event_id, 'etn_event_location', true );
		} else {
			$location_arr = etn_safe_decode( get_post_meta( $event_id, 'etn_event_location_list', true ) );

			if ( ! empty( $location_arr ) && is_array( $location_arr ) ) {
				$location_names = [];

				foreach ( $location_arr as $index => $location_slug ) {
					$location_details = get_term_by( 'slug', $location_slug, 'etn_location' );
					$location_names[] = $location_details->name;
				}

				$location = join( ', ', $location_names );
			}
		}

		return $location;
	}

	  /**
     * Display event location
     *
     * @param   int     $single_event_id
     * @param   array   $data
     *
     * @return  void
     */
    
    public function display_event_location($single_event_id) {
        $event_type = get_post_meta( $single_event_id, 'event_type', true );
        $location 	= get_post_meta( $single_event_id, 'etn_event_location', true );
    
        if ( ('offline' === $event_type || 'hybrid' === $event_type) && ! empty( $location ) ) { 
            return is_array( $location ) &&  ! empty( $location['address'] ) ? $location['address']: '';
        } else if ('online' === $event_type) {
            return ucwords($event_type);
        } else if ( 'hybrid' === $event_type ) {
			$address = is_array( $location ) &&  ! empty( $location['address'] ) ? $location['address']: '';
			return $address;
		}
    
        return '';
    }

	/**
	 * Get Attendee for a event
	 */
	public function attendee_by_events( $query ) {
		if ( ( is_admin()
		       && ( isset( $_GET['post_type'] ) && $_GET['post_type'] == "etn-attendee" ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- admin attendee filter; post_type compared to literal string only.
		     && ( ! empty( $_GET['event_id'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only admin event_id filter.
		     && $query->is_search ) {
			$meta_query = [
				'relation' => 'AND',
				[
					'key'     => 'etn_event_id',
					'value'   => absint( $_GET['event_id'] ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- cast to integer via absint().
					'compare' => '=',
				],
			];
			$query->set( 'meta_query', $meta_query );
		}

		return $query;
	}

	/**
	 *  Global Date format
	 */
	public function etn_date_format() {
		$settings        = \Etn\Utils\Helper::get_settings();
		$date_options    = \Etn\Utils\Helper::get_date_formats();
		$etn_date_format = ! empty( $settings["date_format"] ) ? $date_options[ $settings["date_format"] ] : get_option( "date_format" );

		return $etn_date_format;
	}

	/**
	 *  Get Tickets by Events
	 */
	public function ticket_by_events( $event_id = null ) {
		$ticket_variations = array();
		$get_tickets       = array( 'tickets' => array(), 'ticket_price' => array() );
		if ( ! is_null( $event_id ) ) {
			$ticket_variations = get_post_meta( $event_id, 'etn_ticket_variations', true );
		}

		if ( ! empty( $ticket_variations ) ) {
			foreach ( $ticket_variations as $key => $value ) {
				$get_tickets['tickets'][ $value['etn_ticket_name'] ] = $value['etn_ticket_name'];
				$get_tickets['ticket_price'][ $key ]                 = $value['etn_ticket_price'];
			}
		}

		return $get_tickets;
	}

	/**
	 * Get Upcoming event only
	 */
	public function get_upcoming_event( $event_id ) {
		$result           = true;
		$event_start_date = get_post_meta( $event_id, 'etn_start_date', true );

		if ( gmdate( 'Y-m-d', strtotime( $event_start_date ) ) < gmdate( 'Y-m-d' ) ) {
			$result = false;
		}

		return $result;
	}

	/**
	 * Get time zone
	 *
	 * @param [type] $timezone
	 * @return void
	 */
	public function get_timezone_numeric_value( $timezone ){
		if ( str_contains( $timezone, 'UTC+' ) || str_contains( $timezone, 'UTC-' ) ) {
			$timezone_offset = str_replace('UTC', '', $timezone);
		} else {
			date_default_timezone_set( $timezone ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.timezone_change_date_default_timezone_set -- intentional timezone context for offset calculation.
			$timezone_offset = date( 'Z' ) / 3600; // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date -- must use local date() after date_default_timezone_set() to get the correct offset.
		}

		return $timezone_offset;
	}

	/**
	 * Get formatted seat-id
	 *
	 * @return void
	 */
	public function event_seat_id( $etn_booked_seats ) {
		$booked_id = array();
		if ( !empty($etn_booked_seats) ) {
			foreach ($etn_booked_seats as $key => $item) {
				if (!empty($item)) {
					$item =  explode(",",$item);
					foreach ($item as $i => $data) {
						$data = str_replace($key."-","", $data );
						array_push( $booked_id , $data );
					}
				}
			}
		}

		return $booked_id;
	}

	/**
	 * get Order event id
	 */
	public function order_event_id($item) {
		$item_data        = $item->get_data();
		return !is_null( $item_data['product_id'] ) ? $item_data['product_id'] : "";
	}
	
	/**
	 * Check event expire or not
	 *
	 */
	public function event_registration_deadline($args) {
		extract($args);
		
		$event_end_date = get_post_meta( $single_event_id, "etn_end_date", true );
		$event_end_time = get_post_meta( $single_event_id, "etn_end_time", true );

		if ( str_contains(strtolower($event_end_date), 'invalid') ) {
			return true;
		}

		if ( $event_end_date ) {
			$date = new DateTime( $event_end_date );
			$event_end_date = $date->format('Y-m-d');
		}

		$event_end_date_time_string = strtotime( $event_end_date . ' ' . $event_end_time );
		$is_registration_expried 	= time() > $event_end_date_time_string;

		return $is_registration_expried;
	}

	public function event_expire_date( $post_id ) {
		$settings = etn_get_option();
		//get expiry date condition from db
		$selected_expiry_point  = ( ! empty($settings['expiry_point'])  ) ? $settings['expiry_point'] : "end";
		$selected_expiry_time   = ( ! empty($settings['expiry_time'])) ? $settings['expiry_time'] : "start";
		$event_expire_date_time = "";
		$event_expire_start_date    = !empty( get_post_meta( $post_id, "etn_start_date", true ) ) ? get_post_meta( $post_id, "etn_start_date", true ) : "";
		$event_expire_start_time    = !empty( get_post_meta( $post_id, "etn_start_time", true ) )  ? get_post_meta( $post_id, "etn_start_time", true ) : "";
		$event_expire_end_time      = !empty( get_post_meta( $post_id, "etn_end_time", true ) ) ? get_post_meta( $post_id, "etn_end_time", true ) : $event_expire_start_time;

		if ( $selected_expiry_point == "start" ) {
			//event start date-time
			$event_expire_date      = $event_expire_start_date;
		} elseif ( $selected_expiry_point == "end" ) {
			//event end date-time
			$event_expire_date      = !empty( get_post_meta( $post_id, "etn_end_date", true ) ) && !is_null( get_post_meta( $post_id, "etn_end_date", true ) ) ? get_post_meta( $post_id, "etn_end_date", true ) : $event_expire_start_date;
		}else{
			$deadline               = get_post_meta( $post_id, 'etn_registration_deadline', true );
		}
		
		$event_expire_time      = $selected_expiry_time == "start" ? $event_expire_start_time : $event_expire_end_time;
		$event_expire_date_time = $event_expire_date . " " . $event_expire_time;
		$deadline               = $event_expire_date_time;

		return trim($deadline);
	}
	
	/**
	 * get event object
	 *
	 * @param [type] $product_name
	 */
	public function get_etn_object( $product_name ) {
		$event_object = null;
		$array_of_objects = get_posts(
			[
				'title' => $product_name,
				'post_type' => 'etn'
			]
		);
		if( !empty($array_of_objects ) ){
			$event_object = get_post( $array_of_objects[0]->ID );
		}

		return $event_object;
	}

	/**
	 * get event order status
	 *
	 * @param [type] $product_name
	 */
	public function get_etn_order_status( $order_status ) {
		if ( $order_status == 'wc-pending' ) {
			$status = 'Pending';
		} elseif ( $order_status == 'wc-processing' || $order_status == 'draft') {
			$status = 'Processing';
		} elseif ( $order_status == 'wc-on-hold' ) {
			$status = 'Hold';
		} elseif ( $order_status == 'wc-completed' ) {
			$status = 'Completed';
		} elseif ( $order_status == 'wc-refunded' ) {
			$status = 'Refunded';
		} elseif ( $order_status == 'wc-failed' ) {
			$status = 'Failed';
		}  elseif ( $order_status == 'wc-partial-payment' ) {
			$status = 'Completed'; // 'Partially Paid'
		}  elseif ( $order_status == 'wc-scheduled-payment' ) {
			$status = 'Pending'; // 'Scheduled'
		}  elseif ( $order_status == 'wc-pending-deposit' ) {
			$status = 'Pending'; // 'Pending Deposit Payment'
		} else {
			$status = 'Pending';
		}

		return $status;
	}

	public static function convert_event_time_zone($event_timezone , $date_time ) {
		if ( str_contains( $event_timezone, 'UTC' ) ) {
			$hours      = str_replace('UTC', '', $event_timezone);
			$dt         = new DateTime($date_time);
			$timestamp  = $dt->modify( $hours . ' hours')->format('Y-m-d h:i A');
		} else {
			date_default_timezone_set( $event_timezone ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.timezone_change_date_default_timezone_set -- intentional timezone context for calendar display.
			$timestamp = date("Y-m-d h:i A",strtotime( $date_time )); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date -- must use local date() after date_default_timezone_set() for correct timezone display.
		}

		return $timestamp;
	}

}