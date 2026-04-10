<?php
/**
 * Exporter Class
 * 
 * @package Eventin
 */
namespace Eventin\Exporter;

use Exception;

/**
 * Class Exporter
 */
class ExporterFactory {
    /**
     * Get exporter method
     *
     * @return  \Exporter_Interface
     */
    public static function get_exporter( $format ) {
        switch( $format ) {
            case 'csv':
                return new CSVExporter();

            case 'json':
                return new JsonExporter();

            default:
                throw new Exception( esc_html__( 'Unknown format', 'eventin' ) );
        }
    }
}
