<?php

/**
 * FluentCRM integration
 * 
 * @package Eventin
 */
namespace Eventin\Integrations\Webhook;

defined( 'ABSPATH' ) || exit;

use Eventin\Order\OrderModel;

/**
 * FluentCRM Webhook integration
 */
class FluentCRM implements WebhookIntegrationInterface {
    /**
     * Run action
     *
     * @return  void
     */
    public function run() {
        add_action( 'eventin_after_order_create', [$this, 'send_data_to_fluentcrm'], 10, 2 );
    }

    /**
     * Send data to fluentcrm webhook
     *
     * @param   OrderModel  $order  [$order description]
     *
     * @return  void
     */
    public function send_data( $order, $attendees = [] ) {
        $event_id           = $order->event_id; 
        $fluentCRM_enable   = get_post_meta( $event_id, 'fluent_crm', true );
        $fluentcrm_webhook  = get_post_meta( $event_id, 'fluent_crm_webhook', true ); 

		
        $body = array(
            'email'      => $order->customer_email,
            'first_name' => $order->customer_fname,
        );
	    
		$body = apply_filters( 'eventin_fluentcrm_purchaser_data', $body, $order, $attendees );
		
		
 
        if ( $fluentCRM_enable ==='yes' && !empty( $fluentcrm_webhook ) ) { 
            $response_user = wp_remote_post($fluentcrm_webhook, ['body' => $body]);
        }
    }
	
	/**
	 * @description used to send individual attendees of events data to fluentCRM
	 * @param OrderModel $order
	 * @param string $attendee_name
	 * @param string $attendee_email
	 * @return void
	 */
	public function send_attendee_data(OrderModel $order, $attendee): void
	{
		$event_id           = $order->event_id;
		$fluentCRM_enable   = get_post_meta( $event_id, 'fluent_crm', true );
		$fluentcrm_webhook  = get_post_meta( $event_id, 'fluent_crm_webhook', true );
		
		$body = [ 'email' => $attendee["etn_email"], 'first_name' => $attendee["etn_email"] ];
		$body = apply_filters( 'eventin_fluentcrm_attendee_data', $body, $order, $attendee );
		
		
		if ( $fluentCRM_enable ==='yes' && !empty( $fluentcrm_webhook ) ) {
			$response_user = wp_remote_post($fluentcrm_webhook, ['body' => $body]);
		}
	}
	
	
	/**
	 * @description
	 * @param $order
	 * @param $attendees
	 * @return void
	 */
	public function send_data_to_fluentcrm($order, $attendees)
	{
		// send data to purchaser
		$this->send_data($order, $attendees);
		
		$attendee_registration = "on" === etn_get_option("attendee_registration");
		
		// Sending attendees to fluentCRM
		if ( $attendee_registration ) {
			try {
				foreach ($attendees as $attendee) {
					$this->send_attendee_data(
						$order,
						$attendee
					);
				}
			}
			catch (\Exception $exception) {}
		}
		
	}
}
