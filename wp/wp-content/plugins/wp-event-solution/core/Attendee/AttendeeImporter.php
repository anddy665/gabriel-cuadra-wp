<?php

/**
 * Attendee Importer Class
 *
 * @package Eventin
 */
namespace Eventin\Attendee;

defined( 'ABSPATH' ) || exit;

use Eventin\Importer\PostImporterInterface;
use Eventin\Importer\ReaderFactory;
use Etn\Core\Attendee\Attendee_Model;
/**
 * Class Attendee Importer
 */
class AttendeeImporter implements PostImporterInterface {
    /**
     * Store File
     *
     * @var string
     */
    private $file;

    /**
     * Store data
     *
     * @var array
     */
    private $data;

    /**
     * Attendee import
     *
     * @return  void
     */
    public function import( $file ) {
        $this->file  = $file;
        $file_reader = ReaderFactory::get_reader( $file );

        if ( is_wp_error( $file_reader ) ) {
            return $file_reader;
        }

        $this->data = $file_reader->read_file();
        $this->create_attendee();
    }

    /**
     * Create Attendee
     *
     * @return  void
     */
    private function create_attendee() {
        $attendee  = new Attendee_Model();
        $file_type = ! empty( $this->file['type'] ) ? $this->file['type'] : '';

        $rows = $this->data;

        foreach ( $rows as $row ) {
            $args = [
                'etn_name'                    => ! empty( $row['name'] ) ? $row['name'] : '',
                'etn_email'                   => ! empty( $row['email'] ) ? $row['email'] : '',
                'etn_phone'                   => ! empty( $row['phone'] ) ? $row['phone'] : '',
                'etn_event_id'                => ! empty( $row['event_id'] ) ? $row['event_id'] : '',
                'etn_unique_ticket_id'        => ! empty( $row['ticket_id'] ) ? $row['ticket_id'] : '',
                'ticket_name'                 => ! empty( $row['ticket_name'] ) ? $row['ticket_name'] : '',
                'etn_attendeee_ticket_status' => ! empty( $row['ticket_status'] ) ? $row['ticket_status'] : '',
                'etn_ticket_price'            => ! empty( $row['ticket_price'] ) ? $row['ticket_price'] : '',
                'etn_status'                  => ! empty( $row['payment_status'] ) ? $row['payment_status'] : '',
                'etn_info_edit_token'         => md5( time() . 'etn-attendee-info' ),
                'post_status'                 => 'publish',
            ];

            $extra_fields = $this->get_extra_field_data( $row );

            $args = array_merge( $args, $extra_fields );

            $attendee->create( $args );

            $this->update_extra_fields( $attendee->id, $extra_fields );
        }

    }

    /**
     * Get extra fields value
     *
     * @param   array  $row
     *
     * @return  array
     */
    private function get_extra_field_data( $row ) {
        $event_id     = ! empty( $row['event_id'] ) ? intval( $row['event_id'] ) : 0;
        $extra_fields = get_post_meta( $event_id, 'attendee_extra_fields', true );
        $settings     = etn_get_option();
        $data         = [];
        $file_type = ! empty( $this->file['type'] ) ? $this->file['type'] : '';

        if ( ! $extra_fields ) {
            $extra_fields = ! empty( $settings['attendee_extra_fields'] ) ? $settings['attendee_extra_fields'] : [];
        }

        if ( $extra_fields ) {
            foreach ( $extra_fields as $value ) {
                $column     = strtolower( str_replace( [' ', '-'], '_', $value['label'] ) );
                $meta_key   = 'etn_attendee_extra_field_' . $column;

                if ( 'application/json' === $file_type ) {
                    $meta_value = ! empty( $row[$meta_key] ) ? $row[$meta_key] : '';
                } else {
                    $meta_value = ! empty( $row[$column] ) ? $row[$column] : '';
                }

                $data[$meta_key] = $meta_value;
            }
        }

        return $data;
    }

    /**
     * Updated extra field
     *
     * @param   integer  $attendee_id
     *
     * @return  void
     */
    private function update_extra_fields( $attendee_id, $fields ) {
        
        if ( $fields ) {
            foreach( $fields as $key => $value ) {
                update_post_meta( $attendee_id, $key, $value );
            }
        }
    }
}
