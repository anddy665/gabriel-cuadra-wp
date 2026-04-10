<?php
/**
 * File Reader Interface
 * 
 * @package Eventin
 */
namespace Eventin\Importer;

/**
 * Reader Interface
 */
interface ReaderInterface {
    /**
     * Read file
     *
     * @return  array
     */
    public function read_file();
}
