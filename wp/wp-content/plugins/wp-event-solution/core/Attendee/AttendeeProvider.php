<?php

namespace Eventin\Attendee;

defined( 'ABSPATH' ) || exit;

use Eventin\Abstracts\Provider;

/**
 * Attendee Provider
 */
class AttendeeProvider extends Provider {
    /**
     * Store attendee related services
     *
     * @var array
     */
    protected $services = [
        TicketTemplate::class,
    ];
}