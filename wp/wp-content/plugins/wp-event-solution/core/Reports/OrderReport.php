<?php

namespace Eventin\Reports;

defined( 'ABSPATH' ) || exit;

use Etn\Core\Event\Event_Model;
use Eventin\Input;

/**
 * Order Report class
 * 
 * @package Eventin
 */
class OrderReport extends AbstractReport {
    /**
     * Get total order
     *
     * @param   array  $dates  Date range
     *
     * @return  integer Number of total order
     */
    public static function get_total_order( $dates = [] ) {
        $orders = self::get_orders( $dates );

        if ( is_array( $orders ) ) {
            return count( $orders );
        }

        return 0;
    }

    /**
     * Get total failed order
     *
     * @param   array  $dates  Date range
     *
     * @return  integer Number of total failed order
     */
    public static function get_total_failed_order( $dates = [] ) {
        $orders = self::get_failed_orders( $dates );

        if ( is_array( $orders ) ) {
            return count( $orders );
        }

        return 0;
    }

    /**
     * Get total refunded order
     *
     * @param   array  $dates  Date range
     *
     * @return  integer Number of total refunded order
     */
    public static function get_total_refunded_order( $dates = [] ) {
        $orders = self::get_refunded_orders( $dates );

        if ( is_array( $orders ) ) {
            return count( $orders );
        }

        return 0;
    }

    /**
     * Get orders
     *
     * @param   array  $data  [$data description]
     *
     * @return  array
     */
    public static function get_orders( $data = [], $event_id = null ) {
        $input      = new Input( $data );
        $start_date = $input->get( 'start_date' );
        $end_date   = $input->get( 'end_date' );

        $args = [
            'post_type'  => 'etn-order',
            'start_date' => $start_date,
            'end_date'   => $end_date,
            'meta_query' => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                'Relation' => 'AND',
                [
                    'key'       => 'status',
                    'value'     => 'completed',
                    'compare'   => '=',
                ],
            ]
        ];

        if ( ! empty( $event_id ) ) {
            $args['meta_query'][] = [
                'key'       => 'event_id',
                'value'     => $event_id,
                'compare'   => '=',
            ];
        }

        if ( ! empty( $event_id ) ) {
            $args['meta_query'][] = [
                'key'       => 'event_id',
                'value'     => $event_id,
                'compare'   => '=',
            ];
        }
        elseif ( ! current_user_can( 'manage_options' ) ) {
            $event = new Event_Model();
            $event_ids = $event->get_ids_by_author( get_current_user_id() );
            $event_ids = ! empty( $event_ids ) ? $event_ids : '';

            $args['meta_query'][] = [
                'key'       => 'event_id',
                'value'     => $event_ids,
                'compare'   => 'IN',
            ];
        }

        return self::get_posts( $args );
    }

    /**
     * Get failed orders
     *
     * @param   array  $data  Date range
     *
     * @return  array
     */
    public static function get_failed_orders( $data = [] ) {
        $input      = new Input( $data );
        $start_date = $input->get( 'start_date' );
        $end_date   = $input->get( 'end_date' );

        $args = [
            'post_type'  => 'etn-order',
            'start_date' => $start_date,
            'end_date'   => $end_date,
            'meta_query' => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                'Relation' => 'AND',
                [
                    'key'       => 'status',
                    'value'     => 'failed',
                    'compare'   => '=',
                ],
            ]
        ];

        if ( ! current_user_can( 'manage_options' ) ) {
            $event = new Event_Model();
            $event_ids = $event->get_ids_by_author( get_current_user_id() );
            $event_ids = ! empty( $event_ids ) ? $event_ids : '';

            $args['meta_query'][] = [
                'key'       => 'event_id',
                'value'     => $event_ids,
                'compare'   => 'IN',
            ];
        }

        return self::get_posts( $args );
    }

    /**
     * Get refunded orders
     *
     * @param   array  $data  Date range
     *
     * @return  array
     */
    public static function get_refunded_orders( $data = [] ) {
        $input      = new Input( $data );
        $start_date = $input->get( 'start_date' );
        $end_date   = $input->get( 'end_date' );

        $args = [
            'post_type'  => 'etn-order',
            'start_date' => $start_date,
            'end_date'   => $end_date,
            'meta_query' => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                'Relation' => 'AND',
                [
                    'key'       => 'status',
                    'value'     => 'refunded',
                    'compare'   => '=',
                ],
            ]
        ];

        if ( ! current_user_can( 'manage_options' ) ) {
            $event = new Event_Model();
            $event_ids = $event->get_ids_by_author( get_current_user_id() );
            $event_ids = ! empty( $event_ids ) ? $event_ids : '';

            $args['meta_query'][] = [
                'key'       => 'event_id',
                'value'     => $event_ids,
                'compare'   => 'IN',
            ];
        }

        return self::get_posts( $args );
    }

    /**
     * Get orders by event id
     *
     * @param   array  $data  Date range and event id
     *
     * @return  array Order Ids
     */
    public static function get_orders_by_event( $data = [] ) {
        $input      = new Input( $data );
        $start_date = $input->get( 'start_date' );
        $end_date   = $input->get( 'end_date' );
        $event_id   = $input->get( 'event_id' );

        return self::get_posts([
            'post_type'  => 'etn-order',
            'start_date' => $start_date,
            'end_date'   => $end_date,
            'meta_query' => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                [
                    'key'       => 'status',
                    'value'     => 'completed',
                    'compare'   => '=',
                ],
                [
                    'key'       => 'event_id',
                    'value'     => $event_id,
                    'compare'   => '=',
                ]
            ]
        ]);
    }

    /**
     * Get failed orders by event
     *
     * @param   array  $data  Date range and event id
     *
     * @return  array Order Ids
     */
    public static function get_failed_orders_by_event( $data = [] ) {
        $input      = new Input( $data );
        $start_date = $input->get( 'start_date' );
        $end_date   = $input->get( 'end_date' );
        $event_id   = $input->get( 'event_id' );

        return self::get_posts([
            'post_type'  => 'etn-order',
            'start_date' => $start_date,
            'end_date'   => $end_date,
            'meta_query' => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                [
                    'key'       => 'status',
                    'value'     => 'failed',
                    'compare'   => '=',
                ],
                [
                    'key'       => 'event_id',
                    'value'     => $event_id,
                    'compare'   => '=',
                ]
            ]
        ]);
    }

    /**
     * Get refunded orders by event
     *
     * @param array $data
     *
     * @return array|\WP_Post[]
     */
    public static function get_refunded_orders_by_event( $data = [] ) {
        $input      = new Input( $data );
        $start_date = $input->get( 'start_date' );
        $end_date   = $input->get( 'end_date' );
        $event_id   = $input->get( 'event_id' );

        return self::get_posts([
            'post_type'  => 'etn-order',
            'start_date' => $start_date,
            'end_date'   => $end_date,
            'meta_query' => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                [
                    'key'       => 'status',
                    'value'     => 'refunded',
                    'compare'   => '=',
                ],
                [
                    'key'       => 'event_id',
                    'value'     => $event_id,
                    'compare'   => '=',
                ]
            ]
        ]);
    }

    /**
     * Get total orders by event
     *
     * @param   array  $data  [$data description]
     *
     * @return  integer
     */
    public static function get_total_orders_by_event( $data ) {
        $orders = self::get_orders_by_event( $data );

        if ( is_array( $orders ) ) {
            return count( $orders );
        }

        return 0;
    }

    /**
     * Get total failed orders by event
     *
     * @param   array  $data  [$data description]
     *
     * @return  integer
     */
    public static function get_total_failed_orders_by_event( $data ) {
        $orders = self::get_failed_orders_by_event( $data );

        if ( is_array( $orders ) ) {
            return count( $orders );
        }

        return 0;
    }

    /**
     * Get total refunded orders by event
     *
     * @param   array  $data  [$data description]
     *
     * @return  integer
     */
    public static function get_total_refunded_orders_by_event( $data ) {
        $orders = self::get_refunded_orders_by_event( $data );

        if ( is_array( $orders ) ) {
            return count( $orders );
        }

        return 0;
    }

    /**
     * Get order reports by event
     *
     * @param   array  $data  [$data description]
     *
     * @return  array        [return description]
     */
    public static function get_reports_by_event( $data ) {
        $reports = [
            'total'     => self::get_total_orders_by_event( $data ),
            'failed'    => self::get_total_failed_orders_by_event( $data ),
            'refunded'  => self::get_total_refunded_orders_by_event( $data ),
        ];

        return $reports;
    }
}
