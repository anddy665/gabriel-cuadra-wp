<?php

/**
 * Extension Controller
 *
 * @package Eventin
 */

namespace Eventin\Extensions\Api;

defined( 'ABSPATH' ) || exit;
use Eventin\Extensions\Extension;
use Eventin\Extensions\ImportAutomation;
use Eventin\Extensions\PluginManager;
use Eventin\Input;
use Eventin\Settings;
use WP_Error;
use WP_HTTP_Response;
use WP_REST_Controller;

/**
 * Extension Controller
 *
 */
class ExtensionController extends WP_REST_Controller {

    /**
     * Store api namespace
     *
     * @since 4.0.13
     *
     * @var string $namespace
     */
    protected $namespace = 'eventin/v2';

    /**
     * Store rest base
     *
     * @since 4.0.13
     *
     * @var string $rest_base
     */
    protected $rest_base = 'extensions';

    /**
     * Register routes
     *
     * @return void
     */
    public function register_routes() {
        /*
         * Register route
         */
        register_rest_route( $this->namespace, $this->rest_base, [
            [
                'methods'             => \WP_REST_Server::READABLE,
                'callback'            => [$this, 'get_items'],
                'permission_callback' => function () {
                    return current_user_can( 'etn_manage_addons' );
                },
            ],
        ] );

        register_rest_route( $this->namespace, $this->rest_base, [
            [
                'methods'             => \WP_REST_Server::EDITABLE,
                'callback'            => [$this, 'update_item'],
                'permission_callback' => function () {
                    return current_user_can( 'etn_manage_addons' );
                },
            ],
        ] );

    }

    /**
     * Get all extensions
     *
     * @param   WP_Rest_Request  $request
     *
     * @return  WP_Rest_Response
     */
    public function get_items( $request ) {
        $type = ! empty( $request['type'] ) ? $request['type'] : 'all';

        $types = [
            'module' => Extension::modules(), 
            'addon'  => Extension::addons(),
            'plugin' => Extension::plugins(),
            'integration' => Extension::integrations(),
            'all_except_plugins' => Extension::all_except_plugins(),
            'all'    => Extension::get()
        ];

        return rest_ensure_response( $types[$type] );
    }

    /**
     * Enable or disable extension
     *
     * @param   WP_Rest_Request  $request  [$request description]
     *
     * @return  WP_Response | WP_Error
     */
    public function update_item( $request ) {
        $input_data = json_decode( $request->get_body(), true );

        $input  = new Input( $input_data );
        $name   = $input->get('name');
        $status = $input->get('status');

        $statuses = ['off', 'on', 'install', 'activate', 'deactivate'];

        if ( ! $name ) {
            return new WP_Error( 'extension_name_error', __( 'Please enter extension name', 'eventin' ), ['status' => 422] );
        }

        if (  ! $status ) {
            return new WP_Error( 'extension_status_error', __( 'Please enter status', 'eventin' ), ['status' => 422] );
        }

        if ( ! in_array( $status, $statuses ) ) {
            return new WP_Error( 'extension_status_error', __( 'Please enter status on/off', 'eventin' ), ['status' => 422] );
        }

        if ( ! Extension::find( $name ) ) {
            return new WP_Error( 'invalid_extension', __( 'Invalid extension.', 'eventin' ), ['status' => 422] );
        }

        if ( $name == 'zoom' ) {
            etn_update_option('etn_zoom_api',$status??null);
        }

        if ( $name == 'google_meet' ) {
            etn_update_option('etn_meet_api',$status??null);
        }

        if ( $name == 'google_map' ) {
            etn_update_option('etn_googlemap_api',$status??null);
        }

        if ( $name == 'eventin_ai' ) {
            etn_update_option('etn_ai_api',$status??null);
        }

        if ( $name == 'eventin-addon-for-surecart' ) {
            etn_update_option('etn_surecart_enabled',$status=='on'?true:false);
            if($status=='off'){
                etn_update_option('surecart_status',false);
            }
        }
        if ( $name == 'eventin-addon-for-fluentcart' ) {
            etn_update_option('etn_fluentcart_enabled',$status=='on'?true:false);
            if($status=='off'){
                etn_update_option('fluentcart_status',false);
            }
        }
        if ( $name == 'aisentic' ) {
            etn_update_option('etn_aisentic_enabled',$status=='on'?true:false);
            if($status=='off'){
                etn_update_option('aisentic_status',false);
            }
        }


        $update = Extension::update( $name, $status );

        if($name == 'automation' && $update == 1 && $status == 'on') {
            $is_email_automation_migrated = get_option( 'etn_email_automation_migrated' );
            if ( ! $is_email_automation_migrated ) {
                ImportAutomation::create_automation_flows();
            }
        }
	    
	    // Dokan Event Publish Approval
	    // Automatically publish Dokan Vendor/Seller Event when an event is created.
	    if ( $status == "on" ) {
		    Settings::update(["dokan_event_auto_publish" => "on"]);
	    }
	    if ( $status == "off" ) {
		    Settings::update(["dokan_event_auto_publish" => ""]);
	    }
		
        
        if ( is_wp_error( $update ) ) {
            return new WP_Error( 'update_error', wp_strip_all_tags($update->get_error_message()), ['status' => 422] );
        }

        if ( ! $update ) {
            // translators: %s is the extension status action (e.g. activated, deactivated).
            return new WP_Error( 'update_error', sprintf( __( "Extension couldn't %s", 'eventin' ), $status ), ['status' => 422] );
        }

        $response = [
            'message' => __( 'Successfully updated', 'eventin' ),
        ];

        return rest_ensure_response( $response );
    }
}