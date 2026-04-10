<?php
namespace Eventin\Enqueue;

/**
 * Scripts and Styles class
 */
class Register {
    /**
     * Initialize
     *
     * @return  void
     */
    public function __construct() {
        if ( is_admin() ) {
            add_action( 'admin_enqueue_scripts', [$this, 'register'], 5 );
            add_action( 'admin_footer', [$this, 'admin_helpscout_beacon'] );
        } 
        add_action( 'wp_enqueue_scripts', [$this, 'register'], 5 );

        add_action( 'wp_head', [ $this, 'register_custom_inline' ] );
    }

    /**
     * Register app scripts and styles
     *
     * @return  void
     */
    public function register() {
        $this->register_global_scripts();
        $this->register_scripts( $this->get_scripts() );
        $this->register_styles( $this->get_styles() );
    }

    /**
     * Register scripts
     *
     * @param  array $scripts
     *
     * @return void
     */
    private function register_scripts( $scripts ) {
        foreach ( $scripts as $handle => $script ) {
            $deps      = isset( $script['deps'] ) ? $script['deps'] : [];
            $in_footer = isset( $script['in_footer'] ) ? $script['in_footer'] : true;
            $version   = isset( $script['version'] ) ? $script['version'] : $this->get_version( $script['src'] );

            $deps = $this->get_dependencies( $script['src'], $deps );

            if ( in_array( 'wp-i18n', $deps ) ) {
                $deps[] = 'eventin-i18n';
            }

            wp_register_script( $handle, $script['src'], $deps, $version, $in_footer );

            // Set localize data.
            $this->set_localize( $handle );
        }
    }

    /**
     * Register global scripts
     *
     * @return  void
     */
    private function register_global_scripts() {
        $scripts = [
            'eventin-i18n' => [
                'src' => \Wpeventin::plugin_url( 'build/js/i18n-loader.js' ),
            ],
        ];

        foreach ( $scripts as $handle => $script ) {
            $deps      = isset( $script['deps'] ) ? $script['deps'] : [];
            $in_footer = isset( $script['in_footer'] ) ? $script['in_footer'] : true;
            $version   = isset( $script['version'] ) ? $script['version'] : $this->get_version( $script['src'] );

            $deps = $this->get_dependencies( $script['src'], $deps );

            wp_register_script( $handle, $script['src'], $deps, $version, $in_footer );
        }

    }

    /**
     * Set localize data
     *
     * @param   string  $handle Script handler name that will be registered
     *
     * @return  void
     */
    public function set_localize( $handle ) {
        $localize_data = etn_get_locale_data();
        wp_localize_script( $handle, 'localized_data_obj', $localize_data );
    }

    /**
     * Register styles
     *
     * @param  array $styles
     *
     * @return void
     */
    public function register_styles( $styles ) {
        foreach ( $styles as $handle => $style ) {
            $deps = isset( $style['deps'] ) ? $style['deps'] : false;

            wp_register_style( $handle, $style['src'], $deps, \Wpeventin::version() );
        }
    }

    /**
     * Get all registered scripts
     *
     * @return array
     */
    public function get_scripts() {
        $prefix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '.min' : '';

        $scripts = array_merge( AdminAssets::get_scripts(), FrontendAssets::get_scripts() );

        return apply_filters( 'etn_register_scripts', $scripts );
    }
    /**
     * Get registered styles
     *
     * @return array
     */
    public function get_styles() {

        $styles = array_merge( AdminAssets::get_styles(), FrontendAssets::get_styles() );

        return apply_filters( 'etn_register_styles', $styles );
    }

    /**
     * Get script and style file dependencies
     *
     * @param   string  $file_name
     * @param   array  $deps
     *
     * @return  array
     */
    private function get_dependencies( $file_name, $deps = [] ) {
        $assets = $this->get_file_assets( $file_name );

        $assets_deps = ! empty( $assets['dependencies'] ) ? $assets['dependencies'] : [];

        $merged_deps = array_merge( $assets_deps, $deps );
        return $merged_deps;
    }

    /**
     * Get script file version
     *
     * @param   string  $file_name
     *
     * @return  string
     */
    private function get_version( $file_name ) {
        $assets      = $this->get_file_assets( $file_name );
        $assets_vers = ! empty( $assets['version'] ) ? $assets['version'] : \Wpeventin::version();
        return $assets_vers;
    }

    /**
     * Get file assets
     *
     * @param   string  $file_name
     *
     * @return  array
     */
    private function get_file_assets( $file_url ) {
        $file   = $this->get_file_path( $file_url );
        $assets = [];

        if ( file_exists( $file ) ) {
            $assets = include $file;
        }

        return $assets;
    }

    /**
     * Get file path from url
     *
     * @param   string  $url
     *
     * @return string
     */
    private function get_file_path( $url ) {
        // Check if the URL is valid
        if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
            return false;
        }

        // Parse the URL
        $url_parts = wp_parse_url( $url );

        // Check if the URL has a path component
        if ( ! isset( $url_parts['path'] ) ) {
            return false; // URL does not contain a path
        }

        $clean_path = str_replace( '.js', '.asset.php', $url_parts['path'] );

        // Get the file path from the URL path
        $file_path = ( isset( $_SERVER['DOCUMENT_ROOT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['DOCUMENT_ROOT'] ) ) : '' ) . $clean_path; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput -- DOCUMENT_ROOT is a trusted server variable used only to build a file path, not output or stored.

        // Check if the file exists
        if ( ! file_exists( $file_path ) ) {
            return false; // File does not exist
        }

        return $file_path;
    }

    /**
     * Register custom inline css
     *
     * @return  void
     */
    public function register_custom_inline() {
        $settings        = etn_get_option();
		$etn_custom_css  = '';
		$primary_color   = '#5D78FF';
		$secondary_color = '';

		// SECURITY: Sanitize color values to prevent XSS
		if ( ! empty( $settings['etn_primary_color'] ) ) {
			$primary_color = sanitize_hex_color( $settings['etn_primary_color'] );
			// Fallback to default if sanitization fails
			if ( empty( $primary_color ) ) {
				$primary_color = '#5D78FF';
			}
		}

		if ( ! empty( $settings['etn_secondary_color'] ) ) {
			$secondary_color = sanitize_hex_color( $settings['etn_secondary_color'] );
			// Fallback to empty if sanitization fails
			if ( empty( $secondary_color ) && ! empty( $settings['etn_secondary_color'] ) ) {
				$secondary_color = '';
			}
		}

		$etn_custom_css .= "
        .etn-event-single-content-wrap .etn-event-meta .etn-event-category span,
        .etn-event-item .etn-event-footer .etn-atend-btn .etn-btn-border,
        .etn-btn.etn-btn-border, .attr-btn-primary.etn-btn-border,
        .etn-attendee-form .etn-btn.etn-btn-border,
        .etn-ticket-widget .etn-btn.etn-btn-border,
        .etn-settings-dashboard .button-primary.etn-btn-border,
        .etn-single-speaker-item .etn-speaker-content a:hover,
        .etn-event-style2 .etn-event-date,
        .etn-event-style3 .etn-event-content .etn-title a:hover,
        .event-tab-wrapper ul li a.etn-tab-a,
        .etn-speaker-item.style-3:hover .etn-speaker-content .etn-title a,
		.etn-variable-ticket-widget .ticket-header,
		.events_calendar_list .calendar-event-details:hover .calendar-event-title,
        .etn-event-item:hover .etn-title a,
		.etn-recurring-widget .etn-date-text,
		
		.etn-event-header ul li i {
            color: {$primary_color};
        }
        .etn-event-item .etn-event-category span,
        .etn-btn, .attr-btn-primary,
        .etn-attendee-form .etn-btn,
        .etn-ticket-widget .etn-btn,
        .schedule-list-1 .schedule-header,
        .speaker-style4 .etn-speaker-content .etn-title a,
        .etn-speaker-details3 .speaker-title-info,
        .etn-event-slider .swiper-pagination-bullet, .etn-speaker-slider .swiper-pagination-bullet,
        .etn-event-slider .swiper-button-next, .etn-event-slider .swiper-button-prev,
        .etn-speaker-slider .swiper-button-next, .etn-speaker-slider .swiper-button-prev,
        .etn-single-speaker-item .etn-speaker-thumb .etn-speakers-social a,
        .etn-event-header .etn-event-countdown-wrap .etn-count-item,
        .schedule-tab-1 .etn-nav li a.etn-active,
        .schedule-list-wrapper .schedule-listing.multi-schedule-list .schedule-slot-time,
        .etn-speaker-item.style-3 .etn-speaker-content .etn-speakers-social a,
        .event-tab-wrapper ul li a.etn-tab-a.etn-active,
        .etn-btn, button.etn-btn.etn-btn-primary,
        .etn-schedule-style-3 ul li:before,
        .etn-zoom-btn,
        .cat-radio-btn-list [type=radio]:checked+label:after,
        .cat-radio-btn-list [type=radio]:not(:checked)+label:after,
        .etn-default-calendar-style .fc-button:hover,
        .etn-default-calendar-style .fc-state-highlight,
		.etn-calender-list a:hover,
        .events_calendar_standard .cat-dropdown-list select,
		.etn-event-banner-wrap,
		.events_calendar_list .calendar-event-details .calendar-event-content .calendar-event-category-wrap .etn-event-category,
		.etn-variable-ticket-widget .etn-add-to-cart-block,
		.etn-recurring-event-wrapper #seeMore,
		.more-event-tag,
        .etn-settings-dashboard .button-primary{
            background-color: {$primary_color};
        }

        .etn-event-item .etn-event-footer .etn-atend-btn .etn-btn-border,
        .etn-btn.etn-btn-border, .attr-btn-primary.etn-btn-border,
        .etn-attendee-form .etn-btn.etn-btn-border,
        .etn-ticket-widget .etn-btn.etn-btn-border,
        .event-tab-wrapper ul li a.etn-tab-a,
        .event-tab-wrapper ul li a.etn-tab-a.etn-active,
        .etn-schedule-style-3 ul li:after,
        .etn-default-calendar-style .fc-ltr .fc-basic-view .fc-day-top.fc-today .fc-day-number,
        .etn-default-calendar-style .fc-button:hover,
		.etn-variable-ticket-widget .etn-variable-total-price,
        .etn-settings-dashboard .button-primary.etn-btn-border{
            border-color: {$primary_color};
        }
        .schedule-tab-wrapper .etn-nav li a.etn-active,
        .etn-speaker-item.style-3 .etn-speaker-content{
            border-bottom-color: {$primary_color};
        }
        .schedule-tab-wrapper .etn-nav li a:after,
        .etn-event-list2 .etn-event-content,
        .schedule-tab-1 .etn-nav li a.etn-active:after{
            border-color: {$primary_color} transparent transparent transparent;
        }

        .etn-default-calendar-style .fc .fc-daygrid-bg-harness:first-of-type:before{
            background-color: {$primary_color}2A;
        }
		 .sidebar .etn-default-calendar-style .fc .fc-daygrid-bg-harness:nth-of-type(1)::before,
		 .left-sidebar .etn-default-calendar-style .fc .fc-daygrid-bg-harness:nth-of-type(1)::before,
		 .right-sidebar .etn-default-calendar-style .fc .fc-daygrid-bg-harness:nth-of-type(1)::before,
		  .widget .etn-default-calendar-style .fc .fc-daygrid-bg-harness:nth-of-type(1)::before,
		   .widgets .etn-default-calendar-style .fc .fc-daygrid-bg-harness:nth-of-type(1)::before,
		   .main-sidebar .etn-default-calendar-style .fc .fc-daygrid-bg-harness:nth-of-type(1)::before,
		    #sidebar .etn-default-calendar-style .fc .fc-daygrid-bg-harness:nth-of-type(1)::before{
				background-color: {$primary_color};
		 }


        .etn-event-item .etn-event-location,
        .etn-event-tag-list a:hover,
        .etn-schedule-wrap .etn-schedule-info .etn-schedule-time{
            color: {$secondary_color};
        }
        .etn-event-tag-list a:hover{
            border-color: {$secondary_color};
        }
        .etn-btn:hover, .attr-btn-primary:hover,
        .etn-attendee-form .etn-btn:hover,
        .etn-ticket-widget .etn-btn:hover,
        .speaker-style4 .etn-speaker-content p,
        .etn-btn, button.etn-btn.etn-btn-primary:hover,
        .etn-zoom-btn,
		.events_calendar_list .calendar-event-details .event-calendar-action .etn-btn, .events_calendar_list .calendar-event-details .event-calendar-action .etn-price.event-calendar-details-btn,
        .etn-speaker-item.style-3 .etn-speaker-content .etn-speakers-social a:hover,
        .etn-single-speaker-item .etn-speaker-thumb .etn-speakers-social a:hover,
		.etn-recurring-event-wrapper #seeMore:hover, .etn-recurring-event-wrapper #seeMore:focus,
        .etn-settings-dashboard .button-primary:hover{
            background-color: {$secondary_color};
        }
		.events_calendar_list .calendar-event-details .event-calendar-action .etn-btn {
			max-width: 120px;
			display: block;
			text-align: center;
			margin-left: auto;
		}";

		// add inline css.
		wp_register_style( 'etn-custom-css', false );
		wp_enqueue_style( 'etn-custom-css' );
		wp_add_inline_style( 'etn-custom-css', $etn_custom_css );
    }

    /**
     * Add HelpScout script to admin footer
     *
     * @return  void
     */
    public function admin_helpscout_beacon() {
        $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
        $is_eventin_screen = false;
 
        if ( $screen ) {
            $screen_id = isset( $screen->id ) ? $screen->id : '';
 
            // Simple check: only load on Eventin top-level admin page
            if ( 'toplevel_page_eventin' === $screen_id ) {
                $is_eventin_screen = true;
            }
        }
 
        // Allow overriding detection via filter.
        $is_eventin_screen = apply_filters( 'etn_is_eventin_admin_screen', $is_eventin_screen, $screen );
 
        if ( ! $is_eventin_screen ) {
            return;
        }
        ?>
        <script type="text/javascript">!function(e,t,n){function a(){var e=t.getElementsByTagName("script")[0],n=t.createElement("script");n.type="text/javascript",n.async=!0,n.src="https://beacon-v2.helpscout.net",e.parentNode.insertBefore(n,e)}if(e.Beacon=n=function(t,n,a){e.Beacon.readyQueue.push({method:t,options:n,data:a})},n.readyQueue=[],"complete"===t.readyState)a();else if(e.attachEvent)e.attachEvent("onload",a);else e.addEventListener("load",a,!1)}(window,document,window.Beacon||function(){});</script>
        <script type="text/javascript">
        window.Beacon('config', {
            color: "#6B2EE5",
        });
        window.Beacon('init', 'e0cc7920-ce99-4a2a-9475-8b3ae3be7448');
        window.Beacon('on', 'ready', function(){
            window.Beacon('hide');
        });

        </script>
        <?php
    }

}