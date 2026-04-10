<?php

namespace Eventin\Order;

defined( 'ABSPATH' ) || exit;

use Etn\Core\Attendee\Attendee_Model;
use Etn\Core\Event\Event_Model;
use Eventin\Attendee\Attendee\TicketIdGenerator;
use Eventin\Customer\CustomerModel;
use Eventin\Input;
use Eventin\Emails\AdminOrderEmail;
use Eventin\Emails\AttendeeOrderEmail;
use Eventin\Integrations\Webhook\FluentCRM;
use Eventin\Mails\Mail;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Server;

/**
 * Order controller class
 * 
 * @package Eventin
 */
class OrderController extends WP_REST_Controller {
    /**
     * Constructor for OrderController
     *
     * @return void
     */
    public function __construct() {
        $this->namespace = 'eventin/v2';
        $this->rest_base = 'orders';
    }

    /**
     * Check if a given request has access to get items.
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|boolean
     */
    public function register_routes() {
        register_rest_route( $this->namespace, $this->rest_base, [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [$this, 'get_items'],
                'permission_callback' => [$this, 'get_items_permissions_check'],
            ],
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [$this, 'create_item'],
                'permission_callback' => [$this, 'create_item_permissions_check'],
            ],
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [$this, 'delete_items'],
                'permission_callback' => [$this, 'delete_item_permissions_check'],
            ],
        ] );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<id>[\d]+)',
            array(
                'args'   => array(
                    'id' => array(
                        'description' => __( 'Unique identifier for the post.', 'eventin' ),
                        'type'        => 'integer',
                    ),
                ),
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( $this, 'get_item' ),
                    'permission_callback' => array( $this, 'get_item_permissions_check' ),
                    'args'                => $this->get_collection_params(),
                ),
                array(
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => array( $this, 'update_item' ),
                    'permission_callback' => array( $this, 'update_item_permissions_check' ),
                    'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
                ),
                array(
                    'methods'             => WP_REST_Server::DELETABLE,
                    'callback'            => array( $this, 'delete_item' ),
                    'permission_callback' => array( $this, 'delete_item_permissions_check' ),
                    'args'                => array(
                        'force' => array(
                            'type'        => 'boolean',
                            'default'     => false,
                            'description' => __( 'Whether to bypass Trash and force deletion.', 'eventin' ),
                        ),
                    ),
                ),
                // 'allow_batch' => $this->allow_batch,
                'schema' => array( $this, 'get_item_schema' ),
            ),
        );

        register_rest_route( $this->namespace, $this->rest_base . '/export', [
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [$this, 'export_items'],
                'permission_callback' => [$this, 'export_item_permissions_check'],
            ],
        ] );

        register_rest_route( $this->namespace, $this->rest_base . '/import', [
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [$this, 'import_items'],
                'permission_callback' => [$this, 'import_item_permissions_check'],
            ],
        ] );

        register_rest_route( $this->namespace, $this->rest_base . '/(?P<id>[\d]+)' . '/resend-ticket', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [$this, 'resend_ticket'],
                'permission_callback' => [$this, 'resend_ticket_permissions_check'],
            ],
        ] );

        register_rest_route( $this->namespace, $this->rest_base . '/(?P<id>[\d]+)' . '/refund', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [$this, 'refund'],
                'permission_callback' => [$this, 'refund_ticket_permissions_check'],
            ],
        ] );

        register_rest_route( $this->namespace, $this->rest_base.'/book-seats', [
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [$this, 'book_seats'],
                // Intentionally nonce-only: guests must be able to temporarily hold seats
                // before an account or order exists. The nonce (injected into the page via
                // localized_data_obj.nonce) proves the request originates from a real page
                // load. No PII is read — only transient seat-hold state is written.
                'permission_callback' => function( $request ) {
                    return wp_verify_nonce( $request->get_header( 'X-WP-Nonce' ), 'wp_rest' );
                },
            ],
        ] );
    }

    /**
     * Check if a given request has access to get items.
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|boolean
     */
    /**
     * Check if a given request has access to list items (admin only).
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|boolean
     */
    public function get_items_permissions_check( $request ) {
        return current_user_can( 'etn_manage_event' );
    }

    public function get_item_permissions_check( $request ) {
        if ( current_user_can( 'etn_manage_event' ) ) {
            return true;
        }

        if ( wp_verify_nonce( $request->get_header( 'X-Wp-Nonce' ), 'wp_rest' ) ) {
            $id    = intval( $request['id'] );
            $token = sanitize_text_field( $request->get_param( 'order_token' ) );
            $stored = get_post_meta( $id, '_order_access_token', true );

            if ( $token && $stored && hash_equals( $stored, $token ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get a collection of items.
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function get_items( $request ) {
		
        $per_page = ! empty( $request['per_page'] ) ? intval( $request['per_page'] ) : 20;
        $paged    = ! empty( $request['paged'] ) ? intval( $request['paged'] ) : 1;
        $event_id   = ! empty( $request['event_id'] ) ? sanitize_text_field( $request['event_id'] ) : '';
        $status = ! empty( $request['status'] ) ? sanitize_text_field( $request['status'] ) : '';
        $payment_method = ! empty( $request['payment_method'] ) ? sanitize_text_field( $request['payment_method'] ) : '';

        $search   = ! empty( $request['search_keyword'] ) ? sanitize_text_field( $request['search_keyword'] ) : '';

        $strt_datetime = ! empty( $request['strt_datetime'] ) ? sanitize_text_field( $request['strt_datetime'] ) : '';

        $end_datetime = ! empty( $request['end_datetime'] ) ? sanitize_text_field( $request['end_datetime'] ) : '';

        $args = [
            'post_type'      => 'etn-order',
            'post_status'    => 'any',
            'posts_per_page' => $per_page,
            'paged'          => $paged,
        ];

        if ( is_numeric( $search ) ) {
            $args['p'] = $search;
        }

        $meta_query = [];

        if ( ! empty( $event_id ) ) {
            $meta_query[] = [
                'key'     => 'event_id',
                'value'   => $event_id,
                'compare' => '=',
            ];
        }

        if ( ! empty( $status ) ) {
            $meta_query[] = [
                'key'     => 'status',
                'value'   => $status,
                'compare' => '=',
            ];
        }

        if ( ! empty( $payment_method ) ) {
            $meta_query[] = [
                'key'     => 'payment_method',
                'value'   => $payment_method,
                'compare' => '=',
            ];
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            $event     = new Event_Model();
            $event_ids = $event->get_ids_by_author( get_current_user_id() );
            $event_ids = ! empty( $event_ids ) ? $event_ids : '';

            $meta_query[] = [
                'key'     => 'event_id',
                'value'   => $event_ids,
                'compare' => 'IN',
            ];
        }

        if ( $strt_datetime && $end_datetime ) {
            $args['date_query'] = [
                'relation' => 'AND',
                [
                    'before'    => [
                        'year'  => gmdate('Y', strtotime($end_datetime)),
                        'month' => gmdate('m', strtotime($end_datetime)),
                        'day'   => gmdate('d', strtotime($end_datetime)),
                    ],
                    'after'     => [
                        'year'  => gmdate('Y', strtotime($strt_datetime)),
                        'month' => gmdate('m', strtotime($strt_datetime)),
                        'day'   => gmdate('d', strtotime($strt_datetime)),
                    ],
                    'inclusive' => true, // Include posts that match the exact date range boundaries
                ],
            ];
        }

        if ( ! empty( $meta_query ) ) {
            $meta_query['relation'] = 'AND';

            $args['meta_query'] = $meta_query; 
        }

        if ( $search && ! is_numeric( $search ) ) {
            $meta_query = array(
                'relation' => 'OR', // 'OR' means any meta key can match
                array(
                    'key'     => 'customer_fname',
                    'value'   => $search,
                    'compare' => 'LIKE'
                ),
                array(
                    'key'     => 'customer_lname',
                    'value'   => $search,
                    'compare' => 'LIKE'
                ),
                array(
                    'key'     => 'customer_email',
                    'value'   => $search,
                    'compare' => 'LIKE'
                ),
                array(
                    'key'     => 'customer_phone',
                    'value'   => $search,
                    'compare' => 'LIKE'
                ),
                array(
                    'key'     => 'payment_method',
                    'value'   => $search,
                    'compare' => 'LIKE'
                ),
            );

            $args['meta_query'] = $meta_query; 
        }

		
        $post_query   = new \WP_Query();
        $query_result = $post_query->query( $args );
        $total_posts  = $post_query->found_posts;
        $orders = [];

        if ( $query_result ) {
            // Prime the WordPress meta cache for all orders in a single DB query,
            // so every subsequent get_post_meta() call in the loop is a cache hit.
            update_meta_cache( 'post', wp_list_pluck( $query_result, 'ID' ) );
        }

        foreach ( $query_result as $post ) {
            $order     = new OrderModel( $post->ID );
            $post_data = $this->prepare_item_for_response( $order, $request );
    
            $orders[] = $this->prepare_response_for_collection( $post_data );
        }
    
        $data = [
            'total_items' => $total_posts,
            'items'       => $orders,
        ];

        $response = rest_ensure_response( $data );

        $response->header( 'X-WP-Total', $total_posts );

        return $response;
        
    }

    /**
     * Get one item from the collection.
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function get_item( $request ) {
        $id = intval( $request['id'] );

        $order    = new OrderModel( $id );
        $response = $this->prepare_item_for_response( $order, $request );

        return rest_ensure_response( $response );
    }

    /**
     * Creates a single event.
     *
     * @since 4.0.0
     *
     * @param WP_REST_Request $request Full details about the request.
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function create_item( $request ) {
        $prepared_order = $this->prepare_item_for_database( $request );

        if ( is_wp_error( $prepared_order ) ) {
            return new WP_Error( 'order_create_error', $prepared_order->get_error_message(), ['status' => 400] );
        }

        $prepared_order['date_time'] = gmdate('Y-m-d h:i A', current_time('timestamp')); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
        // Create order.
        $order     = new OrderModel();
        $order->create( $prepared_order );

        do_action( 'eventin_before_attendees_create', $order);

        // Create attendees.
        $attendees = $this->prepare_attendee_data( $prepared_order['attendees'], $prepared_order['event_id'], $order->id );

        $this->create_attendees( $attendees );

        // Make customer.
        $this->create_customer($order, $request);

        $access_token = bin2hex( random_bytes( 16 ) );
        update_post_meta( $order->id, '_order_access_token', $access_token );

        $response = $this->prepare_item_for_response( $order, $request );
        $response['order_access_token'] = $access_token;

        do_action( 'eventin_after_order_create', $order, $attendees);

        if ( 'pending' == $order->status ) {
            $remaining_time_to_pay = get_post_meta( $order->id, 'remaining_time_to_pay', true );        
            wp_schedule_single_event( time() + ( (int)$remaining_time_to_pay + 1 ), 'eventin_release_held_tickets', [ $order->id ] );
        }
	    
        return rest_ensure_response( $response );
    }

    /**
     * Book seats for an event.
     * This method allows users to book seats for a specific event by providing the event ID and seat IDs.
     * 
     * @param WP_REST_Request $request The request object containing the event ID and seat IDs.
     * 
     * @since 4.0.0
     */
    public function book_seats( $request ) {
        $input_data = json_decode( $request->get_body(), true ) ?? [];

        $event_id = isset( $input_data['event_id'] ) ? intval( $input_data['event_id'] ) : 0;
        $seat_ids = isset( $input_data['seat_ids'] ) ? array_map( 'intval', $input_data['seat_ids'] ) : [];
        $booked_tickets = isset( $input_data['booked_tickets'] ) ? $input_data['booked_tickets'] : [];

        // Validate event ID and seat IDs
        if ( empty( $event_id ) && ( empty( $seat_ids ) || empty( $booked_tickets ) ) ) {
            return new WP_Error( 'invalid_data', __( 'Invalid event ID or seat IDs.', 'eventin' ), ['status' => 400] );
        }

        $event_tickets = etn_safe_decode( get_post_meta( $event_id, 'etn_ticket_variations', true ) );
        $pending_seats = etn_safe_decode( get_post_meta( $event_id, 'pending_seats', true ));
        if(empty($pending_seats)){
            $pending_seats = [];
        }
        $booked_seats = get_post_meta( $event_id, '_etn_seat_unique_id', true );
        $already_booked_seats = $booked_seats ? explode(',', $booked_seats) : [];

        // Validate tickets
        $ticket_validation = etn_validate_event_tickets( $event_id, $booked_tickets );
        if (is_wp_error($ticket_validation)) {
            $response = [
                'success' => false,
                'message' => __('The number of tickets u have selected is not available.', 'eventin'),
                'booked_tickets' => $booked_tickets,
            ];
            return rest_ensure_response($response);
        }

        // Validate seats
        if ( !empty($pending_seats) ) {
            $duplicate_seats = array_intersect($seat_ids, $pending_seats);
            if (!empty($duplicate_seats)) {
                $response = [
                    'success' => false,
                    'message' => __( 'Some of selected seats are already booked.', 'eventin' ),
                    'booked_tickets' => $duplicate_seats,
                ];
                return rest_ensure_response( $response );
            }
        }

        // Validate seats
        if ( !empty($already_booked_seats) ) {
            $duplicate_seats = array_intersect($seat_ids, $already_booked_seats);
            if (!empty($duplicate_seats)) {
                $response = [
                    'success' => false,
                    'message' => __( 'Some of selected seats are already booked.', 'eventin' ),
                    'booked_tickets' => $duplicate_seats,
                ];
                return rest_ensure_response( $response );
            }
        }

        if ( is_array($event_tickets) ) {
            foreach ( $event_tickets as &$ticket ) {
                foreach( $booked_tickets as $booked_ticket ) {
                    if ( !empty($booked_ticket['ticket_slug']) && $ticket['etn_ticket_slug'] === $booked_ticket['ticket_slug'] ) {
                        if ( ! isset( $ticket['pending'] ) ) {
                            $ticket['pending'] = 0;
                        }

                        $ticket['pending'] += $booked_ticket['ticket_quantity'];
                    }
                }
            }
        }

        // update ticket variations pending count
        if(is_array($event_tickets)){
            update_post_meta( $event_id, 'etn_ticket_variations', $event_tickets );
        }

        // update pending seats
        if ( is_array( $seat_ids ) && count( $seat_ids ) > 0 ) {
            update_post_meta( $event_id, 'pending_seats', array_merge(
                $pending_seats,
                $seat_ids
            ) );
        }

        $ticket_purchase_timer = etn_get_option( 'ticket_purchase_timer', 10 ) + 1;
        $data = [ $event_id, $seat_ids, $booked_tickets ];

        wp_schedule_single_event( time() + ( $ticket_purchase_timer * MINUTE_IN_SECONDS ), 'eventin_release_held_seats_and_tickets', $data );

        $response = [
            'success' => true,
            'message' => __( 'Seats booked successfully.', 'eventin' ),
            'event_id' => $event_id,
            'seat_ids' => $seat_ids,
            'booked_tickets' => $booked_tickets,
        ];
        return rest_ensure_response( $response );
    }

    /**
     * Checks if a given request has access to create an order.
     *
     * Intentionally nonce-only: ticket purchases are made by unauthenticated guests
     * who have no WordPress account. Login must not be required to buy a ticket.
     * The nonce (injected into the page via localized_data_obj.nonce) confirms the
     * request originates from a real page load, providing CSRF protection without
     * requiring authentication. This is a write-only operation — no existing order
     * data is exposed.
     *
     * @since 4.0.0
     *
     * @param WP_REST_Request $request Full details about the request.
     * @return true|WP_Error True if the request has access to create items, WP_Error object otherwise.
     */
    public function create_item_permissions_check( $request ) {
        return wp_verify_nonce( $request->get_header( 'X-Wp-Nonce' ), 'wp_rest' );
    }

    /**
     * Updates a single event.
     *
     * @since 4.0.0
     *
     * @param WP_REST_Request $request Full details about the request.
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function update_item( $request ) {
		
		// if request for status update
	    if ( isset( $request['action'] ) && $request['action'] == "update_booking_status" ) {
		    $status = $request['status'];
			if ( !in_array($status, ["failed", "completed", "refunded"]) ) {
				return new WP_Error( 'order_update_booking_status_error', __( 'Invalid status', 'eventin' ), ['status' => 400] );
			}
			
            $id        = intval( $request['id'] );
		    $seat_ids = etn_safe_decode(get_post_meta($id, 'seat_ids', true));
            $event_id = get_post_meta($id, 'event_id', true);
            $tickets = etn_safe_decode(get_post_meta($id, 'tickets', true));

            if ( $status === "completed" ) {
                if (!empty($seat_ids)) {
                    $validate_seats = etn_validate_seat_ids( $event_id, $seat_ids );
                    if (is_wp_error($validate_seats)) {
                        return $validate_seats;
                    }
                }
                else{
                    $validate_tickets = etn_validate_event_tickets( $event_id, $tickets );
                    if (is_wp_error($validate_tickets)) {
                        return $validate_tickets;
                    }
                }
            }
                        
            $order     = new OrderModel( $id );
            $current_status = $order->status;
            if ( $current_status === 'failed' && $status === 'refunded' ) {
                return new WP_Error( 'order_update_booking_status_error', __( "You can't refund a failed order", 'eventin' ), ['status' => 400] );
            }

            if ( $current_status === 'refunded' && $status === 'failed' ) {
                return new WP_Error( 'order_update_booking_status_error', __( "You can't fail a refunded order", 'eventin' ), ['status' => 400] );
            }

		    
		    $order->update(["status" => $status]);
			
		    $attendeeModel = new Attendee_Model();
		    $attendees = $attendeeModel->get_attendees_model_by_eventin_order_id(intval($order->id));

		    
			foreach ($attendees as $attendee) {
				if ( $status === "completed") {
					update_post_meta($attendee->ID, 'etn_status', "success");
				} else {
					update_post_meta($attendee->ID, 'etn_status', "failed");
				}
			}
			
			
			if ( $order->payment_method == "wc" ) {
				$status = $status == "failed" ? "cancelled" : $status;
				$this->wc_order_status_update($id, $status);
			}
			
		    $response   = $this->prepare_item_for_response( $order, $request );
		    
			// send only email if order status is completed
		    if ( 'completed' === $status ) {
                do_action( 'eventin_order_status_completed', $order );
                $order->send_email();
			}

            if ( 'refunded' === $status ) {
                if ( $order->payment_method == "sure_cart" ) {
                    $payment = PaymentFactory::get_method( $order->payment_method );

                    if ( $payment->refund( $order ) ) {
                        if ( 'completed' === $order->status ) {
                            $order->update([
                                'status' => 'refunded'
                            ]);
                            do_action( 'eventin_order_refund', $order );
                        }
                        return rest_ensure_response([
                            'message' => __( 'Successfully refunded', 'eventin' )
                        ]);
                    }
                    else{
                        return new WP_Error( 'refund_error', __( 'Refund unsuccessful', 'eventin' ), ['status' => 422] );
                    }
                }
                else{
                    do_action( 'eventin_order_refund', $order );
                }
            }

            
            if ( 'failed' === $status || 'cancelled' === $status ) {
                if ($current_status === 'completed') {
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

                    \Etn\Utils\Helper::decrease_count_by_ticket_slug($formatted_booked_tickets, $event_id);
                }
                do_action( 'eventin_order_status_failed', $order );
            }
			
			return rest_ensure_response( $response );
	    }
	    
	    $id = intval( $request['id'] );
        $prepared_order = $this->prepare_item_for_database( $request );

        if ( is_wp_error( $prepared_order ) ) {
            return new WP_Error( 'order_create_error', $prepared_order->get_error_message(), ['status' => 400] );
        }

        // Create order.
        $order     = new OrderModel( $id );
        $order->update( $prepared_order );

        // Create attendees.
        $attendees = $this->prepare_attendee_data( $prepared_order['attendees'], $prepared_order['event_id'], $order->id );

        $this->update_attendees( $attendees );

        $response  = $this->prepare_item_for_response( $order, $request );

        do_action( 'eventin_after_order_update', $order );
		
		
        return rest_ensure_response( $response );
    }

    /**
     * Checks if a given request has access to update a event.
     *
     * @since 4.0.0
     *
     * @param WP_REST_Request $request Full details about the request.
     * @return true|WP_Error True if the request has access to update the item, WP_Error object otherwise.
     */
    public function update_item_permissions_check( $request ) {
        return current_user_can( 'etn_manage_order' );
    }

    /**
     * Delete one item from the collection.
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function delete_item( $request ) {
        $id = intval( $request['id'] );

        $post = get_post( $id );
        if ( is_wp_error( $post ) ) {
            return $post;
        }

        $order = new OrderModel( $id );

        if ($order->status) {
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

            \Etn\Utils\Helper::decrease_count_by_ticket_slug($formatted_booked_tickets, $order->event_id);
        }

        $previous = $this->prepare_item_for_response( $order, $request );

        do_action( 'eventin_order_before_delete', $order );

        $result   = wp_delete_post( $id, true );
        $response = new \WP_REST_Response();
        $response->set_data(
            array(
                'deleted'  => true,
                'previous' => $previous,
            )
        );

        if ( ! $result ) {
            return new WP_Error(
                'rest_cannot_delete',
                __( 'The order cannot be deleted.', 'eventin' ),
                array( 'status' => 500 )
            );
        }

        do_action( 'eventin_order_deleted', $order );

        return $response;
    }

    /**
     * Delete multiple items from the collection.
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function delete_items( $request ) {
        $ids = ! empty( $request['ids'] ) ? $request['ids'] : [];

        if ( ! $ids ) {
            return new WP_Error(
                'rest_cannot_delete',
                __( 'Order ids can not be empty.', 'eventin' ),
                array( 'status' => 400 )
            );
        }
        $count = 0;

        foreach ( $ids as $id ) {
            $order = new OrderModel( $id );

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

            \Etn\Utils\Helper::decrease_count_by_ticket_slug($formatted_booked_tickets, $order->event_id);

            do_action( 'eventin_order_before_delete', $order );

            if ( $order->delete() ) {
                $count++;

                do_action( 'eventin_order_deleted', $order );
            }
        }

        if ( $count == 0 ) {
            return new WP_Error(
                'rest_cannot_delete',
                __( 'Order cannot be deleted.', 'eventin' ),
                array( 'status' => 500 )
            );
        }

        // translators: %1$d is the number of orders deleted, %2$d is the total number of orders selected.
        $message = sprintf( __( '%1$d orders are deleted of %2$d', 'eventin' ), $count, count( $ids ) );

        return rest_ensure_response( $message );
    }

    /**
     * Delete one item from the collection.
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function delete_item_permissions_check( $request ) {
        return current_user_can( 'etn_manage_order' );
    }

    /**
     * Get the item's schema for display / public consumption purposes.
     *
     * @return array
     */
    public function get_item_schema() {

    }

    /**
     * Prepare the item for the REST response.
     *
     * @param mixed           $item WordPress representation of the item.
     * @param WP_REST_Request $request Request object.
     * @return WP_REST_Response $response
     */
    public function prepare_item_for_response( $item, $request ) {
        $order = $item instanceof OrderModel ? $item : new OrderModel( $item );
        $event = get_post( $order->event_id );

        $wc_order = null;

        if( function_exists( 'WC' ) ){
    	    $wc_order = $order->findWCOrderByEventinOrder();
        }
		
        $order_data = [
            'id'                => $order->id,
            'customer_fname'    => $order->customer_fname,
            'customer_lname'    => $order->customer_lname,
            'customer_email'    => $order->customer_email,
            'customer_phone'    => $order->customer_phone,
            'date_time'         => $order->date_time,
            'event_id'          => $order->event_id,
            'event_name'        => $event ? $event->post_title : '',
            'payment_method'    => $order->payment_method,
            'status'            => $order->status,
            'total_price'       => $order->total_price,
            'discount_total'    => $order->discount_total,
            'tax_total'         => $order->tax_total,
            'tax_display_mode'  => $order->tax_display_mode,
            'total_ticket'      => $order->get_total_ticket(),
            'ticket_items'      => $order->get_tickets(),
            'attendees'         => $order->get_attendees(),
            'seat_ids'          => $order->seat_ids,
            'attendee_seats'    => $order->attendee_seats,
            'customer'          => $order->get_customer(),
	        'wc_order_id'       => $wc_order ? $wc_order->get_id() : null,
            'extra_fields'      => ( $order->extra_fields == "" || $order->extra_fields == null )  ? new \stdClass() : $order->extra_fields,
            'currency'          => $order->currency?$order->currency:etn_currency(),
            'currency_symbol'   => $order->currency_symbol?html_entity_decode($order->currency_symbol, ENT_QUOTES, 'UTF-8'): html_entity_decode(etn_currency_symbol()),
        ];

        return $order_data;
    }

    /**
     * Prepare the item for create or update operation.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_Error|object $prepared_item
     */
    protected function prepare_item_for_database( $request ) {
        $input_data = json_decode( $request->get_body(), true ) ?? [];

        // Get settings to check if phone is required
        $require_phone_number = etn_get_option( 'require_phone_number', 'off' );
        
        $validation_rules = [
            'event_id'          => ['required'],
            'tickets'           => ['required'],
        ];

        // Add phone validation only if it's required in settings
        if ( $require_phone_number === 'on' ) {
            $validation_rules['customer_phone'] = ['required'];
        }

        $validate = etn_validate( $input_data, $validation_rules );

        if ( is_wp_error( $validate ) ) {
            return $validate;
        }


        $ticket_validation = etn_validate_event_tickets( $input_data['event_id'], $input_data['tickets'],true );

        if ( is_wp_error( $ticket_validation ) ) {
            return $ticket_validation;
        }

        if ( isset( $input_data['seat_ids'] ) ) {
            $seat_validation = etn_validate_seat_ids( $input_data['event_id'], $input_data['seat_ids'] );

            if ( is_wp_error( $seat_validation ) ) {
                return $seat_validation;
            }
        }

        $order_data = [];

        // Prepare customer data.
        if ( isset( $input_data['customer_fname'] ) ) {
            $order_data['customer_fname'] = $input_data['customer_fname'];
        }

        if ( isset( $input_data['customer_lname'] ) ) {
            $order_data['customer_lname'] = $input_data['customer_lname'];
        }

        if ( isset( $input_data['customer_email'] ) ) {
            $order_data['customer_email'] = $input_data['customer_email'];
        }

        if ( isset( $input_data['customer_phone'] ) ) {
            $order_data['customer_phone'] = $input_data['customer_phone'];
        }

        $temporary_status = 'failed';
        $is_enable_payment_timer = etn_get_option( 'ticket_purchase_timer_enable', 'off' );
        if ( $is_enable_payment_timer == 'on' ) {
            $temporary_status = 'pending';
        }
        
        $order_data['status'] = isset( $input_data['status'] ) ? $input_data['status'] : $temporary_status;

        // Prepare event data
        if ( isset( $input_data['event_id'] ) ) {
            $order_data['event_id'] = $input_data['event_id'];
        }

        if ( isset( $input_data['tickets'] ) ) {
            $order_data['tickets'] = $input_data['tickets'];
        }

        // Prepare attendee data
        if ( isset( $input_data['attendees'] ) ) {
            $order_data['attendees'] = $input_data['attendees'];
        }

        // Prepare seat ids.
        if ( isset( $input_data['seat_ids'] ) ) {
            $order_data['seat_ids'] = $input_data['seat_ids'];
        }

        if ( isset( $input_data['attendee_seats'] ) ) {
            $order_data['attendee_seats'] = $input_data['attendee_seats'];
        }

        if ( isset( $input_data['status'] ) ) {
            $order_data['status'] = $input_data['status'];
        }

        if ( isset( $input_data['remaining_time_to_pay'] ) ) {
            $order_data['remaining_time_to_pay'] = $input_data['remaining_time_to_pay'];
        }
        else{
            $order_data['remaining_time_to_pay'] = 10;
        }

        if ( isset( $input_data['extra_fields'] ) ) {
            $order_data['extra_fields'] = $input_data['extra_fields'];
        }

        $tax_display_mode = get_option( 'woocommerce_tax_display_shop' );
        $order_data['tax_display_mode'] = $tax_display_mode;

        $order_data['total_price'] = $this->total_price($order_data['event_id'], $order_data['tickets']);

        return $order_data;
    }

    /**
     * Prepare attendee data
     *
     * @param   array  $attendees  Attendees
     * @param   integer  $event_id   Event id that will be purchased
     * @param   integer  $order_id   Order id
     *
     * @return  array   Attendee data
     */
    protected function prepare_attendee_data( $attendees, $event_id, $order_id ) {
        $attendee_data = [];
        $event = new Event_Model( $event_id );

        if ( $attendees ) {
            foreach( $attendees as $attendee ) {
                $ticket_slug = isset( $attendee['ticket_slug'] ) ? $attendee['ticket_slug'] : '';
                $ticket = $event->get_ticket( $ticket_slug );

                $ticket = apply_filters( 'etn/order-controller/ticket', $ticket, $order_id, $event_id );

                $new_attendee = [
                    'id'                   => isset( $attendee['id'] ) ? $attendee['id'] : '',
                    'etn_name'             => isset( $attendee['name'] ) ? $attendee['name'] : '', 
                    'etn_email'            => isset( $attendee['email'] ) ? $attendee['email'] : '', 
                    'etn_phone'            => isset( $attendee['phone'] ) ? $attendee['phone'] : '', 
                    'attendee_seat'        => isset( $attendee['attendee_seat'] ) ? $attendee['attendee_seat'] : '', 
                    'etn_event_id'         => $event_id,
                    'etn_status'           => '',
                    'ticket_name'          => $ticket['etn_ticket_name'],
                    'ticket_slug'          => $ticket_slug,
                    'etn_ticket_price'     => $ticket['etn_ticket_price'],
                    'etn_info_edit_token'  => md5( $ticket['etn_ticket_name'] ),
                    'etn_unique_ticket_id' => substr(md5($ticket['etn_ticket_price']), 0, 10),
                    'eventin_order_id'     => $order_id,
                    'post_status'          => 'publish',

                ];

                $extra_fields = isset( $attendee['extra_fields'] ) ? $this->prepare_attendee_extra_fields( $attendee['extra_fields'] ) : [];
				
                $attendee_data[] = array_merge( $new_attendee, $extra_fields );;
            }
        }

        return $attendee_data;
    }

    /**
     * Prepare attendee extra fields
     *
     * @param   array  $extra_fields Attendee extra fields
     *
     * @return  array Prepare extra fields data for database
     */
    protected function prepare_attendee_extra_fields( $extra_fields ) {
        $prefix = 'etn_attendee_extra_field_';

        $data = [];

        // Add extra fields meta key prefix.
        foreach( $extra_fields as $key => $value ) {
            $text = mb_strtolower( preg_replace( '/[^\p{L}\p{N}]+/u', '_', trim($key) ) );

            $meta_key = $prefix . $text;
            
            $data[$meta_key] = $value;
        }

        return $data;
    }

    /**
     * Create all attendee
     *
     * @param   array  $attendees  Attendees
     *
     * @return  void Create attendees
     */
    protected function create_attendees( $attendees ) {
        if ( $attendees ) {
            foreach( $attendees as $attendee ) {
                $attendee_object = new Attendee_Model();
                $temporary_status = 'failed';
                $is_enable_payment_timer = etn_get_option( 'ticket_purchase_timer_enable', 'off' );
                if ( $is_enable_payment_timer == 'on' ) {
                    $temporary_status = 'pending';
                }
                $attendee['etn_status'] = $temporary_status;
                $attendee['etn_info_edit_token'] = md5(time() . 'etn-attendee-info');
                
                $attendee['etn_attendeee_ticket_status'] = 'unused';
                $attendee['etn_unique_ticket_id']        =  TicketIdGenerator::generate_ticket_id();
                $attendee_object->set_fields( $attendee );
                $attendee_object->create( $attendee );

                do_action( 'eventin_order_attendee_created', $attendee_object, $attendee );
            }
        }
    }

    /**
     * Create all attendee
     *
     * @param   array  $attendees  Attendees
     *
     * @return  void Create attendees
     */
    protected function update_attendees( $attendees ) {

        if ( $attendees ) {
            foreach( $attendees as $attendee ) {
                $attendee_object = new Attendee_Model( $attendee['id'] );

                $attendee_object->set_fields( $attendee );
                $attendee_object->update( $attendee );
            }
        }
    }

    /**
     * Calculate total price for an order
     *
     * @param   integer  $event_id       Event id
     * @param   array  $order_tickets  Selected order ticket variations
     *
     * @return  integer
     */
    protected function total_price( $event_id, $order_tickets ) {
        $event         = new Event_Model( $event_id );
        $total_price   = 0;

        foreach( $order_tickets as $ticket ) {
            $event_ticket = $event->get_ticket( $ticket['ticket_slug'] );

            $total_price += $event_ticket['etn_ticket_price'] * $ticket['ticket_quantity'];
        }
        
        return apply_filters( 'etn/orders/total_price',$total_price, $event_id, $order_tickets );
    }

    /**
     * Export items
     *
     * @return  JSON
     */
    public function export_items( $request ) {
        $format = ! empty( $request['format'] ) ? sanitize_text_field( $request['format'] ) : '';

        $ids    = ! empty( $request['ids'] ) ? $request['ids'] : '';

        if ( ! $format ) {
            return new WP_Error( 'format_error', __( 'Invalid data format', 'eventin' ) );
        }

        if ( ! $ids ) {
            $ids = (new OrderModel())->get_ids();
        }

        $exporter = new OrderExporter();
        $response = $exporter->export( $ids, $format );

        if ( is_wp_error( $response ) ) {
            return $response;
        }
    }

    /**
     * Export items permission check
     *
     * @param   WP_Rest_Request  $request
     *
     * @return  JSON
     */
    public function export_item_permissions_check( $request ) {
        return current_user_can( 'etn_manage_order' );
    }

    /**
     * Import items
     *
     * @return  JSON
     */
    public function import_items( $request ) {
        $data = $request->get_file_params();
        $file = ! empty( $data['order_import'] ) ? $data['order_import'] : '';

        if ( ! $file ) {
            return new WP_Error( 'empty_file', __( 'You must provide a valid file.', 'eventin' ), ['status' => 409] );
        }

        $importer = new OrderImporter();
        $result   = $importer->import( $file );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        $response = [
            'message' => __( 'Successfully imported order', 'eventin' ),
        ];

        return rest_ensure_response( $response );
    }

    /**
     * Export items permission check
     *
     * @param   WP_Rest_Request  $request
     *
     * @return  JSON
     */
    public function import_item_permissions_check( $request ) {
        return current_user_can('etn_manage_order');
    }

    /**
     * Create customer
     *
     * @param   OrderModel  $order  [$order description]
     * @param   array  $data   [$data description]
     *
     * @return  void
     */
    public function create_customer( $order, $data ) {
        $input = new Input( $data );

        $email = $input->get('customer_email');

        if ( email_exists( $email ) ) {
            $user_data = get_user_by( 'email', $email );

            $customer = new CustomerModel( $user_data->ID );
            $customer->assign_role(['etn-customer']);
        } else {
            $customer = CustomerModel::create([
                'first_name'    => $input->get('customer_fname'),
                'last_name'     => $input->get('customer_lname'),
                'email'         => $email,
            ]);
        }

        $order->update( [
            'customer_id' => $customer->id
        ] );
    }

     /** 
     * Resend ticket email to order customer and attendees
     *
     * @param   WP_Rest_Request  $request  [$request description]
     *
     * @return  WP_Error | WP_Rest_Response
     */
    public function resend_ticket( $request ) {
        $id = intval( $request['id'] );

        $post = get_post( $id );

        if ( ! $post ) {
            return new WP_Error( 'id_error', __( 'Invalid order id', 'eventin' ) );
        }

        if ( 'etn-order' !== $post->post_type ) {
            return new WP_Error( 'id_error', __( 'Invalid order id', 'eventin' ) );
        }

        $order = new OrderModel( $id );
        $event = new Event_Model( $order->event_id );
        $attendees = $order->get_attendees();
        $from      = etn_get_email_settings( 'purchase_email' )['from'];
        
        // Send email to customer.
        Mail::to( $order->customer_email )->from( $from )->send( new AdminOrderEmail( $order ) );

        // Send to attendees email.
        if ( $attendees ) {
            foreach( $attendees as $attendee ) {
                $attendee = new Attendee_Model( $attendee['id'] );

                if ( $attendee->etn_email ) {
                    Mail::to( $attendee->etn_email )->from( $from )->send( new AttendeeOrderEmail( $event, $attendee ) );
                }
            }
        }

        $response = [
            'message'   => __( 'Successfully send ticket email to', 'eventin' ),
        ];

        return rest_ensure_response( $response );
    }

    /**
     * Check permissions for resend ticket to attendee
     *
     * @param   WP_Rest_Request  $request  [$request description]
     *
     * @return  bool
     */
    public function resend_ticket_permissions_check( $request ) {
        return current_user_can( 'etn_manage_order' );
    }

    /**
     * Refund an order
     *
     * @param   WP_Rest_Request  $request  [$request description]
     *
     * @return  WP_Rest_Response | WP_Error
     */
    public function refund( $request ) {
        $id = intval( $request['id'] );

        $post = get_post( $id );

        if ( ! $post ) {
            return new WP_Error( 'invalid_order', __( 'Invalid order id', 'eventin' ), ['status' => 404] );
        }

        if ( 'etn-order' !== $post->post_type ) {
            return new WP_Error( 'invalid_order', __( 'Invalid order id', 'eventin' ), ['status' => 404] );
        }

        $order = new OrderModel( $id );

        if ( $order->total_price < 1 ) {
            return new WP_Error( 'amount_low', __( 'Amount is too low', 'eventin' ), ['status' => 422] );
        }

        if ( ! $order->payment_method ) {
            return new WP_Error( 'payment_method_error', __( 'No payment method found', 'eventin' ), ['status' => 422] );
        }

        $payment = PaymentFactory::get_method( $order->payment_method );

        if ( $payment->refund( $order ) ) {
            
            if ( 'completed' === $order->status ) {
                $order->update([
                    'status' => 'refunded'
                ]);
    
                do_action( 'eventin_order_refund', $order );
            }

            return rest_ensure_response([
                'message' => __( 'Successfully refunded', 'eventin' )
            ]);
        }

        return new WP_Error( 'refund_error', __( 'Refund unsuccessful', 'eventin' ), ['status' => 422] );
    }

    /**
     * Check permission to make refund
     *
     * @param   WP_Rest_Request  $request  [$request description]
     *
     * @return  bool
     */
    public function refund_ticket_permissions_check( $request ) {
        return current_user_can( 'manage_options' );
    }
	
	
	private function wc_order_status_update( $eventin_order_id, $status ) {
		$post_type = etn_is_enable_wc_synchronize_order() ? 'shop_order' : 'shop_order_placehold';
		$args = [
			'post_type'   => $post_type,
			'post_status' => 'any',
			'posts_per_page' => -1,
			'fields'          => 'ids',
			'meta_query'    => [
				[
					'key'   => 'eventin_order_id',
					'value' => $eventin_order_id,
					'compare' => '='
				]
			]
		];
		
		
		$orders_ids = get_posts( $args );
		
		if ( ! $orders_ids ) {
			return false;
		}
		
		$order = wc_get_order( $orders_ids[0] );
		
		if ( $order ) {
			$order->update_status( $status );
			
			return true;
		}
		
		return false;
	}
}