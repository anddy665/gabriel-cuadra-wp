<?php

namespace Eventin\Speaker;

defined( 'ABSPATH' ) || exit;

use Eventin\Abstracts\Provider;

/**
 * Speaker Provider Class
 */
class SpeakerProvider extends Provider {
    /**
     * Store all services
     *
     * @var array
     */
    protected $services = [
        SpeakerTemplate::class
    ];
}
