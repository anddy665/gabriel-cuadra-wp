<?php

use Eventin\Settings;
use Eventin\Validation\Validator;
use Etn\Core\Event\Event_Model;
use Etn\Utils\Helper;
use SureCart\Support\Currency;

if ( ! function_exists( 'etn_safe_decode' ) ) {
    /**
     * Safely unserialize data, preventing PHP Object Injection.
     *
     * Uses allowed_classes => false to block instantiation of
     * PHP objects during unserialization.
     *
     * @since 4.1.4
     * @param mixed $data The data to unserialize.
     * @return mixed The unserialized data (no objects), or the original data if not serialized.
     */
    function etn_safe_decode( $data ) {
        if ( ! is_string( $data ) ) {
            return $data;
        }

        if ( ! is_serialized( $data ) ) {
            return $data;
        }

        return @unserialize( $data, [ 'allowed_classes' => false ] );
    }
}

if ( ! function_exists( 'etn_sanitize_array_input' ) ) {
    /**
     * Sanitize input to prevent PHP Object Injection.
     *
     * This function rejects serialized strings that could contain malicious PHP objects.
     * Arrays and non-serialized strings are passed through for further processing.
     *
     * @since 4.1.2
     * @param mixed $input The input to sanitize.
     * @return array|string Returns array if input is array, string if input is non-serialized string, empty array otherwise.
     */
    function etn_sanitize_array_input( $input ) {
        // If it's already an array, return it
        if ( is_array( $input ) ) {
            return $input;
        }
        // If it's a serialized string, reject it to prevent PHP Object Injection
        if ( is_string( $input ) && is_serialized( $input ) ) {
            return [];
        }
        // Return non-serialized strings as-is (safe for further processing like CSV parsing)
        if ( is_string( $input ) ) {
            return $input;
        }
        // Return empty array for other non-array input types
        return [];
    }
}

if ( ! function_exists( 'etn_array_csv_column' ) ) {
    /**
     * Convert array to CSV column
     *
     * @param array $data
     *
     * @return string
     */
    function etn_array_csv_column( $data = [] ) {
        $result_string = '';

        foreach ( $data as $data_key => $value ) {
            if ( ! is_array( $value ) ) {
                return etn_is_associative_array( $data ) ? etn_single_array_csv_column( $data ) : implode( ',', $data );
            }

            if ( etn_is_associative_array( $value ) ) {
                $valueString = etn_single_array_csv_column( $value );
                $result_string .= rtrim( $valueString, ', ' ) . '|';
            } else {
                $result_string .= implode( ',', $value ) . '|';
            }
        }

        // Remove the trailing '|'
        $result_string = rtrim( $result_string, '|' );

        return $result_string;
    }
}

if ( ! function_exists( 'etn_is_associative_array' ) ) {
    /**
     * Check an associative array or not
     *
     * @param array $array
     *
     * @return bool
     */
    function etn_is_associative_array( $array ) {
        return is_array( $array ) && count( array_filter( array_keys( $array ), 'is_string' ) ) > 0;
    }
}

if ( ! function_exists( 'etn_single_array_csv_column' ) ) {
    /**
     * Convert single array to csv column
     *
     * @param array $data
     *
     * @return string
     */
    function etn_single_array_csv_column( $data ) {
        if ( ! is_array( $data ) ) {
            return false;
        }

        $result_string = '';

        foreach ( $data as $key => $value ) {
            if ( is_array( $value ) ) {
                $result_string .= implode( ',', $value );
            } else {
                $result_string .= "$key:$value,";
            }
        }

        return rtrim( $result_string, ',' );
    }
}

if ( ! function_exists( 'etn_csv_column_array' ) ) {
    /**
     * Convert CSV column to array
     *
     * @param string $csvColumn
     *
     * @return array|bool
     */
    function etn_csv_column_array( $csv_column, $separator = '|' ) {
        // Explode the CSV column by '|' to get individual array elements
        if ( strpos( $csv_column, $separator ) !== false ) {
            return etn_csv_column_multi_dimension_array( $csv_column );
        }

        return etn_csv_column_single_array( $csv_column );
    }
}

if ( ! function_exists( 'etn_csv_column_multi_dimension_array' ) ) {
    /**
     * Convert CSV column to multi dimensional array
     *
     * @param   string  $csv_column
     * @param   string  $separator
     *
     * @return  array
     */
    function etn_csv_column_multi_dimension_array( $csv_column, $separator = '|' ) {
        $array_strings = explode( $separator, $csv_column );
        $result_array  = [];

        foreach ( $array_strings as $array_string ) {
            // Add the temporary array to the result array
            $result_array[] = etn_csv_column_single_array( $array_string );
        }

        return $result_array;
    }
}

if ( ! function_exists( 'etn_csv_column_single_array' ) ) {
    /**
     * Convert CSV column to multi dimensional array
     *
     * @param   string  $csv_column
     * @param   string  $separator
     *
     * @return  array
     */
    function etn_csv_column_single_array( $csv_column, $separator = ',' ) {
        $temp_array = [];

        if ( false !== strpos( $csv_column, ':' ) ) {
            $csv_column = explode( $separator, $csv_column );

            foreach ( $csv_column as $pair ) {
                // Explode key-value pairs by ':' and populate the temporary array
                list( $key, $value ) = explode( ':', $pair );
                $temp_array[$key]  = $value;
            }

            return $temp_array;
        }

        return explode( $separator, $csv_column );
    }
}

if ( ! function_exists( 'etn_is_request' ) ) {
    /**
     * What type of request is this?
     *
     * @param  string $type admin, ajax, cron or frontend.
     *
     * @return bool
     */
    function etn_is_request( $type ) {
        switch ( $type ) {
        case 'admin':
            return is_admin();

        case 'ajax':
            return defined( 'DOING_AJAX' );

        case 'rest':
            return defined( 'REST_REQUEST' );

        case 'cron':
            return defined( 'DOING_CRON' );

        case 'frontend':
            return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
        }
    }
}

if ( ! function_exists( 'etn_get_locale_data' ) ) {
    /**
     * Get locale data
     *
     * @return  array
     */
    function etn_get_locale_data() {
        $localize_vars   = include Wpeventin::plugin_dir() . 'utils/locale/vars.php';
        $localize_static = include Wpeventin::plugin_dir() . 'utils/locale/static.php';

        $data = array_merge( $localize_static, $localize_vars );

        return apply_filters( 'etn_locale_data', $data );
    }
}

if ( ! function_exists( 'etn_permision_error' ) ) {
    /**
     * Rest api error message
     *
     * @param   string  $message
     *
     * @return  \WP_REST_Response
     */
    function etn_permision_error( $message = '' ) {
        if ( ! $message ) {
            $message = __( 'Sorry, you are not allowed to do that.', 'eventin' );
        }

        $data = [
            'code'    => 'rest_forbidden',
            'message' => 'Sorry, you are not allowed to do that.',
            'data'    => [
                'status' => 403,
            ],
        ];

        return new WP_REST_Response( $data, 401 );
    }
}

if ( ! function_exists( 'etn_parse_block_content' ) ) {
    /**
     * Parses dynamic blocks out of `post_content` and re-renders them.
     *
     * @param   string  $content
     *
     * @return  string
     */
    function etn_parse_block_content( $content ) {
        return do_blocks( $content );
    }
}

if ( ! function_exists( 'etn_validate' ) ) {
    /**
     * Validate user input
     *
     * @param   array  $request
     * @param   array  $rules
     *
     * @return  bool | WP_Error
     */
    function etn_validate( $request, $rules ) {
        $validator = new Validator( $request );

        $validator->set_rules( $rules );

        if ( ! $validator->validate() ) {
            return $validator->get_error();
        }

        return true;
    }
}

if ( ! function_exists( 'etn_get_option' ) ) {
    /**
     * Get option for eventin
     *
     * @since 1.0.0
     * @return  mixed
     */
    function etn_get_option( $key = '', $default = false ) {
        $value = Settings::get( $key );

        if ( ! $value ) {
            return $default;
        }

        return $value;
    }
}

if ( ! function_exists( 'etn_update_option' ) ) {

    /**
     * Update option
     *
     * @param   string  $key
     *
     * @since 1.0.0
     *
     * @return  boolean
     */
    function etn_update_option( $key = '', $value = false ) {
        if ( ! $key ) {
            return false;
        }

        return Settings::update( [
            $key => $value,
        ] );
    }
}

if ( ! function_exists( 'etn_is_ticket_sale_end' ) ) {
    /**
     * Check an event has attendees or not
     *
     * @param   string  $end_date_time  Event ticket sale end date and time
     * @param   string  $timezone       Event timezone
     *
     * @return  bool
     */
    function etn_is_ticket_sale_end( $end_date_time, $timezone = 'Asia/Dhaka' ) {
        // Create a DateTime object for the end date and time in the given timezone
        $event_end_dt = new DateTime( $end_date_time, new DateTimeZone( $timezone ) );
    
        // Create a DateTime object for the current date and time in the given timezone
        $current_dt = new DateTime( 'now', new DateTimeZone( $timezone ) );
    
        // Compare the dates
        if ( $current_dt > $event_end_dt ) {
            return true;
        }

        return false;
    }
}

if ( ! function_exists( 'etn_is_ticket_sale_start' ) ) {
    /**
     * Check an event has attendees or not
     *
     * @param   string  $start_date_time  Event ticket sale start date and time
     * @param   string  $timezone         Event timezone
     *
     * @return  bool
     */
    function etn_is_ticket_sale_start( $start_date_time, $timezone = 'Asia/Dhaka' ) {
        // Create a DateTime object for the start date and time in the given timezone
        $event_date = new DateTime( $start_date_time, new DateTimeZone( $timezone ) );
    
        // Create a DateTime object for the current date and time in the given timezone
        $current_datte = new DateTime('now', new DateTimeZone( $timezone ) );
    
        // Compare the dates
        if ( $current_datte < $event_date ) {
            return false;
        }

        return true;
    }
}


if ( ! function_exists( 'etn_create_date_timezone' ) ) {
    /**
     * Create datetimezone object
     *
     * @param   string  $timezoneString  Timezone
     *
     * @return  string
     */
    function etn_create_date_timezone( $timezoneString ) {
         // List of valid named timezones
        $validTimezones = DateTimeZone::listIdentifiers();

        // Check if the provided timezone is a valid named timezone
        if ( in_array( $timezoneString, $validTimezones ) ) {
            return $timezoneString;
        }

        // Check if the provided timezone is an offset timezone like UTC+6 or UTC-4.5
        if ( preg_match('/^UTC([+-]\d{1,2})(?:\.(\d))?$/i', $timezoneString, $matches ) ) {
            // Convert the matched offset to a format recognized by DateTimeZone
            $hours = intval( $matches[1] );
            $minutes = isset( $matches[2] ) ? intval($matches[2]) * 6 : 0; // 0.1 fractional part means 6 minutes

            // Ensure the format is like +06:30 or -04:30
            $formattedOffset = sprintf( '%+03d:%02d', $hours, $minutes );
            return $formattedOffset;
        }

        // If the timezone string doesn't match any known format, return default
        return 'America/New_York';
    }
}

if ( ! function_exists( 'etn_convert_to_date' ) ) {
    /**
     * Convert to date from date time string
     *
     * @param   string  $datetimeString  Datetime string
     *
     * @return  string  Date string
     */
    function etn_convert_to_date( $datetimeString ) {
        try {
            // Create a DateTime object using the provided datetime string
            $datetime = new DateTime( $datetimeString );
            
            // Return the formatted date in 'Y-m-d' format
            return $datetime->format( 'Y-m-d' );
        } catch ( Exception $e ) {
            return 'Error: ' . $e->getMessage();
        }
    }
}

if ( ! function_exists( 'etn_get_currency' ) ) {
    /**
     * Get currency list
     *
     * @return  array
     */
    function etn_get_currency () {
        $currencies = require Wpeventin::plugin_dir() . '/utils/currency.php';

        return $currencies;
    }
}

if ( ! function_exists( 'etn_get_currency_by' ) ) {
    /**
     * Get currency by name, symbol
     *
     * @return  string
     */
    function etn_get_currency_by_name( $name ) {
        $currencies = etn_get_currency();

        foreach( $currencies as $currencie ) {
            if ( $currencie['name'] === $name ) {
                return $currencie;
            }
        }

        return null;
    }
}

if ( ! function_exists( 'etn_get_currency_symbol' ) ) {
    /**
     * Get currency by name, symbol
     *
     * @return  string
     */
    function etn_get_currency_symbol( $name ) {
        $currency = etn_get_currency_by_name( $name );

        if ( $currency ) {
            return $currency['symbol'];
        }
    }
}

if ( ! function_exists( 'etn_event_url_editable' ) ) {
    /**
     * Check editable url
     *
     * @return  bool
     */
    function etn_event_url_editable () {
        $permalink_structure = get_option('permalink_structure');

        if ( strpos( $permalink_structure, '%postname%' ) !== false) {
            return true;
        }

        return false;
    }
}

if ( ! function_exists( 'etn_get_timezone' ) ) {
    /**
     * Get valid timezonelists
     *
     * @return  array Timezone lists
     */
    function etn_get_timezone() {
        $validTimezones = DateTimeZone::listIdentifiers();

        return $validTimezones;
    }
}

if ( ! function_exists( 'etn_get_wp_timezones' ) ) {
    /**
     * Get valid wp timezone
     *
     * @return  string
     */
    function etn_get_wp_timezones() {
        $timezones = [];

        // Generate UTC offsets
        $offset_range = range( -12, 14 );
        foreach ( $offset_range as $offset ) {
            // Whole hour
            $hours   = ( $offset > 0 ) ? '+' . $offset : (string) $offset;

            if ( $offset != 0 ) {
                $timezones[] = 'UTC' . $hours;
            }
            else{
                $timezones[] = 'UTC' . '+'.$hours;
            }

            // Half-hour offsets
            if ( $offset != 0 ) {
                $timezones[] = 'UTC' . $hours . ':30';
            }

            if ( $offset == 0 ) {
                $timezones[] = 'UTC' . '+'.$hours . ':30';
                $timezones[] = 'UTC' . '-'.$hours . ':30';
            }

            // Special quarter-hour offsets
            if ( in_array( $offset, [ 5, 8, 12, 13 ] ) ) {
                $timezones[] = 'UTC+' . $offset . ':45';
            }
        }

        // Now add all region identifiers
        $timezones = array_merge( $timezones, DateTimeZone::listIdentifiers() );

        return $timezones;
    }
}

if ( ! function_exists( 'etn_prepare_address' ) ) {
    /**
     * Prepare event address from event location
     *
     * This function is written for temporary solution. We have a nested location issue @since 4.0.0. To resolve this we impletent a temporary function. We have to remove this function when v4.0 is completely statble from location issue. Before remove this function make sure remove from all of the place where we used this.
     *
     * @param   array  $location
     *
     * @return  string
     */
    function etn_prepare_address( $location ) {
        static $depth = 0;
    
        if ( $depth >= 10 ) {
            return '';
        }
    
        $depth++;
    
        if ( ! is_array( $location ) ) {
            return $location;
        }
    
        $address = ! empty( $location['address'] ) ? $location['address'] : '';
    
        return etn_prepare_address( $address );
    }
}

if ( ! function_exists( 'etn_get_email_settings' ) ) {

    /**
     * Get email settings
     *
     * @param   string  $email  Email name for the email setting
     *
     * @return  array
     */
    function etn_get_email_settings( $email = '' ) {

        $email_settings = etn_get_option( 'email' );

        $defaults = etn_get_default_email_settings();

        $email_settings = etn_recursive_wp_parse_args( $email_settings, $defaults );

        if ( ! $email ) {
            return $email_settings;
        }

        return $email_settings[$email];
    }
}

if ( ! function_exists( 'etn_get_default_email_settings' )  ) {
    /**
     * Get default email settings for the email template
     *
     * @return  array
     */
    function etn_get_default_email_settings() {

        $email_settings = [
            'purchase_email' => [
                'from'    => get_option( 'admin_email' ),
                'subject' => sprintf( __( 'Event Ticket', 'eventin' ) ),
                'body'    => __( 'You have purchased ticket(s). Attendee ticket details are as follows.', 'eventin' ),
                'send_to_admin' => true,
                'send_email_to_attendees' => true
            ],
            'certificate_email' => [
                'from'    => get_option( 'admin_email' ),
                'subject' => sprintf( __( 'Event Certificate', 'eventin' ) ),
                // translators: %1$s is the event title
                'body'    => sprintf( __( '<p>Congratulations for successfully attending/completing the event \'%1$s\'. Your certificate is ready! Click on the link provided below to get the PDF certificate. </p>', 'eventin' ), '<span>{%event_title%}</span>' ), // phpcs:ignore WordPress.WP.I18n.NoHtmlWrappedStrings
                'send_to_admin' => true,
            ],
            'rsv_email' => [
                'from'          => get_option( 'admin_email' ),
                'response_type' => 'going',
                'subject'       => sprintf( __( 'RSVP request', 'eventin' ) ),
                'body'          => sprintf( __( 'We received your RSVP request', 'eventin' ) ),
                'send_to_admin' => true,
            ],
            'reminder_email' => [
                'from'    => get_option( 'admin_email' ),
                'subject' => sprintf( __( 'Reminder email', 'eventin' ) ),
                'body'    => __( 'Just sending you a quick reminder about our retailer meet-up you\'ve registered to attend in two days time. If you\'ve misplaced the Invitation that contained all the details. don\'t worry. Cve added them rn below for you.', 'eventin' ),
                'send_to_admin' => true,
            ]
        ];

        return apply_filters( 'etn_default_email_settings', $email_settings );
    }
}

if ( ! function_exists( 'etn_recursive_wp_parse_args' ) ) {
    /**
     * Perse args recursively
     *
     * @param   array  $args      [$args description]
     * @param   array  $defaults  [$defaults description]
     *
     * @return  array             [return description]
     */
    function etn_recursive_wp_parse_args( $args, $defaults ) {
        $args = (array) $args; // Ensure args is an array
    
        // Loop through each default value and apply wp_parse_args recursively if it's an array
        foreach ( $defaults as $key => $value ) {
            if ( is_array( $value ) ) {
                // If the key is an array, call recursively
                $args[$key] = etn_recursive_wp_parse_args( isset( $args[$key] ) ? $args[$key] : [], $value );
            } else {
                // Otherwise, use wp_parse_args for the non-array values
                if ( ! isset( $args[$key] ) ) {
                    $args[$key] = $value;
                }
            }
        }
    
        return $args;
    }
}

if ( !function_exists( 'etn_editor_settings' ) ) {
    /**
     * Retrieves the settings for the Gutenberg editor.
     *
     * This function retrieves the settings for the Gutenberg editor, including the allowed block types,
     * typography, color palette, and other experimental features. It also applies filters to allow
     * customization of the settings.
     *
     * @return array The settings for the Gutenberg editor.
     */
    function etn_editor_settings() {
        
        
        $coreSettings            = get_block_editor_settings( [], 'post' );
        $wordpressCoreTypography = $coreSettings['__experimentalFeatures']['typography'];
        $coreExperimentalSpacing = $coreSettings['__experimentalFeatures']['spacing'];
        

        $themePref     = getThemePrefScheme();

        $settings = array(
            'gradients'                         => [],
            'alignWide'                         => false,
            'allowedMimeTypes'                  => get_allowed_mime_types(),
            '__experimentalBlockPatterns'       => [],
            '__experimentalFeatures'            => [
                'appearanceTools' => true,
                'border'          => [
                    'color'  => false,
                    'radius' => true,
                    'style'  => false,
                    'width'  => false,
                ],
                'color'           => [
                    'background'       => true,
                    'customDuotone'    => false,
                    'defaultGradients' => false,
                    'defaultPalette'   => false,
                    'duotone'          => [],
                    'gradients'        => [],
                    'link'             => false,
                    'palette'          => [
                        'theme' => $themePref['colors'],
                    ],
                    'text'             => true,
                ],
                'spacing'         => $coreExperimentalSpacing,
                'typography'      => $wordpressCoreTypography,
                'blocks'          => [
                    'core/button' => [
                        'border'     => [
                            'radius' => true,
                            "style"  => true,
                            "width"  => true,
                        ],
                        'typography' => [
                            'fontSizes' => [],
                        ],
                        'spacing'    => $coreExperimentalSpacing,
                    ],

                ],
            ],
            '__experimentalSetIsInserterOpened' => false,
            'disableCustomColors'               => get_theme_support( 'disable-custom-colors' ),
            'disableCustomFontSizes'            => false,
            'disableCustomGradients'            => true,
            'enableCustomLineHeight'            => get_theme_support( 'custom-line-height' ),
            'enableCustomSpacing'               => get_theme_support( 'custom-spacing' ),
            'enableCustomUnits'                 => false,
            'keepCaretInsideBlock'              => false,
            'mediaLibrary'                      =>  ['type' => true, 'date' => true, 'allowedTypes' => ['image', 'video', 'audio', 'application']],
            'mediaUpload'                       => true,
        );

        $color_palette = current( (array) get_theme_support( 'editor-color-palette' ) );
        if ( false !== $color_palette ) {
            $settings['colors'] = $color_palette;
        } else {
            $settings['colors'] = [];
        }

        return $settings;
    }
}

if ( !function_exists( 'getThemePrefScheme' ) ) {
    /**
     * Retrieves the theme preference scheme.
     *
     * This function retrieves the theme preference scheme, which includes the color palette and font sizes.
     * The color palette is an array of objects, each representing a color with its name, slug, and hex code.
     * The font sizes is an array of objects, each representing a font size with its name, short name, size, and slug.
     * The function applies filters to allow customization of the theme preference scheme.
     *
     * @return array The theme preference scheme.
     */
    function getThemePrefScheme() {
        static $pref;
        if ( !$pref ) {

            $color_palette = [
                [
                    "name"  => __( "Black", "eventin" ),
                    "slug"  => "black",
                    "color" => "#000000",
                ],
                [
                    "name"  => __( "Cyan bluish gray", "eventin" ),
                    "slug"  => "cyan-bluish-gray",
                    "color" => "#abb8c3",
                ],
                [
                    "name"  => __( "White", "eventin" ),
                    "slug"  => "white",
                    "color" => "#ffffff",
                ],
                [
                    "name"  => __( "Pale pink", "eventin" ),
                    "slug"  => "pale-pink",
                    "color" => "#f78da7",
                ],
                [
                    "name"  => __( "Luminous vivid orange", "eventin" ),
                    "slug"  => "luminous-vivid-orange",
                    "color" => "#ff6900",
                ],
                [
                    "name"  => __( "Luminous vivid amber", "eventin" ),
                    "slug"  => "luminous-vivid-amber",
                    "color" => "#fcb900",
                ],
                [
                    "name"  => __( "Light green cyan", "eventin" ),
                    "slug"  => "light-green-cyan",
                    "color" => "#7bdcb5",
                ],
                [
                    "name"  => __( "Vivid green cyan", "eventin" ),
                    "slug"  => "vivid-green-cyan",
                    "color" => "#00d084",
                ],
                [
                    "name"  => __( "Pale cyan blue", "eventin" ),
                    "slug"  => "pale-cyan-blue",
                    "color" => "#8ed1fc",
                ],
                [
                    "name"  => __( "Vivid cyan blue", "eventin" ),
                    "slug"  => "vivid-cyan-blue",
                    "color" => "#0693e3",
                ],
                [
                    "name"  => __( "Vivid purple", "eventin" ),
                    "slug"  => "vivid-purple",
                    "color" => "#9b51e0",
                ],
            ];

            $font_sizes = [
                [
                    'name'      => __( 'Small', 'eventin' ),
                    'shortName' => 'S',
                    'size'      => 14,
                    'slug'      => 'small',
                ],
                [
                    'name'      => __( 'Medium', 'eventin' ),
                    'shortName' => 'M',
                    'size'      => 18,
                    'slug'      => 'medium',
                ],
                [
                    'name'      => __( 'Large', 'eventin' ),
                    'shortName' => 'L',
                    'size'      => 24,
                    'slug'      => 'large',
                ],
                [
                    'name'      => __( 'Larger', 'eventin' ),
                    'shortName' => 'XL',
                    'size'      => 32,
                    'slug'      => 'larger',
                ],
            ];

            $pref = apply_filters( 'eventin/theme_pref', [
                'colors'     => (array) $color_palette,
                'font_sizes' => (array) $font_sizes,
            ] );
        }

        return $pref;

    }
}

if ( ! function_exists( 'etn_currency' ) ) {
    /**
     * Get currecny
     *
     * @return  string
     */
    function etn_currency() {
        $payment_method = etn_get_option( 'payment_method' );

        $is_enabled_wc = 'woocommerce' === $payment_method;

        $is_enabled_sc = etn_get_option('surecart_status');

        $is_enabled_fc = etn_get_option('fluentcart_status');

        if ( $is_enabled_sc && class_exists(SureCart::class) ) {
            return \SureCart::account()->currency;
        }

        if ( $is_enabled_fc && defined('FLUENTCART_VERSION') ) {
            return (new \FluentCart\Api\StoreSettings())->getCurrency();
        }

        if ( function_exists('WC') &&  $is_enabled_wc ) {
            return get_woocommerce_currency();
        }

        $currency = etn_get_option( 'etn_settings_country_currency', 'USD' );

        return $currency;
    }
}

if ( ! function_exists( 'etn_currency_symbol' ) ) {
    /**
     * Get currency symbol
     *
     * @return  string
     */
    function etn_currency_symbol() {
        $currency = etn_currency();

        $is_enabled_sc = etn_get_option('surecart_status');

        $is_enabled_fc = etn_get_option('fluentcart_status');

        if ( $is_enabled_sc && class_exists(SureCart::class) ) {
            return  html_entity_decode( Currency::getCurrencySymbol( \SureCart::account()->currency ) );
        }

        if ( $is_enabled_fc && defined('FLUENTCART_VERSION') ) {
            return (new \FluentCart\Api\StoreSettings())->getCurrencySymbol();
        }

        return etn_get_currency_symbol( $currency );
    }
}

if ( ! function_exists( 'etn_is_enable_wc' ) ) {
    /**
     * Check event in is used woocommerce payment method
     *
     * @return  bool
     */
    function etn_is_enable_wc() {
        $payment_method = etn_get_option( 'payment_method' );

        return function_exists( 'WC' ) && 'woocommerce' === $payment_method;
    }
}

if ( ! function_exists( 'etn_get_thousand_separator' ) ) {

    /**
     * Thousand separator
     *
     * @return  string
     */
    function etn_get_thousand_separator() {
        if ( etn_is_enable_wc() ) {
            return wc_get_price_thousand_separator();
        }

        $thousand_separator = etn_get_option( 'thousand_separator', ',' );

        return apply_filters( 'etn_thousand_separator', $thousand_separator );
    }
}

if ( ! function_exists( 'etn_get_decimal_separator' ) ) {

    /**
     * Get descimal separator
     *
     * @return  string
     */
    function etn_get_decimal_separator() {
        if ( etn_is_enable_wc() ) {
            return wc_get_price_decimal_separator();
        }

        $decimal_separator = etn_get_option( 'decimal_separator', 'comma_dot' );

        return apply_filters( 'etn_decimal_separator', $decimal_separator );
    }
}

if ( ! function_exists( 'etn_get_decimals' ) ) {

    /**
     * Get number of decimals
     *
     * @return  string
     */
    function etn_get_decimals() {
        if ( etn_is_enable_wc() ) {
            return wc_get_price_decimals();
        }

        $decimals = etn_get_option( 'decimals', 0 );

        return apply_filters( 'etn_decimals', $decimals );
    }
}

if ( ! function_exists( 'etn_get_price_format' ) ) {

    /**
     * Get price format
     *
     * @return  string
     */
    function etn_get_price_format() {
        if ( etn_is_enable_wc() ) {
            return get_woocommerce_price_format();
        }

        $currency_pos = get_option( 'currency_position' );
        $format       = '%1$s%2$s';

        switch ( $currency_pos ) {
            case 'left':
                $format = '%1$s%2$s';
                break;
            case 'right':
                $format = '%2$s%1$s';
                break;
            case 'left_space':
                $format = '%1$s&nbsp;%2$s';
                break;
            case 'right_space':
                $format = '%2$s&nbsp;%1$s';
                break;
        }

        return apply_filters( 'etn_price_format', $format, $currency_pos );
    }
}

if ( ! function_exists( 'etn_get_currency_position' ) ) {

    /**
     * Get price format
     *
     * @return  string
     */
    function etn_get_currency_position() {
        if ( etn_is_enable_wc() ) {
            $currency_pos = get_option( 'woocommerce_currency_pos' );

            return $currency_pos;
        }

        $currency_pos = etn_get_option( 'currency_position', 'left' );

        return apply_filters( 'etn_currency_position', $currency_pos );
    }
}

if ( ! function_exists( 'etn_get_wc_order_status_list' ) ) {
    /**
     * Get all woocommerce order statuses without prefix
     *
     * @return  array  Woocommerce order statuses
     */
    function etn_get_wc_order_status_list() {
        $statuses_without_prefix = [];
    
        if ( ! function_exists( 'WC' ) ) {
            return $statuses_without_prefix;
        }
    
        $statuses = wc_get_order_statuses();
        
    
        foreach ( $statuses as $key => $label ) {
            // Remove the 'wc-' prefix from each status key.
            $new_key = str_replace( 'wc-', '', $key );
            $statuses_without_prefix[$new_key] = $label;
        }
    
        return $statuses_without_prefix;
    }
}

if ( ! function_exists( 'etn_get_wc_order_statuses' ) ) {
    /**
     * Get all woocommerce order statuses stored as eventin settings
     *
     * @return  array  Woocommerce order statuses
     */
    function etn_get_wc_order_statuses() {
        $defaults = [ 'completed', 'processing' ];
        $settings = etn_get_option( 'wc_order_statuses', $defaults );

        return $settings;
    }
}

if ( ! function_exists( 'etn_is_enable_wc_synchronize_order' ) ) {
    /**
     * Check wc order synchronizes orders to the posts table enable or not
     *
     * @return  bool Retur true if synchronizes orders is enable otherwise false
     */
    function etn_is_enable_wc_synchronize_order() {

        if ( false === get_option( 'woocommerce_custom_orders_table_enabled' ) ) {
            return true;
        }

        $enable_custom_order_table = get_option( 'woocommerce_custom_orders_table_enabled', true );

        if ( 'no' === $enable_custom_order_table ) {
            return true;
        }

        $data_sync_enabled          = get_option( 'woocommerce_custom_orders_table_data_sync_enabled', true );

        if ( 'yes' === $data_sync_enabled ) {
            return true;
        }

        return false;
    }
}

if ( ! function_exists( 'etn_gutenberg_template_path' ) ) {
    /**
     * Gutenberg block paths
     *
     * @return  string
     */
    function etn_block_path() {
        return Wpeventin::core_dir() . 'Blocks/';
    }
}

if ( ! function_exists( 'etn_upload_image_from_url' ) ) {
    /**
     * Upload image from url
     *
     * @param   string  $image_url  Public Image url
     *
     * @return  integer Image attatchment id
     */
    function etn_upload_image_from_url( $image_url ) {
        // Check if the URL is valid
        if ( empty( $image_url ) || ! filter_var( $image_url, FILTER_VALIDATE_URL ) ) {
            return new WP_Error('invalid_url', __('Invalid image URL.', 'eventin'));
        }
    
        // Get the file name from the URL
        $file_name = basename( wp_parse_url( $image_url, PHP_URL_PATH ) );
    
        // Download the image
        $image_data = wp_remote_get( $image_url, [
            'timeout' => 30, // Increase timeout to 30 seconds
            'blocking' => true,
        ] );
        if ( is_wp_error( $image_data ) ) {
            return new WP_Error( 'download_failed', __( 'Failed to download image.', 'eventin') );
        }
    
        $image_body = wp_remote_retrieve_body( $image_data );
        if ( empty( $image_body ) ) {
            return new WP_Error( 'empty_image', __( 'Downloaded image is empty.', 'eventin' ) );
        }
    
        // Get the upload directory
        $upload_dir = wp_upload_dir();
        $file_path = $upload_dir['path'] . '/' . $file_name;
    
        // Save the image file
        if ( ! file_put_contents( $file_path, $image_body ) ) {
            return new WP_Error( 'file_write_error', __('Failed to write image file.', 'eventin' ) );
        }
    
        // Prepare file data for WordPress upload
        $file_info = [
            'name'     => $file_name,
            'type'     => mime_content_type( $file_path ),
            'tmp_name' => $file_path,
            'error'    => 0,
            'size'     => filesize( $file_path ),
        ];
    
        // Include WordPress file handling functions
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
    
        // Upload image to WordPress media library
        $attachment_id = media_handle_sideload($file_info, 0);
    
        // Check for errors
        if ( is_wp_error( $attachment_id ) ) {
            wp_delete_file($file_path); // Delete the temporary file if upload fails
            return $attachment_id;
        }
    
        // Delete the temporary file after successful upload
        wp_delete_file($file_path);
    
        return $attachment_id; // Return the attachment ID
    }
}

if ( ! function_exists( 'etn_date_format' ) ) {
    /**
     * Get date formate from wordpress
     *
     * @return  string
     */
    function etn_date_format() {
        $date_format = get_option( 'date_format' );

        return $date_format;
    }
}

if ( ! function_exists( 'etn_time_format' ) ) {
    /**
     * Get time formate from wordpress
     *
     * @return  string
     */
    function etn_time_format() {
        $time_format = get_option( 'time_format' );

        return $time_format;
    }
}

if ( ! function_exists( 'etn_validate_event_tickets' ) ) {
    /**
     * Validate event tickets that verify to prevent overselling
     *
     * @param   integer  $event_id       Event id that will be purchased
     * @param   array  $order_tickets    Order ticket list
     *
     * @return  bool | WP_Error
     */
    function etn_validate_event_tickets( $event_id, $order_tickets,$is_for_update = false ) {
        $event         = new Event_Model( $event_id );
        $sold_tickets    = (array)Helper::etn_get_sold_tickets_by_event( $event_id );

        foreach( $order_tickets as $ticket ) {
            $event_ticket = $event->get_ticket( $ticket['ticket_slug'] );

            $available = $event_ticket['etn_avaiilable_tickets']??0;
            $sold      = $sold_tickets[$ticket['ticket_slug']]??0;
            $pending   = $event_ticket['pending']??0;

			// check if `etn_avaiilable_tickets` exists. if not means unlimited ticket
			if ( !isset($available) || !is_numeric($available) ) {
				return true;
			}
            if($is_for_update){
                $ticket_left = intval($available) - intval($sold) - intval($pending) + intval($ticket['ticket_quantity']);
            }else{
                $ticket_left = intval($available) - intval($sold) - intval($pending);
            }

            $available = $event_ticket['etn_avaiilable_tickets']??0;
            if ($available > 0 && $ticket['ticket_quantity'] > $ticket_left ) {
                return new WP_Error( 'ticket_limit', __( 'The ticket limit has been exceeded', 'eventin' ), ['status' => 422] );
            }
        }
        
        return true;
    }
}

if ( ! function_exists( 'etn_validate_seat_ids' ) ) {
    /**
     * Validate seat ids
     *
     * @param   array  $seat_ids  Seat ids
     *
     * @return  bool | WP_Error
     */
    function etn_validate_seat_ids( $event_id, $seat_ids ) {
        $booked_seats = etn_safe_decode( get_post_meta( $event_id, '_etn_seat_unique_id', true ));
        $already_booked_seats = $booked_seats ? explode(',', $booked_seats) : [];
        $pending_seats = etn_safe_decode( get_post_meta( $event_id, 'pending_seats', true ));
        if ( empty( $pending_seats ) ) {
            $pending_seats = [];
        }
        $is_enable_payment_timer = etn_get_option( 'ticket_purchase_timer_enable', 'off' );

        foreach ( $seat_ids as $seat_id ) {
            // need to handle the corner in rush condition 
            // if ( !in_array( $seat_id, $pending_seats ) &&  $is_enable_payment_timer == 'on') {
            //     return new WP_Error( 'seat_limit', __( 'The requested seat is already booked, please select another seat', 'eventin' ), ['status' => 422] );
            // }
            if ( in_array( $seat_id, $already_booked_seats ) ) {
                return new WP_Error( 'seat_limit', __( 'The requested seat is already booked', 'eventin' ), ['status' => 422] );
            }
        }

        return true;
    }
}

if ( ! function_exists('etn_humanize_number') ) {

	function etn_humanize_number( $number ) {
		if (!is_numeric($number)) {
			return $number;
		}
		
		$number = (int)$number;
		
		if ($number >= 1000000000) {
			return round($number / 1000000000, 1) . 'b';
		}
		
		if ($number >= 1000000) {
			return round($number / 1000000, 1) . 'm';
		}
		
		if ($number >= 1000) {
			return round($number / 1000, 1) . 'k';
		}
		
		return $number;
	}
}



if ( ! function_exists('is_event_template_builder') ) {

	function is_event_template_builder() {
		$current_post_id = get_the_ID();

        $post_type = get_post_type($current_post_id);

        if ( $post_type != 'etn-template' ) {
            return false;
        }

        return $current_post_id;
	}
}

if ( ! function_exists('get_first_published_event') ) {

	function get_first_published_event() {
		$query = new WP_Query( [
            'post_type'      => 'etn',
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'orderby'        => 'date',
            'order'          => 'ASC'
        ] );
        
        $first_post_id = $query->have_posts() ? $query->posts[0]->ID : 0;

        return $first_post_id;       
	}
}

if ( ! function_exists( 'etn_get_selected_template_builder' ) ) {
    /**
     * Get name of the selected template builder
     * If selected template builder is deactive or null (not selected) then return emptry string
     */
    function etn_get_selected_template_builder() {
        $selected_template_builder = etn_get_option( 'selected_template_builder' ) ?? '';

        if ( $selected_template_builder == 'elementor' ) {
            if ( class_exists( '\Elementor\Plugin' ) ) {
                return 'elementor';
            }

            return '';
        }

        return $selected_template_builder;
    }
}

if ( ! function_exists('etn_get_static_event_templates') ) {
    /**
     * Returns list of static event templates
     * 
     * @return array containing data form the templates.
     */
    function etn_get_static_event_templates() {
        return [
            [
                'id'                    => 'event-one',
                'name'                  => __( 'Event Template One', 'eventin' ),
                'status'                => 'publish',
                'type'                  => 'event',
                'orientation'           => 'portrait',
                'thumbnail'             => \Wpeventin::plugin_url() . 'assets/images/landing_template_1.webp',
                'content'               => '',
                'is_clone'              => false,
                'is_pro'                => false,
                'template_css'          => '',
                'edit_link'             => '',
                'preview_link'          => 'https://product.themewinter.com/eventin/event/event-details-1/',
                'preview_event_id'      => null,
                'template_builder'      => 'gutenberg',
                'edit_with_elementor'   => false,
                'isStatic'              => true,
            ],
            [
                'id'                    => 'event-two',
                'name'                  => __( 'Event Template Two', 'eventin' ),
                'status'                => 'publish',
                'type'                  => 'event',
                'orientation'           => 'portrait',
                'thumbnail'             => \Wpeventin::plugin_url() . 'assets/images/landing_template_2.webp',
                'content'               => '',
                'is_clone'              => false,
                'is_pro'                => true,
                'template_css'          => '',
                'edit_link'             => '',
                'preview_link'          => 'https://product.themewinter.com/eventin/event/event-details-2/',
                'preview_event_id'      => null,
                'template_builder'      => 'gutenberg',
                'edit_with_elementor'   => false,
                'isStatic'              => true,
            ],
            [
                'id'                    => 'event-three',
                'name'                  => __( 'Event Template Three', 'eventin' ),
                'status'                => 'publish',
                'type'                  => 'event',
                'orientation'           => 'portrait',
                'thumbnail'             => \Wpeventin::plugin_url() . 'assets/images/landing_template_3.webp',
                'content'               => '',
                'is_clone'              => false,
                'is_pro'                => true,
                'template_css'          => '',
                'edit_link'             => '',
                'preview_link'          => 'https://product.themewinter.com/eventin/event/event-details-3/',
                'preview_event_id'      => null,
                'template_builder'      => 'gutenberg',
                'edit_with_elementor'   => false,
                'isStatic'              => true,
            ],
        ];
    }
}

if ( ! function_exists('etn_get_static_ticket_templates') ) {
    /**
     * Returns data for static ticket templates
     * 
     * @return array
     */
    function etn_get_static_ticket_templates() {
        return [
            [
                'id'                    => 'style-1',
                'name'                  => __( 'Template One', 'eventin' ),
                'status'                => 'publish',
                'type'                  => 'ticket',
                'orientation'           => 'landscape',
                'thumbnail'             => \Wpeventin::plugin_url() . 'assets/images/ticket_template_1.webp',
                'content'               => '',
                'is_clone'              => false,
                'is_pro'                => false,
                'template_css'          => '',
                'edit_link'             => '',
                'preview_link'          => 'https://product.themewinter.com/eventin/ticket-template-one/',
                'preview_event_id'      => null,
                'template_builder'      => 'gutenberg',
                'edit_with_elementor'   => false,
                'isStatic'              => true,
            ],
            [
                'id'                    => 'style-2',
                'name'                  => __( 'Template Two', 'eventin' ),
                'status'                => 'publish',
                'type'                  => 'ticket',
                'orientation'           => 'landscape',
                'thumbnail'             => \Wpeventin::plugin_url() . 'assets/images/ticket_template_2.webp',
                'content'               => '',
                'is_clone'              => false,
                'is_pro'                => true,
                'template_css'          => '',
                'edit_link'             => '',
                'preview_link'          => 'https://product.themewinter.com/eventin/ticket-template-two/',
                'preview_event_id'      => null,
                'template_builder'      => 'gutenberg',
                'edit_with_elementor'   => false,
                'isStatic'              => true,
            ],
        ];
    }
}

if ( ! function_exists('etn_get_static_speaker_templates') ) {
    /**
     * Returns data for static speaker templates
     * 
     * @return array
     */
    function etn_get_static_speaker_templates() {
        return [
            [
                'id'                    => 'speaker-one',
                'name'                  => __( 'Template One', 'eventin' ),
                'status'                => 'publish',
                'type'                  => 'speaker',
                'orientation'           => 'portrait',
                'thumbnail'             => \Wpeventin::plugin_url() . 'assets/images/speaker_template_1.webp',
                'content'               => '',
                'is_clone'              => false,
                'is_pro'                => false,
                'template_css'          => '',
                'edit_link'             => '',
                'preview_link'          => 'https://product.themewinter.com/eventin/admin/james/',
                'preview_event_id'      => null,
                'template_builder'      => 'gutenberg',
                'edit_with_elementor'   => false,
                'isStatic'              => true,
            ],
            [
                'id'                    => 'speaker-two-lite',
                'name'                  => __( 'Template Two', 'eventin' ),
                'status'                => 'publish',
                'type'                  => 'speaker',
                'orientation'           => 'portrait',
                'thumbnail'             => \Wpeventin::plugin_url() . 'assets/images/speaker_template_2.webp',
                'content'               => '',
                'is_clone'              => false,
                'is_pro'                => false,
                'template_css'          => '',
                'edit_link'             => '',
                'preview_link'          => 'https://product.themewinter.com/eventin/admin/henri/',
                'preview_event_id'      => null,
                'template_builder'      => 'gutenberg',
                'edit_with_elementor'   => false,
                'isStatic'              => true,
            ],
            [
                'id'                    => 'speaker-two',
                'name'                  => __( 'Template Three', 'eventin' ),
                'status'                => 'publish',
                'type'                  => 'speaker',
                'orientation'           => 'portrait',
                'thumbnail'             => \Wpeventin::plugin_url() . 'assets/images/speaker_template_3.webp',
                'content'               => '',
                'is_clone'              => false,
                'is_pro'                => true,
                'template_css'          => '',
                'edit_link'             => '',
                'preview_link'          => 'https://product.themewinter.com/eventin/admin/jim/',
                'preview_event_id'      => null,
                'template_builder'      => 'gutenberg',
                'edit_with_elementor'   => false,
                'isStatic'              => true,
            ],
            [
                'id'                    => 'speaker-three',
                'name'                  => __( 'Template Four', 'eventin' ),
                'status'                => 'publish',
                'type'                  => 'speaker',
                'orientation'           => 'portrait',
                'thumbnail'             => \Wpeventin::plugin_url() . 'assets/images/speaker_template_4.webp',
                'content'               => '',
                'is_clone'              => false,
                'is_pro'                => true,
                'template_css'          => '',
                'edit_link'             => '',
                'preview_link'          => 'https://product.themewinter.com/eventin/admin/laura-bryant/',
                'preview_event_id'      => null,
                'template_builder'      => 'gutenberg',
                'edit_with_elementor'   => false,
                'isStatic'              => true,
            ],
        ];
    }
}

if ( ! function_exists('etn_get_static_templates_by_type') ) {
    /**
     * Returns list of static templates by type or all templates if no type specified
     *
     * @param string $type Template type ('event', 'ticket', 'speaker') or empty for all
     * @return array containing data from the templates
     */
    function etn_get_static_templates_by_type( $type = '' ) {
        $static_templates = [];

        if ( empty( $type ) || $type === 'event' ) {
            $static_templates = array_merge( $static_templates, etn_get_static_event_templates() );
        }

        if ( empty( $type ) || $type === 'ticket' ) {
            $static_templates = array_merge( $static_templates, etn_get_static_ticket_templates() );
        }

        if ( empty( $type ) || $type === 'speaker' ) {
            $static_templates = array_merge( $static_templates, etn_get_static_speaker_templates() );
        }

        return $static_templates;
    }
}
