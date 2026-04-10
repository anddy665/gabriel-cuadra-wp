<?php

namespace Eventin\Attendee;

use Etn\Utils\Helper;

defined( "ABSPATH" ) || exit;

class Hooks {

    public $cpt;
    public $action;
    public $base;
    public $settings;
    public $actionPost_type = ['etn-attendee'];

    public function __construct() {
        // woocommerce my account > purchased events sidebar menu related hook
        add_action( 'init', [ $this, 'add_purchased_events_endpoint' ] );
        add_filter( 'query_vars', [ $this, 'purchased_events_query_vars' ], 0 );
        add_filter( 'woocommerce_account_menu_items', [ $this, 'add_purchased_events_link_my_account' ] );
        add_action( 'woocommerce_account_purchased-events_endpoint', [ $this, 'purchased_events_content' ] );

        // woo thank you page contains key in url so don't show attendee info here. this is for user purchased events
        if ( !isset( $_GET['key'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only check for WooCommerce order key param; no data is modified.
            add_action( 'woocommerce_order_details_after_order_table', [ $this, 'after_order_table_show_attendee_information' ], 9, 1 );
        }
    }

    /**
     * adding purchased-events endpoint
     */
    public function add_purchased_events_endpoint() {
        add_rewrite_endpoint( 'purchased-events', EP_ROOT | EP_PAGES );
    }

    /**
     * add extra item purchase-events
     *
     * @param [array] $vars
     * @return array
     */
    public function purchased_events_query_vars( $vars ) {
        $vars[] = 'purchased-events';

        return $vars;
    }

    /**
     * add extra item purchase events in sidebar menu
     * 
     * @param [array] $items
     * @return array
     */
    public function add_purchased_events_link_my_account( $items ) {
        $extra_item = [ 
            'purchased-events' => esc_html__( 'Purchased events', 'eventin' )
        ];

        $split_1 = array_slice( $items, 0, 3 );
        $split_2 = array_slice( $items, 3, count( $items ) );

        $items = $split_1 + $extra_item + $split_2;
        return $items;
    }

    /**
     * view of purchased events page
     */
    public function purchased_events_content() {
        global $wpdb;

        $current_user_id = get_current_user_id();
        $customer_orders = wc_get_orders([
            'customer' => $current_user_id,
            'status'   => array_keys(wc_get_order_statuses()),
            'return'   => 'ids',
        ]);


        $user_events = [];
        foreach ($customer_orders as $order_id) {
            $order          = wc_get_order( $order_id );
            $order_status   = $order->get_status();
            $order_url      = $order->get_view_order_url();
            
            foreach ( $order->get_items() as $item_id => $item ) {
                $product_name  = $item->get_name();
                $event_id      = $item->get_meta('event_id', true);

                if ( !empty( $event_id ) ) {
                    $user_events[ $order_id ][ $event_id ] = [
                        'event_id'     => $event_id,
                        'event_name'   => $product_name,
                        'order_status' => $order_status,
                        'order_id'     => $order_id,
                        'order_url'    => $order_url,
                    ];
                }
            }
        }

        if (!empty($user_events)) {
            include_once \Wpeventin::templates_dir() . "attendee/purchased-events.php";
        } else {
            echo esc_html__('No event has been purchased yet!', 'eventin');
        }
    }

    /**
     * show attendee information in woo order details
     *
     * @param [type] $order
     * @return void
     */
    public function after_order_table_show_attendee_information( $order ) { 
        foreach ( $order->get_items() as $item_id => $item ) {
            $event_id = !is_null( $item->get_meta( 'event_id', true ) ) ? $item->get_meta( 'event_id', true ) : "";

            if ( !empty( $event_id ) ) {

                $is_enable_custom_order_table = get_option( 'woocommerce_custom_orders_table_enabled', true );

                if ( 'no' === $is_enable_custom_order_table ) {
                    $order_id = $order->ID;
                } else {
                    $order_id = $order->get_id();
                }

                $eventin_order_id = get_post_meta( $order_id, 'eventin_order_id', true );

                $args = array(
                    'post_type'      => 'etn-attendee',
                    'post_status'    => 'publish',
                    'posts_per_page' => -1,
                    'meta_query'     => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                        'relation'  => 'OR',
                        [
                            'key'       => 'eventin_order_id',
                            'value'     => $eventin_order_id,
                            'compare'   => '='
                        ],
                        [
                            'key'       => 'etn_attendee_order_id',
                            'value'     => $eventin_order_id,
                            'compare'   => '='
                        ]
                    ]
                );

                $attendees = get_posts($args);
                
                if( count( $attendees ) > 0 ) {
                    $settings        = Helper::get_settings();
                    $include_email   = !empty( $settings["reg_require_email"] ) ? true : false;
                    $include_phone   = !empty( $settings["reg_require_phone"] ) ? true : false;
    
                    $base_url               = home_url( );
                    $attendee_cpt           = new \Etn\Core\Attendee\Cpt();
                    $attendee_endpoint      = $attendee_cpt->get_name();
                    $action_url             = $base_url . "/" . $attendee_endpoint;
    
                    $ticket_download_link   = $action_url . "?etn_action=". urlencode('download_ticket') ."&attendee_id="; 
                    $edit_information_link  = $action_url . "?etn_action=" . urlencode( 'edit_information' ) . "&attendee_id=";
    
                    include_once \Wpeventin::templates_dir() . "attendee/attendee-details.php";
                }
            }
        }
    }
}
