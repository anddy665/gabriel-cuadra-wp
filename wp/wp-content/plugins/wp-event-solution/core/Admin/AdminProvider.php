<?php

namespace Eventin\Admin;

defined( 'ABSPATH' ) || exit;

use Eventin\Abstracts\Provider;
use Eventin\AccessControl\PermissionManager;
use Eventin\Admin\Role\RoleManager;
use Eventin\Integrations\Integration;
use Eventin\Integrations\Webhook\WebhookIntegration;

/**
 * Admin Provider class
 * 
 * @package Eventin/Admin
 */

class AdminProvider extends Provider {
    /**
     * Holds classes that should be instantiated
     *
     * @var array
     */
    protected $services = [
        Integration::class,
        Menu::class,
        EventReminder::class,
        TemplateRender::class,
        WebhookIntegration::class,
        RoleManager::class,
        PermissionManager::class,
    ];
}
