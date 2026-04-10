<?php

namespace Eventin\Reports;

defined( 'ABSPATH' ) || exit;

use Eventin\Input;
use Eventin\Order\OrderModel;
use Etn\Core\Event\Event_Model;

/**
 * Revenue  Report class
 * 
 * @package Eventin
 */
class RevenueReport extends AbstractReport {
    /**
     * Get total revenue
     *
     * @param   array  $dates  Start and end date
     *
     * @return  number
     */
    public static function get_total_revenue( $dates = [], $event_id = null ) {
        global $wpdb;

        $tax_display_mode = get_option( 'woocommerce_tax_display_shop' );

        $revenue_expr = $tax_display_mode === 'incl'
            ? 'COALESCE(price.meta_value + 0, 0) - COALESCE(discount.meta_value + 0, 0)'
            : 'COALESCE(price.meta_value + 0, 0) - COALESCE(discount.meta_value + 0, 0) + COALESCE(tax.meta_value + 0, 0)';

        $sql = "
            SELECT COALESCE(SUM({$revenue_expr}), 0)
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} status_m
                ON status_m.post_id = p.ID
                AND status_m.meta_key = 'status'
                AND status_m.meta_value = 'completed'
            INNER JOIN {$wpdb->postmeta} price
                ON price.post_id = p.ID
                AND price.meta_key = 'total_price'
            LEFT JOIN {$wpdb->postmeta} discount
                ON discount.post_id = p.ID
                AND discount.meta_key = 'discount_total'
            LEFT JOIN {$wpdb->postmeta} tax
                ON tax.post_id = p.ID
                AND tax.meta_key = 'tax_total'
            WHERE p.post_type = 'etn-order'
            AND p.post_status != 'trash'
        ";

        $params = [];

        if ( ! empty( $dates['start_date'] ) && ! empty( $dates['end_date'] ) ) {
            $sql     .= " AND p.post_date >= %s AND p.post_date <= %s";
            $params[] = $dates['start_date'] . ' 00:00:00';
            $params[] = $dates['end_date'] . ' 23:59:59';
        }

        if ( ! empty( $event_id ) ) {
            $sql     .= " AND EXISTS (
                SELECT 1 FROM {$wpdb->postmeta} em
                WHERE em.post_id = p.ID AND em.meta_key = 'event_id' AND em.meta_value = %d
            )";
            $params[] = $event_id;
        } elseif ( ! current_user_can( 'manage_options' ) ) {
            $event     = new Event_Model();
            $event_ids = $event->get_ids_by_author( get_current_user_id() );

            if ( empty( $event_ids ) ) {
                return 0;
            }

            $placeholders = implode( ',', array_fill( 0, count( $event_ids ), '%d' ) );
            $sql         .= " AND EXISTS (
                SELECT 1 FROM {$wpdb->postmeta} em
                WHERE em.post_id = p.ID AND em.meta_key = 'event_id' AND em.meta_value IN ({$placeholders})
            )";
            $params = array_merge( $params, $event_ids );
        }

        if ( ! empty( $params ) ) {
            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            $sql = $wpdb->prepare( $sql, $params );
        }

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        return (float) $wpdb->get_var( $sql );
    }

    /**
     * Get revenue for multiple events in a single query.
     *
     * @param   int[]  $event_ids  List of event IDs
     *
     * @return  array  Map of event_id => revenue
     */
    public static function get_revenue_map_by_event_ids( array $event_ids ) {
        global $wpdb;

        if ( empty( $event_ids ) ) {
            return [];
        }

        $tax_display_mode = get_option( 'woocommerce_tax_display_shop' );

        $revenue_expr = $tax_display_mode === 'incl'
            ? 'COALESCE(price.meta_value + 0, 0) - COALESCE(discount.meta_value + 0, 0)'
            : 'COALESCE(price.meta_value + 0, 0) - COALESCE(discount.meta_value + 0, 0) + COALESCE(tax.meta_value + 0, 0)';

        $placeholders = implode( ',', array_fill( 0, count( $event_ids ), '%d' ) );

        // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $sql = $wpdb->prepare(
            "SELECT em.meta_value AS event_id, COALESCE(SUM({$revenue_expr}), 0) AS revenue
            FROM {$wpdb->posts} p
            INNER JOIN {$wpdb->postmeta} status_m
                ON status_m.post_id = p.ID
                AND status_m.meta_key = 'status'
                AND status_m.meta_value = 'completed'
            INNER JOIN {$wpdb->postmeta} price
                ON price.post_id = p.ID
                AND price.meta_key = 'total_price'
            LEFT JOIN {$wpdb->postmeta} discount
                ON discount.post_id = p.ID
                AND discount.meta_key = 'discount_total'
            LEFT JOIN {$wpdb->postmeta} tax
                ON tax.post_id = p.ID
                AND tax.meta_key = 'tax_total'
            INNER JOIN {$wpdb->postmeta} em
                ON em.post_id = p.ID
                AND em.meta_key = 'event_id'
            WHERE p.post_type = 'etn-order'
            AND p.post_status != 'trash'
            AND em.meta_value IN ({$placeholders})
            GROUP BY em.meta_value", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare
            $event_ids
        );

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared,WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching
        $rows = $wpdb->get_results( $sql );

        $map = [];
        foreach ( $rows as $row ) {
            $map[ (int) $row->event_id ] = (float) $row->revenue;
        }

        return $map;
    }

    /**
     * Get total refunded amount
     *
     * @param   array  $dates  Start and end date
     *
     * @return  number
     */
    public static function get_total_refunded_amount( $dates = [], $event_id = null ) {
        $total = 0;
        $order_ids = OrderReport::get_refunded_orders( $dates, $event_id );

        if ( empty( $order_ids ) ) {
            return $total;
        }

        // Batch-load all order meta in a single query
        update_meta_cache( 'post', $order_ids );

        // Get tax display mode once, outside the loop
        $tax_display_mode = get_option( 'woocommerce_tax_display_shop' );

        foreach ( $order_ids as $order_id ) {
            $total_price    = floatval( get_post_meta( $order_id, 'total_price', true ) );
            $float_discount = floatval( get_post_meta( $order_id, 'discount_total', true ) );
            $float_tax      = floatval( get_post_meta( $order_id, 'tax_total', true ) );

            if ( $tax_display_mode === 'incl' ) {
                $total += $total_price - $float_discount;
            } else {
                $total += $total_price - $float_discount + $float_tax;
            }
        }

        return $total;
    }

    /**
     * Get revenue by event
     *
     * @param   array  $data  Date range and event id
     *
     * @return  array
     */
    public static function get_reports_by_event( $data = [] ) {
        $reports = [
            'total' => self::get_total_revenue_by_event( $data ),
        ];

        $ticket_reports = self::get_total_revenue_by_tickets( $data );

        return array_merge( $reports, $ticket_reports );
    }

    /**
     * Get total revenue by event
     *
     * @param   array  $data  [$data description]
     *
     * @return  integer
     */
    public static function get_total_revenue_by_event( $data = [] ) {
        $orders = OrderReport::get_orders_by_event( $data );
        $total = 0;

        if ( is_array( $orders ) ) {
            foreach( $orders as $order_id ) {
                $order = new OrderModel( $order_id );
                $total += $order->total_price;
            }
        }

        return $total;
    }

    /**
     * Get revenue for every ticket
     *
     * @param   array  $data  Event data
     *
     * @return  array
     */
    private static function get_total_revenue_by_tickets( $data ) {
        $tickets    = EventReport::get_ticket_reports_by_event( $data );
        $total      = 0;
        $event      = new Event_Model( $data['event_id'] );
        $variations = $event->etn_ticket_variations;
        $revenue    = [];

        if ( is_array( $tickets ) ) {
            foreach( $tickets as $ticket_name => $ticket ) {
                $price = $event->get_ticket_price_by_name( $ticket_name );
                $revenue[$ticket_name] = $tickets[$ticket_name]['sold'] * $price;
            }
        }
        
        return $revenue;
    }
}
