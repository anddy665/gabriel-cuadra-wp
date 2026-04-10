<?php
/**
 * Post Exporter Interface
 * 
 * @package Eventin
 */
namespace Eventin\Exporter;

/**
 * Post exporter interface
 */
interface PostExporterInterface {
    /**
     * Export data
     *
     * @param   array  $data
     * @param   string  $format
     *
     * @return void
     */
    public function export( $data, $format );
}
