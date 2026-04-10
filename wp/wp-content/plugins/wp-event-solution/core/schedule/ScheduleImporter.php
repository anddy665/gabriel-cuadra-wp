<?php

/**
 * Schedule Importer Class
 * 
 * @package Eventin
 */
namespace Eventin\Schedule;

defined( 'ABSPATH' ) || exit;

use Eventin\Importer\PostImporterInterface;
use Eventin\Importer\ReaderFactory;
use Etn\Core\Schedule\Schedule_Model;

/**
 * Class Schedule Importer
 */
class ScheduleImporter implements PostImporterInterface {
    /**
     * Store File
     *
     * @var string
     */
    private $file;

    /**
     * Store data
     *
     * @var array
     */
    private $data;

    /**
     * Schedule import
     *
     * @return  void
     */
    public function import( $file ) {
        $this->file = $file;
        $file_reader = ReaderFactory::get_reader( $file );

        if ( is_wp_error( $file_reader ) ) {
            return $file_reader;
        }

        $this->data = $file_reader->read_file();
        $this->create_schedule();
    }

    /**
     * Create schedule
     *
     * @return  void
     */
    private function create_schedule() {
        $schedule   = new Schedule_Model();
        $file_type  = ! empty( $this->file['type'] ) ? $this->file['type'] : '';

        $rows = $this->data;

        foreach( $rows as $row ) {
            $slots = ! empty( $row['schedule_slot'] ) ? $row['schedule_slot'] : '';
            if ( 'text/csv' == $file_type ) {
                $slots = json_decode( $slots, true );
            }
            // Sanitize slots to prevent PHP Object Injection
            $slots = etn_sanitize_array_input( $slots );

            $args = [
                'etn_schedule_title'    => ! empty( $row['program_title'] ) ? $row['program_title'] : '',
                'etn_schedule_date'     => ! empty( $row['date'] ) ? $row['date'] : '',
                'etn_schedule_day'      => ! empty( $row['day_name'] ) ? $row['day_name'] : '',
                'etn_schedule_topics'   => $slots
            ];

            $schedule->create( $args );
        }
 
    }
}
