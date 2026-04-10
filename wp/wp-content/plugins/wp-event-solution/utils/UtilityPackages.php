<?php

namespace Eventin\Utils;

use Eventin\Utils\Banner\Banner;
use Eventin\Utils\Rating\Rating;
use Eventin\Utils\Stories\Stories;
use Eventin\Utils\Notice\Notice;
use Eventin\Traits\Singleton;

defined( 'ABSPATH' ) || exit;

class UtilityPackages {

    use Singleton;

    /**
     * UtilityPackages class constructor.
     * private for singleton
     *
     * @return void
     * @since 1.0.0
     */
    public function __construct() {

        $filter_string = 'eventin,eventin-free-only';

        if ( class_exists( 'Wpeventin_Pro' ) ) {

            $filter_string .= ',eventin-pro';
            $filter_string = str_replace( ',eventin-free-only', '', $filter_string );
        }

        /**
         * Initializes the Notice utility package.
         *
         * This function initializes the Notice utility package, allowing you to display notices in your WordPress plugin or theme.
         * It is recommended to call this function during the initialization phase of your plugin or theme.
         *
         * @since 1.0.0
         */
        Notice::init();

        /**
         * UtilityPackages.php
         *
         * This file contains the code for the UtilityPackages class, which is responsible for setting up and configuring the utility packages for the Gutenkit Blocks Addon plugin.
         *
         * @package Gutenkit_Blocks_Addon
         * @subpackage Includes\Libs
         */
        Stories::instance( 'eventin' )   # @plugin_slug
        // ->is_test(true)                                                      # @check_interval
        ->set_filter( $filter_string )                                          # @active_plugins
        ->set_plugin( 'Eventin', 'https://themewinter.com/eventin/' )  # @plugin_name  @plugin_url
        ->set_api_url( 'https://banner.themefunction.com/public/stories/' )                # @api_url_for_stories
        ->call();

        /**
         * Show WPMET banner (codename: jhanda)
         *
         * This code snippet is responsible for displaying the WPMET banner, also known as codename "jhanda".
         * It initializes the UtilityPackage\Banner\Banner class and sets various properties and options.
         * The banner is associated with the 'testplugin' plugin slug and is set to run in test mode.
         * The active plugins are filtered based on the provided filter string.
         * The API URL for the banners is set to 'https://api.wpmet.com/public/jhanda'.
         * The allowed screen for the banner is set to 'toplevel_page_gutenkit'.
         * Finally, the `call()` method is invoked to display the banner.
         *
         * @package Gutenkit_Blocks_Addon
         * @subpackage Libs
         * @since 1.0.0
         */
        Banner::instance( 'eventin' )     # @plugin_slug
        // ->is_test(true)                                                      # @check_interval
        ->set_filter( ltrim( $filter_string, ',' ) )                            # @active_plugins
        ->set_api_url( 'https://banner.themefunction.com/public/jhanda' )                  # @api_url_for_banners
        ->set_plugin_screens( 'toplevel_page_eventin' )                     # @set_allowed_screen
        ->call();

        /**
         * Ask for Ratings
         *
         * This code initializes the utility package for asking users to rate the Gutenkit Blocks Addon plugin.
         * It sets various properties such as the plugin logo, plugin name and URL, allowed screens, priority,
         * time interval, and conditions for displaying the rating prompt.
         *
         * @package Gutenkit_Blocks_Addon
         * @subpackage Libs
         */
        // Rating::instance('eventin')# @plugin_slug
        //     ->set_plugin('Eventin', 'https://wordpress.org/support/plugin/wp-event-solution/reviews/#new-post')   # @plugin_name  @plugin_url
        //     ->set_allowed_screens('toplevel_page_eventin')                     # @set_allowed_screen
        //     ->set_priority(30)                                                          # @priority
        //     ->set_first_appear_day(7)                                                   # @time_interval_days
        //     ->set_condition(true)                                                       # @check_conditions
        //     ->set_support_url('https://themewinter.com/support/')                 # @support_url
        //     ->call();
    }
}