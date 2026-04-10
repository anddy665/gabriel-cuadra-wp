<?php

/**
 * Attendee Exporter Class
 *
 * @package Eventin
 */
namespace Eventin\Attendee;

defined( 'ABSPATH' ) || exit;

use Eventin\Exporter\ExporterFactory;
use Eventin\Exporter\PostExporterInterface;
/**
 * Class Attendee Exporter
 *
 * Export Attendee Data
 */
class RsvpExporter implements PostExporterInterface {
    /**
     * Store file name
     *
     * @var string
     */
    private $file_name = 'rsvp-data';

    /**
     * Store attendee extra fields columns
     *
     * @var array
     */
    private $extra_fields = [];

    /**
     * Store attendee data
     *
     * @var array
     */
    private $data;

    /**
     * Export attendee data
     *
     * @return void
     */
    public function export( $rsvps, $format ) {
        $this->data = $rsvps;

        $rows      = $this->prepare_data();
        $columns   = $this->get_columns();
        $file_name = $this->file_name;

        $exporter = ExporterFactory::get_exporter( $format );

        $exporter->export( $rows, $columns, $file_name );
    }

    /**
     * Prepare data to export
     *
     * @return  array
     */
    private function prepare_data() {
        $rsvps           = $this->data;
        $exported_data = [];

        foreach ( $rsvps as $rsvp ) {
	        $id                 = $rsvp->ID;
	        $post               = get_post( $id );
	        $number_of_attendee = intval( get_post_meta( $id, 'number_of_attendee', true ) );
	        $etn_rsvp_status    = get_post_meta( $id, 'etn_rsvp_value', true);
	        $event_id           = intval( get_post_meta( $id, 'event_id', true ) );
	        $attendee_name      = get_the_title( $id );
	        $attendee_email     = get_post_meta( $id, 'attendee_email', true );
	        $rsvp_date          = $post->post_modified;
	        $guest              = $this->get_guest_details( $id );
	        $extra_details      = get_post_meta( $id, 'extra_fields');
			
	        
	        
	        $rsvp_data = [
		        'id'                      => $id,
		        'number_of_attendee'      => ( $number_of_attendee >= 2 ) ? $number_of_attendee : 0,
		        'status'                  => $etn_rsvp_status,
		        'event_id'                => $event_id,
		        'attendee_name'           => $attendee_name,
		        'attendee_email'          => $attendee_email,
		        'received_on'             => $rsvp_date,
		        'guest'                   => $guest,
		        'extra_details'           => $extra_details,
	        ];
            array_push( $exported_data, $rsvp_data );
        }

        return $exported_data;
    }
	
	public function get_guest_details( $post_parent_id ) {
		$guests = array();
		
		$parent_email   = get_post_meta( $post_parent_id, 'attendee_email', true );
		$attendee_count = get_post_meta( $post_parent_id, 'number_of_attendee', true );
		// Set up the query arguments
		$args = array(
			'post_type'      => 'etn_rsvp',
			'post_parent'    => $post_parent_id,
			'posts_per_page' => -1
		);
		
		$query = new \WP_Query( $args );
		
		// Check if there are posts
		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$post_id = get_the_ID();
				
				// Retrieve post meta
				$name = get_the_title( $post_id );
				$email = get_post_meta($post_id, 'attendee_email', true);
				
				// If parent and child post have the same email, and the attendee is equal to 1, return an empty array
				if ( $email == $parent_email && $attendee_count == 1 ) {
					wp_reset_postdata();
					return $guests;
				}
				
				if ( $name && $email ) {
					$guests[] = array(
						'name'  => $name,
						'email' => $email
					);
				}
			}
			wp_reset_postdata();
		}
		
		// Return the array of guest objects
		return $guests;
	}
	
	
    /**
     * Prepare extra field data
     *
     * @param   integer  $attendee_id
     *
     * @return  array
     */
    private function get_extra_field_data( $attendee_id ) {
        $event_id     = get_post_meta( $attendee_id, 'etn_event_id', true );
        $extra_fields = get_post_meta( $event_id, 'attendee_extra_fields', true );
        $settings     = etn_get_option();
        $data         = [];
        if ( ! $extra_fields ) {
            $extra_fields = ! empty( $settings['extra_fields'] ) ? $settings['extra_fields'] : [];
        }

        if ( $extra_fields ) {
            foreach ( $extra_fields as $index => $value ) {
                $field_id          = ! empty( $value['id'] ) ? $value['id'] : ( $index + 1 );
                $slug              = rtrim( \Etn_Pro\Utils\Helper::get_name_structure_from_label( $value['label'] ), '_' );
                $key               = 'etn_attendee_extra_field_' . $slug . '_' . $field_id;
                $this->extra_fields[$key] = $value['label'];
                $extra_field_value = get_post_meta( $attendee_id, $key, true );
                // Backward-compat: legacy entries stored without the _{id} suffix.
                if ( '' === $extra_field_value || false === $extra_field_value ) {
                    $legacy_key        = 'etn_attendee_extra_field_' . $slug;
                    $extra_field_value = get_post_meta( $attendee_id, $legacy_key, true );
                }
                switch($value['field_type']){
                    case 'radio':
                        $data[$key] = $extra_field_value;
                    break;

                    case 'checkbox': 
                        $data[$key] = $extra_field_value;
                    break;

                    case 'date':
                        $date_format = get_option( 'date_format' );
                        $date   = gmdate( $date_format, strtotime( $extra_field_value ) );

                        if ( ! $extra_field_value ) {
                            $date = '';
                        }
                        
                        $data[$key] = $date;
                    break;

                    default:
                        $data[$key] = get_post_meta( $attendee_id, $key, true );
                }
            }
        }

        return $data;
    }

    /**
     * Get columns
     *
     * @return  array
     */
    private function get_columns() {
        $columns = [
            'id'                => __( 'Id', 'eventin' ),
            'number_of_attendee'=> __( 'Attendees', 'eventin' ),
            'status'            => __( 'Status', 'eventin' ),
            'event_id'          => __( 'Event ID', 'eventin' ),
            'attendee_name'     => __( 'Attendee Name', 'eventin' ),
            'attendee_email'    => __( 'Attendee Email', 'eventin' ),
            'received_on'       => __( 'Received On', 'eventin' ),
            'guest'             => __( 'Guests', 'eventin' ),
            'extra_details'     => __( 'Extra Details', 'eventin' ),
        ];

        $columns = apply_filters( 'etn_prepare_attendee_data_columns', $columns );

        return array_merge( $columns, $this->extra_fields );
    }
}
