<?php

use Eventin\Eventin;
use Eventin\Upgrade\Upgrade;
use Eventin\Upgrade\Upgraders\V_3_3_57;

defined('ABSPATH') || exit;

/**
 * Plugin Name:       Eventin
 * Plugin URI:        https://themewinter.com/eventin/
 * Description:       Simple and Easy to use Event Management Solution
 * Version:           4.1.9
 * Author:            Themewinter
 * Author URI:        https://themewinter.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       eventin
 * Domain Path:       /languages
 * Requires at least: 6.2
 * Requires PHP:      7.4
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once plugin_dir_path(__FILE__) . '/utils/functions.php';

class Wpeventin
{

    /**
     * Instance of self
     *
     * @since 2.4.3
     *
     * @var Wpeventin
     */
    public static $instance = null;

	/**
	 * Plugin Version
	 *
	 * @since 2.4.3
	 *
	 * @var string The plugin version.
	 */
	public static function version() {
		return "4.1.9";
	}
    /**
     * Initializes the Wpeventin() class
     *
     * Checks for an existing Wpeventin() instance
     * and if it doesn't find one, creates it.
     */
    public static function init()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Instance of Wpeventin
     */
    private function __construct()
    {

        $this->define_constants();

        $this->activate();
        $this->deactivate();

        add_action('plugins_loaded', [$this, 'initialize_modules'], 999);
    }

    /**
     * Define Plugin Constants
     *
     * @return void
     */
    public function define_constants()
    {
        // handle demo site features.
        define('ETN_ASSETS', self::assets_dir());
        define('ETN_PLUGIN_TEMPLATE_DIR', self::templates_dir());
        define('ETN_THEME_TEMPLATE_DIR', self::theme_templates_dir());
        define('ETN_DEMO_SITE', false);
        if (ETN_DEMO_SITE === true) {
            define('ETN_EVENT_TEMPLATE_ONE_ID', '41');
            define('ETN_EVENT_TEMPLATE_TWO_ID', '13');
            define('ETN_EVENT_TEMPLATE_THREE_ID', '39');

            define('ETN_SPEAKER_TEMPLATE_ONE_ID', '19');
            define('ETN_SPEAKER_TEMPLATE_TWO_LITE_ID', '20');
            define('ETN_SPEAKER_TEMPLATE_TWO_ID', '17');
            define('ETN_SPEAKER_TEMPLATE_THREE_ID', '21');
        }

        define('ETN_DEFAULT_TICKET_NAME', 'DEFAULT');

        global $wpdb;
        define('ETN_EVENT_PURCHASE_HISTORY_TABLE', $wpdb->prefix . 'etn_events');
        define('ETN_EVENT_PURCHASE_HISTORY_META_TABLE', $wpdb->prefix . 'etn_trans_meta');
    }

    /**
     * Initialize Modules
     *
     * @since 2.4.3
     */
    public function initialize_modules()
    {
        do_action('eventin/before_load');

        Eventin::instance();

        if (class_exists('Wpeventin_Pro') && version_compare(Wpeventin_Pro::version(), '4.0.16', '>')) {
            do_action('eventin/after_load');
        }

        $this->load_composer_packages();
    }

    /**
     * Theme's Templates Folder Directory Path
     *
     * @since 2.4.3
     *
     * @return string
     */
    public static function theme_templates_dir()
    {
        return trailingslashit('/eventin/templates');
    }

    /**
     * Templates Folder Directory Path
     *
     * @since 2.4.3
     *
     * @return string
     */
    public static function templates_dir()
    {
        return trailingslashit(self::plugin_dir() . 'templates');
    }

    /**
     * Utils Folder Directory Path
     *
     * @since 2.4.3
     *
     * @return string
     */
    public static function utils_dir()
    {
        return trailingslashit(self::plugin_dir() . 'utils');
    }

    /**
     * Widgets Directory Url
     *
     * @return string
     */
    public static function widgets_url()
    {
        return trailingslashit(self::plugin_url() . 'widgets');
    }

    /**
     * Widgets Folder Directory Path
     *
     * @since 2.4.3
     *
     * @return string
     */
    public static function widgets_dir()
    {
        return trailingslashit(self::plugin_dir() . 'widgets');
    }

    /**
     * Assets Directory Url
     *
     * @return string
     */
    public static function assets_url()
    {
        return trailingslashit(self::plugin_url() . 'assets');
    }

    /**
     * Assets Folder Directory Path
     *
     * @since 2.4.3
     *
     * @return string
     */
    public static function assets_dir()
    {
        return trailingslashit(self::plugin_dir() . 'assets');
    }

    /**
     * Plugin Core File Directory Url
     *
     * @since 2.4.3
     *
     * @return string
     */
    public static function core_url()
    {
        return trailingslashit(self::plugin_url() . 'core');
    }

    /**
     * Plugin Core File Directory Path
     *
     * @since 2.4.3
     *
     * @return string
     */
    public static function core_dir()
    {
        return trailingslashit(self::plugin_dir() . 'core');
    }

    /**
     * Plugin Url
     *
     * @since 2.4.3
     *
     * @return string
     */
    public static function plugin_url($path = '')
    {
        return trailingslashit(plugin_dir_url(self::plugin_file())) . $path;
    }

    /**
     * Plugin Directory Path
     *
     * @since 2.4.3
     *
     * @return string
     */
    public static function plugin_dir()
    {
        return trailingslashit(plugin_dir_path(self::plugin_file()));
    }

    /**
     * Plugins Basename
     *
     * @since 2.4.3
     *
     * @return string
     */
    public static function plugins_basename()
    {
        return plugin_basename(self::plugin_file());
    }

    /**
     * Plugin File
     *
     * @since 2.4.3
     *
     * @return string
     */
    public static function plugin_file()
    {
        return __FILE__;
    }

    /**
     * Initialize on plugin activation
     *
     * @return  void
     */
    public function activate()
    {
        register_activation_hook($this->plugin_file(), [$this, 'activate_actions']);
    }

    /**
     * Run on deactivation hook
     *
     * @return  void
     */
    public function deactivate()
    {
        register_deactivation_hook($this->plugin_file(), [$this, 'deactivate_actions']);
    }

    /**
     * Run on deactivation hooks
     *
     * @return  void
     */
    public function deactivate_actions()
    {
        $current_version = self::version();

        if ('4.0.0' == $current_version) {
            $v3_3_57 = new V_3_3_57();

            $v3_3_57->run();
        }
    }

    /**
     * Fire on activation hook
     *
     * @return  void
     */
    public function activate_actions()
    {
        Upgrade::register();

        // Update plugin version and existing user roles.
        $version         = get_option('etn_version', true);
        $current_version = self::version();

        delete_transient('etn_event_list');

        flush_rewrite_rules();
    }

    /**
     * @return void
     */
    public function load_composer_packages()
    {
        if (file_exists(plugin_dir_path(__FILE__) . '/vendor/autoload.php')) {
            require_once plugin_dir_path(__FILE__) . '/vendor/autoload.php';
        }

        // load UninstallerForm plugin
        $this->load_uninstallerform_package();

        $etn_addons_options      = get_option('etn_addons_options') ?? [];
        $is_automation_module_on = "off";
        if (is_array($etn_addons_options)) {
            $is_automation_module_on = $etn_addons_options["automation"] ?? "off";
        }

        // check if automation module is on
        if ('on' === $is_automation_module_on) {
            $this->load_automation_package();
        }
    }

    private function load_uninstallerform_package()
    {
        if (class_exists('UninstallerForm\UninstallerForm') && is_callable(['\UninstallerForm\UninstallerForm', 'init'])) {

            $reflection = new ReflectionMethod('\UninstallerForm\UninstallerForm', 'init');

            // Maximum number of parameters allowed
            $totalParams = $reflection->getNumberOfParameters();

            if ($totalParams === 6) {
                add_filter('rest_request_before_callbacks', function ($response, $handler, $request) {
                    if ($request->get_route() === '/eventin/v1/feedback') {
                        $params = $request->get_json_params();

                        if (empty($params['email'])) {
                            $params['email'] = get_option('admin_email');
                            $request->set_body(wp_json_encode($params));
                        }
                    }
                    return $response;
                }, 10, 3);

                \UninstallerForm\UninstallerForm::init(
                    'Eventin', // Plugin name
                    'eventin', // Plugin Slug
                    __FILE__,
                    'eventin',       // Text Domain Name
                    'etn-dashboard', // plugins-admin-script-handler
                    'https://themewinter.com/?fluentcrm=1&route=contact&hash=50d358fa-e039-4459-a3d0-ef73b3c7d451'
                );
            } else {
                add_filter('rest_request_before_callbacks', function ($response, $handler, $request) {
                    if ($request->get_route() === '/eventin/v1/feedback') {
                        $params = $request->get_json_params();

                        if (empty($params['email'])) {
                            $params['email'] = get_option('admin_email');
                            $request->set_body(wp_json_encode($params));
                        }
                    }
                    return $response;
                }, 10, 3);

                \UninstallerForm\UninstallerForm::init(
                    'Eventin', // Plugin name
                    'eventin', // Plugin Slug
                    __FILE__,
                    'eventin', // Text Domain Name
                    'etn-dashboard'
                );
            }
        }
    }

    private function load_automation_package()
    {
        if (class_exists(\Ens\Core\SDK::class)) {
            \Ens\Core\SDK::get_instance()->setup([
                'plugin_name'          => 'Eventin',
                'plugin_slug'          => 'eventin',
                'general_prefix'       => 'eve',
                'text_domain'          => 'eventin',
                'admin_script_handler' => 'etn-dashboard',
                'sub_menu_filter_hook' => 'eventin_menu',
                'sub_menu_details'     => [
                    'title'      => 'Email Automation',
                    'capability' => 'manage_options',
                    'url'        => 'admin.php?page=' . 'eventin' . '#/automation',
                    'position'   => 8,
                ],
            ])
                ->init();

            add_filter('ens_eve_available_actions', function ($actions) {
                $actions = [ // Array of all actions, on which you want to send email
                    [
                        "trigger_label"            => "Event Ticket Purchase", // Name of the event
                        "trigger_value"            => "event_ticket_purchase", // Event slug
                        "trigger_data"             => [                        // Data you have after the event happened
                            [
                                "label" => "Site Name",
                                "value" => "site_name",
                                "type"  => "string",
                            ],
                            [
                                "label" => "Site Link",
                                "value" => "site_link",
                                "type"  => "string",
                            ],
                            [
                                "label" => "Site Logo",
                                "value" => "site_logo",
                                "type"  => "string",
                            ],
                            [
                                "label" => "Event Title",
                                "value" => "event_title",
                                "type"  => "string",
                            ],
                            [
                                "label" => "Event Date",
                                "value" => "event_date",
                                "type"  => "date",
                            ],
                            [
                                "label" => "Event Time",
                                "value" => "event_time",
                                "type"  => "string",
                            ],
                            [
                                "label" => "Event Location",
                                "value" => "event_location",
                                "type"  => "string",
                            ],
                        ],
                        "conditional_dependencies" => [ // Data you have after the event happened
                            [
                                "label" => "Event Title",
                                "value" => "event_title",
                                "type"  => "string",
                            ],
                        ],
                        "delay_dependencies"       => [
                            [
                                "label" => "After Booking Time",
                                "value" => "after_booking_time",
                            ],
                        ],
                        "email_receivers"          => [
                            [
                                "label" => "Attendee",
                                "value" => "attendee_email",
                            ],
                            [
                                "label" => "Customer",
                                "value" => "customer_email",
                            ],
                            [
                                "label" => "Admin",
                                "value" => "admin_email",
                            ],
                        ],
                    ],
                    [
                        "trigger_label"            => "RSVP Email",       // Name of the event
                        "trigger_value"            => "event_rsvp_email", // Event slug
                        "trigger_data"             => [                   // Data you have after the event happened
                            [
                                "label" => "Site Name",
                                "value" => "site_name",
                                "type"  => "string",
                            ],
                            [
                                "label" => "Site Link",
                                "value" => "site_link",
                                "type"  => "string",
                            ],
                            [
                                "label" => "Event Title",
                                "value" => "event_title",
                                "type"  => "string",
                            ],
                            [
                                "label" => "Event Date",
                                "value" => "event_date",
                                "type"  => "date",
                            ],
                            [
                                "label" => "Event Time",
                                "value" => "event_time",
                                "type"  => "string",
                            ],
                            [
                                "label" => "Event Location",
                                "value" => "event_location",
                                "type"  => "string",
                            ],

                        ],
                        "conditional_dependencies" => [ // Data you have after the event happened
                            [
                                "label" => "Event Title",
                                "value" => "event_title",
                                "type"  => "string",
                            ],
                        ],
                        "delay_dependencies"       => [
                            [
                                "label" => "After Registration Time",
                                "value" => "after_registration_time",
                            ],
                        ],
                        "email_receivers"          => [
                            [
                                "label" => "Attendee",
                                "value" => "attendee_email",
                            ],
                        ],
                    ],
                    [
                        "trigger_label"            => "Event Reminder Email", // Name of the event
                        "trigger_value"            => "event_reminder_email", // Event slug
                        "trigger_data"             => [                       // Data you have after the event happened
                            [
                                "label" => "Site Name",
                                "value" => "site_name",
                                "type"  => "string",
                            ],
                            [
                                "label" => "Site Link",
                                "value" => "site_link",
                                "type"  => "string",
                            ],
                            [
                                "label" => "Event Title",
                                "value" => "event_title",
                                "type"  => "string",
                            ],
                            [
                                "label" => "Event Date",
                                "value" => "event_date",
                                "type"  => "date",
                            ],
                            [
                                "label" => "Event Time",
                                "value" => "event_time",
                                "type"  => "string",
                            ],
                            [
                                "label" => "Event Location",
                                "value" => "event_location",
                                "type"  => "string",
                            ],
                        ],
                        "conditional_dependencies" => [ // Data you have after the event happened
                            [
                                "label" => "Event Title",
                                "value" => "event_title",
                                "type"  => "string",
                            ],
                        ],
                        "delay_dependencies"       => [
                            [
                                "label" => "Before Event Date",
                                "value" => "before_event_date",
                            ],
                        ],
                        "email_receivers"          => [
                            [
                                "label" => "Attendee",
                                "value" => "attendee_email",
                            ],
                        ],
                    ],
                    [
                        "trigger_label"            => "Send Email To All Attendees", // Name of the event
                        "trigger_value"            => "send_email_to_all_attendees", // Event slug
                        "trigger_data"             => [                              // Data you have after the event happened
                            [
                                "label" => "Site Name",
                                "value" => "site_name",
                                "type"  => "string",
                            ],
                            [
                                "label" => "Site Link",
                                "value" => "site_link",
                                "type"  => "string",
                            ],
                            [
                                "label" => "Event Title",
                                "value" => "event_title",
                                "type"  => "string",
                            ],
                            [
                                "label" => "Event Date",
                                "value" => "event_date",
                                "type"  => "date",
                            ],
                            [
                                "label" => "Event Time",
                                "value" => "event_time",
                                "type"  => "string",
                            ],
                            [
                                "label" => "Event Location",
                                "value" => "event_location",
                                "type"  => "string",
                            ],
                        ],
                        "conditional_dependencies" => [ // Data you have after the event happened
                            [
                                "label" => "Event Title",
                                "value" => "event_title",
                                "type"  => "string",
                            ],
                        ],
                        "delay_dependencies"       => [
                            [
                                "label" => "Before Event Date",
                                "value" => "before_event_date",
                            ],
                        ],
                        "email_receivers"          => [
                            [
                                "label" => "Attendee",
                                "value" => "attendee_email",
                            ],
                        ],
                    ],
                ];

                return $actions;
            });
        }
    }
}

/**
 * Load Wpeventin plugin when all plugins are loaded
 *
 * @return Wpeventin
 */
function wpeventin()
{
    return Wpeventin::init();
}

// Let's Go...
wpeventin();
