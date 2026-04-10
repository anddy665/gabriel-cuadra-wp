<?php
/**
 * Updater for version 4.0.10
 *
 * @package Eventin\Upgrade
 */

namespace Eventin\Upgrade\Upgraders;

defined( 'ABSPATH' ) || exit;
 
    use Etn\Core\Event\Event_Model;
    use Eventin\Order\OrderModel;
 
    /**
     * Updater class for v4.0.29
     *
     * @since 4.0.9
     */
    class V_4_0_33 implements UpdateInterface {
        /**
         * Run the updater
         *
         * @return  void
         */
        public function run() {
 
            $email_settings = etn_get_option( 'email' );
            $purchase_email_settings = $email_settings['purchase_email'] ?? [];
            $disable_ticket_email = etn_get_option( 'disable_ticket_email' );
 
            if('on' == $disable_ticket_email){
                $purchase_email_settings['send_email_to_attendees'] = false;
				$email_settings['purchase_email'] = $purchase_email_settings;
                etn_update_option( 'email', $email_settings );
            }
            else{
                $purchase_email_settings['send_email_to_attendees'] = true;
				$email_settings['purchase_email'] = $purchase_email_settings;
                etn_update_option( 'email', $email_settings );
            }
 
            // Enable purchase email by default
            etn_update_option( 'enable_purchase_email', 'on' );
 
            //Flush orphan attendees
            $this->trash_orphan_attendees();
 
            // reset event tickets count
            $this->reset_event_tickets_count();
        }
 
        /**
         * Reset event tickets count
         *
         * This function iterates through all completed orders,
         * retrieves the associated event, and updates the ticket counts
         * based on the order details.
         *
         * @return void
         */
        public function reset_event_tickets_count(){
            // Reset ticket count to zero before updating
            $this->reset_ticket_set_zero();
 
            $orders = get_posts([
                'post_type'      => 'etn-order',
                'post_status'    => 'any',
                'posts_per_page' => -1,
                'meta_query'     => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                    [
                        'key'     => 'status',
                        'value'   => 'completed',
                        'compare' => '='
                    ]
                ]
            ]);
                
            
            if ( ! empty( $orders ) && is_array( $orders) ) {
                foreach ( $orders as $order ) {
                    $booking_id = $order->ID;
                    $event_id = get_post_meta( $booking_id, 'event_id', true );
 
                    $booking = new OrderModel( $booking_id );
                    $event = new Event_Model( $event_id );
            
                    $event_tickets = $event->etn_ticket_variations;
            
                    $updated_tickets = [];
            
                    if ( $event_tickets ) {
                        foreach( $event_tickets as $ticket ) {
                            $updated_ticket = $this->prepare_event_ticket( $booking, $ticket );
            
                            $updated_tickets[] = $updated_ticket;
                        }
                    }
                    
                    $event->update([ 'etn_ticket_variations' => $updated_tickets ]);
                }
            }
        }
 
        /**
         * Prepare updated event ticket
         *
         * @param   OrderModel  $order  [$order description]
         * @param   string  $slug   [$slug description]
         *
         * @return  array          [return description]
         */
        public function prepare_event_ticket( $order, $event_ticket ) {
            $order_tickets = $order->tickets;
            foreach( $order_tickets as $ticket ) {
                if ( $ticket['ticket_slug'] === $event_ticket['etn_ticket_slug'] ) {
                    $event_ticket['etn_sold_tickets'] = $event_ticket['etn_sold_tickets'] + $ticket['ticket_quantity'];
                    break;
                }
            }
    
            return $event_ticket;
        }
 
        /**
         * Reset ticket count to zero
         *
         * This function iterates through all events and sets the sold tickets count to zero.
         *
         * @return void
         */
        private function reset_ticket_set_zero() {
            // Update event tickets count as fallback.
            $events = get_posts([
                'post_type'      => 'etn',
                'post_status'    => 'any',
                'posts_per_page' => -1,
            ]);
 
            if ( ! empty( $events ) && is_array( $events) ) {
                foreach ( $events as $event ) {
                    $event_model = new Event_Model( $event->ID );
                    $event_tickets = $event_model->etn_ticket_variations;
 
                    $updated_tickets = [];
 
                    if ( $event_tickets ) {
                        foreach( $event_tickets as $ticket ) {
                            
                            $ticket['etn_sold_tickets'] = 0;
                            
                            $updated_ticket = $ticket;
 
                            $updated_tickets[] = $updated_ticket;
                        }
                    }
 
                    $event_model->update([ 'etn_ticket_variations' => $updated_tickets ]);
                }
            }
        }
 
        /**
         * Trash orphan attendees that do not have a valid order
         *
         * @return void
         */
        public function trash_orphan_attendees() {
            // Fetch all etn-attendee posts with eventin_order_id
            $attendees = get_posts([
                'post_type'      => 'etn-attendee',
                'post_status'    => 'publish',
                'posts_per_page' => -1,
                'meta_key'       => 'eventin_order_id', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
                'fields'         => 'ids',
            ]);
 
            foreach ( $attendees as $attendee_id ) {
                $order_id = get_post_meta( $attendee_id, 'eventin_order_id', true );
 
                // If order_id is empty or the post doesn't exist, trash the attendee
                if ( empty( $order_id ) || get_post_status( $order_id ) === false ) {
                    update_post_meta( $attendee_id, 'etn_status', 'failed' );
                    update_post_meta( $attendee_id, 'etn_migrated_failed_attendee', true );
                }
            }
        }
    }