<?php

namespace Eventin\Emails;

defined( 'ABSPATH' ) || exit;

use Eventin\Mails\Content;

/**
 * Attendee Order Email
 * 
 * @package eventin
 */
class EnsHooks {

    private $attendee;
    private $event;
    private $order;
    /**
     * Store email body
     *
     * @var string
     */
     public $email_body;

    /**
     * Constructor for Admin Order Class
     *
     * @return  void
     */
    public function __construct() {
        add_filter( 'notification_sdk_email_message', array($this, 'get_latest_email_content'), 10, 5 );
        add_filter( 'notification_sdk_to_emails', array($this, 'get_to_attendee_emails'), 10, 3 );
    }

    /**
     * Get latest email content
     *
     * @param string $action_name
     * @param string $message
     * @param array  $action_data
     *
     * @return string|false
     */
    public function get_latest_email_content( $message, $receiver_type, $action_name, $action_data, $count ) {
        if($action_name == 'event_ticket_purchase' && $receiver_type == 'attendee_email') {
            $this->email_body = $message;
            if(isset($action_data['attendee_id']) && !empty($action_data['attendee_id'][$count])) {
                $this->attendee = new \Etn\Core\Attendee\Attendee_Model( $action_data['attendee_id'][$count] );
            }
            
            if(isset($action_data['event_id']) && !empty($action_data['event_id'])) {
                $this->event = new \Etn\Core\Event\Event_Model( $action_data['event_id'] );
            }

            return $this->content();
        }
        elseif($action_name == 'event_ticket_purchase' && ($receiver_type == 'customer_email' || $receiver_type == 'admin_email')) {
            $this->email_body = $message;
            if(isset($action_data['order_id']) && !empty($action_data['order_id'])) {
                $this->order = new \Eventin\Order\OrderModel( $action_data['order_id'] );
            }
            
            if(isset($action_data['event_id']) && !empty($action_data['event_id'])) {
                $this->event = new \Etn\Core\Event\Event_Model( $action_data['event_id'] );
            }

            return $this->email_content_for_customer_and_admin();
        }
        elseif($action_name == 'event_rsvp_email') {
            $this->email_body = $message;
            return $this->email_body;
        }
        elseif($action_name == 'event_reminder_email' && $receiver_type == 'attendee_email') {
            $this->email_body = $message;

            if(empty($action_data['attendee_id'])) {
                $action_data['attendee_id'] = $this->get_to_attendee_ids($action_data);
            }

            if(isset($action_data['attendee_id']) && !empty($action_data['attendee_id']) && !empty($action_data['attendee_id'][$count])) {
                $this->attendee = new \Etn\Core\Attendee\Attendee_Model( $action_data['attendee_id'][$count] );
            }
            
            if(isset($action_data['event_id']) && !empty($action_data['event_id'])) {
                $this->event = new \Etn\Core\Event\Event_Model( $action_data['event_id'] );
            }

            return $this->get_content_for_attendee_reminder_email();
        }
        return $message;
    }

    public function get_to_attendee_emails( $to_emails, $action_data, $action_name ) {
        if($action_name == 'event_reminder_email' || $action_name == 'send_email_to_all_attendees') {
            $event_id = $action_data['event_id'];

            $args = array(
                'post_type'         => 'etn-attendee',
                'post_status'       => 'any',
                'posts_per_page'    => -1,
                
                'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                    array(
                        'key'     => 'etn_event_id',
                        'value'   => $event_id,
                        'compare' => '=',
                    ),
                    array(
                        'key'     => 'etn_status',
                        'value'   => 'success',
                        'compare' => '=',
                    ),
                ),
            );

            $attendees = get_posts( $args );
            $attendee_emails = [];
            
            if ( $attendees ) {
                foreach( $attendees as $attendee ) {
                    $attendee_emails[] = $attendee->etn_email;
                }
            }

            return $attendee_emails;
        }

        return $to_emails;
    }


    public function get_to_attendee_ids( $action_data ) {
            $event_id = $action_data['event_id'];

            $args = array(
                'post_type'         => 'etn-attendee',
                'post_status'       => 'any',
                'posts_per_page'    => -1,
                
                'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                    array(
                        'key'     => 'etn_event_id',
                        'value'   => $event_id,
                        'compare' => '=',
                    ),
                ),
            );

            $attendees = get_posts( $args );
            $attendee_ids = [];
            
            if ( $attendees ) {
                foreach( $attendees as $attendee ) {
                    $attendee_ids[] = $attendee->ID;
                }
            }

            return $attendee_ids;
        }

    /**
     * Email content
     *
     * @return  string  email body
     */
    public function content(): string {
        $content = $this->email_body;

        return Content::get( 'attendee-order-email-template', [
            'content'       => $content,
            'attendee'      => $this->attendee,
            'event'         => $this->event
        ] );
    }

    /**
     * Get content for attendee reminder email
     *
     * @return  string  email body
     */
    public function get_content_for_attendee_reminder_email(): string {
        $content = $this->email_body;

        return Content::get( 'attendee-event-reminder-email-template', [
            'content'       => $content,
            'attendee'      => $this->attendee,
            'event'         => $this->event
        ] );
    }

    /**
     * Email content for customer
     *
     * @return  string  email body
     */
    public function email_content_for_customer_and_admin(): string {
        $content = $this->email_body;

        return Content::get( 'admin-order-email-template', [
            'content'       => $content,
            'order'         => $this->order,
            'event'         => $this->event,
        ] );
    }
}
