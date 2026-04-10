<?php
namespace Eventin\Enqueue;

use Wpeventin;

/**
 * Admin class
 */
class Admin {
    /**
     * Initialize the class
     *
     * @return  void
     */
    public function __construct() {
        add_action( 'admin_enqueue_scripts', [$this, 'enqueue_scripts'] );
        add_action( 'elementor/frontend/before_enqueue_scripts', array( $this, 'elementor_js' ) );
    }

    public function i18n_loader() {
        $data = [
            'baseUrl'     => false,
            'locale'      => determine_locale(),
            'domainMap'   => [],
            'domainPaths' => [],
        ];
        
        $lang_dir    = WP_LANG_DIR;
        $content_dir = WP_CONTENT_DIR;
        $abspath     = ABSPATH;
        
        if ( strpos( $lang_dir, $content_dir ) === 0 ) {
            $data['baseUrl'] = content_url( substr( trailingslashit( $lang_dir ), strlen( trailingslashit( $content_dir ) ) ) );
        } elseif ( strpos( $lang_dir, $abspath ) === 0 ) {
            $data['baseUrl'] = site_url( substr( trailingslashit( $lang_dir ), strlen( untrailingslashit( $abspath ) ) ) );
        }
        
        wp_enqueue_script('eventin-i18n');
        
        $data['domainMap']   = (object) $data['domainMap']; // Ensure it becomes a json object.
        $data['domainPaths'] = (object) $data['domainPaths']; // Ensure it becomes a json object.
        wp_add_inline_script( 'eventin-i18n', 'if (typeof wp.eventinI18nLoader === "undefined") { wp.eventinI18nLoader = {}; } wp.eventinI18nLoader.state = ' . wp_json_encode( $data, JSON_UNESCAPED_SLASHES ) . ';' );
    }

    /**
     * Enqueue scripts and styles
     *
     * @return  void
     */
    public function enqueue_scripts( $top ) {
        wp_enqueue_style( 'etn-event-manager-admin' ); 

        if($top == 'plugins.php'){
            wp_enqueue_style( 'etn-feedback-modal-styles' );
            wp_enqueue_script('etn-fedback-modal-js');
        }
        
        $screens = [
            'toplevel_page_eventin',
            'eventin_page_etn-event-shortcode',
            'eventin_page_etn_addons',
            'eventin_page_etn-license',
            'eventin_page_eventin_get_help',
            'admin_page_etn-wizard',
            'plugins.php',
        ];

        if ( ! in_array( $top, $screens ) ) {
            return;
        }

        wp_enqueue_style( 'etn-dashboard' );
        
        // Block editor styles and scripts 
        do_action('enqueue_block_assets');
        $settings = etn_editor_settings();
        wp_add_inline_script( 'etn-dashboard', 'window.eventinEditorSettings = ' . wp_json_encode( $settings ) . ';' );
        wp_enqueue_script('wp-edit-post');

        wp_enqueue_style( 'etn-public-css' );
        
        
        //experimental enqueue by Sajib
        wp_enqueue_script('etn-dashboard' , plugins_url('build/js/dashboard.js', __FILE__), array('wp-edit-post'), \Wpeventin::version(), true);
        
        /**
         * @method wp_set_script_translations
         * It helps to load the translation file for the script
         */ 
        wp_set_script_translations( 'etn-dashboard', 'eventin' );

        wp_localize_script('etn-dashboard' , 'eventinData', array(
        'publicPath' => plugins_url('../../build/', __FILE__),
        ));

        $this->i18n_loader();

        $screen    = get_current_screen();
        $screen_id = $screen->id;
        
        if ( 'toplevel_page_eventin' === $screen_id && class_exists( 'EventinAI' ) ) {
            wp_enqueue_style( 'etn-ai' );
            wp_enqueue_script( 'etn-ai' );
        }

        
        wp_enqueue_script( 'wp-color-picker' );
        wp_enqueue_script( 'media-upload' );
        wp_set_script_translations( 'etn-app-index', 'eventin' );
        wp_enqueue_script( 'etn-app-index' );
        
        // Enqueue the WordPress editor scripts
        wp_enqueue_editor();
        //setting pro translations for pro components via hooks
        wp_set_script_translations( 'etn-script-pro', 'eventin-pro' );
        
       


        if ( ! did_action( 'wp_enqueue_media' ) ) {
            wp_enqueue_media();
        }

        if ( ! wp_style_is( 'wp-color-picker', 'enqueued' ) ) {
            wp_enqueue_style( 'wp-color-picker' );
        }
        
        wp_enqueue_style( 'etn-app-index' ); 
        
        wp_enqueue_style( 'etn-onboard-index' );
        wp_enqueue_script( 'etn-onboard-index' );
        wp_set_script_translations( 'etn-onboard-index', 'eventin' );
        $localize_data = etn_get_locale_data();
        wp_localize_script( 'etn-onboard-index', 'localized_data_obj', $localize_data );
        wp_enqueue_style( 'etn-icon' );
        // Enque block editor style in events create and edit pages only
        if ( isset( $_GET['page'] ) && $_GET['page'] === 'eventin' ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- admin script enqueue condition; page param compared to a literal string only.
            wp_enqueue_style( 'wp-block-editor' );
        }
    }

    /**
     * Enqueue Elementor Assets
     *
     * @return void
     */
    public function elementor_js() {
        wp_enqueue_script( 'etn-elementor-inputs', \Wpeventin::assets_url() . 'js/elementor.js', array( 'elementor-frontend' ), \Wpeventin::version(), true );
        wp_enqueue_script( 'etn-app-index' );
    }
}