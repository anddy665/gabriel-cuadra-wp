<?php

/**
 * Extentions class
 * 
 * @package Eventin
 */
namespace Eventin\Extensions;

defined( 'ABSPATH' ) || exit;

use Eventin\Integrations\Zoom\Zoom;

/**
 * Class extention
 */
class Extension {

    /**
     * Get all modules
     *
     * @return  array
     */
    public static function modules() {
        $extentions = self::get();

        return array_filter( $extentions, function( $extension ) {
            return $extension['type'] === 'module';
        } );
    }

    /**
     * Get all addons
     *
     * @return  array
     */
    public static function addons() {
        $extensions = self::get(); // Fixed the typo

        return array_values( array_filter($extensions, function($extension) {
            return $extension['type'] === 'addon';
        } ) );
    }

    /**
     * Get all addons
     *
     * @return  array
     */
    public static function plugins() {
        $extensions = self::get(); // Fixed the typo

        return array_values( array_filter($extensions, function($extension) {
            return $extension['type'] === 'plugin';
        } ) );
    }

    /**
     * Get all integrations
     *
     * @return  array
     */
    public static function integrations() {
        $extensions = self::get(); // Fixed the typo

        return array_values( array_filter($extensions, function($extension) {
            return $extension['type'] === 'integration';
        } ) );
    }

    /**
     * Get all except plugins
     *
     * @return  array
     */
    public static function all_except_plugins() {
        $extensions = self::get();
        return array_values( array_filter($extensions, function($extension) {
            return ( $extension['type'] === 'integration' || $extension['type'] === 'module' || $extension['type'] === 'addon' );
        } ) );
    }

    /**
     * Get all extensions
     *
     * @return  array
     */
    public static function get() {
        $extensions = self::extensions();
        
        return array_map( function( $extension ) {
            $settings = get_option( 'etn_addons_options', [] );

            if ( isset( $settings[ $extension['name'] ] ) && $settings[ $extension['name']] === 'on' ) {

                $extension['status'] = ( $extension['type'] != 'integration' ) ? 'on' : $extension['status'];

                if ( 'addon' === $extension['type'] ) {
                    if ( 
                            self::is_need_upgrade( $extension['name'] )
                            && ! PluginManager::is_installed( $extension['slug'] )
                        ) {
                        $extension['status'] = 'upgrade';
                    }

                    if ( PluginManager::is_installed( $extension['slug'] ) ) {
                        $extension['status'] = 'install';
                    }

                    if ( PluginManager::is_activated( $extension['slug'] ) ) {
                        $extension['status'] = 'activate';
                    }
                }

                if ( 'module' === $extension['type'] ) {
                    $dependencies = self::get_depencies( $extension['slug'] );
                    $dependency   = is_array( $dependencies ) ? $dependencies[0] : '';

                    if ( 
                        self::is_need_upgrade( $extension['name'] )
                        && ! PluginManager::is_installed( $dependency )
                    ) {
                        $extension['status'] = 'upgrade';
                    }

                    if ( PluginManager::is_installed( $dependency ) ) {
                        $extension['status'] = 'install';
                    }

                    if ( PluginManager::is_activated( $dependency ) ) {
                        $extension['status'] = 'activate';
                    }
                }
    
                if ( self::dependencies_resolved( $extension['name'] ) ) {
                    $extension['notice'] = false;
                }

            }

            if ( 'plugin' === $extension['type'] ) {
                if ( 
                        self::is_need_upgrade( $extension['name'] )
                        && ! PluginManager::is_installed( $extension['slug'] )
                    ) {
                    $extension['status'] = 'upgrade';
                }

                if ( PluginManager::is_installed( $extension['slug'] ) ) {
                    $extension['status'] = 'install';
                }

                if ( PluginManager::is_activated( $extension['slug'] ) ) {
                    $extension['status'] = 'activate';
                }
            }

            return $extension;
            
        }, $extensions );
    }

    /**
     * Find extension by name
     *
     * @return  array
     */
    public static function find( $name ) {
        $extensions = self::extensions();
        
        if ( array_key_exists( $name, $extensions ) ) {
            return $extensions[$name];  
        }

        return null;
    }

    /**
     * Update extension status
     *
     * @param   string  $name
     *
     * @return  bool | WP_Error
     */
    public static function update( $name, $status ) {
        $extension = self::find( $name );

        if ( ! $extension ) {
            return false;
        }

        $settings = self::get_settings();
        $updated_status = 'off' !== $status ? 'on' : 'off';
        $settings[$name] = $updated_status;

        $slug = ! empty( $extension['slug'] ) ? $extension['slug'] : '';

        $result = true;

        if ( 'addon' === $extension['type'] && 'on' === $updated_status ) {
            if ( ! $slug ) {
                return false;
            }

            $dependencies = ! empty( $extension['deps'] ) ? $extension['deps'] : [];

            if ( is_array( $dependencies ) ) {
                foreach ( $dependencies as $dependency ) {
                    if ( ! PluginManager::is_installed( $dependency ) ) {
                        $result = PluginManager::install_plugin( $dependency );
                        if ( ! $result || is_wp_error( $result ) ) {
                            return $result ?: false;
                        }
                    }

                    if ( ! PluginManager::is_activated( $dependency ) ) {
                        $result = PluginManager::activate_plugin( $dependency );
                        if ( ! $result || is_wp_error( $result ) ) {
                            return $result ?: false;
                        }
                    }
                }
            }

            if ( 'install' === $status && ! PluginManager::is_installed( $slug ) ) {
                $result = PluginManager::install_plugin( $slug );
                if ( ! $result || is_wp_error( $result ) ) {
                    return $result ?: false;
                }
            }

            if ( 'activate' === $status && ! PluginManager::is_activated( $slug ) ) {
                $result = PluginManager::activate_plugin( $slug );
                if ( ! $result || is_wp_error( $result ) ) {
                    return $result ?: false;
                }
            }

            if ( 'deactivate' === $status && PluginManager::is_activated( $slug ) ) {
                $result = PluginManager::deactivate_plugin( $slug );
                if ( ! $result || is_wp_error( $result ) ) {
                    return $result ?: false;
                }
            }
        }

        if ( 'plugin' === $extension['type'] ) {
            if ( ! $slug ) {
                return false;
            }

            if ( 'install' === $status && ! PluginManager::is_installed( $slug ) ) {
                $result = PluginManager::install_plugin( $slug );
            }

            if ( 'activate' === $status && ! PluginManager::is_activated( $slug ) ) {
                $result = PluginManager::activate_plugin( $slug );
            }

            if ( 'deactivate' === $status && PluginManager::is_activated( $slug ) ) {
                $result = PluginManager::deactivate_plugin( $slug );
            }
        }

        // if ( 'addon' === $extension['type'] && 'off' === $status ) {

        //     if ( ! $slug ) {
        //         return false;
        //     }

        //     if ( PluginManager::is_activated( $slug ) ) {
        //         $result = PluginManager::deactivate_plugin( $slug );
        //     }
        // }

        if ( 'module' === $extension['type']  && ! empty( $extension['deps'] ) ) {
            $dependency = $extension['deps'][0];
            if ( 'install' === $status && ! PluginManager::is_installed( $dependency ) ) {
                $result = PluginManager::install_plugin( $dependency );
            }

            if ( 'activate' === $status && ! PluginManager::is_activated( $dependency ) ) {
                $result = PluginManager::activate_plugin( $dependency );
            }

            if ( 'deactivate' === $status && PluginManager::is_activated( $dependency ) ) {
                $result = PluginManager::deactivate_plugin( $dependency );
            }
        }

        update_option( 'etn_addons_options', $settings );

        return $result;
    }

    /**
     * Get settings
     *
     * @return  array
     */
    public static function get_settings() {
        $settings = get_option( 'etn_addons_options', [] );

        return $settings;
    }

    /**
     * Check if an extension's dependencies are resolved.
     *
     * @param string $extension_name Name of the extension.
     * @return bool True if all dependencies are resolved, false otherwise.
     */
    public static function dependencies_resolved( $extension_name ) {
        $depencies = self::get_depencies( $extension_name );

        if ( ! $depencies ) {
            return true;
        }

        foreach ( $depencies as $dependency ) {
            if ( ! PluginManager::is_activated( $dependency ) ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get dependencies
     *
     * @param   string  $extension_name  [$extension_name description]
     *
     * @return array
     */
    public static function get_depencies( $extension_name ) {
        $extension = self::find( $extension_name );

        if ( ! $extension ) {
            return null;
        }

        if ( empty( $extension['deps'] ) ) {
            return null;
        }

        return $extension['deps'];
    }

    /**
     * Get dependencies
     *
     * @param   string  $extension_name  [$extension_name description]
     *
     * @return array
     */
    public static function get_depency_names( $extension_name ) {
        $depencies = self::get_depencies( $extension_name );
        
        $names = [];

        if ( is_array( $depencies ) ) {
            foreach( $depencies as $dependency ) {
                $names[] = PluginManager::get_plugin_name_by_slug( $dependency );
            }
        }

        return $names;
    }

    /**
     * Get dependencies
     *
     * @param   string  $extension_name  [$extension_name description]
     *
     * @return string
     */
    public static function get_depency_string( $extension_name ) {
        $depencies = self::get_depency_names( $extension_name );
        
        return implode( ',', $depencies );
    }

    /**
     * Check a module or addon need to upgrade
     *
     * @param   string  $extension_name  [$extension_name description]
     *
     * @return  bool
     */
    public static function is_need_upgrade( $extension_name ) {
        $extension = self::find( $extension_name );

        if ( ! $extension ) {
            return false;
        }

        if ( isset( $extension['upgrade'] ) && $extension['upgrade'] ) {
            return true;
        }

        return false;
    }
    
    /**
     * List of all extensions
     *
     * @return  array
     */
    private static function extensions() {
        $extensions = [
            'dokan' => [
                'name'          => 'dokan',
                'slug'          => 'dokan',
                'type'          => 'module',
                'status'        => 'off',
                'is_pro'        => true,
                'deps'          => ['dokan-lite'],
                'title'         => __('Dokan', 'eventin'),
                'description'   => __('It allows you to create a Multivendor Event marketplace and make commission for each sale.', 'eventin'),
                'icon'          => ExtensionIcon::get('dokan'),
                'notice'        => __('NB: Need to active Dokan plugin', 'eventin'),
                'demo_link'     => 'https://product.themewinter.com/eventin/',
                'settings_link' => '',
                'doc_link'      => 'https://support.themewinter.com/docs/plugins/plugin-docs/integration/multivendor-event-marketplace/',
                'badge_tags'    => ['Popular', 'Trending'],
                'video_link'    => '',
            ],
            'buddyboss' => [
                'name'          => 'buddyboss',
                'slug'          => 'buddyboss',
                'type'          => 'module',
                'upgrade'       => true,
                'upgrade_link'  => 'https://www.buddyboss.com/pricing',
                'status'        => 'off',
                'is_pro'        => true,
                'deps'          => ['buddyboss'],
                'title'         => __('BuddyBoss', 'eventin'),
                'description'   => __('It allows you to create and manage events and sell tickets inside the BuddyBoss theme.', 'eventin'),
                'icon'          => ExtensionIcon::get('buddyboss'),
                'notice'        => __('NB: Need to active BuddyBoss plugin', 'eventin'),
                'demo_link'     => 'https://product.themewinter.com/eventin/',
                'settings_link' => '',
                'doc_link'      => 'https://support.themewinter.com/docs/plugins/plugin-docs/integration/buddyboss-integration/',
                'badge_tags'    => ['Pro'],
            ],
            'certificate_builder' => [
                'name'          => 'certificate_builder',
                'slug'          => 'certificate_builder',
                'type'          => 'module',
                'status'        => 'off',
                'is_pro'        => true,
                'title'         => __('Certificate Builder', 'eventin'),
                'description'   => __('You can design and send a PDF certificate for the event attendee.', 'eventin'),
                'icon'          => ExtensionIcon::get('certificate_builder'),
                'notice'        => '',
                'demo_link'     => 'https://product.themewinter.com/eventin/',
                'settings_link' => admin_url('admin.php?page=eventin#/settings/event-settings/attendees'),
                'doc_link'      => 'https://support.themewinter.com/docs/plugins/plugin-docs/event/certificate-builder-for-attendee/',
                'badge_tags'    => ['Pro'],
            ],
            'rsvp' => [
                'name'          => 'rsvp',
                'slug'          => 'rsvp',
                'type'          => 'module',
                'status'        => 'off',
                'is_pro'        => true,
                'title'         => __('RSVP Module', 'eventin'),
                'description'   => __('It allows you to add RSVP at your upcoming events and grab user\'s attention easily.', 'eventin'),
                'icon'          => ExtensionIcon::get('rsvp'),
                'notice'        => '',
                'demo_link'     => 'https://product.themewinter.com/eventin/',
                'settings_link' => admin_url('admin.php?page=eventin#/settings/email/purchase-email'),
                'doc_link'      => 'https://support.themewinter.com/docs/plugins/plugin-docs/rsvp-settings/what-is-rsvp-event/',
                'badge_tags'    => ['Popular'],
            ],
            'seat_map' => [
                'name'          => 'seat_map',
                'slug'          => 'seat_map',
                'type'          => 'module',
                'upgrade'       => true,
                'upgrade_link'  => 'https://arraytics.com/timetics/#pricing',
                'status'        => 'off',
                'is_pro'        => false,
                'deps'          => ['timetics-pro'],
                'title'         => __('Seat Map', 'eventin'),
                'description'   => __('With the features, you can now add a visual seat plan with different ticket pricing for events.', 'eventin'),
                'icon'          => ExtensionIcon::get('seat_map'),
                'notice'        => __('NB: Need to active Timetics Pro plugin', 'eventin'),
                'demo_link'     => 'https://product.themewinter.com/eventin/',
                'settings_link' => '',
                'doc_link'      => 'https://support.themewinter.com/docs/plugins/plugin-docs/visual-seat-map/visual-seat-plan/',
                'badge_tags'    => ['Free'],
            ],
            'automation' => [
                'name'          => 'automation',
                'slug'          => 'automation',
                'type'          => 'module',
                'status'        => 'off',
                'is_pro'        => false,
                'title'         => __('Automation', 'eventin'),
                'description'   => __('Skip the manual steps — Enable the Eventin’s automation to send emails for event creation, booking confirmation, reminders, and RSVP updates.', 'eventin'),
                'icon'          => ExtensionIcon::get('automation'),
                'notice'        => '',
                'demo_link'     => 'https://themewinter.com/eventin/',
                'settings_link' => '',
                'doc_link'      => 'https://support.themewinter.com/docs/plugins/plugin-docs/email-settings/automation/',
                'badge_tags'    => ['Free'],
            ],
            'eventin-divi-addon' => [
                'name'          => 'eventin-divi-addon',
                'slug'          => 'eventin-divi-addon',
                'type'          => 'addon',
                'status'        => 'off',
                'title'         => __('Eventin Divi Addon', 'eventin'),
                'description'   => __('It enable the Eventin featured and module inside DIVI editing panel.', 'eventin'),
                'icon'          => ExtensionIcon::get('eventin-divi-addon'),
                'notice'        => '',
                'demo_link'     => 'https://product.themewinter.com/eventin/',
                'settings_link' => '',
                'doc_link'      => 'https://support.themewinter.com/docs/plugins/plugin-docs/integration/divi-builder-integration/',
                'badge_tags'    => ['Free'],
            ],
            'eventin-bricks-addon' => [
                'name'          => 'eventin-bricks-addon',
                'slug'          => 'eventin-bricks-addon',
                'type'          => 'addon',
                'upgrade'       => true,
                'upgrade_link'  => 'https://themewinter.com/purchase-history',
                'status'        => 'off',
                'is_pro'        => true,
                'title'         => __('Eventin Bricks Addon', 'eventin'),
                'description'   => __('It\'s enable the Eventin featured and module inside Bricks editing panel.', 'eventin'),
                'icon'          => ExtensionIcon::get('eventin-bricks-addon'),
                'notice'        => '',
                'demo_link'     => 'https://support.themewinter.com/docs/plugins/plugin-docs/integration/bricks-builder-integration/',
                'settings_link' => '',
                'doc_link'      => 'https://support.themewinter.com/docs/plugins/plugin-docs/integration/bricks-builder-integration/',
                'badge_tags'    => ['Pro'],
            ],
            'eventin-oxygen-addon' => [
                'name'          => 'eventin-oxygen-addon',
                'slug'          => 'eventin-oxygen-addon',
                'type'          => 'addon',
                'upgrade'       => true,
                'upgrade_link'  => 'https://themewinter.com/purchase-history',
                'status'        => 'off',
                'is_pro'        => true,
                'title'         => __('Eventin Oxygen Addon', 'eventin'),
                'description'   => __('It\'s enable the Eventin featured and module inside Oxygen editing panel.', 'eventin'),
                'icon'          => ExtensionIcon::get('eventin-oxygen-addon'),
                'notice'        => '',
                'demo_link'     => 'https://support.themewinter.com/docs/plugins/plugin-docs/integration/oxygen-builder-integration-pro',
                'settings_link' => '',
                'doc_link'      => 'https://support.themewinter.com/docs/plugins/plugin-docs/integration/oxygen-builder-integration-pro',
                'badge_tags'    => ['Pro'],
            ],
            'wpcafe' => [
                'name'          => 'wpcafe',
                'slug'          => 'wpcafe',
                'type'          => 'plugin',
                'upgrade'       => false,
                'upgrade_link'  => 'https://themewinter.com/purchase-history',
                'status'        => 'on',
                'is_pro'        => false,
                'title'         => __('WPCafe', 'eventin'),
                'description'   => __('WPCafe - A restaurant plugin to increase sales with an Online Ordering System, Delivery, Pickup, Food Menu, Reservations, and Table Management.', 'eventin'),
                'icon'          => ExtensionIcon::get('wpcafe'),
                'notice'        => '',
                'demo_link'     => 'https://product.themewinter.com/wpcafe',
                'settings_link' => '',
                'doc_link'      => 'https://support.themewinter.com/docs/plugins/docs/wp-cafe/',
                'badge_tags'    => ['Free'],
            ],
            'timetics' => [
                'name'          => 'timetics',
                'slug'          => 'timetics',
                'type'          => 'plugin',
                'upgrade'       => false,
                'upgrade_link'  => 'https://themewinter.com/purchase-history',
                'status'        => 'on',
                'is_pro'        => false,
                'title'         => __('WP Timetics', 'eventin'),
                'description'   => __('Manage appointments anytime, anywhere. WP Timetics makes scheduling simple with an all-in-one WordPress booking solution.', 'eventin'),
                'icon'          => ExtensionIcon::get('timetics'),
                'notice'        => '',
                'demo_link'     => 'https://arraytics.com/timetics',
                'settings_link' => '',
                'doc_link'      => 'https://docs.arraytics.com/docs/timetics/getting-started/',
                'badge_tags'    => ['Free'],
            ],
            'poptics' => [
                'name'          => 'poptics',
                'slug'          => 'poptics',
                'type'          => 'plugin',
                'upgrade'       => false,
                'upgrade_link'  => 'https://themewinter.com/purchase-history',
                'status'        => 'on',
                'is_pro'        => false,
                'title'         => __('Poptics', 'eventin'),
                'description'   => __(' Discover the ultimate popup mix with Poptics Popup Builder to boost your lead generation and sales conversions.', 'eventin'),
                'icon'          => ExtensionIcon::get('poptics'),
                'notice'        => '',
                'demo_link'     => 'https://demo.aethonic.com/popticsadmin/',
                'settings_link' => '',
                'doc_link'      => 'https://docs.aethonic.com/docs/getting-started/intro/',
                'badge_tags'    => ['Free'],
            ],
            'booktics' => [
                'name'          => 'booktics',
                'slug'          => 'booktics',
                'type'          => 'plugin',
                'upgrade'       => false,
                'upgrade_link'  => 'https://arraytics.com/booktics/#pricing-plan',
                'status'        => 'on',
                'is_pro'        => false,
                'title'         => __('Booktics', 'eventin'),
                'description'   => __('Booktics is a WordPress plugin that helps service businesses take bookings, manage teams, and accept payments easily and professionally.', 'eventin'),
                'icon'          => ExtensionIcon::get('booktics'),
                'notice'        => '',
                'demo_link'     => 'https://arraytics.com/booktics/#booktics-services',
                'settings_link' => '',
                'doc_link'      => 'https://docs.arraytics.com/docs/booktics/getting-started/',
                'badge_tags'    => ['Free'],
            ],
            'zoom' => [
                'name'          => 'zoom',
                'slug'          => 'zoom',
                'type'          => 'integration',
                'status'        => (etn_get_option('etn_zoom_api') && etn_get_option('etn_zoom_api') !== 'off') ? 'on' : 'off',
                'is_pro'        => false,
                'title'         => __('Zoom', 'eventin'),
                'description'   => __('Zoom Integration for Eventin.', 'eventin'),
                'icon'          => ExtensionIcon::get('zoom'),
                'notice'        => '',
                'demo_link'     => 'https://themewinter.com/eventin/',
                'settings_link' => '',
                'doc_link'      => 'https://support.themewinter.com/docs/plugins/plugin-docs/integration/zoom-meeting-2/?utm_source=documentations&utm_medium=eventin&utm_campaign=eventin+documentations',
                'data'          => [
                    'etn_zoom_api'  =>  etn_get_option('etn_zoom_api') ? 'yes' : 'no',
                    'zoom_authorize_url' => !empty(etn_get_option('zoom_authorize_url')) ? etn_get_option('zoom_authorize_url') : '',
                    'zoom_connected' => empty(etn_get_option('zoom_token')) ? 'no' : 'yes',
                    'zoom_redirect_url' => home_url().'/eventin-integration/zoom-auth',
                    'zoom_client_id' => !empty(etn_get_option('zoom_client_id')) ? etn_get_option('zoom_client_id') : '',
                    'zoom_client_secret' => !empty(etn_get_option('zoom_client_secret')) ? etn_get_option('zoom_client_secret') : '',
                    'zoom_token' => !empty(etn_get_option('zoom_token')) ? etn_get_option('zoom_token') : ''
                ],
                'badge_tags'    => ['Free'],
            ],
            'google_meet' => [
                'name'          => 'google_meet',
                'slug'          => 'google_meet',
                'type'          => 'integration',
                'status'        => (etn_get_option('etn_meet_api') && etn_get_option('etn_meet_api') !== 'off') ? 'on' : 'off',
                'is_pro'        => true,
                'title'         => __('Google Meet', 'eventin'),
                'description'   => __('Use Google Meet to host your meetings and manage virtual events from your dashboard.', 'eventin'),
                'icon'          => ExtensionIcon::get('google_meet'),
                'notice'        => '',
                'demo_link'     => 'https://product.themewinter.com/eventin/',
                'settings_link' => admin_url('admin.php?page=eventin#/settings/integrations/google-meet'),
                'doc_link'      => 'https://support.themewinter.com/docs/plugins/plugin-docs/integration/google-meet/',
                'video_link'    => 'https://www.youtube.com/watch?v=7RCx6yIKULY&list=PLW54c-mt4ObDwu0GWjJIoH0aP1hQHyKj7&index=22',
                'data'          => [
                    'google_meet_connected' => empty(etn_get_option('google_token')) ? 'no' : 'yes',
                    'google_meet_authorize_url' => !empty(etn_get_option('google_meet_authorize_url')) ? etn_get_option('google_meet_authorize_url') : '',
                    'google_meet_redirect_url' => home_url().'/eventin-integration/google-auth',
                    'etn_meet_api' => etn_get_option('etn_meet_api') ? 'yes' : 'no',
                    'google_meet_client_id' => !empty(etn_get_option('google_meet_client_id'))?etn_get_option('google_meet_client_id'):'',
                    'google_meet_client_secret_key' => !empty(etn_get_option('google_meet_client_secret_key'))?etn_get_option('google_meet_client_secret_key'):'',
                ],
                'badge_tags'    => ['Pro'],
            ],
            'google_map' => [
                'name'          => 'google_map',
                'slug'          => 'google_map',
                'type'          => 'integration',
                'status'        => (etn_get_option('etn_googlemap_api') && etn_get_option('etn_googlemap_api') !== 'off') ? 'on' : 'off',
                'is_pro'        => true,
                'title'         => __('Google Map', 'eventin'),
                'description'   => __('Google Map Integration for Eventin.', 'eventin'),
                'icon'          => ExtensionIcon::get('google-map'),
                'notice'        => '',
                'demo_link'     => 'https://product.themewinter.com/eventin/',
                'settings_link' => '',
                'doc_link'      => 'https://support.themewinter.com/docs/plugins/plugin-docs/event/event-location/?utm_source=documentations&utm_medium=eventin&utm_campaign=eventin+documentations',
                'data'          => [
                    'etn_googlemap_api'  =>  etn_get_option('etn_googlemap_api') ? 'no' : 'yes',
                    'google_api_key' => !empty(etn_get_option('google_api_key')) ? etn_get_option('google_api_key') : ''
                ],
                'badge_tags'    => ['Pro'],
            ],
            'eventin_ai' => [
                'name'          => 'eventin_ai',
                'slug'          => 'eventin_ai',
                'type'          => 'addon',
                'status'        => (etn_get_option('etn_ai_api') && etn_get_option('etn_ai_api') !== 'off') ? 'on' : 'off',
                'is_pro'        => true,
                'title'         => __('Eventin AI', 'eventin'),
                'description'   => __('Eventin AI Integration for Eventin.', 'eventin'),
                'icon'          => ExtensionIcon::get('eventin-ai'),
                'notice'        => '',
                'demo_link'     => 'https://product.themewinter.com/eventin/',
                'settings_link' => '',
                'doc_link'      => 'https://support.themewinter.com/docs/plugins/plugin-docs/integration/ai-integration/?utm_source=documentations&utm_medium=eventin&utm_campaign=eventin+documentations',
                'data'          => [
                    'eventin_ai' => etn_get_option('eventin_ai') ? etn_get_option('eventin_ai') : 'off',
                    'eventin_ai_auth_key' => !empty(etn_get_option('eventin_ai_auth_key')) ? etn_get_option('eventin_ai_auth_key') : '',
                    'etn_ai_api' => etn_get_option('etn_ai_api') ? etn_get_option('etn_ai_api') : 'off',
                ],
                'badge_tags'    => ['Pro'],
            ],
        ];

        $extensions['eventin-addon-for-surecart'] = [
            'name'          => 'eventin-addon-for-surecart',
            'slug'          => 'eventin-addon-for-surecart',
            'type'          => 'addon',
            'status'        => 'off',
            'is_pro'        => false,
            'deps'          => ['surecart'],
            'title'         => __('Eventin Surecart Addon', 'eventin'),
            'description'   => __('It allows eventin to sell tickets through surecart checkout.', 'eventin'),
            'icon'          => ExtensionIcon::get('sure_cart'),
            'demo_link'     => 'https://product.themewinter.com/eventin/',
            'settings_link' => '',
            'doc_link'      => 'https://support.themewinter.com/docs/plugins/plugin-docs/payment-type/how-to-configure-surecart-in-eventin',
            'notice'        => __('NB: Need to activate Surecart plugin', 'eventin'),
            'badge_tags'    => ['Free', 'New'],
        ];
        $extensions['eventin-addon-for-fluentcart'] = [
            'name'          => 'eventin-addon-for-fluentcart',
            'slug'          => 'eventin-addon-for-fluentcart',
            'type'          => 'addon',
            'status'        => 'off',
            'is_pro'        => false,
            'deps'          => ['fluent-cart'],
            'title'         => __('Eventin Addon for FluentCart', 'eventin'),
            'description'   => __('It allows eventin to sell tickets through FluentCart checkout.', 'eventin'),
            'icon'          => ExtensionIcon::get('fluent_cart'),
            'demo_link'     => 'https://product.themewinter.com/eventin/',
            'settings_link' => '',
            'doc_link'      => 'https://support.themewinter.com/docs/plugins/plugin-docs/integration/how-to-integrate-fluentcart-with-eventin/',
            'notice'        => __('NB: Need to activate FluentCart plugin', 'eventin'),
            'badge_tags'    => ['Free', 'New'],
        ];
        $extensions['aisentic'] = [
            'name'          => 'aisentic',
            'slug'          => 'aisentic',
            'type'          => 'addon',
            'status'        => 'on',
            'is_pro'        => false,
            'deps'          => ['aisentic'],
            'title'         => __('Aisentic Addon for Eventin', 'eventin'),
            'description'   => __('AI-powered assistant for Eventin to help manage events more efficiently.', 'eventin'),
            'icon'          => ExtensionIcon::get('aisentic'),
            'demo_link'     => 'https://product.themewinter.com/eventin/',
            'settings_link' => '',
            'doc_link'      => 'https://support.themewinter.com/docs/plugins/plugin-docs/gettings-started-aisentic/how-to-manage-eventin-events-using-aisentics-ai-chatbot/',
        ];

        return $extensions;
    }
}
