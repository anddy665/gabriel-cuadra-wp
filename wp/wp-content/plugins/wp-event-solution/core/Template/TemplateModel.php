<?php

/**
 * Template Model class
 * 
 * @package Eventin
 */
namespace Eventin\Template;

defined( 'ABSPATH' ) || exit;

use Etn\Base\Post_Model;
use Etn\Core\Attendee\Attendee_Model;
use Etn\Core\Event\Event_Model;

/**
 * Template Model
 */
class TemplateModel extends Post_Model {
    /**
     * Store post type
     *
     * @var string
     */
    protected $post_type = 'etn-template';

    /**
     * Template properties
     *
     * @var array
     */
    protected $data = [
        'type'          => '',
        'orientation'   => '',
        'is_clone'      => '',
        'is_pro'        => '',
        'is_custom'     => '',
        'thumbnail'     => '',
        'template_css'  => '',
        'template_builder' => '',
        'preview_event_id' => '',
        'width'        => '',
        'height'       => '',
    ];

    /**
     * Constructor for the TemplateModel class
     *
     * @param   mixed  $template
     *
     * @return  void
     */
    public function __construct( $template = 0) {
        parent::__construct( $template );
    }

    /**
     * Get template name
     *
     * @return  string  Return template title if post exist otherwise null
     */
    public function get_name() {
        $post = get_post( $this->id );

        if ( ! $post ) {
            return null;
        }

        return $post->post_title;
    }

    public function get_width() {
        return $this->width;
    }

    public function get_height() {
        return $this->height;
    }

    /**
     * Get template content
     *
     * @return  string  Return template content if post exist otherwise null
     */
    public function get_content() {
        $post = get_post( $this->id );

        if ( ! $post ) {
            return null;
        }

        return $post->post_content;
    }

    /**
     * Get template status
     *
     * @return  string  Return template content if post exist otherwise null
     */
    public function get_status() {
        $post = get_post( $this->id );

        if ( ! $post ) {
            return null;
        }

        return $post->post_status;
    }

    /**
     * Get template orientation
     *
     * @return  string  Return orientation Landscape or Protrait
     */
    public function get_orientation() {
        return $this->orientation;
    }

    /**
     * Get template type
     *
     * @return  string  Return template template type will be ticket, certificate, event
     */
    public function get_type() {
        return $this->type;
    }

    /**
     * Get the template builder used to make this template
     * 
     * @return string The id of the template builder. Can be 'elementor', 'gutenberg'
     */
    public function get_template_builder() {
        return $this->template_builder;
    }

    /**
     * Get placeholders
     *
     * @return  array
     */
    public function get_place_holder( $attendee_id ) {
        $attendee = new Attendee_Model( $attendee_id );
        $event    = new Event_Model( $attendee->etn_event_id );

        $date_format = get_option( 'date_format' );
		$time_format = get_option( 'time_format' );

		$wp_timezone = wp_timezone();

		$start_date_time = $event->etn_start_date . ' ' . $event->etn_start_time;
		$end_date_time   = $event->etn_end_date . ' ' . $event->etn_end_time;

		$start_dt = new \DateTime( $start_date_time, $wp_timezone );
		$end_dt   = new \DateTime( $end_date_time, $wp_timezone );

		$start_date = wp_date( $date_format, $start_dt->getTimestamp() );
		$end_date   = wp_date( $date_format, $end_dt->getTimestamp() );
		$start_time = wp_date( $time_format, $start_dt->getTimestamp() );
		$end_time   = wp_date( $time_format, $end_dt->getTimestamp() );

        $placeholders = [
            '{{event_title}}'         => esc_html( $event->get_title() ),
            '{{event_location}}'      => esc_html( $event->get_address() ),
            '{{event_start_date}}'    => esc_html( $start_date ),
            '{{event_end_date}}'      => esc_html( $end_date ),
            '{{event_start_time}}'    => esc_html( $start_time ),
            '{{event_end_time}}'      => esc_html( $end_time ),
            '{{event_timezone}}'      => esc_html( $event->event_timezone ),
            '{{ticket_price}}'        => esc_html( $attendee->etn_ticket_price ),
            '{{ticket_type}}'         => esc_html( $attendee->ticket_name ),
            '{{attendee_seat}}'       => esc_html( $attendee->attendee_seat ),
            '{{payment_status}}'      => esc_html( $attendee->etn_status ),
            '{{ticket_id}}'           => esc_html( $attendee->etn_unique_ticket_id ),
            '{{attendee_name}}'       => esc_html( $attendee->etn_name ),
            '{{attendee_email}}'      => esc_html( $attendee->etn_email ),
            '{{attendee_phone}}'      => esc_html( $attendee->etn_phone ),
            '{{extra_fields}}'        => wp_kses_post( $attendee->get_extra_fields_content() ),
            '{{extra_fields_inline}}' => esc_html( $attendee->get_extra_fields_inline() ),
            // '{{qr_code}}',
        ];

        // Dynamic per-field tokens: {{extra_field_KEY}}
        foreach ( $attendee->get_extra_fields() as $key => $value ) {
            $placeholders[ '{{extra_field_' . $key . '}}' ] = esc_html( is_array( $value ) ? implode( ', ', $value ) : $value );
        }

        return $placeholders;
    }

    /**
     * Get event placeholders for event pages (without attendee data)
     *
     * @since 1.0.0
     * @param   int  $event_id  Event post ID
     *
     * @return  array
     */
    public function get_event_placeholders( $event_id ) {
        if ( ! $event_id ) {
            return [];
        }

        $event = new Event_Model( $event_id );

        $date_format = get_option( 'date_format' );
		$time_format = get_option( 'time_format' );

		$wp_timezone = wp_timezone();

		$start_date_time = $event->etn_start_date . ' ' . $event->etn_start_time;
		$end_date_time   = $event->etn_end_date . ' ' . $event->etn_end_time;

		$start_dt = new \DateTime( $start_date_time, $wp_timezone );
		$end_dt   = new \DateTime( $end_date_time, $wp_timezone );

		$start_date = wp_date( $date_format, $start_dt->getTimestamp() );
		$end_date   = wp_date( $date_format, $end_dt->getTimestamp() );
		$start_time = wp_date( $time_format, $start_dt->getTimestamp() );
		$end_time   = wp_date( $time_format, $end_dt->getTimestamp() );

        return [
            '{{event_title}}'       => esc_html( $event->get_title() ),
            '{{event_location}}'    => esc_html( $event->get_address() ),
            '{{event_start_date}}'  => esc_html( $start_date ),
            '{{event_end_date}}'    => esc_html( $end_date ),
            '{{event_start_time}}'  => esc_html( $start_time ),
            '{{event_end_time}}'    => esc_html( $end_time ),
            '{{event_timezone}}'    => esc_html( $event->event_timezone ),
        ];
    }

    /**
     * Get html content
     *
     * @return  string
     */
    public function get_html_content() {
        $content = $this->add_proxy_image( $this->get_content() );
        $content = do_blocks( $content ); // Process Gutenberg blocks
        $content = do_shortcode( $content ); // Process shortcodes

        return $content;
    }

    /**
     * Get fully rendered content, handling both Elementor and Gutenberg.
     *
     * @return string
     */
    private function get_rendered_content() {
        if ( did_action( 'elementor/loaded' ) ) {
            $document = \Elementor\Plugin::$instance->documents->get( $this->id );

            if ( $document && $document->is_built_with_elementor() ) {
                $content = \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $this->id, true );
                return $this->add_proxy_image( $content );
            }
        }

        return $this->get_html_content();
    }

    /**
     * Returns the event id used for previewing the template
     * 
     * @return int|string returns event id or emptry string
     */
    public function get_preview_event_id() {
        $event_id = $this->preview_event_id;
        if ( ! isset( $event_id ) ) {
            $event_id = $this->get_available_event_id();
        }
        
        $post = get_post( $event_id );
        
        if ( $post && 'etn' === $post->post_type ) {
            return $post->ID;
        }
 
        return '';
    }
    /**
     * Retrieves the first availble ( event with status publish) events id
     * 
     * @return int $event_id
     */
    private function get_available_event_id() {
        $args = array(
            'post_type'      => 'etn',
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'fields'         => 'ids',
            'orderby'        => 'ID',
            'order'          => 'ASC',
        );
    
        $query = new \WP_Query( $args );
    
        if ( $query->have_posts() ) {
            return (int) $query->posts[0];
        }
    
        return 0;
    }

    /**
     * Render html with actual value
     *
     * @param   array  $data
     *
     * @return
     */
    public function get_rendable_content( $attendee_id ) {
        if ( ! $attendee_id ) {
            return null;
        }

        $placeholder = $this->get_place_holder( $attendee_id );

        $content = strtr( $this->get_rendered_content(), $placeholder );

        // Remove any extra_field tokens that had no matching attendee data.
        $content = preg_replace( '/\{\{extra_field_[^}]+\}\}/', '', $content );

        return $content;
    }

    /**
     * Render html with actual event values (without attendee data)
     *
     * @since 1.0.0
     * @param   int  $event_id  Event post ID
     *
     * @return  string|null
     */
    public function get_rendable_event_content( $event_id ) {
        if ( ! $event_id ) {
            return null;
        }

        $placeholder = $this->get_event_placeholders( $event_id );

        return strtr( $this->get_rendered_content(), $placeholder );
    }

    /**
     * Render html with actual value
     *
     * @param   array  $data  
     *
     * @return  
     */
    public function get_default_rendable_content( $attendee_id, $template_name ) {
        if ( ! $attendee_id ) {
            return null;
        }


        $placeholder = $this->get_place_holder( $attendee_id );

        $template = DefaultTemplate::get_template( $template_name );

        $content = strtr( $template['content'], $placeholder );

        // Remove any extra_field tokens that had no matching attendee data.
        $content = preg_replace( '/\{\{extra_field_[^}]+\}\}/', '', $content );

        return $content;
    }

    /**
     * Render block content
     *
     * @since 1.0.0
     * @param   string  $template_name  Template name for default templates
     * @param   int     $event_id       Event ID for placeholder replacement
     *
     * @return  void
     */
    public function render_content( $template_name = '', $event_id = 0 ) {
        if ( ! empty( $template_name ) ) {
            $template = DefaultTemplate::get_template( $template_name );

            if ( ! empty( $template['content'] ) ) {
                $content = $template['content'];

                // Replace placeholders if event ID is provided
                if ( $event_id ) {
                    $placeholder = $this->get_event_placeholders( $event_id );
                    $content = strtr( $content, $placeholder );
                }

                echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Full HTML template; placeholders are escaped in get_place_holder()
            }

            return;
        }

        if ( $event_id ) {
            echo $this->get_rendable_event_content( $event_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Full HTML template; placeholders are escaped in get_event_placeholders()
        } else {
            echo $this->get_rendered_content(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Full HTML template rendered via Elementor or WP blocks
        }
    }


    /**
     * Render demo content
     *
     * @return  void
     */
    public function get_demo_content() {
        $placeholder = $this->get_demo_placeholder();
        $content     = strtr( $this->get_rendered_content(), $placeholder );

        // Dynamically replace any remaining {{extra_field_KEY}} tokens with a
        // human-readable demo label derived from the key (e.g. "gender_1" → "Gender").
        $content = preg_replace_callback(
            '/\{\{extra_field_([^}]+)\}\}/',
            function ( $matches ) {
                $label = ucwords( str_replace( '_', ' ', preg_replace( '/_\d+$/', '', $matches[1] ) ) );
                return $label;
            },
            $content
        );

        return $content;
    }

    /**
     * Get demo placeholder
     *
     * @return  array
     */
    public function get_demo_placeholder() {
        $date_format = get_option( 'date_format' );
		$time_format = get_option( 'time_format' );

        return [
            '{{event_title}}'       => 'Event Title',
            '{{event_location}}'    => 'Springfield, IL 62701, United States',
            '{{event_start_date}}'  => gmdate( $date_format ),
            '{{event_end_date}}'    => gmdate( $date_format ),
            '{{event_start_time}}'  => gmdate( $time_format ),
            '{{event_end_time}}'    => gmdate( $time_format ),
            '{{event_timezone}}'    => 'America/New_York',
            '{{ticket_price}}'      => 500,
            '{{ticket_type}}'       => 'VIP',
            '{{payment_status}}'    => 'success',
            '{{ticket_id}}'         => '#nr5s1v40d4',
            '{{attendee_name}}'     => 'John Doe',
            '{{attendee_seat}}'     => 'Vip-01',
            '{{attendee_email}}'    => 'john@gmail.com',
            '{{attendee_phone}}'    => '995571089087',
            '{{extra_fields}}'        => '<strong>Company:</strong> Acme Corp<br><strong>Job Title:</strong> Developer<br><strong>Phone:</strong> +1 555 1234',
            '{{extra_fields_inline}}' => 'Company: Acme Corp, Job Title: Developer',
            '{{extra_field_etn_phone}}' => '995571089087',
            // '{{qr_code}}',
        ];
    }

    /**
     * Replace image and backgroud url for remote server
     *
     * @param   string  $content
     *
     * @return  string
     */
    public function add_proxy_image( $content ) {
        $proxy_url_base = site_url() . '?action=proxy_image&url=';

        // Replace all image url.
        preg_match_all('/<img[^>]+src="([^">]+)"/i', $content, $matches);

        if ( ! empty( $matches[1] ) ) {
            foreach ( $matches[1] as $image_url ) {
                if ( ! $this->is_same_origin( $image_url ) ) {
                    $proxied_image_url = $proxy_url_base . urlencode($image_url);
                    $content = str_replace( $image_url, $proxied_image_url, $content );
                }
            }
        }

        // Replace all background image url
        preg_match_all( '/background-image\s*:\s*url\(["\']?([^"\')]+)["\']?\)/i', $content, $background_matches );

        if ( ! empty( $background_matches[1] ) ) {
            foreach ( $background_matches[1] as $bg_image_url ) {
        
                if ( ! $this->is_same_origin( $bg_image_url ) ) {
                    $proxied_bg_image_url = $proxy_url_base . urlencode( $bg_image_url );
                    $content = str_replace( $bg_image_url, $proxied_bg_image_url, $content );
                }
            }
        }

        return $content;
    }

    /**
     * Site url and requested url are same  or not
     *
     * @param   string  $url
     *
     * @return  bool
     */
    public function is_same_origin( $url ) {
        return strpos( $url, site_url() ) !== false;
    }
}
