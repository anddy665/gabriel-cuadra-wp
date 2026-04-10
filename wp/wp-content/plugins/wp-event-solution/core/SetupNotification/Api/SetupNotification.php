<?php

/**
 * Setup Notification Controller
 *
 * @package Eventin\SetupNotification
 */
namespace Eventin\SetupNotification\Api;

defined( 'ABSPATH' ) || exit;

use WP_REST_Controller;
use WP_REST_Server;
use WP_User_Query;

/**
 * Settings Controller Class
 */
class SetupNotification extends WP_REST_Controller {
    /**
     * Constructor for SettingsController
     *
     * @return void
     */
    public function __construct() {
        $this->namespace = 'eventin/v2';
        $this->rest_base = 'setup-notification';
    }

    /**
     * Check if a given request has access to get items.
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|boolean
     */
    public function register_routes() {
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( $this, 'get_details' ),
                    'args'                => array(),
                    'permission_callback' => array( $this, 'get_item_permissions_check' ),
                ),
                array(
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => array( $this, 'update_notification_dismiss_status' ),
                    'args'                => array(),
                    'permission_callback' => array( $this, 'get_item_permissions_check' ),
                ),
            )
        );
    }

    /**
     * Check if a given request has access to get items.
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|boolean
     */
    public function get_item_permissions_check( $request ) {
        return current_user_can( 'etn_manage_setting' ) 
                || current_user_can( 'etn_manage_event' );
    }

    /**
     * Get a setup notification details.
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function get_details( $request ) {

        // Check if attendee registration is enabled
        $attendees_enabled = etn_get_option( 'attendee_registration' ) == 'on' ? true : false;
        
        // Check if payment methods are enabled
        $payment_enabled = !empty(etn_get_option( 'payment_method' )) || !empty(etn_get_option( 'paypal_status' )) || !empty(etn_get_option( 'etn_sells_engine_stripe' )) || !empty(etn_get_option( 'local_payment_status' )) || !empty(etn_get_option( 'surecart_status' )) || !empty(etn_get_option( 'fluentcart_status' )) ? true : false;
        
        // Check if the wizard setup is active
        $wizard_setup = get_option( 'etn_wizard' ) == 'active' ? true : false;
        
        // Check if the notification has been dismissed
        $notification_dismissed = get_option( 'etn_notification_dismissed' ) ? true: false;

        // Check if any events have been created
        $count_object = wp_count_posts( 'etn' );
        $published_events = ( $count_object && isset( $count_object->publish ) ) ? (int) $count_object->publish : 0;
        $event_created = $published_events > 0 ? true : false;
        
        
        // Check if any speakers or organizers have been created
        $user_query = new WP_User_Query( [
            'role__in' => [ 'etn-speaker', 'etn-organizer' ],
            'fields'   => 'ID',
        ] );
        $user_count = count( $user_query->get_results() );
        $speakers_created = $user_count > 0 ? true : false;

        if(!$notification_dismissed){
            $notification_dismissed = ($wizard_setup && $event_created && $attendees_enabled && $speakers_created && $payment_enabled) ? true : false;
        }

        $setup_details = [
            'notification_dismissed' => $notification_dismissed,
            'wizard_setup' => $wizard_setup,
            'event_created' => $event_created,
            'attendees_enabled' => $attendees_enabled,
            'speakers_created' => $speakers_created,
            'payment_enabled' => $payment_enabled,
        ]; 

        $setup_details['total_completed_steps'] = $this->total_completed_steps( $setup_details );

        return rest_ensure_response( $setup_details );
    }


    /**
     * Update the notification dismissed status.
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function update_notification_dismiss_status( $request ) {
        $params = $request->get_params();
        $dismissed = isset( $params['dismissed'] ) ? (bool) $params['dismissed'] : false;

        // Update the notification dismissed status
        update_option( 'etn_notification_dismissed', $dismissed );

        return rest_ensure_response( [ 'status' => 'success', 'dismissed' => $dismissed ] );
    }

    /**
     * Calculate the total number of completed setup steps.
     *
     * @param array $setup_details The setup details array.
     * @return int The total number of completed steps.
     */
    public function total_completed_steps($setup_details) {
       $total_completed_steps = 0;

       foreach ($setup_details as $key => $value) {
            if (is_bool($value) && $value) {
                $total_completed_steps++;
            }
        }

        return $total_completed_steps;
    }
}