<?php

namespace Eventin\Order;

defined( 'ABSPATH' ) || exit;

use DateTime;
use Etn\Core\Attendee\Attendee_Model;
use Etn\Core\Event\Event_Model;
use Eventin\Emails\AdminOrderEmail;
use Eventin\Emails\AttendeeOrderEmail;
use Eventin\Mails\Mail;

/**
 * Order email trait
 *
 * @package Eventin
 */
trait OrderEmailTrait {
    /**
     * Send email for a specific order
     *
     * @return  Void
     */
    public function send_email() {
        $etn_addons_options = get_option( 'etn_addons_options' ) ?? [];
        $is_automation_module_on = 'off';

        if ( is_array( $etn_addons_options ) ) {
            $is_automation_module_on = $etn_addons_options["automation"] ?? "off";
        }

        // check if automation module is on
        if ( 'on' === $is_automation_module_on ) {
            $this->send_email_through_automation();
        } else {
            $this->send_email_through_default_system();
        }
    }

    /**
     * Send email through default system
     *
     * @return  Void
     */
    public function send_email_through_default_system() {
        $order = $this;

        $attendees   = $order->get_attendees();
        $event       = new Event_Model( $order->event_id );
        $admin_email = get_option('admin_email');
        $from        = etn_get_email_settings( 'purchase_email' )['from'];
        $send_to_admin = etn_get_email_settings( 'purchase_email' )['send_to_admin'];

        $purchase_email = etn_get_email_settings( 'purchase_email' );
        if(is_array($purchase_email) && array_key_exists( 'send_email_to_attendees', $purchase_email ) ){
            // If the setting exists, use it
           $send_email_to_attendees = $purchase_email['send_email_to_attendees'];
        }
        else{
            $send_email_to_attendees = true;
        }
        
        $enable_purchase_email = etn_get_option( 'enable_purchase_email','on' );

        if('off' == $enable_purchase_email){
            return;
        }


        // Send to admin order email.
        if ( $send_to_admin ) {
            Mail::to( $admin_email )->from( $from )->send( new AdminOrderEmail( $order ) );
        }
        
        
        // Send to customer order email.
        Mail::to( $order->customer_email )->from( $from )->send( new AdminOrderEmail( $order ) );

        // Send to attendees email.
        if ( $attendees && $send_email_to_attendees ) {
            foreach( $attendees as $attendee ) {
                $attendee = new Attendee_Model( $attendee['id'] );
                
                if ( $attendee->etn_email ) {
                    Mail::to( $attendee->etn_email )->from( $from )->send( new AttendeeOrderEmail( $event, $attendee ) );
                }
            }
        }

    }

    /**
     * Send email through automation
     *
     * @return  Void
     */
    public function send_email_through_automation() {
        $order = $this;

        $attendees = $order->get_attendees();
        $event = new Event_Model( $order->event_id );

        $this->send_email_to_admin_through_automation( $order, $event );
        $this->send_email_to_customer_through_automation( $order, $event );
        $this->send_email_to_attendee_through_automation( $attendees, $event, $order );
    }

    /**
     * Send email to admin, customer and attendees through automation
     *
     * @param   Order_Model $order
     * @param   Event_Model $event
     * @return  Void
     */
    public function send_email_to_admin_through_automation( $order, $event ) {
        $date_format = get_option( 'date_format' );
        $dt = new DateTime( $event->get_start_date(), wp_timezone() );
        $event_start_date_timestamp = $dt->getTimestamp();

        $admin_email = get_option( 'admin_email' );

        do_action( 'global_notification_hook', 'event_ticket_purchase', [
            'site_name'      => get_bloginfo( 'name' ),
            'site_link'      => get_site_url(),
            'site_logo'      => get_site_icon_url(),
            'event_title'    => $event->get_title(),
            'event_date'     => $event->get_start_date($date_format),
            'event_date_timestamp'     => $event_start_date_timestamp,
            'event_time'     => $event->get_start_time(),
            'booking_time_timestamp'     => current_time('timestamp'),
            'event_location' => $event->get_address(),
            'event_id'       => $order->event_id,
            'order_id'       => $order->id,
            'admin_email'    => $admin_email,
            'session_id'     => uniqid(),
        ] );
    }

    /**
     * Send email to customer and attendees through automation
     *
     * @param   Order_Model $order
     * @param   Event_Model $event
     * @return  Void
     */
    public function send_email_to_customer_through_automation( $order, $event ) {
        $date_format = get_option( 'date_format' );
        $dt = new DateTime( $event->get_start_date(), wp_timezone() );
        $event_start_date_timestamp = $dt->getTimestamp();

        do_action( 'global_notification_hook', 'event_ticket_purchase', [
            'site_name'      => get_bloginfo( 'name' ),
            'site_link'      => get_site_url(),
            'site_logo'      => get_site_icon_url(),
            'event_title'    => $event->get_title(),
            'event_date'     => $event->get_start_date( $date_format ),
            'event_date_timestamp'     => $event_start_date_timestamp,
            'event_time'     => $event->get_start_time(),
            'booking_time_timestamp'     => current_time('timestamp'),
            'event_location' => $event->get_address(),
            'event_id'       => $order->event_id,
            'order_id'       => $order->id,
            'customer_email' => $order->customer_email,
            'session_id'     => uniqid(),
        ] );
    }

    /**
     * Send email to attendees through automation
     *
     * @param   array $attendees
     * @param   Event_Model $event
     * @param   Order_Model $order
     * @return  Void
     */
    public function send_email_to_attendee_through_automation( $attendees, $event, $order ) {
        $date_format = get_option( 'date_format' );
        $attendee_ids = [];
        $attendee_emails = [];
        foreach ( $attendees as $attendee ) {
            $attendee_ids[] = $attendee['id'];
            $attendee_emails[] = $attendee['etn_email'];
        }

        $dt = new DateTime( $event->get_start_date(), wp_timezone() );
        $event_start_date_timestamp = $dt->getTimestamp();

        do_action( 'global_notification_hook', 'event_ticket_purchase', [
            'site_name'      => get_bloginfo( 'name' ),
            'site_link'      => get_site_url(),
            'site_logo'      => get_site_icon_url(),
            'event_title'    => $event->get_title(),
            'event_date'     => $event->get_start_date( $date_format ),
            'event_date_timestamp'     => $event_start_date_timestamp,
            'event_time'     => $event->get_start_time(),
            'booking_time_timestamp'     => current_time('timestamp'),
            'event_location' => $event->get_address(),
            'attendee_id'    => $attendee_ids,
            'attendee_email' => $attendee_emails,
            'event_id'       => $order->event_id,
            'session_id'     => uniqid(),
        ] );
    }

}