<?php

/**
 * Attendee Model Class
 *
 * @package Eventin
 */
namespace Etn\Core\Attendee;

defined( 'ABSPATH' ) || exit;

use Etn\Base\Post_Model;
use function cli\err;

/**
 * Attendee Model
 */
class Attendee_Model extends Post_Model {
    protected $post_type = 'etn-attendee';

    /**
     * Store attendee data
     *
     * @var array
     */
    protected $data = [
        'etn_name'                    => '',
        'etn_email'                   => '',
        'etn_phone'                   => '',
        'etn_event_id'                => '',
        'eventin_order_id'            => '',
        'etn_unique_ticket_id'        => '',
        'ticket_name'                 => '',
        'ticket_slug'                 => '',
        'etn_attendeee_ticket_status' => 'unused',
        'etn_ticket_price'            => '',
        'etn_status'                  => 'failed',
        'etn_info_edit_token'         => '',
        'extra_fields'                => '',
        'attendee_seat'               => '',
    ];

    /**
     * Set extra field data
     *
     * @param   string  $name   Extra field name
     * @param   mixed  $value  Extra field value
     *
     * @return  void
     */
    public function __set( $name, $value ) {
        $this->data[$name] = $value;
    }

    /**
     * Set extra fields
     *
     * @param   array  $extra_fields  Attendee extra fields
     *
     * @return  void
     */
    public function set_fields( $extra_fields ) {
        foreach( $extra_fields as $key => $value ) {
            $this->data[$key] = $value;
        }
    }

    /**
     * Get all attendees by key. Example all attendess for an event or an order
     *
     * @param   string  $key    [$key description]
     * @param   mixed  $value  [$value description]
     *
     * @return  array
     */
    public function get_attendees_by( $key, $value, $post_status = ['publish', 'trash'], $limit = -1 ) {
        $args = [
            'post_type'      => 'etn-attendee',
            'post_status'    => $post_status,
            'posts_per_page' => $limit,
            'meta_query'     => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                [
                    'key'     => $key,
                    'value'   => $value,
                    'compare' => '=',
                ]
            ]
        ];

        $attendees = get_posts( $args );

        $data = [];

        if ( $attendees ) {
            $attendee_ids = wp_list_pluck( $attendees, 'ID' );
            update_postmeta_cache( $attendee_ids );

            foreach( $attendees as $attendee ) {
                $attendee_object = new Attendee_Model( $attendee->ID );
                $attendee_data   = $attendee_object->get_data();
                $attendee_data['extra_fields'] = $attendee_object->get_extra_fields();
                $data[] = $attendee_data;
            }
        }

        return $data;
    }

    /**
     * Get attendee extra fields data
     *
     * @return  array  Extra fields data
     */
    public function get_extra_fields() {
        $fields = get_post_meta( $this->id );
        $extra_fields = [];

        if ( ! is_array( $fields ) ) {
            return $extra_fields;
        }
        
        foreach( $fields as $key => $value ) {
            // Check extra fields exist or not.
            if ( strpos( $key, 'etn_attendee_extra_field_' ) !== false ) {
                $new_key = str_replace( 'etn_attendee_extra_field_', '', $key );
                // $value is an array from get_post_meta($id) with no key; use first element directly
                // to avoid a redundant DB round-trip (or cache hit) for get_post_meta($id, $key, true).
                $extra_fields[ $new_key ] = get_post_meta( $this->id, $key, true );
            }
        }

        return $extra_fields;
    }

    /**
     * Get scanner update time
     *
     * @return  string
     */
    public function get_scanner_update_time() {
        $scanner_update_time = get_post_meta( $this->id, 'scanner_update_time', true );

        if ( empty( $scanner_update_time ) ) {
            return null;
        }
		
	    $date_format = get_option( 'date_format' );
	    $time_format = get_option( 'time_format' );
	    return date_i18n( $date_format . ' ' . $time_format, strtotime($scanner_update_time) );
    }

    /**
     * Get extra fields as html content
     *
     * @return  string
     */
    public function get_extra_fields_content() {
        $fields = '<ul class="etn-attendee-extra-data" style="list-style: none; list-style-type: none; padding-left: 0; margin-left: 0;">';
        $extra_fields = $this->get_extra_fields();

        if ( $extra_fields ) {
            foreach ( $extra_fields as $key => $field ) {
                // Format label: replace underscores with spaces and capitalize each word
                $label = ucwords(str_replace('_', ' ', $key));

                $fields .= "<li><label style='font-weight: bold;'>{$label}:</label> <span>{$field}</span></li>";
            }
        }
        $fields .= '</ul>';
        return $fields;
    }
    /**
     * Get a single extra field value by key
     *
     * @param   string  $field_key  The field key (without prefix)
     *
     * @return  string
     */
    public function get_single_extra_field( $field_key ) {
        $extra_fields = $this->get_extra_fields();

        if ( ! isset( $extra_fields[ $field_key ] ) ) {
            return '';
        }

        $value = $extra_fields[ $field_key ];

        return is_array( $value ) ? implode( ', ', $value ) : $value;
    }

    /**
     * Get extra fields as a single comma-separated inline string
     *
     * @return  string
     */
    public function get_extra_fields_inline() {
        $extra_fields = $this->get_extra_fields();
        $parts        = [];

        foreach ( $extra_fields as $key => $field ) {
            $label   = ucwords( str_replace( '_', ' ', $key ) );
            $value   = is_array( $field ) ? implode( ', ', $field ) : $field;
            $parts[] = $label . ': ' . $value;
        }

        return implode( ', ', $parts );
    }

    /**
     * Get attendde data
     *
     * @return  array
     */
    public function get_data() {
        $event_id = get_post_meta( $this->id, 'etn_event_id', true );
        $event = get_post( intval($event_id) );
        $response_data = [
            'id'         => $this->id,
            'event_name' => $event ? $event->post_title : '',
            'attendee_post_status' => get_post_status( $this->id ),
        ];

        foreach ( $this->data as $key => $value ) {
            $meta_key = $this->meta_prefix . $key;

            $meta_value = get_post_meta( $this->id, $meta_key, true );
            
            $response_data[$key] = $meta_value;
        }

        return $response_data;
    }
	
	
	public function get_attendees_model_by_eventin_order_id( $eventin_order_id ) {
		$args = [
			'post_type'      => 'etn-attendee',
			'post_status'    => 'any',
			'posts_per_page' => -1,
		'meta_query'     => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			[
					'key'     => "eventin_order_id",
					'value'   => $eventin_order_id,
					'compare' => '=',
				]
			]
		];
		
		return  get_posts( $args );
	}
	
	
	/**
	 * @return bool
	 */
	public function trash_post() {
		return !! wp_trash_post( $this->id );
	}
}
