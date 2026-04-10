<?php
/**
 * Post Importer Interface
 * 
 * @package Eventin
 */
namespace Eventin\Importer;

/**
 * Post Importer Interface
 */
interface PostImporterInterface {
    /**
     * Import file
     *
     * @return  void
     */
    public function import( $file );
}
