<?php

namespace Eventin\Blocks;

defined( 'ABSPATH' ) || exit;

use Eventin\Abstracts\Provider;

/**
 * BlockProvider class
 */
class BlockProvider extends Provider {
    /**
     * Store services
     *
     * @var array
     */
    protected $services = [
        BlockTypesController::class,
        BlockService::class,
        BlockLegacySupportHooks::class
    ];
}