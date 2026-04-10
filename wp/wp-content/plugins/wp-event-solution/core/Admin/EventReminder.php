<?php

namespace Eventin\Admin;

defined( 'ABSPATH' ) || exit;

use DateTime;
use Etn\Core\Attendee\Attendee_Model;
use Etn\Core\Event\Event_Model;
use Eventin\Emails\AttendeeEventReminderEmail;
use Eventin\Interfaces\HookableInterface;
use Eventin\Mails\Mail;

class EventReminder implements HookableInterface {

    /**
     * Register service
     *
     * @return  void
     */
    public function register_hooks(): void {
        add_action( 'eventin_event_created', [$this, 'register_schedule'] );
        add_action( 'eventin_reset_reminder_email', [$this, 'reset_reminder_email_schedule'], 10, 2 );

        add_action( 'run_event_scheduler', [$this, 'run_event_schedule'] );

        add_action( 'send_reminder_email', [$this, 'send_reminder_email'] );
    }

    /**
     * Register schedule for event reminder
     *
     * @param   Event_Model $event
     *
     * @return  void
     */
    public function register_schedule( $event ) {
        $etn_addons_options = get_option( 'etn_addons_options' ) ?? [];
        
        $is_automation_module_on = 'off';
        if ( is_array( $etn_addons_options ) ) {
            $is_automation_module_on = $etn_addons_options["automation"] ?? "off";
        }

        // check if automation module is on
        if ( 'on' === $is_automation_module_on ) {
            $this->register_automated_schedule( $event );
        } else {
            $this->register_default_schedule( $event );
        }

    }

    /**
     * Register cron job for schedule a reminder email
     *
     * @param   integer  $event_id
     *
     * @return  void
     */
    public function register_default_schedule( $event ) {

        $date = $event->etn_start_date;
        $time = $event->etn_start_time;

        $event_timestamp = strtotime( $date . ' ' . $time );

        $reminder_time = etn_get_option( 'remainder_time' );

        if ( !$reminder_time ) {
            return;
        }

        foreach ( $reminder_time as $time ) {
            $timestamp = 0;
            $duration = intval( $time['duration-time'] );

            // if `duration-time` value not properly set, skip setting remainder
            if ( !isset( $duration ) || !is_numeric( $duration ) ) {
                continue;
            }

            switch ( $time['custom_duration_type'] ) {
            case 'min':
                $timestamp = $duration * 60;
                break;
            case 'hour':
                $timestamp = $duration * 60 * 60;
                break;
            case 'day':
                $timestamp = ( $duration * 24 ) * 60 * 60;
                break;
            }

            $timestamp = $event_timestamp - $timestamp;

            wp_schedule_single_event( $timestamp, 'send_reminder_email', [$event->id] );

//            if ( ! wp_next_scheduled( 'event_remainder' ) ) {
//            }
        }

    }

    /**
     * event schedule
     *
     * @return  void
     */
    public function run_event_schedule( $event = null ) {

        if ( !$event ) {
            return;
        }

        add_action( 'send_reminder_email', [$this, 'send_reminder_email'] );
        // Run cron action.
    }

    /**
     * Send email to attendees
     *
     * @param   integer  $event_id  Event id
     *
     * @return  void
     */
    public function send_reminder_email( $event_id ) {

        $args = [
            'post_type'      => 'etn-attendee',
            'post_status'    => 'any',
            'posts_per_page' => -1,

            'meta_query'     => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                [
                    'key'     => 'etn_event_id',
                    'value'   => $event_id,
                    'compare' => '=',
                ],
            ],
        ];

        $attendees = get_posts( $args );
        $event = new Event_Model( $event_id );

        if ( $attendees ) {

            foreach ( $attendees as $attendee ) {
                $attendee = new Attendee_Model( $attendee->ID );

                Mail::to( $attendee->etn_email )->send( new AttendeeEventReminderEmail( $event, $attendee ) );
            }

        }

    }

    /**
     * Register cron job for schedule a reminder email
     *
     * @param   integer  $event_id
     *
     * @return  void
     */
    public function register_automated_schedule( $event ) {
        $date_format = get_option( 'date_format' );

        do_action( 'global_notification_hook', 'event_reminder_email', [
            'site_name'            => get_bloginfo( 'name' ),
            'site_link'            => get_site_url(),
            'event_title'          => $event->get_title(),
            'event_date'           => $event->get_start_date($date_format),
            'event_time'           => $event->get_start_time(),
            'event_date_timestamp' => $this->get_event_date_timestamp( $event->get_start_date(), $event->get_start_time() ),
            'previous_event_date'  => $this->get_event_date_timestamp( $event->get_start_date(), $event->get_start_time() ),
            'event_location'       => $event->get_address(),
            'attendee_id'          => [],
            'attendee_email'       => [],
            'event_id'             => $event->id,
            'post_id'              => $event->id,
            'session_id'           => uniqid(),
        ] );
    }

    /**
     * Reset reminder email schedule
     *
     * @param   Event_Model $event
     * @param   array       $previous_event_date
     */
    public function reset_reminder_email_schedule( $event, $previous_event_date ) {
        $date_format = get_option( 'date_format' );
        $previous_event_date = $this->get_event_date_timestamp(
            $previous_event_date['previous_event_start_date'],
            $previous_event_date['previous_event_start_time']
        );

        $current_event_date = $this->get_event_date_timestamp( $event->get_start_date(), $event->get_start_time() );

        if ( $previous_event_date != $current_event_date ) {
            do_action( 'global_notification_hook', 'event_reminder_email', [
                'site_name'                     => get_bloginfo( 'name' ),
                'site_link'                     => get_site_url(),
                'event_title'                   => $event->get_title(),
                'event_date'                    => $event->get_start_date($date_format),
                'event_time'                    => $event->get_start_time(),
                'event_date_timestamp'          => $current_event_date,
                'previous_event_date_timestamp' => $previous_event_date,
                'event_location'                => $event->get_address(),
                'attendee_id'                   => [],
                'attendee_email'                => [],
                'event_id'                      => $event->id,
                'post_id'                       => $event->id,
                'session_id'                    => uniqid(),
            ] );
        }

    }

    /**
     * Get event date timestamp
     *
     * @param   string $date
     * @param   string $time
     * @return  int
     */
    public function get_event_date_timestamp( $date, $time ) {
        $datetime = new DateTime( $time ); // Default timezone is used unless specified
        $formatted = $datetime->format( 'H:i:s' ); // 24-hour format

        $date = $date . ' ' . $formatted;

        $dt = new DateTime( $date, wp_timezone() );
        $timestamp = $dt->getTimestamp();

        return $timestamp;
    }

}
