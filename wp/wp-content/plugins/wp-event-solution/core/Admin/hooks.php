<?php

/**
 * Admin Hooks Class
 *
 * @package Eventin
 */
namespace Etn\Core\Admin;

defined( 'ABSPATH' ) || exit;

use Etn\Base\Exporter\Post_Exporter;
use Etn\Base\Importer\Post_Importer;
use Etn\Core\Event\Event_Model;
use Etn\Traits\Singleton;
use Eventin\Attendee\Hooks as AttendeeHooks;
use Eventin\Integrations\Zoom\ZoomCredential;
use Eventin\Template\CPT;
use Eventin\Template\DefaultTemplate;
use Eventin\Upgrade\Upgrade;
use Eventin\Upgrade\Upgraders\V_4_0_8;
use Eventin\Utils\UtilityPackages;
use WP_Error;
use Wpeventin;
use Wpeventin_Pro;

/**
 * Admin Hooks Class
 */
class Hooks {
    use Singleton;

    private $has_pro;

    /**
     * Initialize
     *
     * @return  void
     */
    public function init() {
        $this->handle_buy_pro_module();

        add_filter( 'eventin_settings', [$this, 'add_settings'] );

        add_filter( 'get_edit_post_link', [ $this, 'modifiy_event_edit_link' ], 10, 2 );

        // add_action( 'in_plugin_update_message-' . Wpeventin::plugins_basename(), function( $plugin_data ) {
        //     $this->version_update_warning( Wpeventin::version(), $plugin_data['new_version'] );
        // } );

        add_action( 'admin_init', [ $this, 'do_upgrade' ] );

        add_action( 'admin_init', [$this, 'etn_speaker_group_insert_to_user'] );

        add_filter( 'eventin_settings', [ $this, 'update_extra_field_settings' ] );

        add_action( 'eventin_event_updated', [ $this, 'update_seat_price' ] );

        add_action( 'eventin_event_after_clone', [ $this, 'update_clone_event_sold_tickets' ] );

        add_action( 'init', [$this, 'register_post_type' ] );

        add_action( 'init', [ $this, 'proxy_image' ] );

        add_action( 'after_setup_theme', [ $this, 'etn_activation_redirect' ], 99 );

        add_action( 'template_redirect', [ $this, 'render_template_preview' ] );
        add_action( 'wp_loaded', [ $this, 'render_ics_download' ], 1 );

        new \Etn\Core\Event\Api();


        // Todo: Temporary added this function for version compatibility. Need to remove this after 4.0.21
        add_action( 'admin_notices', [ $this, 'eventin_pro_admin_notice' ] );
        $this->add_eventin_pro_plugin_upgrade_notice();

        // show get-help and upgrade-to-premium menu.
        $this->has_pro = defined( 'ETN_PRO_FILES_LOADED' );
        // $this->handle_get_help_and_upgrade_menu();

        new AttendeeHooks();
    }

    /**
     * Get post ids
     *
     * @param   string  $post_type
     *
     * @return  array
     */
    private function get_post_ids( $post_type ) {
        $args = [
            'post_type'   => $post_type,
            'numberposts' => -1,
            'post_status' => 'publish',
            'fields'      => 'ids',
        ];

        $posts = get_posts( $args );

        return $posts;
    }

    /**
     * Added settings
     *
     * @param   array  $settings
     *
     * @return  array
     */
    public function add_settings( $settings ) {
        $payment_method = etn_get_option( 'payment_method' );
        $sells_engine   = etn_get_option( 'etn_sells_engine_stripe' ) ?: '';
        $payment_method = $payment_method ? $payment_method : $sells_engine; 

        $new_settings = [
            'wc_enabled'         => function_exists( 'WC' ),
            'payment_method'     => $payment_method,
            'plugin_version'     => Wpeventin::version(),  
            'modules'            => get_option( 'etn_addons_options' ),
            'zoom_authorize_url' => ZoomCredential::get_auth_url(),
            'event_url_editable' => etn_event_url_editable(),
            'email'              => etn_get_email_settings(), 
            'etn_settings_country_currency' => etn_currency(),
            'decimal_separator'  => etn_get_decimal_separator(),
            'thousand_separator' => etn_get_thousand_separator(),
            'decimals'           => etn_get_decimals(),
            'price_format'       => etn_get_price_format(),
            'currency_position'  => etn_get_currency_position(),
            'wc_order_status_list' => etn_get_wc_order_status_list(), 
            'wc_order_statuses'    => etn_get_wc_order_statuses(),
            'show_ticket_expiry_date'  => etn_get_option( 'show_ticket_expiry_date', false ),
            "add_to_cart_redirect"     => etn_get_option( 'add_to_cart_redirect','checkout' ),
            "order_thank_you_redirect" => etn_get_option( 'order_thank_you_redirect','woo_thankyou' ),
            'enable_purchase_email'    => etn_get_option( 'enable_purchase_email', 'on' ),
            'ticket_purchase_timer'    => etn_get_option( 'ticket_purchase_timer', 10 ),
            'ticket_purchase_timer_enable'   => etn_get_option( 'ticket_purchase_timer_enable', 'off' ),
        ];

        return array_merge( $settings, $new_settings );
    }

    /**
     * Modify event edit link
     *
     * @param   string  $link
     * @param   integer  $post_id
     *
     * @return  string
     */
    public function modifiy_event_edit_link( $link, $post_id ) {
        $post_type = get_post_type( $post_id );

        if ( 'etn' !== $post_type ) {
            return $link;
        }

        $url = admin_url( "admin.php?page=eventin#/events/edit/{$post_id}/basic" );

        return $url;
    }

    /**
     * Plugin upgrade warning notification
     *
     * @param   string  $current_version  Plugin current version
     * @param   string  $new_version      Plugin new version
     *
     * @return  void
     */
    public function version_update_warning( $current_version, $new_version ) {
        if ( version_compare( $current_version, $new_version, '>=',  ) ) {
            return;
        }

        ?>
            <hr class="e-major-update-warning__separator" />
            <div class="e-major-update-warning">
                <div class="e-major-update-warning__icon">
                    <i class="eicon-info-circle"></i>
                </div>
                <div>
                    <div class="e-major-update-warning__title">
                        <?php echo esc_html__('Heads up! Please backup before upgrading!', 'eventin'); ?>
                    </div>
                    <div class="e-major-update-warning__message">
                        <?php
                        printf(
                            esc_html__( 'Eventin 4.0, the latest version, includes major changes across different areas of the plugin. For a smooth transition, we strongly advise you to backup your site before upgrading and testing it in a staging environment first.', 'eventin' )
                        );
                        ?>
                    </div>
                </div>
            </div>
        <?php
    }

    public function migrate_speaker_organizer() {
        $installed_version = get_option( 'etn_version' );
        $upgrade_versions  = ['4.0.0'];

        if ( $installed_version && version_compare( $installed_version, end( $upgrade_versions ), '<' ) ) {
            return;
        }

        $args = [
            'post_type'      => 'etn',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
        ];
        $events = [];

        $post_query   = new \WP_Query();
        $query_result = $post_query->query( $args );

        foreach ( $query_result as $post ) {
            $event = new Event_Model( $post->ID );

            $this->migrate_event_speaker_organizer( $event );
        }
    }

    /**
     * Migrate event speaker and organizer
     *
     * @param   Event_Model  $event  [$event description]
     *
     * @return  void
     */
    protected function migrate_event_speaker_organizer( $event ) {
        $organizer = get_post_meta( $event->id, 'etn_event_organizer', true );
        $speaker   = get_post_meta( $event->id, 'etn_event_speaker', true );

        

        $speaker_category   = get_term_by( 'slug', 'speaker', 'etn_speaker_category' );
        $organizer_category = get_term_by( 'slug', 'organizer', 'etn_speaker_category' );

        if ( $speaker_category ) {
            $speaker_category = $speaker_category->term_id;
        }

        if ( $organizer_category ) {
            $organizer_category = $organizer_category->term_id;
        }

        if ( $organizer ) {
            $event->update( [
                'etn_event_organizer' => $this->prepare_organizer(),
                'organizer_type'      => 'group',
                'organizer_group'     => [$organizer_category],
            ] );
        }

        if ( $speaker ) {
            $event->update( [
                'etn_event_speaker' => $this->prepare_speaker(),
                'speaker_type'      => 'group',
                'speaker_group'     => [$speaker_category],
            ] );
        }
    }

    /**
     * Get organizer by term slug
     *
     * @return  array
     */
    protected function prepare_organizer() {
        $args = array(
            'numberposts'   => -1,
            'post_type'     => 'etn-speaker',
            'post_status'   => 'any',
            'fields'        => 'ids',
            
            'tax_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
                'relation' => 'AND',
                [
                    'taxonomy' => 'etn_speaker_category',
                    'field'    => 'slug',
                    'terms'    => 'organizer'
                ]
            )
        );

        $organizers = get_posts( $args );

        return $organizers;
    }

    /**
     * Get speaker by term slug
     *
     * @return  array
     */
    protected function prepare_speaker() {
        $args = array(
            'numberposts'   => -1,
            'post_type'     => 'etn-speaker',
            'post_status'   => 'any',
            'fields'        => 'ids',
            
            'tax_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
                'relation' => 'AND',
                [
                    'taxonomy' => 'etn_speaker_category',
                    'field'    => 'slug',
                    'terms'    => 'speaker'
                ]
            )
        );

        $speakers = get_posts( $args );

        return $speakers;
    }

    /**
     * Upgrade the plugin migration
     *
     * @return  void
     */
    public function do_upgrade() {
        $db_migration    = get_option( 'etn_db_migration' );
        $current_version = Wpeventin::version();

        if ( ! $db_migration || version_compare( $current_version, $db_migration, '>' ) ) {
            
            Upgrade::register();
            update_option( 'etn_db_migration', $current_version, true );
        }
    }

    /**
     * Include speaker group to user
     * 
     * @since 4.0.7
     * return void
     */
    public function etn_speaker_group_insert_to_user() {

        // Check if the 'Uncategorized' term exists in the 'etn_speaker_category' taxonomy
        $term = term_exists('Uncategorized', 'etn_speaker_category');

        // If the term doesn't exist, create it
        if ( ! $term ) {
            $term = wp_insert_term( 'Uncategorized', 'etn_speaker_category' );
        }
        // Get the term_id
        $term_id    = is_array( $term ) ? $term['term_id'] : '';
        $args       = array(
            'role__in' => array('etn-speaker', 'etn-organizer'),
            'meta_key' => 'etn_speaker_group', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
            'meta_compare' => 'NOT EXISTS'
        );
    
        $users      = get_users( $args );
    
        if ( $users ) {
            foreach ( $users as $user ) {
                update_user_meta($user->ID, 'etn_speaker_group', $term_id, true);
            }

            // Determine the 'etn_speaker_category' value based on the user's role
            if ( in_array('etn-speaker', $user->roles ) ) {
                $category_value = ['speaker'];
            } elseif ( in_array('etn-organizer', $user->roles) ) {
                $category_value = ['organizer'];
            }

            // Update or add the 'etn_speaker_category' user meta
            update_user_meta($user->ID, 'etn_speaker_category', $category_value, true);
        }
        
    }

    /**
     * Update settins extra fields
     *
     * @param   array  $settings
     *
     * @return  array
     */
    public function update_extra_field_settings( $settings ) {
        $extra_fields = $extra_fields = etn_get_option( 'extra_fields', [] ) ?: etn_get_option( 'attendee_extra_fields', [] );

        $settings['extra_fields'] = $extra_fields;
        unset($settings['attendee_extra_fields']);

        return $settings;
    }
    
    /**
     * Update seat price when update event tickets
     *
     * @param   Event_Model  $event  [$event description]
     *
     * @return  void
     */
    public function update_seat_price( $event ) {
        $event_id          = $event->id;
        $tickets           = $event->etn_ticket_variations;
        $seats             = $event->seat_plan;

        if ( ! $seats ) {
            return;
        }

        foreach ( $seats as $seat_key => $seat ) {
            $ticket_price = $event->get_ticket_price_by_name( $seat['ticketType'] );

            if ( 'table' === $seat['type'] ) {
                $chairs = [];
                foreach( $seat['chairs'] as $chair_key => $chair ) {
                    $chair['price'] = $ticket_price;

                    $chairs[] = $chair;
                }
                $seats[$seat_key]['chairs'] = $chairs;
            } else {
                $seats[$seat_key]['price'] = $ticket_price;
            }
        }
    
        $event->update( [
            'seat_plan' => $seats
        ] );
    }

    /**
     * Update sold tickets on event clone
     *
     * @param   Event_Model  $event  [$event description]
     *
     * @return  void
     */
    public function update_clone_event_sold_tickets( $event ) {
        $tickets = $event->etn_ticket_variations;

        if ( is_array( $tickets ) ) {
            foreach( $tickets as &$ticket ) {
                $ticket['etn_sold_tickets'] = 0;
            }
        }
        
        $event->update([
            'etn_ticket_variations' => $tickets
        ]);
    }
    
    /**
     * Register post type
     *
     * @return  void
     */
    public function register_post_type() {
        $template_post_type = new CPT();

        $template_post_type->register_post_type();
    }

    /**
     * Preapre proxi image for template builder
     *
     * @return  string
     */
    public function proxy_image() {
        $action = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Proxy image endpoint; action param is a hardcoded string comparison, not persisted.

        if ( $action !== 'proxy_image' ) {
            return;
        }

        ob_start();

        if ( isset( $_SERVER['REQUEST_METHOD'] ) && $_SERVER['REQUEST_METHOD'] === 'OPTIONS' ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- REQUEST_METHOD is a trusted server variable, only compared against a literal string.
            http_response_code(200);
            ob_end_flush();
            exit;
        }
	    
        $imageUrl = isset( $_GET['url'] ) ? esc_url_raw( wp_unslash( $_GET['url'] ) ) : null; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Proxy endpoint; URL is immediately validated by is_valid_image_url() below.
	    
        // Validate and sanitize the URL
        if ( ! $this->is_valid_image_url( $imageUrl ) ) {
            http_response_code(404);
            ob_end_flush();
            exit;
        }
        
        if ( $imageUrl ) {
	        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
	        $file_ext = strtolower( pathinfo( $imageUrl, PATHINFO_EXTENSION ) );
	        if ( ! in_array( $file_ext, $allowed_extensions, true ) ) {
		        return http_response_code(404);
	        }
            
            if ( $this->is_same_origin($imageUrl) ) {
                return http_response_code(404);
            }
	        
         
	        $imageContent = file_get_contents($imageUrl);
         
         
	        if (  false !== $imageContent && getimagesize($imageUrl) ) {
		        
                // Check file size limit (5MB)
                if ( strlen( $imageContent ) > 5 * 1024 * 1024 ) {
                    http_response_code(413); // Request Entity Too Large
                    ob_end_flush();
                    exit;
                }
                
                $finfo    = finfo_open( FILEINFO_MIME_TYPE );
                $mimeType = finfo_buffer( $finfo, $imageContent );
                finfo_close( $finfo );
                
                // Validate MIME type
                $allowed_mimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                if ( ! in_array( $mimeType, $allowed_mimes, true ) ) {
                    http_response_code(400);
                    ob_end_flush();
                    exit;
                }
                
                header("Content-Type: $mimeType");

                
                $tempStream = fopen('php://temp', 'r+'); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fopen -- php://temp stream, WP_Filesystem cannot handle in-memory streams.
                fwrite( $tempStream, $imageContent ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fwrite
                rewind( $tempStream );

                fpassthru( $tempStream );
                fclose( $tempStream ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
            } else {
                http_response_code(404);
            }
        } else {
            http_response_code(400);
        }

        ob_end_flush(); // End output buffering
    }

    /**
     * Show buy-pro menu if pro plugin not active
     *
     * @return void
     */
    public function handle_buy_pro_module() {

        /**
         * Show banner (codename: jhanda)
         */
        $filter_string = 'eventin,eventin-free-only';

        // if ( class_exists( 'Wpeventin_Pro' ) ) {

        //     $filter_string .= ',eventin-pro';
        //     $filter_string = str_replace( ',eventin-free-only', '', $filter_string );

        // }
        // \Wpmet\Libs\Banner::instance('eventin')
        //     ->is_test(true)
        //     ->set_filter(ltrim($filter_string, ','))
        //     ->set_api_url('http://xpeed.xyz/public/jhanda')
        //     ->set_plugin_screens('toplevel_page_eventin')

        //      ->call();

        UtilityPackages::instance();
    }

    /**
     * redirect to setup wizard when active pluginn
     *
     */
    public function etn_activation_redirect() {
        if ( ( ! get_option( 'etn_wizard' ) ) ) {
            update_option( 'etn_wizard', 'active' );
            wp_redirect( admin_url( 'admin.php?page=etn-wizard' ) );
            exit;
        }
    }

    /**
     * Render preview template
     *
     * @return  void
     */
    public function render_template_preview() {
        $action      = ! empty( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Template preview action; value is compared to a literal string only.
        $template_id = ! empty( $_GET['template_id'] ) ? absint( $_GET['template_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Template preview; ID is cast to integer before use.

        if ( 'etn-preview-template' !== $action ) {
            return;
        }

        if ( ! current_user_can( 'edit_post', $template_id ) ) {
            wp_safe_redirect( admin_url() );
            exit;
        }

        include_once Wpeventin::core_dir() . 'Template/TemplatePreview.php';

        exit;
    }

    /**
     * Handle ICS calendar file download via template_redirect
     *
     * @return void
     */
    public function render_ics_download() {
        $action = ! empty( $_GET['etn_action'] ) ? sanitize_text_field( wp_unslash( $_GET['etn_action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

        if ( 'download_ics' !== $action ) {
            return;
        }

        $event_id = ! empty( $_GET['event_id'] ) ? absint( $_GET['event_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $event    = $event_id ? get_post( $event_id ) : null;

        // Security: only serve published Eventin event posts.
        if ( ! $event || 'etn' !== $event->post_type || 'publish' !== $event->post_status ) {
            wp_die( esc_html__( 'Event not found.', 'eventin' ), '', [ 'response' => 404 ] );
        }

        if ( $event ) {
            $event_meta    = get_post_meta( $event_id );
            $start_date    = $event_meta['etn_start_date'][0] ?? '';
            $end_date      = $event_meta['etn_end_date'][0] ?? $start_date;
            $start_time    = $event_meta['etn_start_time'][0] ?? '00:00:00';
            $end_time      = $event_meta['etn_end_time'][0] ?? '00:00:00';
            $summary       = $event->post_title;
            $description   = html_entity_decode( wp_strip_all_tags( $event->post_content ), ENT_QUOTES | ENT_HTML5, 'UTF-8' );
            $event_type    = get_post_meta( $event_id, 'event_type', true );
            $location_meta = get_post_meta( $event_id, 'etn_event_location', true );

            if ( 'offline' === $event_type && ! empty( $location_meta['address'] ) ) {
                $location = $location_meta['address'];
            } elseif ( 'hybrid' === $event_type ) {
                $location = 'Hybrid';
            } else {
                $location = 'Online';
            }

            // Resolve the event's own timezone (stored per-event), fall back to WP site timezone.
            $tz_string = get_post_meta( $event_id, 'event_timezone', true );
            $tz_string = $tz_string ? etn_create_date_timezone( $tz_string ) : '';
            try {
                $event_tz = $tz_string ? new \DateTimeZone( $tz_string ) : wp_timezone();
            } catch ( \Exception $e ) {
                $event_tz = wp_timezone();
            }

            // Parse the stored local datetime in the event's timezone, then convert to UTC.
            // Commas are stripped to match the same cleanup the event model performs.
            try {
                $utc     = new \DateTimeZone( 'UTC' );
                $dtstart = ( new \DateTime( str_replace( ',', '', $start_date . ' ' . $start_time ), $event_tz ) )->setTimezone( $utc )->format( 'Ymd\THis\Z' );
                $dtend   = ( new \DateTime( str_replace( ',', '', $end_date . ' ' . $end_time ), $event_tz ) )->setTimezone( $utc )->format( 'Ymd\THis\Z' );
            } catch ( \Exception $e ) {
                $dtstart = '';
                $dtend   = '';
            }
        }

        // Escape text values per RFC 5545 §3.3.11.
        // Replace \r\n, \r, \n atomically (in that order) to avoid double-escaping.
        $esc_ics = function( $text ) {
            $text = str_replace( '\\', '\\\\', $text );
            $text = str_replace( ',', '\\,', $text );
            $text = str_replace( ';', '\\;', $text );
            $text = str_replace( "\r\n", '\\n', $text );
            $text = str_replace( "\r", '\\n', $text );
            $text = str_replace( "\n", '\\n', $text );
            return $text;
        };

        // Fold long content lines per RFC 5545 §3.1 (max 75 octets, CRLF + space continuation).
        // Uses mb_strcut to avoid splitting in the middle of a multi-byte UTF-8 character.
        $fold = function( $line ) {
            $result = '';
            while ( strlen( $line ) > 75 ) {
                $chunk   = mb_strcut( $line, 0, 75, 'UTF-8' );
                $result .= $chunk . "\r\n ";
                $line    = mb_strcut( $line, strlen( $chunk ), null, 'UTF-8' );
            }
            return $result . $line . "\r\n";
        };

        nocache_headers();
        header( 'Content-Type: text/calendar; charset=utf-8' );
        header( 'Content-Disposition: attachment; filename=event.ics' );

        $uid = md5( $event_id . $dtstart . $dtend ) . '@' . wp_parse_url( home_url(), PHP_URL_HOST );

        $ics  = "BEGIN:VCALENDAR\r\n";
        $ics .= "VERSION:2.0\r\n";
        $ics .= "PRODID:-//Eventin//Event Calendar//EN\r\n";
        $ics .= "CALSCALE:GREGORIAN\r\n";
        $ics .= "METHOD:PUBLISH\r\n";
        $ics .= "BEGIN:VEVENT\r\n";
        $ics .= $fold( "UID:"         . $uid );
        $ics .= $fold( "SUMMARY:"     . $esc_ics( $summary ) );
        $ics .= $fold( "DESCRIPTION:" . $esc_ics( $description ) );
        $ics .= $fold( "LOCATION:"    . $esc_ics( $location ) );
        $ics .= "DTSTAMP:"            . gmdate( 'Ymd\THis\Z' ) . "\r\n";
        $ics .= "DTSTART:"            . $dtstart . "\r\n";
        $ics .= "DTEND:"              . $dtend   . "\r\n";
        $ics .= "END:VEVENT\r\n";
        $ics .= "END:VCALENDAR\r\n";

        echo $ics; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        exit;
    }

    /**
     * Add admin notice
     * if eventin is not acitve add notice to active eventin
     * if eventin pro version is 4.0.0 then add notice to required eventin verion 4.0.0
     * @return void
     */
    public function eventin_pro_admin_notice() {

        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $message = '';

        $eventin_pro_required_version = '4.0.17';

        if ( class_exists( 'Wpeventin_Pro' ) && version_compare( Wpeventin_Pro::version(), '4.0.17', '<' ) ) {
            $message = sprintf(
                '<div class="notice notice-warning is-dismissible">
                    <p><strong>%s</strong> %s <a href="%s" target="_blank">%s</a></p>
                </div>',
                __( 'Important Update:', 'eventin' ),
                __( 'A new version of <strong>Eventin Pro</strong> is available with major improvements and new features. Please update to the latest version for the best experience.', 'eventin' ),
                esc_url( 'https://themewinter.com/eventin/pricing' ),
                __( 'Update Now', 'eventin' )
            );
        }

        if ( ! empty( $message ) ) {
            ?>
                <p>
                    <?php echo wp_kses_post( $message ); ?>                
                </p>
            <?php
        }
    }
    
    /**
     * Show plugin update notice for upgrade version
     *
     * @return  void
     */
    public function add_eventin_pro_plugin_upgrade_notice() {

        if ( ! class_exists( 'Wpeventin_Pro' ) ) {
            return;
        }

        require_once Wpeventin_Pro::plugin_dir() . 'utils/updater/edd-warper.php';
        require_once Wpeventin_Pro::core_dir() . 'License/Utils.php';

        $is_valid = \EventinPro\License\Utils::is_valid_license();

        if ( ! $is_valid ) {
            return;
        }

        $required_version = '4.0.17';
        $pro_version      = Wpeventin_Pro::version();

        if ( version_compare( $pro_version, $required_version, '<' ) ) {
            $license_key = \EventinPro\License\Utils::get_license_key();
            
            new \Etn_Pro\Utils\Updater\Edd_Warper(
                'https://themewinter.com',
                'eventin-pro/eventin-pro.php',
                array(
                    'version' => Wpeventin_Pro::version(),
                    'license' => $license_key,
                    'item_id' => '1013',
                    'author'  => 'themewinter',
                    'url'     => site_url(),
                )
            );
        }
    }
	
	
	public function is_same_origin( $url ) {
		return strpos( $url, site_url() ) !== false;
	}
    
    public function proxy_image_allowed_origins(): array {
        return [
                "https://product.themewinter.com"
        ];
    }
	
	private function is_origin_allowed($imageUrl): bool {
        $allowedOrigins = $this->proxy_image_allowed_origins();
        foreach ($allowedOrigins as $allowedOrigin) {
            if (strpos($imageUrl, $allowedOrigin) !== false) {
                return true;
            }
        }
        return false;
	}

    /**
     * Validate if the URL is a safe image URL from allowed origins
     *
     * @param string $imageUrl
     * @return bool
     */
    private function is_valid_image_url($imageUrl): bool {
        if ( empty( $imageUrl ) ) {
            return false;
        }

        // Remove URL fragments before validation

        $cleanUrl = preg_replace('/#.*$/', '', $imageUrl);

        if (strpos($cleanUrl, 'https://product.themewinter.com') !== 0) {
            return false;
        }
        
        // Validate URL format
        if ( ! filter_var( $cleanUrl, FILTER_VALIDATE_URL ) ) {
            return false;
        }

        // Parse URL to get components
        $parsedUrl = wp_parse_url( $cleanUrl );
        if ( ! $parsedUrl || ! isset( $parsedUrl['scheme'] ) || ! isset( $parsedUrl['host'] ) ) {
            return false;
        }

        // Only allow HTTPS
        if ( $parsedUrl['scheme'] !== 'https' ) {
            return false;
        }

        // Check if origin is allowed
        $allowedOrigins = $this->proxy_image_allowed_origins();
        $requestedOrigin = $parsedUrl['scheme'] . '://' . $parsedUrl['host'];
        
        foreach ( $allowedOrigins as $allowedOrigin ) {
            if ( $requestedOrigin === $allowedOrigin ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Show menu for get-help
     * Show menu for upgrade-te-premium if pro version not active
     *
     * @return void
     */
    public function handle_get_help_and_upgrade_menu() {

        /**
         * Show go Premium menu
         */
        \Wpmet\Libs\Pro_Awareness::instance( 'eventin' )
            ->set_parent_menu_slug( 'eventin' )
            ->set_plugin_file( 'wp-event-solution/eventin.php' )
            // ->set_pro_link( $this->has_pro ? '' : 'https://themewinter.com/eventin/' )
            ->set_default_grid_thumbnail( \Wpeventin::plugin_url() . '/utils/pro-awareness/assets/document.png' )
            ->set_default_grid_link( 'https://support.themewinter.com/docs/plugins/docs-category/eventin/' )
            ->set_default_grid_desc( esc_html__( 'Learn More', 'eventin' ) )
            ->set_page_grid(
                array(
                    'url'         => 'https://themewinter.com/support/',
                    'title'       => esc_html__( 'Email Support', 'eventin' ),
                    'thumbnail'   => \Wpeventin::plugin_url() . '/utils/pro-awareness/assets/envelope.png',
                    'description' => esc_html__( 'Learn More', 'eventin' ),
                )
            )
            ->set_page_grid(
                array(
                    'url'         => 'https://themewinter.com/',
                    'title'       => esc_html__( 'Live Chat', 'eventin' ),
                    'thumbnail'   => \Wpeventin::plugin_url() . '/utils/pro-awareness/assets/chat.png',
                    'description' => esc_html__( 'Learn More', 'eventin' ),
                )
            )
            ->set_page_grid(
                array(
                    'url'         => 'https://www.youtube.com/watch?v=FSC-jtN9xgg&list=PLW54c-mt4ObDwu0GWjJIoH0aP1hQHyKj7',
                    'title'       => esc_html__( 'Video Tutorials', 'eventin' ),
                    'thumbnail'   => \Wpeventin::plugin_url() . '/utils/pro-awareness/assets/video.png',
                    'description' => esc_html__( 'Learn More', 'eventin' ),
                )
            )
            ->set_plugin_row_meta( 'Documentation', 'https://support.themewinter.com/docs/plugins/docs-category/eventin/', array( 'target' => '_blank' ) )
            ->set_plugin_row_meta( 'Facebook Community', 'https://www.facebook.com/groups/themewinter', array( 'target' => '_blank' ) )
            ->set_plugin_action_link( 'Settings', admin_url() . 'admin.php?page=eventin#/settings/event-settings/event-details' )
            ->set_plugin_action_link(
                ( $this->has_pro ? '' : 'Go Premium' ),
                'https://themewinter.com/eventin/',
                array(
                    'target' => '_blank',
                    'style'  => 'color: #FCB214; font-weight: bold;',
                )
            )
            ->set_plugin_row_meta( 'Rate the plugin ★★★★★', 'https://wordpress.org/support/plugin/wp-event-solution/reviews/#new-post', array( 'target' => '_blank' ) )
            ->call();
    }
}
