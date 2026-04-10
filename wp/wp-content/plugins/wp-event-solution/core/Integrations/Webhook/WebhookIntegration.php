<?php

namespace Eventin\Integrations\Webhook;

defined( 'ABSPATH' ) || exit;

use Eventin\Interfaces\HookableInterface;

/**
 * Webhook integrations
 */
class WebhookIntegration implements HookableInterface {
    /**
     * Webhook integration
     *
     * @var array
     */
    private $integrations = [
        FluentCRM::class,
    ];

    /**
     * Register service
     *
     * @return  void
     */
    public function register_hooks(): void {
        foreach( $this->integrations as $integration ) {
            if ( class_exists( $integration ) ) {
                $integration_object = new $integration;
                $integration_object->run();
            }  
        }
    }
}