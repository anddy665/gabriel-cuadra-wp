<?php
/**
 * Exporter Interface
 * 
 * @package Eventin
 */
namespace Eventin\Exporter;

/**
 * Exporter interface
 */
interface ExporterInterface {
    /**
     * Export data
     *
     * @return void
     */
    public function export( $data, $columns = [], $file_name = '' );
}
