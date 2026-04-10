<?php

namespace Eventin\Template;

defined( 'ABSPATH' ) || exit;

use Eventin\Abstracts\Provider;
use Eventin\Template\TemplateLimitHooks;
use Eventin\Template\TemplateBlockAssets;
use Eventin\Template\EtnHomepageHooks;
use Eventin\Template\TemplateLoader;

class TemplateProvider extends Provider {
    protected $services = [
        TemplateLimitHooks::class,
        EtnHomepageHooks::class,
        TemplateLoader::class,
    ];

    /**
     * Register services conditionally
     *
     * @return void
     */
    public function register(): void {
        // Add TemplateBlockAssets only for admin requests
        if ( etn_is_request( 'admin' ) ) {
            $this->services[] = TemplateBlockAssets::class;
        }

        parent::register();
    }
}