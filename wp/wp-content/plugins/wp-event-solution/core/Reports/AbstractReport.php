<?php

namespace Eventin\Reports;

defined( 'ABSPATH' ) || exit;
use Eventin\Input;

/**
 * Abstract report class
 * 
 * @package Eventin
 */
abstract class AbstractReport {
    /**
     * Get posts
     *
     * @param   array  $data  [$data description]
     *
     * @return  array       Posts
     */
    protected static function get_posts( $data ) {
        $input      = new Input( $data );
        $post_type  = $input->get( 'post_type' );
        $status     = $input->get( 'status', 'any' );
        $start_date = $input->get( 'start_date' );
        $end_date   = $input->get( 'end_date' );
        $meta_query = $input->get( 'meta_query' );
        $author     = $input->get( 'author' );

        $args = [
            'post_type'      => $post_type,
            'post_status'    => $status,
            'fields'         => 'ids',
            'posts_per_page' => -1,
        ];

        if ( $author ) {
            $args['author'] = $author;
        }

        if ( $start_date && $end_date ) {
            $args['date_query'] = [
                [
                    'after'     => $start_date,
                    'before'    => $end_date,
                    'inclusive' => true,
                ]
            ];
        }

        if ( $meta_query ) {
            $args['meta_query'] = $meta_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
        }

        $posts = get_posts( $args );

        return $posts;
    }

    /**
     * Get users
     *
     * @param   array  $data  Date range
     *
     * @return  array        User data
     */
    protected static function get_users( $data ) {
        $input      = new Input( $data );
        $roles      = $input->get( 'roles', [] );
        $start_date = $input->get( 'start_date' );
        $end_date   = $input->get( 'end_date' );

        $args = array(
            'role__in' => $roles,
            'number'   => -1,
            'fields'   => 'ids',
        );

        if ( $start_date && $end_date ) {
            $args['date_query'] = [
                [
                    'after'     => $start_date,
                    'before'    => $end_date,
                    'inclusive' => true,
                ],
            ];
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            $args['meta_query'] = [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                [
                    'key'   => 'author',
                    'value' => get_current_user_id(),
                    'compare' => '='
                ]
            ];
        }

        return get_users( $args );
    }
}
