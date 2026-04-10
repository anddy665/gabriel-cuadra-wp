<?php

namespace Eventin\Event;

defined( 'ABSPATH' ) || exit;

use Eventin\Abstracts\Provider;

/**
 * Event Provider Class
 */
class EventProvider extends Provider {
    /**
     * Store all services
     *
     * @var array
     */
    protected $services = [
        EventTemplate::class,
    ];
}
