<?php
// download-ics.php

// Load WordPress
if (!defined('ABSPATH')) {
    // Find WordPress root directory
    $wp_root = dirname(__FILE__);
    for ($i = 0; $i < 10; $i++) {
        if (file_exists($wp_root . '/wp-load.php')) {
            require_once($wp_root . '/wp-load.php');
            break;
        }
        $wp_root = dirname($wp_root);
    }
}   

// phpcs:disable WordPress.Security.NonceVerification.Recommended -- iCalendar download links are public, bookmarkable URLs; nonces would break shared/email calendar links.
$start_date  = isset($_GET['date_start']) ? sanitize_text_field( wp_unslash( $_GET['date_start'] ) ) : '';
$end_date    = isset($_GET['date_end']) ? sanitize_text_field( wp_unslash( $_GET['date_end'] ) ) : '';
$summary     = isset($_GET['summary']) ? sanitize_text_field( wp_unslash( $_GET['summary'] ) ) : '';
$location    = isset($_GET['location']) ? sanitize_text_field( wp_unslash( $_GET['location'] ) ) : '';
$description = isset($_GET['description']) ? sanitize_textarea_field( wp_unslash( $_GET['description'] ) ) : '';
// phpcs:enable WordPress.Security.NonceVerification.Recommended

// Escape special characters for ICS format (RFC 5545)
function esc_ics_text($text) {
    $text = str_replace('\\', '\\\\', $text);
    $text = str_replace(',', '\\,', $text);
    $text = str_replace(';', '\\;', $text);
    $text = str_replace("\n", '\\n', $text);
    $text = str_replace("\r", '', $text);
    return $text;
}

// Send headers to download as .ics file
header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename=event.ics');

// Output ICS content
$ics  = "BEGIN:VCALENDAR\r\n";
$ics .= "VERSION:2.0\r\n";
$ics .= "CALSCALE:GREGORIAN\r\n";
$ics .= "METHOD:PUBLISH\r\n";
$ics .= "BEGIN:VEVENT\r\n";
$ics .= "SUMMARY:" . esc_ics_text($summary) . "\r\n";
$ics .= "DESCRIPTION:" . esc_ics_text($description) . "\r\n";
$ics .= "LOCATION:" . esc_ics_text($location) . "\r\n";
$ics .= "DTSTAMP:" . gmdate('Ymd\THis\Z') . "\r\n";

// Timed event using input date-time strings
$ics .= "DTSTART:" . esc_ics_text( $start_date ) . "\r\n";
$ics .= "DTEND:"   . esc_ics_text( $end_date ) . "\r\n";

$ics .= "END:VEVENT\r\n";
$ics .= "END:VCALENDAR\r\n";

echo $ics; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Raw iCalendar format output sent with text/calendar headers; HTML escaping would corrupt the .ics file.
exit;
