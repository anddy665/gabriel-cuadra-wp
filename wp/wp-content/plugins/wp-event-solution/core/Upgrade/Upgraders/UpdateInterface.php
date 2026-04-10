<?php

/**
 * Updater interface
 *
 * @package Eventin\Upgrades
 */
namespace Eventin\Upgrade\Upgraders;

defined( 'ABSPATH' ) || exit;

interface UpdateInterface {
    /**
     * Run the updater
     *
     * @return  void
     */
    public function run();
}
