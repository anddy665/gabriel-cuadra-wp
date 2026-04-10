<?php
/**
 * Updater for version 4.1.2
 *
 * @package Eventin\Upgrade
 */

namespace Eventin\Upgrade\Upgraders;

use Etn\Utils\Helper;

/**
 * Updater class for v4.1.2
 *
 * Creates etn_ticket_sales_summary table and populates it with legacy data
 *
 * @since 4.1.2
 */
class V_4_1_2 implements UpdateInterface {
    /**
     * Run the updater
     *
     * @return void
     */
    public function run() {
        global $wpdb;

        // Start transaction
        $wpdb->query( 'START TRANSACTION' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Transaction control; caching is inapplicable to DDL/TCL statements.

        try {
            $table_created = $this->create_ticket_sales_summary_table();

            if ( ! $table_created ) {
                throw new \Exception( 'Failed to create ticket sales summary table' );
            }

            $populated = $this->populate_ticket_sales_summary();

            if ( ! $populated ) {
                throw new \Exception( 'Failed to populate ticket sales summary table' );
            }

            // Commit transaction if everything succeeded
            $wpdb->query( 'COMMIT' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Transaction control; caching is inapplicable.
        } catch ( \Exception $e ) {
            // Rollback inserts
            $wpdb->query( 'ROLLBACK' ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Transaction control; caching is inapplicable.

            // DROP the table since DDL (CREATE TABLE) can't be rolled back in MySQL
            $table_name = $wpdb->prefix . 'etn_ticket_sales_summary';
            $wpdb->query( "DROP TABLE IF EXISTS {$table_name}" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- Rollback DDL; $table_name is constructed from $wpdb->prefix (internal).
        }
    }

    /**
     * Create the etn_ticket_sales_summary table if it doesn't exist
     *
     * @return bool True on success, false on failure
     */
    private function create_ticket_sales_summary_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'etn_ticket_sales_summary';
        $charset_collate = $wpdb->get_charset_collate();

        // Check if table already exists
        if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" ) === $table_name ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- SHOW TABLES requires literal table name; $table_name built from $wpdb->prefix.
            return true; // Table already exists, consider it success
        }

        $sql = "CREATE TABLE {$table_name} (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            event_id BIGINT(20) UNSIGNED NOT NULL,
            ticket_slug VARCHAR(255) NOT NULL,
            sold_count INT(11) NOT NULL DEFAULT 0,
            last_updated DATETIME NOT NULL,
            PRIMARY KEY (id),
            UNIQUE KEY event_ticket (event_id, ticket_slug),
            KEY event_id (event_id)
        ) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );

        // Verify table was created successfully
        if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" ) !== $table_name ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- SHOW TABLES cannot be parameterised; $table_name built from $wpdb->prefix.
            return false;
        }

        return true;
    }

    /**
     * Populate the ticket sales summary table with legacy data
     *
     * @return bool True on success, false on failure
     */
    private function populate_ticket_sales_summary() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'etn_ticket_sales_summary';

        // Check if table exists
        if ( $wpdb->get_var( "SHOW TABLES LIKE '{$table_name}'" ) !== $table_name ) { // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- SHOW TABLES cannot be parameterised; $table_name built from $wpdb->prefix.
            return false;
        }

        // Check if table already has data (avoid duplicate migration)
        $count = $wpdb->get_var( "SELECT COUNT(*) FROM {$table_name}" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- One-time migration guard; $table_name built from $wpdb->prefix.
        if ( $count > 0 ) {
            return true; // Already populated, consider it success
        }

        // Get all events
        $events = get_posts([
            'post_type'      => 'etn',
            'post_status'    => 'any',
            'posts_per_page' => -1,
            'fields'         => 'ids',
        ]);

        if ( empty( $events ) ) {
            return true; // No events to process, consider it success
        }

        foreach ( $events as $event_id ) {
            // Get sold tickets using legacy function
            $sold_tickets = Helper::etn_get_sold_tickets_by_event_legacy( $event_id );

            if ( empty( $sold_tickets ) || ! is_object( $sold_tickets ) ) {
                continue;
            }

            // Convert object to array and insert each record
            $sold_array = (array) $sold_tickets;

            foreach ( $sold_array as $ticket_slug => $sold_count ) {
                if ( empty( $ticket_slug ) || $sold_count <= 0 ) {
                    continue;
                }

                $result = $wpdb->insert( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- One-time migration insert; $wpdb->insert() escapes values internally.
                    $table_name,
                    [
                        'event_id'     => $event_id,
                        'ticket_slug'  => $ticket_slug,
                        'sold_count'   => (int) $sold_count,
                        'last_updated' => current_time( 'mysql' ),
                    ],
                    [ '%d', '%s', '%d', '%s' ]
                );

                // Check if insert failed
                if ( false === $result ) {
                    return false;
                }
            }
        }

        return true;
    }
}
