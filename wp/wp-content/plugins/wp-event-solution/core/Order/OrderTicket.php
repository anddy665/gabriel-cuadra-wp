<?php

namespace Eventin\Order;

defined( 'ABSPATH' ) || exit;

use Etn\Core\Event\Event_Model;
use Eventin\Emails\AttendeeOrderEmail;
use Eventin\Interfaces\HookableInterface;
use Eventin\Mails\Mail;
use Wpeventin;
use Eventin\Order\OrderModel;
use Etn\Core\Attendee\Attendee_Model;
use Etn\Utils\Helper;

class OrderTicket implements HookableInterface {
    /**
     * Register service
     *
     * @return  void
     */
    public function register_hooks(): void {
        add_action( 'eventin_order_completed', [$this, 'order_status_completed'] );
        add_action( 'eventin_order_status_completed', [$this, 'order_status_completed'] );
        add_action( 'eventin_order_status_failed', [$this, 'order_status_failed'] );

        add_action( 'eventin_attendee_created', [ $this, 'send_attendee_ticket' ] );

        add_action( 'eventin_attendee_created', [ $this, 'decrease_ticket_after_attendee_create' ] );

        add_action( 'eventin_order_refund', [ $this, 'decrese_event_sold_ticket_after_refund' ] );
        
        add_action( 'eventin_order_before_delete', [ $this, 'decrese_event_sold_ticket_after_order_delete' ] );

        add_action( 'eventin_attendee_before_delete', [ $this, 'decrese_event_sold_ticket_after_attendee_delete' ] );

        add_action( 'eventin_release_held_tickets', [ $this, 'release_held_tickets' ] ); // from cron
        add_action( 'eventin_release_held_seats_and_tickets', [ $this, 'release_held_seats_and_tickets' ], 10, 3 );
        add_action( 'eventin_release_pending_seats_and_tickets', [ $this, 'eventin_release_pending_seats_and_tickets' ] ); // from cron

        /**
         * Add custom cron schedule
         *
         * @param array $schedules Schedules.
         *
         * @return array
         */
        add_filter( 'cron_schedules', function ( $schedules ) {
            $schedules['every_sixty_minutes'] = [
                'interval' => 60 * 60,
                'display'  => 'Every 60 Minutes'
            ];

            return $schedules;
        });

        /**
         * Schedule event to release pending seats and tickets
         *
         * @return void
         */
        add_action( 'init', function () {
            if ( ! wp_next_scheduled( 'eventin_release_pending_seats_and_tickets' ) ) {
                wp_schedule_event( time(), 'every_sixty_minutes', 'eventin_release_pending_seats_and_tickets' );
            }
        });
    }

    /**
     * Release pending seats and tickets
     *
     * @return void
     */
    public function eventin_release_pending_seats_and_tickets() {
        // Calculate timestamp for 20 minutes ago
        $twenty_minutes_ago = gmdate( 'Y-m-d H:i:s', strtotime( '-20 minutes' ) );

        // Fetch all failed bookings from the last 20 minutes using WP_Query
        $args = [
            'post_type'      => 'etn-order',
            'post_status'    => 'any',
            'posts_per_page' => -1,
            'date_query'     => [
                [
                    'after'     => $twenty_minutes_ago,
                    'inclusive' => true,
                    'column'    => 'post_modified',
                ],
            ],
            'meta_query'     => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                [
                    'key'   => 'status',
                    'value' => 'failed',
                ],
            ],
        ];

        $failed_orders_query = new \WP_Query( $args );

        // Group orders by event_id for efficient processing
        $event_orders = [];

        if ( $failed_orders_query->have_posts() ) {
            while ( $failed_orders_query->have_posts() ) {
                $failed_orders_query->the_post();
                $order_id = get_the_ID();
                $event_id = get_post_meta( $order_id, 'event_id', true );

                if ( ! $event_id ) {
                    continue;
                }

                if ( ! isset( $event_orders[$event_id] ) ) {
                    $event_orders[$event_id] = [];
                }
                $event_orders[$event_id][] = $order_id;
            }

            wp_reset_postdata();
        }

        // Get all events to process
        $events_args = [
            'post_type'      => 'etn',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
        ];

        $events_query = new \WP_Query( $events_args );

        if ( ! $events_query->have_posts() ) {
            return;
        }

        // Process each event
        while ( $events_query->have_posts() ) {
            $events_query->the_post();
            $event_id = get_the_ID();
            $allocated_seats = [];
            $allocated_tickets = [];

            // Collect allocated seats and tickets from failed orders for this event (if any)
            if ( isset( $event_orders[$event_id] ) ) {
                foreach ( $event_orders[$event_id] as $order_id ) {
                    // Get allocated seats
                    $order_seats = etn_safe_decode( get_post_meta( $order_id, 'seat_ids', true ) );
                    if ( is_array( $order_seats ) && ! empty( $order_seats ) ) {
                        $allocated_seats = array_merge( $allocated_seats, $order_seats );
                    }

                    // Get allocated tickets
                    $order_tickets = etn_safe_decode( get_post_meta( $order_id, 'tickets', true ) );
                    if ( is_array( $order_tickets ) && ! empty( $order_tickets ) ) {
                        foreach ( $order_tickets as $ticket ) {
                            $ticket_slug = $ticket['ticket_slug'];
                            $ticket_quantity = isset( $ticket['ticket_quantity'] ) ? (int) $ticket['ticket_quantity'] : 0;

                            if ( ! isset( $allocated_tickets[$ticket_slug] ) ) {
                                $allocated_tickets[$ticket_slug] = 0;
                            }
                            $allocated_tickets[$ticket_slug] += $ticket_quantity;
                        }
                    }
                }

                // Remove duplicate seats
                $allocated_seats = array_unique( $allocated_seats );
            }

            // Get event's pending seats
            $pending_seats = etn_safe_decode( get_post_meta( $event_id, 'pending_seats', true ) );
            if ( ! is_array( $pending_seats ) ) {
                $pending_seats = [];
            }

            // If there are allocated seats from failed orders, remove them from pending seats
            // Otherwise, clear all pending seats
            if ( ! empty( $allocated_seats ) && ! empty( $pending_seats ) ) {
                $seats_to_remove = array_intersect( $pending_seats, $allocated_seats );
                if ( ! empty( $seats_to_remove ) ) {
                    $pending_seats = array_diff( $pending_seats, $seats_to_remove );
                    update_post_meta( $event_id, 'pending_seats', array_values( $pending_seats ) );
                }
            } elseif ( empty( $allocated_seats ) && ! empty( $pending_seats ) ) {
                // No failed bookings for this event, clear all pending seats
                update_post_meta( $event_id, 'pending_seats', [] );
            }

            // Get event tickets
            $event_tickets = etn_safe_decode( get_post_meta( $event_id, 'etn_ticket_variations', true ) );

            if ( is_array( $event_tickets ) ) {
                $tickets_updated = false;

                foreach ( $event_tickets as &$ticket ) {
                    $ticket_slug = isset( $ticket['etn_ticket_slug'] ) ? $ticket['etn_ticket_slug'] : '';

                    if ( ! empty( $allocated_tickets ) && isset( $allocated_tickets[$ticket_slug] ) ) {
                        // Decrease pending count by allocated tickets from failed orders
                        $pending_count = isset( $ticket['pending'] ) ? (int) $ticket['pending'] : 0;
                        $ticket['pending'] = max( 0, $pending_count - $allocated_tickets[$ticket_slug] );
                        $tickets_updated = true;
                    } elseif ( empty( $allocated_tickets ) && isset( $ticket['pending'] ) && $ticket['pending'] > 0 ) {
                        // No failed bookings for this event, set pending to 0
                        $ticket['pending'] = 0;
                        $tickets_updated = true;
                    }
                }

                if ( $tickets_updated ) {
                    update_post_meta( $event_id, 'etn_ticket_variations', $event_tickets );
                }
            }
        }

        wp_reset_postdata();
    }

    /**
     * After booking update event ticket status
     *
     * @param   OrderModel  $order  The order need to update
     *
     * @return  void
     */
    public function order_status_completed( $order ) {
        if ( 'completed' !== $order->status ) {
            return;
        }

        $event = new Event_Model( $order->event_id );

        $event_tickets = $event->etn_ticket_variations;

        $updated_tickets = [];

        if ( $event_tickets ) {
            foreach( $event_tickets as $ticket ) {
                $updated_ticket = $this->prepare_event_ticket( $order, $ticket );

                $updated_tickets[] = $updated_ticket;
            }
        }

        $event->update([ 'etn_ticket_variations' => $updated_tickets ]);

        $this->update_booked_seat($event, $order);
        $this->update_pending_seat($event, $order);

        $booked_seats   = etn_safe_decode(get_post_meta($order->id, 'seat_ids', true));
        $booked_tickets = $order->tickets;
        $formatted_booked_tickets = [];
        if (!empty($booked_tickets)) {
            foreach ($booked_tickets as $ticket) {
                $formatted_booked_tickets[] = [
                    'ticket_slug' => $ticket['ticket_slug'],
                    'ticket_quantity' => $ticket['ticket_quantity']
                ];
            }
        }

        \Etn\Utils\Helper::increase_count_by_ticket_slug($formatted_booked_tickets,$event->id);
        

        $data = [
            $event->id,
            $booked_seats,
            $formatted_booked_tickets,
        ];

        $this->clear_hold_seats_and_tickets_cron($data);
    }

    /**
     * Prepare updated event ticket
     *
     * @param   OrderModel  $order  [$order description]
     * @param   string  $slug   [$slug description]
     *
     * @return  array          [return description]
     */
    private function prepare_event_ticket( $order, $event_ticket ) {
        $order_tickets = $order->tickets;
        $event_id = $order->event_id ?? null;
        $sold_tickets = $event_id ? (array)Helper::etn_get_sold_tickets_by_event( $event_id ) : [];

        foreach( $order_tickets as $ticket ) {
            if ( $ticket['ticket_slug'] === $event_ticket['etn_ticket_slug'] ) {
                $event_ticket['etn_sold_tickets'] = $sold_tickets[$ticket['ticket_slug']] ?? 0;
                $event_ticket['pending'] = isset( $event_ticket['pending'] ) ? $event_ticket['pending'] - $ticket['ticket_quantity'] : 0;
                if ( $event_ticket['pending'] < 0 ) {
                    $event_ticket['pending'] = 0;
                }
                break;
            }
        }

        return $event_ticket;
    }

    /**
     * Update booked event booked seats
     *
     * @param   Event_Model  $event  [$event description]
     * @param   Order_Model  $order  [$order description]
     *
     * @return  void
     */
    private function update_booked_seat( $event, $order ) {
        $event_seats = get_post_meta( $event->id, '_etn_seat_unique_id', true );

        $order_seats = $order->seat_ids;

        if ( ! $order_seats ) {
            return;
        }

        $event_seats = explode(',', $event_seats );

        $event_seats = array_merge( $event_seats, $order_seats );
        $event_seats = implode( ',', array_unique( $event_seats ) );

        update_post_meta( $event->id, '_etn_seat_unique_id', $event_seats );
    }

    /**
     * Update pending seats after booking
     *
     * @param   Event_Model  $event  [$event description]
     * @param   Order_Model  $order  [$order description]
     *
     * @return  void
     */
    public function update_pending_seat( $event, $order ) {
        $order_seats = $order->seat_ids;

        if ( empty( $order_seats ) ) {
            return;
        }

        $pending_seats = etn_safe_decode(get_post_meta($event->id, 'pending_seats', true));
        if (! is_array($pending_seats)) {
            $pending_seats = [];
        }

        $pending = array_diff($pending_seats, $order_seats);
        
        update_post_meta($event->id, 'pending_seats', $pending);
    }

    /**
     * Send attendee ticket after creating a attendee
     *
     * @param   Attendee_Model  $attendee  [$attendee description]
     *
     * @return  void             [return description]
     */
    public function send_attendee_ticket( $attendee ) {
        $purchase_email = etn_get_email_settings( 'purchase_email' );
        if(is_array($purchase_email) && array_key_exists( 'send_email_to_attendees', $purchase_email ) ){
            // If the setting exists, use it
           $send_email_to_attendees = $purchase_email['send_email_to_attendees'];
        }
        else{
            $send_email_to_attendees = true;
        }

        if ( !$send_email_to_attendees ) {
            return;
        }

        if ( $attendee->etn_email ) {
            $from  = etn_get_email_settings( 'purchase_email' )['from'];
            $event = new Event_Model( $attendee->etn_event_id );
            Mail::to($attendee->etn_email)->from( $from )->send(new AttendeeOrderEmail($event, $attendee));
        }
    }

    /**
     * Update event ticket quantity after attendee create
     *
     * @return  void
     */
    public function decrease_ticket_after_attendee_create( $attendee ) {
        $event = new Event_Model( $attendee->etn_event_id );

        $event_tickets = $event->etn_ticket_variations;

        $event_id = $attendee->etn_event_id ?? null;
        $sold_tickets = !empty($event_id) ? (array)Helper::etn_get_sold_tickets_by_event($event_id) : [];

        if ( $event_tickets ) {
            foreach( $event_tickets as &$ticket ) {
                if ( $ticket['etn_ticket_name'] === $attendee->ticket_name ) {
                    $ticket['etn_sold_tickets'] = $sold_tickets[$ticket['etn_ticket_slug']] ?? 0;
                }
            }
        }

        $event->update([
            'etn_ticket_variations' => $event_tickets,
            'etn_total_sold_tickets' => (int) $event->etn_total_sold_tickets + 1
        ]);
    }

    /**
     * Decrese event ticket variation amount after refunded
     *
     * @param   OrderModel  $order  The order need to refund
     *
     * @return  void
     */
    public function decrese_event_sold_ticket_after_refund( OrderModel $order ) {
        if ( 'refunded' != $order->status ) {
            return;
        }

        $event = new Event_Model( $order->event_id );

        $booked_tickets = $order->tickets;
        $formatted_booked_tickets = [];
        if ( !empty($booked_tickets) ) {
            foreach ($booked_tickets as $ticket) {
                $formatted_booked_tickets[] = [
                    'ticket_slug' => $ticket['ticket_slug'],
                    'ticket_quantity' => $ticket['ticket_quantity']
                ];
            }
        }

        \Etn\Utils\Helper::decrease_count_by_ticket_slug($formatted_booked_tickets,$event->id);



        $event_tickets = $event->etn_ticket_variations;

        $event_id = $order->event_id ?? null;
        $sold_tickets = !empty($event_id) ? (array)Helper::etn_get_sold_tickets_by_event($event_id) : [];

        if ( $event_tickets ) {
            foreach( $event_tickets as &$ticket ) {
                $ticket_amount = $order->get_total_ticket_by_ticket( $ticket['etn_ticket_slug'] );
                if ( $ticket_amount > 0 ) {
                    $ticket['etn_sold_tickets'] = $sold_tickets[$ticket['etn_ticket_slug']] ?? 0;
                }
            }
        }

        $event->update([
            'etn_ticket_variations' => $event_tickets,
        ]);

        // Update seat on refunded.
        $event_seats = get_post_meta( $event->id, '_etn_seat_unique_id', true );
        $order_seats = $order->seat_ids;

        if ( $order_seats ) {
            $event_seats = explode(',', $event_seats );

            $event_seats = array_diff( $event_seats, $order_seats );
            $event_seats = implode( ',', array_unique( $event_seats ) );

            update_post_meta( $event->id, '_etn_seat_unique_id', $event_seats );
        }
    }

    /**
     * Decrese event ticket variation amount after order status failed
     *
     * @param   OrderModel  $order  The order need to update
     *
     * @return  void
     */
    public function order_status_failed( $order ) {
        if ( 'failed' != $order->status ) {
            return;
        }

        $event = new Event_Model( $order->event_id );

        $event_tickets = $event->etn_ticket_variations;


        $event_id = $order->event_id ?? null;
        $sold_tickets = !empty($event_id) ? (array)Helper::etn_get_sold_tickets_by_event($event_id) : [];

        if ( $event_tickets ) {
            foreach( $event_tickets as &$ticket ) {
                $ticket_amount = $order->get_total_ticket_by_ticket( $ticket['etn_ticket_slug'] );
                if ( $ticket_amount > 0 ) {
                    $ticket['etn_sold_tickets'] = $sold_tickets[$ticket['etn_ticket_slug']] ?? 0;
                }
            }
        }

        $event->update([
            'etn_ticket_variations' => $event_tickets,
        ]);

        // Update seat on refunded.
        $event_seats = get_post_meta( $event->id, '_etn_seat_unique_id', true );
        $order_seats = etn_safe_decode(get_post_meta( $order->id, 'seat_ids', true ));

        if ( $order_seats ) {
            $event_seats = explode(',', $event_seats );

            $event_seats = array_diff( $event_seats, $order_seats );
            $event_seats = implode( ',', array_unique( $event_seats ) );

            update_post_meta( $event->id, '_etn_seat_unique_id', $event_seats );
        }
    }

    /**
     * Decrese event ticket variation amount after order deleted
     *
     * @param   OrderModel  $order  The order need to delete
     *
     * @return  void
     */
    public function decrese_event_sold_ticket_after_order_delete( OrderModel $order ) {
        if ( $order->status !== 'completed' ) {
            return;
        }
        $event = new Event_Model( $order->event_id );

        $event_tickets = $event->etn_ticket_variations;

        $event_id = $order->event_id ?? null;
        $sold_tickets = !empty($event_id) ? (array)Helper::etn_get_sold_tickets_by_event($event_id) : [];

        if ( $event_tickets ) {
            foreach( $event_tickets as &$ticket ) {
                $ticket_amount = $order->get_total_ticket_by_ticket( $ticket['etn_ticket_slug'] );
                if ( $ticket_amount > 0 ) {
                    $ticket['etn_sold_tickets'] = $sold_tickets[$ticket['etn_ticket_slug']] ?? 0;
                }
            }
        }

        $event->update([
            'etn_ticket_variations' => $event_tickets,
        ]);

        // Update seat on refunded.
        $event_seats = get_post_meta( $event->id, '_etn_seat_unique_id', true );
        $order_seats = $order->seat_ids;

        if ( $order_seats ) {
            $event_seats = explode(',', $event_seats );

            $event_seats = array_diff( $event_seats, $order_seats );
            $event_seats = implode( ',', array_unique( $event_seats ) );

            update_post_meta( $event->id, '_etn_seat_unique_id', $event_seats );
        }
    }

    /**
     * Decrese event sold ticket after attendee delete
     *
     * @param   Attendee_Model  $attendee
     *
     * @return  void
     */
    public function decrese_event_sold_ticket_after_attendee_delete( $attendee ) {
        if ( $attendee->etn_status != 'success' ) {
            return;
        }

        $event = new Event_Model( $attendee->etn_event_id );
        $order = new OrderModel( $attendee->eventin_order_id );

        // Decrease sold ticket quantity from event
        $event_tickets = $event->etn_ticket_variations;


        $event_id = $attendee->etn_event_id ?? null;
        $sold_tickets = !empty($event_id) ? (array)Helper::etn_get_sold_tickets_by_event($event_id) : [];

        if ( $event_tickets ) {
            foreach( $event_tickets as &$ticket ) {
                if ( $ticket['etn_ticket_name'] == $attendee->ticket_name ) {
                    $ticket['etn_sold_tickets'] = $sold_tickets[$ticket['etn_ticket_slug']] ?? 0;
                }
            }
        }

        $event->update([
            'etn_ticket_variations' => $event_tickets,
        ]);

        // Decrease sold ticket quantity from order
        $order_tickets = $order->tickets;
        $updated_tickets = [];
        if ( $order_tickets ) {
            foreach( $order_tickets as $ticket ) {
                
                $ticket_slug = $event->get_ticket_slug_by_name( $attendee->ticket_name );
                if ( $ticket['ticket_slug'] === $ticket_slug ) {
                    $ticket['ticket_quantity'] = $ticket['ticket_quantity'] - 1;
                }

                if ( $ticket['ticket_quantity'] > 0 ) {
                    $updated_tickets[] = $ticket;
                }
            }
        }
        
        // Decrease ticket quantity from order
        $order->update([
            'tickets'     => $updated_tickets,
            'total_price' => floatval($order->total_price) - floatval($attendee->etn_ticket_price),
        ]);
    }

    /**
     * Release held tickets after order status changed to pending
     *
     * @param   integer  $order_id  The order ID
     * 
     * @return  void
     */
    public function release_held_tickets( $order_id ) {
        $order = new OrderModel( $order_id );

        if ( 'pending' !== $order->status ) {
            return;
        }

        $order->update([
            'status' => 'failed'
        ]);

        // Update order attendees status
        $attendees = $order->get_attendees();
        if ( $attendees ) {
            foreach( $attendees as $attendee ) {
                $attendee = new Attendee_Model( $attendee['id'] );
                $attendee->update([
                    'etn_status' => 'failed'
                ]);
            }
        }
    }

    /**
     * Release held seats and tickets
     *
     * @param   integer  $event_id  The event ID
     *
     * @param   array    $seat_ids  The seat IDs to release
     *
     * @param   array    $booked_tickets  The booked tickets to release
     *
     * @return  void
     */
    public function release_held_seats_and_tickets( $event_id, $seat_ids = [], $booked_tickets = [] ) {
        $event_tickets = etn_safe_decode( get_post_meta( $event_id, 'etn_ticket_variations', true ) );
        $pending_seats = etn_safe_decode( get_post_meta( $event_id, 'pending_seats', true ));

        if ( ! is_array( $pending_seats ) ) {
            $pending_seats = [];
        }

        if ( is_array( $event_tickets ) ) {
            foreach( $event_tickets as &$ticket ) {
                foreach( $booked_tickets as $booked_ticket ) {
                    if ( $ticket['etn_ticket_slug'] === $booked_ticket['ticket_slug'] ) {
                        $ticket['pending'] -= $booked_ticket['ticket_quantity'];
                        if($ticket['pending'] < 0){
                            $ticket['pending'] = 0;
                        }
                    }
                }
            }
        }
        
        // update ticket variations pending count
        if(is_array($event_tickets)){
            update_post_meta( $event_id, 'etn_ticket_variations', $event_tickets );
        }

        if ( is_array( $seat_ids ) && count( $seat_ids ) > 0 ) {
            update_post_meta( $event_id, 'pending_seats', array_diff(
                $pending_seats,
                $seat_ids
            ) );
        }
    }

    /**
     * Clear hold tickets cron
     *
     * @param   OrderModel  $order  The order need to clear hold tickets
     *
     * @return  void
     */
    // public function clear_hold_tickets_cron( $order ) {
    //     wp_clear_scheduled_hook( 'eventin_release_held_tickets', [ $order->id ] );
    // }

    public function clear_hold_seats_and_tickets_cron( $data ) {
        wp_clear_scheduled_hook( 'eventin_release_held_seats_and_tickets', $data );
    }
}