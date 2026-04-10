<?php
/**
 * Post Importer Class
 *
 * @package Eventin
 */
namespace Eventin\Importer;

use Eventin\Attendee\AttendeeImporter;
use Eventin\Event\EventImporter;
use Eventin\Schedule\ScheduleImporter;
use Eventin\Speaker\SpeakerImporter;

/**
 * Post Importer Class
 */
class PostImporter {

    /**
     * Post importer class
     *
     * @param   string  $post_type
     *
     * @return
     */
    public static function get_importer( $post_type ) {
        $exporters = [
            'etn-speaker'  => SpeakerImporter::class,
            'etn-schedule' => ScheduleImporter::class,
            'etn'          => EventImporter::class,
            'etn-attendee' => AttendeeImporter::class,
        ];

        $exporters = apply_filters( 'etn_post_importers', $exporters );

        if ( ! empty( $exporters[$post_type] ) ) {
            return new $exporters[$post_type]();
        }

        throw new \Exception( esc_html__( 'Unknown Post Type', 'eventin' ) );
    }
}
