<?php

/**
 * Event Model Class
 *
 * @package Eventin
 */
namespace Etn\Core\Event;

defined( 'ABSPATH' ) || exit;

use Etn\Base\Post_Model;
use Eventin\Input;
use Etn\Core\Attendee\Attendee_Model;
use Etn\Core\Speaker\User_Model;

/**
 * Event Model
 */
class Event_Model extends Post_Model {
    /**
     * Store post type
     *
     * @var string
     */
    protected $post_type = 'etn';

    /**
     * Store event data
     *
     * @var array
     */
    protected $data = [
        'etn_select_speaker_schedule_type'  => '',
        'etn_event_organizer'               => '',
        'etn_event_speaker'                 => '',
        'event_timezone'                    => '',
        'etn_start_date'                    => '',
        'etn_end_date'                      => '',
        'etn_start_time'                    => '',
        'etn_end_time'                      => '',
        'etn_ticket_availability'           => '',
        'etn_total_sold_tickets'            => '',
        'etn_ticket_variations'             => '',
        'etn_registration_deadline'         => '',
        'etn_zoom_id'                       => '',
        'etn_zoom_event'                    => '',
        'etn_total_avaiilable_tickets'      => '',
        'etn_google_meet'                   => '',
        'etn_google_meet_short_description' => '',
        'fluent_crm'                        => '',
        'etn_event_location_type'           => '',
        'etn_event_location'                => '',
        'etn_event_socials'                 => [],
        'etn_event_schedule'                => [],
        'etn_event_faq'                     => [],
        'recurring_enabled'                 => '',
        'etn_event_recurrence'              => [],
        'etn_google_meet'                   => '',
        'rsvp_settings'                     => '',
        'seat_plan'                         => '',
        'seat_plan_settings'                => '',
        'certificate_template'              => '',
        'ticket_template'                   => '',
        'external_link'                     => '',
        'speaker_type'                      => '',
        'organizer_type'                    => '',
        'speaker_group'                     => '',
        'organizer_group'                   => '',
        'etn_event_logo'                    => '',
        'etn_event_calendar_bg'             => '',
        'etn_event_calendar_text_color'     => '',
        'fluent_crm_webhook'                => '',
        'attende_page_link'                 => '',
        'event_banner'                      => '',
        'event_layout'                      => '',
        'attendee_extra_fields'             => '',
        'event_type'                        => '',
        'location'                          => '',
        'meeting_link'                      => '',
        'is_clone'                          => false,
        'certificate_preference'            => '',
        '_virtual'                           => '',
        'event_logo_id'                      => '',
        'event_banner_id'                   => '',
        'excerpt_enable'                    => false,
        'enable_seatmap'                    => false,
        'enable_legacy_certificate_template' => false,
        '_tax_status'                        => 'taxable',
        'pending_seats'                     => [],
        'etn_last_update_date'              => ''
    ];

    /**
     * Get total tickets
     *
     * @return  integer
     */
    public function get_total_ticket() {
        $ticket_variations = $this->etn_ticket_variations;
        $total_ticket      = 0;

        if ( is_array( $ticket_variations ) ) {
            foreach ( $ticket_variations as $ticket ) {
                $is_unlimited = ! empty( $ticket['etn_unlimited_tickets'] ) || ( isset( $ticket['etn_avaiilable_tickets'] ) && intval( $ticket['etn_avaiilable_tickets'] ) === -1 );
                if ( $is_unlimited ) {
                    return -1;
                }
                if ( ! empty( $ticket['etn_avaiilable_tickets'] ) ) {
                    $total_ticket += $ticket['etn_avaiilable_tickets'];
                }
            }
        }

        return $total_ticket;
    }

    /**
     * Get event status
     *
     * @return  string
     */
    public function get_status()
    {
        $start_date = $this->etn_start_date;
        $start_time = $this->etn_start_time;
        $end_date   = $this->etn_end_date;
        $end_time   = $this->etn_end_time;
        $status     = get_post_status($this->id);
        $timezone   = $this->event_timezone ? etn_create_date_timezone($this->event_timezone) : 'Asia/Dhaka';

        $start_date_time = str_replace(',', '', $start_date . ' ' . $start_time);
        $end_date_time   = str_replace(',', '', $end_date . ' ' . $end_time);

        if (str_contains(strtolower($start_date_time), 'invalid') || str_contains(strtolower($end_date_time), 'invalid')) {
            return $status;
        }

        try {
            $tz         = new \DateTimeZone($timezone);
            $start_date = new \DateTime($start_date_time, $tz);
            $end_date   = new \DateTime($end_date_time, $tz);

            // Create a DateTime object for the current date and time in the given timezone
            $current_date = new \DateTime('now', new \DateTimeZone($timezone));

            if ('publish' === $status) {
                if ($current_date > $end_date) {
                    $status = __('Expired', 'eventin');
                    return [
                        'key'=>"expired",
                        'value'=> $status
                    ];
                } elseif ($current_date >= $start_date && $current_date <= $end_date) {
                    $status = __('Ongoing', 'eventin');
                    return [
                        'key'=>"ongoing",
                        'value'=> $status
                    ];
                } else {
                    $status = __('Upcoming', 'eventin');
                    return [
                        'key'=>"upcoming",
                        'value'=> $status
                    ];
                }
            }

            return $status;
        } catch (\Exception $e) {
            return $status;
        }
    }

    /**
     * Get tickets
     *
     * @param   string  $slug
     *
     * @return  array
     */
    public function get_ticket( $slug = '' ) {
        $ticket_variations = $this->etn_ticket_variations;

        if ( ! $ticket_variations ) {
            return;
        }

        if ( ! $slug ) {
            return $ticket_variations;
        }
        
        foreach( $ticket_variations as $variation ) {
            if ( $slug === $variation['etn_ticket_slug'] ) {
                return $variation;
            }
        }
    }

    /**
     * Get event title
     *
     * @return  string
     */
    public function get_title() {
        $post = get_post( $this->id );

        return $post->post_title;
    }

    /**
     * Get event description
     *
     * @return  string
     */
    public function get_description() {
        $post = get_post( $this->id );

        return $post->post_content;
    }

    /**
     * Get event social media
     *
     * @return  array
     */
    public function get_social() {
        return $this->etn_event_socials;
    }

    /**
     * Get event location address
     *
     * @return  string
     */
    public function get_address() {
        $address = '';

        if ( $this->event_type === 'offline' || $this->event_type === 'hybrid' ) {
            $location = $this->etn_event_location;

            $address = ! empty( $location['address'] ) ? $location['address'] : '';
        }

        return $address;
    }

    /**
     * Get event start date time
     *
     * @param   string  $format  [$format description]
     *
     * @return  string Event start date time
     */
    public function get_start_datetime( $format = 'Y-m-d h:i a' ) {
        $datetime = $this->get_datetime( $this->etn_start_date, $this->etn_start_time );

        return $datetime->format( $format );
    }

    /**
     * Get end date time
     *
     * @param   string  $format  [$format description]
     *
     * @return  string           [return description]
     */
    public function get_end_datetime($format = 'Y-m-d h:i a') {
        $datetime = $this->get_datetime( $this->etn_end_date, $this->etn_end_time );
        
        return $datetime->format( $format );
    }

    /**
     * Get event timezone
     *
     * @return  string
     */
    public function get_timezone() {
        $timezone   = $this->event_timezone ? etn_create_date_timezone( $this->event_timezone ) : 'Asia/Dhaka';

        return $timezone;
    }

    /**
     * Get date time object
     *
     * @param   string  $date
     * @param   string  $time  [$time description]
     *
     * @return  Datetime
     */
    private function get_datetime($date, $time) {
        $date_time_string = $date . ' ' . $time;

        $datetime = new \DateTime( $date_time_string, new \DateTimeZone( $this->get_timezone() ) );

        return $datetime;
    }

    /**
     * Check an event is expired or not
     *
     * @return  bool
     */
    public function is_expaired() {
        return time() > strtotime( $this->get_end_datetime() );
    }

    /**
     * Get total sold tickets
     *
     * @return  integer  Total number of sold tickets
     */
    public function get_total_sold_ticket() {
        $ticket_variations = $this->etn_ticket_variations;
        $total_ticket      = 0;

        if ( is_array( $ticket_variations ) ) {
            foreach ( $ticket_variations as $ticket ) {
                if ( ! empty( $ticket['etn_sold_tickets'] ) ) {
                    $total_ticket += $ticket['etn_sold_tickets'];
                }
            }
        }

        return $total_ticket;
    }

    /**
     * Get ticket price by ticket name
     *
     * @param   string  $ticket_name  [$ticket_name description]
     *
     * @return  int | float
     */
    public function get_ticket_price_by_name( $ticket_name ) {
        $tickets = $this->etn_ticket_variations;

        if ( is_array( $tickets ) ) {
            foreach( $tickets as $ticket ) {
                $input = new Input( $ticket );

                if ( $input->get( 'etn_ticket_name' ) === $ticket_name ) {
                    return $input->get( 'etn_ticket_price' );
                }
            }
        }

        return 0;
    }

    /**
     * Get ticket price by ticket name
     *
     * @param   string  $ticket_name  [$ticket_name description]
     *
     * @return  int | float
     */
    public function get_ticket_slug_by_name( $ticket_name ) {
        $tickets = $this->etn_ticket_variations;

        if ( is_array( $tickets ) ) {
            foreach( $tickets as $ticket ) {
                $input = new Input( $ticket );

                if ( $input->get( 'etn_ticket_name' ) === $ticket_name ) {
                    return $input->get( 'etn_ticket_slug' );
                }
            }
        }

        return '';
    }

    /**
     * Check seatp is enable or not
     *
     * @return  bool
     */
    public function is_enable_seatmap() {
        $seat_map_switcher = ! metadata_exists( 'post', $this->id, 'enable_seatmap' ) && $this->seat_plan ? true : $this->enable_seatmap;

        return $seat_map_switcher;
    }

    /**
     * Check event already has meeting link or not
     *
     * @return  bool  Return true if an event has a meeting link otherwise false
     */
    public function has_meeting_link() {
        return $this->meeting_link ? true : false;
    }

    /**
     * Get meeting platform name
     *
     * @return  string  Meeting platform name: zoom, google-meet, custom-url
     */
    public function get_meeting_platform() {
        $location = $this->etn_event_location;

        return ! empty( $location['integration'] ) ? $location['integration'] : '';
    }

    /**
     * Get evend ids by author
     *
     * @param   integer  $author_id  [$author_id description]
     *
     * @return  array
     */
    public function get_ids_by_author( $author_id ) {
        $event_ids = get_posts( [
            'post_type'      => 'etn',
            'author'         => $author_id,
            'fields'         => 'ids',
            'posts_per_page' => -1, // Fetch all events by this author
        ] );
    
        return $event_ids;
    }

    /**
     * Get all attenddes for an order
     *
     * @return  array Attendee data
     */
    public function get_attendees($status = ['publish', 'trash'], $limit = -1) {
        $attendee_obect = new Attendee_Model();

        $attendees = $attendee_obect->get_attendees_by( 'etn_event_id', $this->id, $status, $limit );

        return $attendees;
    }

    /**
     * Get related events based on etn_tags.
     *
     * @param int $limit Number of related events to fetch.
     * @return array List of related Event_Model objects.
     */
    public function get_related_events( $limit = 5 ) {
        $tags = get_the_terms( $this->id, 'etn_tags' );

        if ( empty( $tags ) || is_wp_error( $tags ) ) {
            return [];
        }

        $tag_ids = wp_list_pluck( $tags, 'term_id' );

        $args = [
            'post_type'      => $this->post_type,
            'post_status'    => 'any',
            'posts_per_page' => $limit,
            'post__not_in'   => [ $this->id ], // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_post__not_in
            'tax_query'      => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
                [
                    'taxonomy' => 'etn_tags',
                    'field'    => 'term_id',
                    'terms'    => $tag_ids,
                ]
            ],
        ];

        $posts = get_posts( $args );

        if ( ! $posts ) {
            return [];
        }

        $related_events = [];

        foreach ( $posts as $post ) {
            $related_events[] = new self( $post->ID );
        }

        return $related_events;
    }

    /**
     * Get event speakers based on the event's speaker IDs
     *
     * @return array
     */
    public function get_speakers() {
        $speakers = [];

        // Get speaker IDs associated with the event
        $speaker_ids = $this->etn_event_speaker;

        if ( ! $speaker_ids ) {
            return [];
        }

        // Loop through speaker IDs and create User_Model objects for each
        foreach ( $speaker_ids as $speaker_id ) {
            $speakers[] = new User_Model( $speaker_id );
        }

        return $speakers;
    }

    /**
     * Get event organizers based on the event's organizer IDs
     *
     * @return array
     */
    public function get_organizers() {
        $organizers = [];

        // Get organizer IDs associated with the event
        $organizer_ids = $this->etn_event_organizer;

        if ( ! $organizer_ids ) {
            return [];
        }

        // Loop through organizer IDs and create User_Model objects for each
        foreach ( $organizer_ids as $organizer_id ) {
            $organizers[] = new User_Model( $organizer_id );
        }

        return $organizers;
    }

    /**
     * Get start date
     *
     * @param   string  $format  Date format that will be need to display
     *
     * @return  string
     */
    public function get_start_date( $format = 'Y-m-d') {
        return $this->get_start_datetime( $format );
    }

    /**
     * Get start time
     *
     * @param   string  $format  Time format that will be need to display
     *
     * @return  string
     */
    public function get_start_time( $format = 'h:i a') {
        return $this->get_start_datetime( $format );
    }

    /**
     * Get end date
     *
     * @param   string  $format  Date format that will be need to display
     *
     * @return  string
     */
    public function get_end_date( $format = 'Y-m-d') {
        return $this->get_end_datetime( $format );
    }

    /**
     * Get end time
     *
     * @param   string  $format  Time format that will be need to display
     *
     * @return  string
     */
    public function get_end_time( $format = 'h:i a') {
        return $this->get_end_datetime( $format );
    }
    
    /**
     * Get event tags
     *
     * @return  array  All tags for this event
     */
    public function get_tags() {
        $tags = get_the_terms( $this->id, 'etn_tags' );

        if ( ! $tags ) {
            return [];
        }

        return $tags;
    }

    /**
     * Get event tags
     *
     * @return  array  All tags for this event
     */
    public function get_categories() {
        $tags = get_the_terms( $this->id, 'etn_category' );

        if ( ! $tags ) {
            return [];
        }

        return $tags;
    }
}
