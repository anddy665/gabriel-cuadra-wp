<?php

namespace Eventin\Integrations\WC;

defined( 'ABSPATH' ) || exit;

use Eventin\Order\OrderModel;
use Eventin\Order\PaymentInterface;

/**
 * Pay using Woocommerce payment
 * 
 * @package Eventin
 */
class WCPayment implements PaymentInterface {
    /**
     * Create payment for woocommerce payment methods
     *
     * @return  void
     */
    public function create_payment( $order ) {
        WC()->cart->empty_cart();

        if ( WC()->session->get( 'event_order_id' ) ) {
            WC()->session->__unset( 'event_order_id' );
        }
        
        WC()->session->set( 'event_order_id', $order->id );

        $cart_id  = WC()->cart->add_to_cart( $order->event_id );

        return [ 'id' => $cart_id ];
    }

    /**
     * Create refund for woocommere order
     *
     * @param   OrderModel  $order
     *
     * @return
     */
    public function refund( OrderModel $order ) {
        $post_type = etn_is_enable_wc_synchronize_order() ? 'shop_order' : 'shop_order_placehold'; 
        $args = [
            'post_type'   => $post_type,
            'post_status' => 'any',
            'posts_per_page' => -1,
            'fields'          => 'ids',        
            'meta_query'    => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                [
                    'key'   => 'eventin_order_id',
                    'value' => $order->id,
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
            $order->update_status( 'refunded' );

            return true;
        }

        return false;
    }
}
