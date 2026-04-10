<?php
namespace Eventin\Template\Api;

use WP_REST_Controller;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;
use Plugin_Upgrader;
use WP_Ajax_Upgrader_Skin;

class TemplateBuilderController extends WP_REST_Controller {
    /**
     * Constructor for TemplateBuilderController
     *
     * @return void
     */
    public function __construct() {
        $this->namespace = 'eventin/v2';
        $this->rest_base = 'templates/template-builder';
    }

    /**
     * Register the routes for the objects of the controller.
     *
     * @return void
     */
    public function register_routes() {
        // GET /wp-json/eventin/v2/templates/template-builder
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base,
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [$this, 'get_template_builders'],
                    'permission_callback' => [$this, 'get_items_permissions_check'],
                ],
            ]
        );

        // POST /wp-json/eventin/v2/templates/template-builder/activate
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/activate',
            [
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [$this, 'activate_template_builder'],
                    'permission_callback' => [$this, 'create_item_permissions_check'],
                ],
            ]
        );
    }

    /**
     * Get a collection of template builders
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function get_template_builders($request) {
        $builders = [
            [
                'id' => 'elementor',
                'name' => 'Elementor',
                'is_active' => $this->is_template_builder_active_for( 'elementor' ),
            ],
            [
                'id' => 'gutenberg',
                'name' => 'Gutenberg',
                'is_active' => $this->is_template_builder_active_for( 'gutenberg' ),
            ],
            // Add more builders as needed
        ];

        return rest_ensure_response( $builders );
    }

    /**
     * Check if a template builder is active and installed
     *
     * @param string $template_builder_id The ID of the template builder to check
     * @return bool Whether the template builder is active and installed
     */
    private function is_template_builder_active_for( $template_builder_id ) {
        if ( 'gutenberg' === $template_builder_id ) {
            return true; // Gutenberg is always available in WordPress 5.0+
        }

        if ( 'elementor' === $template_builder_id ) {
            // Check if Elementor is installed and activated
            $is_elementor_active = did_action( 'elementor/loaded' );
            
            // Additional check in case the action wasn't triggered yet
            if ( ! $is_elementor_active ) {
                $is_elementor_active = class_exists( '\Elementor\Plugin' );
            }
            
            return (bool) $is_elementor_active;
        }

        return false;
    }

    /**
     * Check if a given request has access to get items.
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|bool
     */
    public function get_items_permissions_check( $request ) {
        return current_user_can('manage_options');
    }

    /**
     * Activate a template builder
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function activate_template_builder( $request ) {
        $parameters = json_decode( $request->get_body(), true ) ?? [];
        $builder_id = isset($parameters['builder_id']) ? sanitize_text_field($parameters['builder_id']) : '';

        if ( empty( $builder_id ) ) {
            return new WP_Error( 'missing_parameter', __('Builder ID is required', 'eventin'), [ 'status' => 400 ] );
        }

        if ( 'elementor' === $builder_id ) {
            if ( $this->is_plugin_active( 'elementor/elementor.php' ) ) {
                return new WP_Error( 
                    'template_builder_already_active',
                    __( 'This template builder is already installed and active', 'eventin' ),
                    [ 'statuts' => 409 ]
                );
            }

            if ( ! current_user_can( 'install_plugins' ) || ! current_user_can( 'activate_plugins' ) ) {
                return new WP_Error(
                    'insufficient_permissions',
                    __( 'You do not have sufficient permissions to install or activate plugins.', 'eventin' ),
                    [ 'status' => 403 ]
                );
            }

            // Include required files for plugin installation
            if ( ! function_exists( 'plugins_api' ) ) {
                require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
            }
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
            require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
            require_once ABSPATH . 'wp-admin/includes/class-wp-ajax-upgrader-skin.php';
            require_once ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php';

            $result = $this->install_and_activate_plugin( 'elementor' );
            
            if ( is_wp_error( $result ) ) {
                return $result;
            }

            return rest_ensure_response([
                'builder_id' => $builder_id,
                'is_active'  => $this->is_template_builder_active_for( 'elementor' ),
            ]);
        }

        // For other builders or default case
        return rest_ensure_response([
            'builder_id' => $builder_id,
            'is_active'  => $this->is_template_builder_active_for( 'gutenberg' ),
        ]);
    }

    /**
     * Check if a plugin is active
     *
     * @param string $plugin_file Plugin file path relative to plugins directory
     * @return bool
     */
    private function is_plugin_active( $plugin_file ) {
        if ( ! function_exists( 'is_plugin_active' ) ) {
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        return is_plugin_active( $plugin_file );
    }

    /**
     * Install and activate a plugin
     *
     * @param string $plugin_slug Plugin slug (e.g., 'elementor')
     * @return true|WP_Error True on success, WP_Error on failure
     */
    private function install_and_activate_plugin( $plugin_slug ) {
        if ( ! function_exists( 'request_filesystem_credentials' ) ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
            require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
            require_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        $plugin_file = $plugin_slug . '/' . $plugin_slug . '.php';

        if ( file_exists( WP_PLUGIN_DIR . '/' . $plugin_file ) ) {
            $activate = activate_plugin( $plugin_file );
            if ( is_wp_error( $activate ) ) {
                return new WP_Error(
                    'activation_failed',
                    sprintf(
                        // translators: %1$s is the plugin slug, %2$s is the error message.
                        __( 'Failed to activate %1$s: %2$s', 'eventin' ),
                        $plugin_slug,
                        $activate->get_error_message()
                    ),
                    [ 'status' => 500 ]
                );
            }

            // Elementor specific config after activation
            if ( 'elementor' === $plugin_slug ) {
                $this->enable_etn_templates_for_elementor();
            }

            return true;
        }

        // Plugin not installed, proceed with installation
        $api = plugins_api( 'plugin_information', [
            'slug'   => $plugin_slug,
            'fields' => [ 'sections' => false ],
        ] );

        if ( is_wp_error( $api ) ) {
            return new WP_Error(
                'plugin_info_failed',
                sprintf(
                    // translators: %1$s is the plugin slug, %2$s is the error message.
                    __( 'Could not get plugin information for %1$s: %2$s', 'eventin' ),
                    $plugin_slug,
                    $api->get_error_message()
                ),
                [ 'status' => 500 ]
            );
        }

        $upgrader = new Plugin_Upgrader( new WP_Ajax_Upgrader_Skin() );
        $install  = $upgrader->install( $api->download_link );

        if ( is_wp_error( $install ) ) {
            return new WP_Error(
                'installation_failed',
                sprintf(
                    // translators: %1$s is the plugin slug, %2$s is the error message.
                    __( 'Failed to install %1$s: %2$s', 'eventin' ),
                    $plugin_slug,
                    $install->get_error_message()
                ),
                [ 'status' => 500 ]
            );
        }

        $activate = activate_plugin( $plugin_file );
        if ( is_wp_error( $activate ) ) {
            return new WP_Error(
                'activation_failed',
                sprintf(
                    // translators: %1$s is the plugin slug, %2$s is the error message.
                    __( 'Failed to activate %1$s: %2$s', 'eventin' ),
                    $plugin_slug,
                    $activate->get_error_message()
                ),
                [ 'status' => 500 ]
            );
        }

        // Elementor specific config after install + activation
        if ( 'elementor' === $plugin_slug ) {
            $this->enable_etn_templates_for_elementor();
        }

        return true;
    }

    /**
     * Ensure ETN Templates post type is enabled for Elementor.
     */
    private function enable_etn_templates_for_elementor() {
        $cpt_support = get_option( 'elementor_cpt_support', [] );

        if ( ! is_array( $cpt_support ) ) {
            $cpt_support = [];
        }

        if ( ! in_array( 'etn-template', $cpt_support, true ) ) {
            $cpt_support[] = 'etn-template';
            update_option( 'elementor_cpt_support', $cpt_support );
        }
    }

    /**
     * Check if a given request has access to create items.
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|bool
     */
    public function create_item_permissions_check( $request ) {
        return current_user_can( 'manage_options' );
    }

}