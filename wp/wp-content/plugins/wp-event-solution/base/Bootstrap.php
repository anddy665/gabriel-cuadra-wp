<?php
namespace Eventin;

use Eventin\Admin\AdminProvider;
use Eventin\Blocks\BlockProvider;
use Eventin\Attendee\AttendeeProvider;
use Eventin\Event\EventProvider;
use Eventin\Interfaces\ProviderInterface;
use Eventin\Order\OrderProvider;
use Eventin\Schedule\ScheduleProvider;
use Eventin\Speaker\SpeakerProvider;
use Eventin\Base\Speaker_role;
use Eventin\Emails\EmailHookProvider;
use Eventin\Template\TemplateProvider;
use Eventin\Upgrade\Upgraders\V_4_0_29;

/**
 * Class Bootstrap
 *
 * Handles the plugin's bootstrap process
 *
 * @package Eventin
 */
class Bootstrap {
    /**
     * Holds plugin's provider classes.
     *
     * @var array
     */
    protected static $providers = [
        AdminProvider::class,
        BlockProvider::class,
        OrderProvider::class,
        EventProvider::class,
        AttendeeProvider::class,
        ScheduleProvider::class,
        SpeakerProvider::class,
        EmailHookProvider::class,
        TemplateProvider::class,
    ];

    /**
     * Runs the plugins bootstrap
     *
     * @return  void
     */
    public static function run(): void {
        self::include_require_files();
	    self::init_classes();
        add_action( 'init', [ self::class, 'init' ], 5 );
        add_action( 'rest_api_init', [ ApiManager::class, 'register' ] );
    }

    /**
     * Bootstraps the plugin. Load all necessary providers
     *
     * @return  void
     */
    public static function init(): void {
        self::register_providers();
        CustomEndpoint::register();
        self::register_cpt_modules();
		
		$seeder = new V_4_0_29();
		$seeder->run();
    }

    /**
     * Registers providers
     *
     * @return  void
     */
    protected static function register_providers(): void {
        foreach ( self::$providers as $provider ) {
            if ( class_exists( $provider ) && is_subclass_of( $provider, ProviderInterface::class ) ) {
                new $provider();
            }
        }
    }

    /**
     * Init required classes
     *
     * @return  void
     */
    private static function init_classes() {
        \Etn\Core\Woocommerce\Base::instance()->init();
        \Etn\Core\Shortcodes\Hooks::instance()->init();
        \Etn\Widgets\Manifest::instance()->init();

        // seat plan
        if ( \Etn\Core\Addons\Helper::instance()->check_active_module( "seat_map" ) ) {
            \Etn\Core\Modules\Seat_Plan\Seat_Plan::instance()->init();
        }

        new \Eventin\Enqueue\Register();

        // Instantiate Eventin AI module.
        \Etn\Core\Modules\Eventin_Ai\Eventin_AI::instance()->init();

        if ( etn_is_request( 'admin' ) ) {
            new \Eventin\Enqueue\Admin();
        }

        if ( etn_is_request( 'frontend' ) ) {
            new \Eventin\Enqueue\Frontend();
        }

        Speaker_role::instance()->init();

        \Etn\Core\Admin\Hooks::instance()->init();
        
        // Dependency Controls
        new \Etn\Core\Event\DependencyControls();
    }

    /**
     * Inlcude require files
     *
     * @return  void
     */
    private static function include_require_files() {
        include_once \Wpeventin::plugin_dir() . 'core/event/template-functions.php';
        include_once \Wpeventin::plugin_dir() . 'core/woocommerce/etn-product-data-store-cpt.php';
        include_once \Wpeventin::plugin_dir() . '/core/woocommerce/etn-order-item-product.php';
        include_once \Wpeventin::plugin_dir() . 'core/wpml/init.php';

        require_once \Wpeventin::plugin_dir() . '/utils/banner/banner.php';
        require_once \Wpeventin::plugin_dir() . '/utils/pro-awareness/pro-awareness.php';

        require_once \Wpeventin::plugin_dir() . '/core/speaker/template-functions.php';
        require_once \Wpeventin::plugin_dir() . '/core/speaker/template-hooks.php';
    }

    private static function register_cpt_modules(){
        // CPT Modules
        \Etn\Core\Event\Hooks::instance()->init();
        \Etn\Core\Recurring_Event\Hooks::instance()->init();
        \Etn\Core\Schedule\Hooks::instance()->init();
        \Etn\Core\Speaker\Hooks::instance()->init();
        \Etn\Core\Attendee\InfoUpdate::instance()->init();
        new \Etn\Core\Attendee\Cpt();
    }
}
