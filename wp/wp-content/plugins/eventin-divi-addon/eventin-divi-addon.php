<?php

/**
 *  @package eventin-addon-for-divi-Builder
 */
/**
 * Plugin Name:        Eventin Addon for Divi Builder
 * Requires Plugins:   wp-event-solution
 * Plugin URI:         https://product.themewinter.com/eventin
 * Description:        Simple and Easy to use Event Management Solution With Divi.
 * Version:            1.0.10
 * Author:             Themewinter
 * Author URI:         http://themewinter.com/
 * License:            GPL-2.0+
 * License URI:        http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:        eventin-divi-addon
 * Domain Path:       /languages
 * Requires PHP:      7.4
 */

defined('ABSPATH') || exit;

require_once ABSPATH . 'wp-admin/includes/plugin.php';

final class Eventin_Divi_Addon
{

    /**
     * Plugin Version
     *
     * @since 1.0.0
     *
     * @var string The plugin version.
     */
    static function version()
    {
        return '1.0.10';
    }

    /**
     * Instance of self
     *
     * @since 1.0.0
     *
     * @var Eventin_Divi_Addon
     */
    private static $instance = null;

    /**
     * Initializes the Eventin_Divi_Addon() class
     *
     * Checks for an existing Eventin_Divi_Addon() instance
     * and if it doesn't find one, creates it.
     */
    public static function init()
    {

        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Instance of Eventin_Divi_Addon
     */
    private function __construct()
    {
        // Instantiate Base Class after plugins loaded
        add_action('divi_extensions_init', [$this, 'initialize_module']);


        // check eventin plugin active or not
        if (!is_plugin_active('wp-event-solution/eventin.php') && ! class_exists('ET_Builder_Element')) {
            add_action('admin_notices', [$this, 'notice_eventin_not_active']);
            return;
        }
    }

    /**
     * check eventin plugin active or not
     *
     * @since 1.0.0
     */
    public function notice_eventin_not_active()
    {
        $class = 'notice notice-warning';
        $message = esc_html__('In order to use eventin divi module, you must have installed and activated Eventin Plugin and Divi theme builder',  'eventin-divi-addon');
        printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), esc_html($message));
    }


    /**
     * Initialize Module
     *
     * @since 1.0.0
     */
    public function initialize_module()
    {
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
        do_action('eventin_divi_addon_before_load');

        require_once self::plugin_dir() . 'includes/EventinDiviModule.php';
        // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
        do_action('eventin_divi_addon_after_load');
    }


    /**
     * Plugin Url
     *
     * @since 1.0.0
     *
     * @return void
     */
    public static function plugin_url()
    {
        return trailingslashit(plugin_dir_url(self::plugin_file()));
    }

    /**
     * Plugin Directory Path
     *
     * @since 1.0.0
     *
     * @return void
     */
    public static function plugin_dir()
    {
        return trailingslashit(plugin_dir_path(self::plugin_file()));
    }

    /**
     * Plugins Basename
     *
     * @since 1.0.0

     */
    public static function plugins_basename()
    {
        return plugin_basename(self::plugin_file());
    }

    /**
     * Plugin File
     *
     * @since 1.0.0
     *
     * @return void
     */
    public static function plugin_file()
    {
        return __FILE__;
    }
}

/**
 * Load Eventin Divi Addon when all plugins are loaded
 *
 * @return Eventin_Divi_Addon
 */
// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
function eventin_divi_addon_init()
{
    return Eventin_Divi_Addon::init();
}

// Let's Go...
eventin_divi_addon_init();
