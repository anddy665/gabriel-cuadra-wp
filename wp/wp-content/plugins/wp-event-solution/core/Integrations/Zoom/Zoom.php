<?php

/**
 * Zoom Integration
 *
 * @package Eventin
 */
namespace Eventin\Integrations\Zoom;

defined( 'ABSPATH' ) || exit;

use DateTime;
use DateTimeZone;
use Eventin\Interfaces\MeetingPlatformInterface;

/**
 * Zoom meeting class
 */
class Zoom implements MeetingPlatformInterface {
    /**
     * Store meeting data
     *
     * @var array
     */
    protected static $meeting_data;

    /**
     * Check zoom is connected or not
     *
     * @return  bool
     */
    public static function is_connected(): bool {
        if ( ZoomToken::get() ) {
            return true;
        }

        return false;
    }

    /**
     * Get zoom meeting link
     *
     * @return string
     */
    public static function create_link( $args = [] ): string {
        $defaults = [
            'title'      => '',
            'start_date' => '',
            'start_time' => '',
            'end_date'   => '',
            'end_time'   => '',
            'timezone'   => 'America/New_York',
        ];

        $args = wp_parse_args( $args, $defaults );

        $args['start_time'] = self::format_start_time(
            $args['start_date'],
            $args['start_time'],
            $args['timezone'],
        );

        return self::create_meeting( $args )->get_join_url();
    }

    /**
     * Get meeting join url
     *
     * @return  string
     */
    public static function get_join_url() {
        return self::$meeting_data['join_url'];
    }

    /**
     * Get meeting start url
     *
     * @return  string
     */
    public static function get_start_url() {
        return self::$meeting_data['start_url'];
    }

    /**
     * Get meeting data
     *
     * @return  array
     */
    public static function get_meeting_data() {
        return self::$meeting_data;
    }

    /**
     * Create zoom meeting
     *
     * @param   array  $args  Meeting args
     *
     * @return  Object
     */
    private static function create_meeting( $args ) {
        $access_token = ZoomToken::get();
        $zoom         = new ZoomClient( $access_token );

        

        if(isset($args['end_date']) && isset($args['end_time'])){
            // Combine date and time into one string
            $datetime = $args['end_date'] . ' ' . $args['end_time'];

            // Set timezone
            $timezone = $args['timezone'];

            // - Normalize timezone format (convert UTC+6 to +06:00)
            // - Added timezone normalization code (lines 113-119) that:
            // - Detects timezone formats like "UTC+6", "UTC-5", etc.
            // - Converts them to PHP-compatible format: "+06:00", "-05:00", etc.
            // - Handles decimal offsets like "UTC+5.5" → "+05:30"
            if (preg_match('/^UTC([+-]\d+(?:\.\d+)?)$/', $timezone, $matches)) {
                $offset = floatval($matches[1]);
                $hours = floor(abs($offset));
                $minutes = ($offset - floor($offset)) * 60;
                $timezone = sprintf('%s%02d:%02d', $offset >= 0 ? '+' : '-', $hours, $minutes);
            }

            // Create DateTime object with timezone
            $dt = new DateTime($datetime, new DateTimeZone($timezone));

            // Convert to UTC timezone  
            $dt->setTimezone(new DateTimeZone('UTC'));

            // Format to ISO 8601 (e.g., 2025-10-23T09:00:00Z)
            $formatted = $dt->format('Y-m-d\TH:i:s\Z');

            $args['end_time'] = $formatted;
        }

        $datetime1 = $args['start_time'];
        $datetime2 = $args['end_time'];

        // Create DateTime objects (Z means UTC)
        $dt1 = new DateTime($datetime1);
        $dt2 = new DateTime($datetime2);

        // Get the difference
        $duration = $dt2->getTimestamp() - $dt1->getTimestamp();
        $args['duration'] = $duration/60;        

        self::$meeting_data = $zoom->create_meeting( $args );

        return new self;
    }

    /**
     * Normalize timezone strings like "UTC+13" to a format acceptable by PHP DateTimeZone.
     *
     * Supported patterns:
     * - UTC±H
     * - UTC±HH
     * - UTC±H:MM
     * - UTC±HH:MM
     * Returns unchanged if it's already a valid IANA zone (e.g., "America/New_York").
     *
     * @param string $timezone
     * @return string
     */
    private static function normalize_timezone( $timezone ) {
        // Match UTC offsets like UTC+13 or UTC-05:30
        if ( preg_match( '/^UTC([+-])(\d{1,2})(?::?(\d{2}))?$/i', $timezone, $m ) ) {
            $sign = $m[1];
            $hour = str_pad( $m[2], 2, '0', STR_PAD_LEFT );
            $min  = isset( $m[3] ) && $m[3] !== '' ? $m[3] : '00';
            return $sign . $hour . ':' . $min; // e.g., +13:00
        }

        return $timezone;
    }

    /**
     * Format start time
     *
     * @param   string  $date      [$date description]
     * @param   string  $time      [$time description]
     * @param   string  $timezone  [$timezone description]
     *
     * @return  string             [return description]
     */
    private static function format_start_time( $date, $time, $timezone ) {
        // Combine date and time into a single string
        $date_time = $date . ' ' . $time;
        
        // Normalize timezone (handles inputs like "UTC+13") and create DateTime safely
        $tz_input = self::normalize_timezone( $timezone );
        try {
            $tz = new \DateTimeZone( $tz_input );
        } catch (\Exception $e) {
            // Fallback to UTC on invalid timezone
            $tz = new \DateTimeZone('UTC');
        }

        $date_time_obj = new \DateTime( $date_time, $tz );

        // Convert to ISO 8601 format in UTC for Zoom
        $date_time_obj->setTimezone( new \DateTimeZone('UTC') );
        return $date_time_obj->format('Y-m-d\TH:i:s\Z');
    }
}
