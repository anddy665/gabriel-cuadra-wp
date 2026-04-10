<?php
/**
 * Post Exporter Class
 *
 * @package Eventin
 */
namespace Eventin\Exporter;

use Eventin\Attendee\AttendeeExporter;
use Eventin\Event\EventExporter;
use Eventin\Schedule\ScheduleExporter;
use Eventin\Speaker\SpeakerExporter;

/**
 * Post Exporter Class
 */
class PostExporter {
    /**
     * Get post exporter
     *
     * @return
     */
    public static function get_post_exporter( $post_type ) {

        $exporters = [
            'etn'          => EventExporter::class,
            'etn-attendee' => AttendeeExporter::class,
            'etn-speaker'  => SpeakerExporter::class,
            'etn-schedule' => ScheduleExporter::class,
            'etn-attendee' => AttendeeExporter::class,
        ];

        $exporters = apply_filters( 'etn_post_exporters', $exporters );

        if ( ! empty( $exporters[$post_type] ) ) {
            return new $exporters[$post_type]();
        }

        throw new \Exception( esc_html__( 'Unknown Post Type', 'eventin' ) );
    }
}
