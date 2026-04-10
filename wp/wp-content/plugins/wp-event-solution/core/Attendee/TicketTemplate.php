<?php

namespace Eventin\Attendee;

defined( 'ABSPATH' ) || exit;

use Eventin\Interfaces\HookableInterface;
use Etn\Core\Event\Event_Model;

/**
 * Ticket template render for ticket view
 */
class TicketTemplate implements HookableInterface {
    /**
     * Register all hooks
     *
     * @return  void
     */
    public function register_hooks(): void {
        add_action( 'template_redirect', [$this, 'generate_ticket_pdf'] );
    }

    /**
	 * Attendee data array both for generate and download
	 */
	public function attendee_ticket_data( $data ) {
		$result_data                   		= [];
		$result_data['user_id']        		= intval( $data["attendee_id"] );
		$result_data['ticket_price']   		= $data['etn_ticket_price'];
		$result_data['event_location_type'] = $data['event_location_type'];
		$result_data['event_location'] 		= $data['event_location'];
		$result_data['event_terms'] 		= $data['event_terms'];

		$result_data['event_name']     = $data['event_name'];
		$result_data['ticket_name']    = ! empty( $data['ticket_name'] ) ? $data['ticket_name'] : ETN_DEFAULT_TICKET_NAME;
		$result_data['attendee_seat']    = ! empty( $data['attendee_seat'] ) ? $data['attendee_seat'] : '';

		$settings                 = etn_get_option();
		$date_options             = get_option( 'date_format' );
		$etn_settings_time_format = empty( $settings["time_format"] ) ? '12' : $settings["time_format"];
		$etn_settings_time_format = $etn_settings_time_format == '24' ? "H:i" : get_option( "time_format" );
		$etn_settings_date_format = ! empty( $settings["date_format"] ) ? $date_options[$settings["date_format"]] : get_option( "date_format" );

		$date_format = !empty($settings['date_format']) ? $etn_settings_date_format : get_option("date_format");
		$result_data['start_date'] = date_i18n($date_format, strtotime($data['etn_start_date']));
		$result_data['end_date'] = !empty($data['etn_end_date']) 
		? date_i18n($date_format, strtotime($data['etn_end_date'])) 
		: '';

		$result_data['start_time']     = ! empty( $settings['time_format'] ) ? date_i18n( $etn_settings_time_format, strtotime( $data['etn_start_time'] ) ) : date_i18n( get_option( "time_format" ), strtotime( $data['etn_start_time'] ) );
		$result_data['end_time']       = ! empty( $settings['time_format'] ) ? date_i18n( $etn_settings_time_format, strtotime( $data['etn_end_time'] ) ) : date_i18n( get_option( "time_format" ), strtotime( $data['etn_end_time'] ) );
		$result_data['event_timezone'] = ! empty( $data['event_timezone'] ) ? $data['event_timezone'] : $data['event_timezone'];

		$result_data['attendee_name']  = get_post_meta( $result_data['user_id'], 'etn_name', true );
		$result_data['attendee_email'] = get_post_meta( $result_data['user_id'], "etn_email", true );
		$result_data['attendee_phone'] = get_post_meta( $result_data['user_id'], "etn_phone", true );
		$result_data['attendee_seat']  = get_post_meta( $result_data['user_id'], "attendee_seat", true );

		return $result_data;
	}

	/**
	 * Download PDF from email and admin dashboard
	 */
	public function generate_ticket_pdf() {
		if ( isset( $_GET['etn_action'] ) && sanitize_text_field( wp_unslash( $_GET['etn_action'] ) ) === 'download_ticket' ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- ticket PDF download link; etn_info_edit_token acts as a secret access token.

			$get_arr = filter_input_array( INPUT_GET, FILTER_SANITIZE_FULL_SPECIAL_CHARS );

			if ( empty( $get_arr["attendee_id"] ) || empty( $get_arr["etn_info_edit_token"] ) ) {
				wp_die( esc_html__( 'Invalid data', 'eventin' ) );
			}

			if ( ! $this->verify_attendee_edit_token( $get_arr["attendee_id"], $get_arr["etn_info_edit_token"] ) ) {
				wp_die( esc_html__( 'Invalid data', 'eventin' ) );
			}
			$attendee_id = $get_arr["attendee_id"];
			$event_id    = get_post_meta( $attendee_id, "etn_event_id", true );

			$attendee_data = [
				"attendee_id"      		=> $attendee_id,
				"etn_ticket_price" 		=> get_post_meta( $attendee_id, "etn_ticket_price", true ),
				"event_location_type"   => get_post_meta( $event_id, "etn_event_location_type", true ),
				"event_terms"           => !empty(get_the_terms($event_id, 'etn_location')) ? get_the_terms($event_id, 'etn_location') : [],
				"event_location"   		=> get_post_meta( $event_id, "etn_event_location", true ),
				"event_name"       		=> get_post_field( 'post_title', $event_id, 'raw' ),
				"ticket_name"      		=> !empty( get_post_meta( $attendee_id, 'ticket_name', true ) ) ? html_entity_decode( get_post_meta( $attendee_id, 'ticket_name', true ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ) : ETN_DEFAULT_TICKET_NAME,
				"etn_start_date"   		=> get_post_meta( $event_id, "etn_start_date", true ),
				"etn_end_date"     		=> get_post_meta( $event_id, "etn_end_date", true ),
				"etn_start_time"   		=> get_post_meta( $event_id, "etn_start_time", true ),
				"etn_end_time"     		=> get_post_meta( $event_id, "etn_end_time", true ),
				"etn_end_time"     		=> get_post_meta( $event_id, "etn_end_time", true ),
				"event_timezone"   		=> get_post_meta( $event_id, "event_timezone", true ),
			];

			$result_data = $this->attendee_ticket_data( $attendee_data );
			
			if ( is_array( $result_data ) && ! empty( $result_data ) ) {
				$this->generate_pdf(
					$attendee_id,
					$event_id,
					$result_data['event_name'],
					$result_data['start_date'],
					$result_data['end_date'],
					$result_data['start_time'],
					$result_data['end_time'],
					$result_data['event_location'],
					$result_data['event_location_type'],
					$result_data['event_terms'],
					$result_data['ticket_name'],
					$result_data['ticket_price'],
					$result_data['attendee_name'],
					$result_data['attendee_email'],
					$result_data['attendee_phone'],
					$result_data['event_timezone'],
					$result_data['attendee_seat']
				);
			}
			exit;
		}

		return;
	}

	/**
	 * Generate PDF file with provided data
	 */
	public function generate_pdf( $attendee_id, $event_id, $event_name,
		$start_date, $end_date, $start_time, $end_time,
		$event_location, $event_location_type, $event_terms, $ticket_name, $ticket_price, $attendee_name,
		$attendee_email, $attendee_phone, $time_zone , $attendee_seat ) {
		$settings       = etn_get_option();
		$include_phone  = ! empty( $settings["reg_require_phone"] ) ? true : false;
		$include_email  = ! empty( $settings["reg_require_email"] ) ? true : false;
		$time_zone      = ! empty( $time_zone ) ? ' (' . $time_zone . ') ' : '';
		$date_separator = ! empty( $end_date ) ? ' - ' : '';
		$time_separator = ! empty( $end_time ) ? ' - ' : '';

		// Format date time following wordpress settings.

		$date_format = get_option( 'date_format' );
		$time_format = get_option( 'time_format' );

		$event = new Event_Model( $event_id );

		$start_date   = gmdate( $date_format, strtotime( $event->etn_start_date ) );
		$end_date     = gmdate( $date_format, strtotime( $event->etn_end_date ) );

		$start_date_time = $event->etn_start_date . ' ' . $event->etn_start_time;
		$end_date_time 	 = $event->etn_end_date . ' ' . $event->etn_end_time;

		$start_time	  = gmdate( $time_format, strtotime( $start_date_time ) );
		$end_time	  = gmdate( $time_format, strtotime( $end_date_time ) );


		$date           = $start_date . $date_separator . $end_date;
		$time           = $start_time . $time_separator . $end_time . $time_zone;

		$ticket_style = isset( $settings['attendee_ticket_style'] ) ? $settings['attendee_ticket_style'] : 'style-1';


		$event_ticket_template = get_post_meta( $event_id, 'ticket_template', true );

		$layouts = [
			'style-1' => 'style-1',
			'style-2' => 'style-2',
		];

		if ( ! empty( $layouts[$event_ticket_template] ) ) {
			$ticket_style = $layouts[$event_ticket_template];
		}

		$post = get_post( $event_ticket_template );

		if ( $post && $post->post_type !== 'etn-template' ) {
			$post = get_post( etn_get_option('attendee_ticket_style') );
		}

		if ( $post && $post->post_type === 'etn-template' ) {
			include_once \Wpeventin::templates_dir() . "template-parts/attendee/ticket-markup-block.php";
		} else {
			if(class_exists('Wpeventin_Pro') && 'style-2' === $ticket_style) {
				include_once \Wpeventin_Pro::templates_dir() . "attendee/ticket-markup-".esc_html($ticket_style).".php";
			}else {
				$ticket_style = $ticket_style == 'style-2'?'style-1':$ticket_style;
				include_once \Wpeventin::templates_dir() . "attendee/ticket-markup-".esc_html($ticket_style).".php";
			}
		}
	}
    
    /**
	 * Undocumented function
	 *
	 * @param [type] $attendee_id
	 * @param [type] $check_info_edit_token
	 *
	 * @return void
	 */
	public function verify_attendee_edit_token( $attendee_id, $check_info_edit_token ) {
		$post_status = get_post_status( $attendee_id );


		if ( "publish" !== $post_status || empty( $attendee_id ) || empty( $check_info_edit_token ) ) {
			return false;
		}

		$stored_edit_token = get_post_meta( $attendee_id, "etn_info_edit_token", true );

		if ( $stored_edit_token == $check_info_edit_token ) {
			return true;
		}

		return false;
	}
}
