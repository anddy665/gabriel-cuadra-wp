<?php

/**
 * Template Controller
 * 
 * @package Eventin
 */
namespace Eventin\Template\Api;

defined( 'ABSPATH' ) || exit;

use Eventin\Template\TemplateModel;
use Eventin\Template\StaticTemplate;
use WP_REST_Controller;
use WP_REST_Server;
use Eventin\Input;
use WP_Error;

class TemplateController extends WP_REST_Controller {
    /**
     * Constructor for TemplateController
     *
     * @return void
     */
    public function __construct() {
        $this->namespace = 'eventin/v2';
        $this->rest_base = 'templates';
    }

    /**
     * Check if a given request has access to get items.
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|boolean
     */
    public function register_routes() {
        register_rest_route( $this->namespace, $this->rest_base, [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [$this, 'get_items'],
                'permission_callback' => [$this, 'get_item_permissions_check'],
            ],
        ]);

        register_rest_route( $this->namespace, $this->rest_base, [
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [$this, 'create_item'],
                'permission_callback' => [$this, 'create_item_permission_check'],
            ],
        ]);

        register_rest_route( $this->namespace, $this->rest_base, [
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [$this, 'delete_items'],
                'permission_callback' => [$this, 'delete_item_permissions_check'],
            ],
        ]);

        register_rest_route( $this->namespace, $this->rest_base.'/delete_static_item', [
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [$this, 'delete_static_item'],
                'permission_callback' => [$this, 'delete_item_permissions_check'],
            ],
        ]);

        register_rest_route(  $this->namespace,
        '/' . $this->rest_base . '/(?P<id>[\d]+)', [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [$this, 'get_item'],
                'permission_callback' => [$this, 'get_item_permissions_check'],
            ],
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [$this, 'update_item'],
                'permission_callback' => [$this, 'update_item_permission_check'],
            ],
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [$this, 'delete_item'],
                'permission_callback' => [$this, 'delete_item_permissions_check'],
            ],
        ]);

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<id>[\d]+)' . '/clone',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( $this, 'clone_item' ),
                    'permission_callback' => array( $this, 'create_item_permission_check' ),
                    'args'                => $this->get_collection_params(),
                ],
            ],
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<id>[\d]+)' . '/status',
            [
                [
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => array( $this, 'update_item_status' ),
                    'permission_callback' => array( $this, 'get_item_permissions_check' ),
                    'args'                => $this->get_collection_params(),
                ],
            ],
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<id>[\d]+)' . '/thumbnail',
            [
                [
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => array( $this, 'item_thumbnail_update' ),
                    'permission_callback' => array( $this, 'get_item_permissions_check' ),
                    'args'                => $this->get_collection_params(),
                ],
            ],
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/assign-template',
            [
                [
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => array( $this, 'assign_template' ),
                    'permission_callback' => array( $this, 'assign_template_permission_check' ),
                    'args'                => $this->get_assign_template_validation_params(),
                ],
            ],
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/select-default-template',
            [
                [
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => array( $this, 'select_default_template' ),
                    'permission_callback' => array( $this, 'select_default_template_permission_check' ),
                    'args'                => array(
                        'id' => array(
                            'required'    => true,
                            'description' => __( 'Template ID to set as default', 'eventin' ),
                        ),
                        'type' => array(
                            'required'    => true,
                            'type'        => 'string',
                            'description' => __( 'Template type (event or ticket)', 'eventin' ),
                            'enum'        => array( 'event', 'ticket', 'speaker' ),
                        ),
                    ),
                ],
            ],
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<id>[\d]+)' . '/events',
            [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( $this, 'get_template_events' ),
                    'permission_callback' => array( $this, 'get_item_permissions_check' ),
                ],
            ],
        );
    }

    /**
     * Get collections  of template
     *
     * @param   WP_Rest_Request  $request  Rest request object
     *
     * @return  WP_Rest_Response | WP_Error
     */
    public function get_items( $request ) {
        $per_page = ! empty( $request['per_page'] ) ? intval( $request['per_page'] ) : 20;
        $paged    = ! empty( $request['paged'] ) ? intval( $request['paged'] ) : 1;
        $status   = ! empty( $request['status'] ) ? sanitize_text_field( $request['status'] ) : '';
        $type     = ! empty( $request['type'] ) ? sanitize_text_field( $request['type'] ) : '';
        $search_keyword = ! empty( $request['search'] ) ? sanitize_text_field( $request['search'] ) : '';
        $is_remote = isset( $request['is_remote'] ) && filter_var( $request['is_remote'], FILTER_VALIDATE_BOOLEAN );
        $is_for_edit = isset( $request['is_for_edit'] ) && filter_var( $request['is_for_edit'], FILTER_VALIDATE_BOOLEAN );
     
        $args = [
            'post_type'      => 'etn-template',
            'post_status'    => 'any',
            'posts_per_page' => $per_page,
            'paged'          => $paged,
        ];

        if ( 'all' === $type ) {
            $type = '';
        }

        if ( ! empty( $status ) ) {
            $args['post_status'] = $status;
        }

        if ( $is_remote ) {
            $args['post_status'] = 'publish';
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            $args['author'] = get_current_user_id(); 
        }

        $meta_query = [];

        if ( $search_keyword ) {
            $args['s'] = $search_keyword;
        }

        if ( ! empty( $type ) ) {
            $meta_query[] = [
                'key'     => 'type',
                'value'   => $type,
                'compare' => '=',
            ];
        }

        if ( ! empty( $meta_query ) ) {
            $args['meta_query'] = $meta_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query   
        }

        $post_query   = new \WP_Query();
        $query_result = $post_query->query( $args );
        $total_posts  = $post_query->found_posts;
        $templates    = [];
        $static_templates_data = etn_get_static_templates_by_type( $type );

        // Add static templates through prepare_item_for_response
        if( $is_remote || $is_for_edit ) {
            foreach ( $static_templates_data as $static_data ) {
                $key = 'eventin_template_' . $static_data['id'];
                $is_template_in_list = etn_get_option( $key );
                $static_template = new StaticTemplate( $static_data );
                $static_template->is_added = $is_template_in_list ? true : false;
                $templates[] = $this->prepare_item_for_response( $static_template, $request );
            }
        }

        foreach($static_templates_data as $static_data) {
            $key = 'eventin_template_' . $static_data['id'];
            $is_template_in_list = etn_get_option($key);

            if($is_template_in_list) {
                $static_template = new StaticTemplate( $static_data );
                $templates[] = $this->prepare_item_for_response( $static_template, $request );
            }
        }

        foreach ( $query_result as $post ) {
            $template = new TemplateModel( $post->ID );
            $templates[] = $this->prepare_item_for_response( $template, $request );
        }

        $response = rest_ensure_response( $templates );
    
        $response->header( 'X-WP-Total', $total_posts );
    
        return $response;
    }

    /**
     * Prepare item for response
     *
     * @param   TemplateModel|StaticTemplate  $item     Template item
     * @param   WP_Rest_Request  $request  Request object
     *
     * @return  array
     */
    public function prepare_item_for_response( $item, $request ) {
        $is_static = $item instanceof StaticTemplate;

        // Get thumbnail: WordPress featured image if available, otherwise use item thumbnail
        $post_id = $is_static ? $item->get_id() : $item->id;
        $featured_image = ! $is_static ? get_the_post_thumbnail_url( $post_id, 'full' ) : false;
        $thumbnail = $featured_image ? $featured_image : $item->thumbnail;

        $response = [
            'id'            => $is_static ? $item->get_id() : $item->id,
            'name'          => $item->get_name(),
            'status'        => $item->get_status(),
            'type'          => $item->get_type(),
            'orientation'   => $item->get_orientation(),
            'thumbnail'     => $thumbnail,
            'content'       => $item->get_content(),
            'is_clone'      => $item->is_clone,
            'is_pro'        => $item->is_pro,
            'template_css'  => $item->template_css,
            'edit_link'     => $is_static ? '' : $this->get_template_edit_link( $item->id ),
            'preview_link'  => $is_static ? $item->preview_link : get_preview_post_link( $item->id ),
            'preview_event_id' => $item->preview_event_id,
            'template_builder' => ! empty( $item->get_template_builder() ) ? $item->get_template_builder() : ( $is_static ? '' : $this->get_legacy_template_models_template_builder( $item ) ),
            'edit_with_elementor' => $is_static ? false : $this->check_post_edit_with_elementor( $item->id ),
            'is_default'    => $this->is_default_template( $item , $is_static),
            'is_custom'     => $is_static ? false : (bool) get_post_meta( $post_id, 'is_custom', true ),
        ];

        if ( $is_static ) {
            $response['isStatic'] = true;
            $response['isAdded'] = isset( $item->is_added ) ? $item->is_added : false;
        }

        return $response;
    }

    private function is_default_template( $item , $is_static = false) {
        $item_type = $item->get_type();
        $item_id   = $is_static ? $item->get_id() : $item->id;

        if ( $item_type == 'event' ) {
            return $item_id == etn_get_option( 'event_layout' );
        }

        if ( $item_type == 'ticket' ) {
            return $item_id == etn_get_option( 'attendee_ticket_style' );
        }

        if ( $item_type == 'speaker' ) {
            return $item_id == etn_get_option( 'speaker_template' );    
        }
    }
    /**
     * Get the template builder for the legacy template models
     *
     * @param TemplateModel $item the template model whose builder we will be returning
     *
     * @return string $builder the builder for the template model
     */
    private function get_legacy_template_models_template_builder( $item ){
        if ( $this->check_post_edit_with_elementor( $item->id ) ) {
            return 'elementor';
        }

        return 'gutenberg';
    }

    /**
     * Get the appropriate edit link for the template
     *
     * @param  int    $post_id
     * @return string
     */
    private function get_template_edit_link( $post_id ) {
        if ( $this->should_use_elementor_editor( $post_id ) ) {
            return add_query_arg([
                'post'   => absint( $post_id ),
                'action' => 'elementor',
            ], admin_url( 'post.php' ) );
        }

        return get_edit_post_link( $post_id, 'raw' );
    }

    /**
     * Check if Elementor editor should be used for the given post
     *
     * @param  int  $post_id
     * @return bool true when post should be opened in elementor editor
     */
    private function should_use_elementor_editor( $post_id ) {
        $template = new TemplateModel( $post_id );
        $elementor_is_active = class_exists('Elementor\Plugin');
        $post_made_with_elementor = $template->get_template_builder() === 'elementor';

        return $elementor_is_active && $post_made_with_elementor;
    }

    /**
     * Get item permission check
     *
     * @param   WP_Rest_Request  $request  [$request description]
     *
     * @return  WP_Rest_Response | WP_Error
     */
    public function get_item_permissions_check( $request ) {
        return true;
    }

    /**
     * Disable Elementor for a given post
     *
     * @param int $post_id The ID of the post to disable Elementor for
     * @return void
     */
    public function check_post_edit_with_elementor( $post_id ) {
        return get_post_meta( $post_id, '_elementor_edit_mode', true );
    }

    /**
     * Get single item
     *
     * @param   WP_Rest_Request  $request  [$request description]
     *
     * @return  WP_Rest_Response | WP_Error
     */
    public function get_item( $request ) {
        $id = intval( $request['id'] );

        $post = get_post( $id );

        if ( ! $post ) {
            return new WP_Error( 'invalid_id', __( 'Invalid template id', 'eventin' ), ['status' => 422] );
        }

        if ( 'etn-template' !== $post->post_type ) {
            return new WP_Error( 'invalid_id', __( 'Invalid template id', 'eventin' ), ['status' => 422] );
        }

        $template = new TemplateModel( $id );

        $response = $this->prepare_item_for_response( $template, $request );

        return rest_ensure_response( $response );
    }

    /**
     * Create item
     *
     * @param   WP_Rest_Request  $request  [$request description]
     *
     * @return  WP_Rest_Response | WP_Error
     */
    public function create_item( $request ) {
        $this->validate_create_item_request( $request );
        $data = $this->prepare_item_for_database( $request );

        $input_data = json_decode( $request->get_body(), true ) ?? [];
        $input = new Input( $input_data );

        if(!empty($input->get('id')) && !empty($input->get('is_static'))){
            etn_update_option( 'eventin_template_' . $input->get('id'), true );

            return rest_ensure_response( [
                'success' => true,
                'message' => __( 'Template added to list successfully', 'eventin' ),
            ] );
        }

        if ( is_wp_error( $data ) ) {
            return $data;
        }

        $template = new TemplateModel();
        $template_created = $template->create( $data );

        if ( ! $template_created ) {
            return new WP_Error( 'template_create_error', __( 'Couldn\'t create template. Please try again.', 'eventin' ), ['status' => 422] );
        }

        $response = $this->prepare_item_for_response( $template, $request );

        return rest_ensure_response( $response );
    }

    private function validate_create_item_request( $request ) {
        if ( ! $this->is_within_template_limit() ) {
            return new WP_Error(
                'template_limit_reached',
                __( 'You can only have one template in the free version. Please upgrade to Pro to create more templates.', 'eventin' ),
                [ 'status' => 403 ]
            );
        }

        if ( ! class_exists( 'Wpeventin_Pro' ) && $request['type'] !== 'event' ) {
            return new WP_Error(
                'template_limit_reached',
                __( 'You can only create event landing page templates in the free version.', 'eventin' ),
                [ 'status' => 403 ]
            );
        }
    }

    /**
     * Create item permission check
     * Check if user has permission to create template and if pro is not active no template exists
     * 
     * @param WP_REST_Request $request The request object
     * @return bool|WP_Error True if user can create template, false or WP_Error otherwise
     */
    public function create_item_permission_check( $request ) {
        if ( ! $this->user_has_template_permission() ) {
            return false;
        }

        return true;
    }

    /**
     * Check if user has permission to manage templates
     *
     * @return bool True if user has permission, false otherwise
     */
    private function user_has_template_permission() {
        return current_user_can( 'etn_manage_template' );
    }

    /**
     * Check if user is within the allowed template limit
     *
     * @return bool True if within limit or Pro version is active, false otherwise
     */
    private function is_within_template_limit() {
        if ( class_exists( 'Wpeventin_Pro' ) ) {
            return true;
        }

        $existing_templates = get_posts( [
            'post_type'      => 'etn-template',
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'fields'         => 'ids',
        ]);

        return empty( $existing_templates );
    }

    /**
     * Update item
     *
     * @param   WP_Rest_Request  $request  [$request description]
     *
     * @return  WP_Rest_Response | WP_Error
     */
    public function update_item( $request ) {
        $data = $this->prepare_item_for_database( $request );

        if ( is_wp_error( $data ) ) {
            return $data;
        }

        $id = intval( $request['id'] );

        $post = get_post( $id );

        if ( ! $post ) {
            return new WP_Error( 'invalid_id', __( 'Invalid template id', 'eventin' ), ['status' => 422] );
        }

        if ( 'etn-template' !== $post->post_type ) {
            return new WP_Error( 'invalid_id', __( 'Invalid template id', 'eventin' ), ['status' => 422] );
        }

        $template = new TemplateModel( $request['id'] );
        $template_update = $template->update( $data );

        if ( ! $template_update ) {
            return new WP_Error( 'template_update_error', __( 'Couldn\'t update template. Please try again.', 'eventin' ), ['status' => 422] );
        }

        $response = $this->prepare_item_for_response( $template, $request );

        return rest_ensure_response( $response );
    }

    /**
     * Update item permission check
     *
     * @param   WP_Rest_Request  $request  [$request description]
     *
     * @return  bool
     */
    public function update_item_permission_check( $request ) {
        if ( ! class_exists( 'Wpeventin_Pro' ) && $request['type'] !== 'event' ) {
            return false;
        }

        return current_user_can( 'etn_manage_template' );
    }

    /**
     * Assign template to event permission check
     * 
     * @param WP_Rest_Request $request
     * 
     * @return bool
     */
    public function select_default_template_permission_check( $request ) {
        if ( class_exists( 'Wpeventin_Pro' ) ) {
            return current_user_can( 'etn_manage_template' );
        }

        return current_user_can( 'manage_options');
    }

    /**
     * Update template thumbnail
     *
     * @param WP_REST_Request $request
     * @return WP_REST_Response|WP_Error
     */
    public function item_thumbnail_update( $request ) {
        $id = intval( $request['id'] );
        $input_data = json_decode( $request->get_body(), true );

        $thumbnail_data['thumbnail'] = $input_data['thumbnail'];

        $template = new TemplateModel( $id );
        $template->update( $thumbnail_data );

        return rest_ensure_response( $template );
    }

    /**
     * Clone item
     *
     * @param   WP_Rest_Request  $request
     *
     * @return  WP_Rest_Response
     */
    public function clone_item( $request ) {
        $id = intval( $request['id'] );

        $post = get_post( $id );

        if ( ! $post ) {
            return new WP_Error( 'invalid_id', __( 'Invalid template id', 'eventin' ), ['status' => 422] );
        }

        if ( 'etn-template' !== $post->post_type ) {
            return new WP_Error( 'invalid_id', __( 'Invalid template id', 'eventin' ), ['status' => 422] );
        }

        $template = new TemplateModel( $id );

        $clone_template = $template->clone();

        $response = $this->prepare_item_for_response( $clone_template, $request );

        return rest_ensure_response( $response );
    }

    /**
     * Update template status
     *
     * @param   WP_Rest_Request  $request
     *
     * @return  WP_Rest_Response | WP_Error
     */
    public function update_item_status( $request ) {
        $id = intval( $request['id'] );

        $post = get_post( $id );
        $status = ! empty( $request['status'] ) ? $request['status'] : ''; 

        if ( ! $post ) {
            return new WP_Error( 'invalid_id', __( 'Invalid template id', 'eventin' ), ['status' => 422] );
        }

        if ( 'etn-template' !== $post->post_type ) {
            return new WP_Error( 'invalid_id', __( 'Invalid template id', 'eventin' ), ['status' => 422] );
        }

        if ( ! $status ) {
            return new WP_Error( 'status_error', __( 'Invalid template status', 'eventin' ), ['status' => 422] );
        }

        $statuses = [
            'publish', 'draft'
        ];

        if ( ! in_array( $status, $statuses ) ) {
            return new WP_Error( 'invalid_status', __( 'Invalid template status', 'eventin' ), ['status' => 422] );
        }

        // Prepare the post data
        $post_data = array(
            'ID'          => $id,
            'post_status' => $status, // Change this to 'draft', 'pending', 'private', etc.
        );

        // Update the post status
        $updated_template = wp_update_post( $post_data, true );

        if ( is_wp_error( $updated_template ) ) {
            return $updated_template;
        }

        $response = [
            'message' => __( 'Successfully updated template status', 'eventin' ),
        ];

        return rest_ensure_response( $response );
    }

    /**
     * Prepare item for database
     *
     * @param   WP_Rest_Re  $request  [$request description]
     *
     * @return  [type]            [return description]
     */
    public function prepare_item_for_database( $request ) {
        $input_data = json_decode( $request->get_body(), true ) ?? [];
        $validate   = etn_validate( $input_data, [
            'name'      => [
                'required',
            ],
            'type'   => [
                'required',
            ],
            'content' => [
                'required',
            ],
            'orientation'   => [
                'required',
            ]
        ] );

        if ( is_wp_error( $validate ) ) {
            return $validate;
        }

        $input = new Input( $input_data );

        $template_data = [
            'post_title'    => $input->get('name'),
            'post_content'  => $input->get('content'),
            'post_status'   => $input->get('status'),
            'type'          => $input->get('type'),
            'orientation'   => $input->get('orientation'),
            'thumbnail'     => $input->get('thumbnail'),
            'template_css'  => $input->get('template_css'),
            'is_pro'        => false,
            'is_custom'     => (bool) $input->get('isCustom'),
            'template_builder' => $input->get('template_builder') ?? etn_get_selected_template_builder(),
            'preview_event_id' => $input->get('preview_event_id'),
        ];

        return $template_data;
    }

    /**
     * Delete static item
     *
     * @param   WP_Rest_Request  $request
     *
     * @return  WP_Rest_Response | WP_Error
     */
    public function delete_static_item( $request ) {
        $id = ! empty( $request['id'] ) ?  sanitize_text_field( $request['id'] ) : '';
        $is_in_list = etn_get_option('eventin_template_' . $id);
        if($is_in_list){
            etn_update_option( 'eventin_template_' . $id, false );
        }
        
        return rest_ensure_response( [
            'success' => true,
            'message' => __( 'Template deleted successfully', 'eventin' ),
        ] );
    }

    /**
     * Delete item
     *
     * @param   WP_Rest_Request  $request
     *
     * @return  WP_Rest_Response | WP_Error
     */
    public function delete_item( $request ) {
        $id = intval( $request['id'] );

        $post = get_post( $id );

        if ( ! $post ) {
            return new WP_Error( 'invalid_id', __( 'Invalid template id', 'eventin' ), ['status' => 422] );
        }

        if ( 'etn-template' !== $post->post_type ) {
            return new WP_Error( 'invalid_id', __( 'Invalid template id', 'eventin' ), ['status' => 422] );
        }

        $template = new TemplateModel( $id );

        $deleted = $template->delete();

        if ( ! $deleted ) {
            return new WP_Error( 'delete_error', __( 'Unable to delete this template. Please try again.', 'eventin' ), ['status' => 409] );
        }

        $response = [
            'message'   => __( 'Successfully deleted template', 'eventin' )
        ];

        return rest_ensure_response( $response );
    }

    /**
     * Delete item permission check
     *
     * @param   WP_Rest_Request  $request  [$request description]
     *
     * @return  bool
     */
    public function delete_item_permissions_check( $request ) {
        return current_user_can( 'etn_manage_template' );
    }

    /**
     * Bulk delete templates
     *
     * @param   WP_Rest_Request  $request  [$request description]
     *
     * @return  WP_Rest_Response | WP_Error
     */
    public function delete_items( $request ) {
        $ids = ! empty( $request['ids'] ) ? $request['ids'] : [];

        if ( ! $ids ) {
            return new WP_Error(
                'rest_cannot_delete',
                __( 'Template ids can not be empty.', 'eventin' ),
                array( 'status' => 422 )
            );
        }

        $count = 0;

        foreach ( $ids as $id ) {
            $template = new TemplateModel( $id );

            if ( $template->delete() ) {
                $count++;
            }
        }

        if ( $count == 0 ) {
            return new WP_Error(
                'rest_cannot_delete',
                __( 'Template cannot be deleted.', 'eventin' ),
                array( 'status' => 409 )
            );
        }

        // translators: %1$d is the number of templates deleted, %2$d is the total number of templates selected.
        $message = sprintf( __( '%1$d templates are deleted of %2$d', 'eventin' ), $count, count( $ids ) );

        return rest_ensure_response( $message );
    }

    /**
     * Permission check for assigning template
     */
    public function assign_template_permission_check( $request ) {
        if ( class_exists( 'Wpeventin_Pro' ) ) {
            return current_user_can( 'etn_manage_template' );
        }

        return current_user_can( 'manage_options' );
    }

    /**
     * Assign template to events
     *
     * @param   WP_Rest_Request  $request  [$request description]
     *
     * @return  WP_Rest_Response | WP_Error
     */
    public function assign_template( $request ) {
        $assignment_data = $this->parse_assignment_request( $request );

        $validation_result = $this->validate_event_ids_for_scope( $assignment_data['template_for'], $assignment_data['event_ids'] );
        if ( is_wp_error( $validation_result ) ) {
            return $validation_result;
        }

        // Empty existing template occurences in other events
        $meta_key = $this->get_event_meta_key( $assignment_data['template_type'] );
        $this->etn_empty_template_meta($meta_key, $assignment_data['template_id']);

        $assignment_result = $this->execute_template_assignment( $assignment_data );
        if ( is_wp_error( $assignment_result ) ) {
            return $assignment_result;
        }

        if ( $assignment_data['preview_event_id'] ) {
            $this->update_template_preview( $assignment_data['template_id'], $assignment_data['preview_event_id'] );
            $assignment_result['preview_event_id'] = $assignment_data['preview_event_id'];
        }

        return rest_ensure_response( $assignment_result );
    }

    /**
     * Parse assignment request data
     * Note: Basic validation (required fields, types, enums) is handled by WordPress REST API route args
     *
     * @param WP_Rest_Request $request
     * @return array
     */
    private function parse_assignment_request( $request ) {
        $input_data = json_decode( $request->get_body(), true );

        return [
            'template_id'   => isset( $input_data['template_id'] )   ? intval( $input_data['template_id'] ) : 0,
            'template_type' => isset( $input_data['template_type'] ) ? sanitize_text_field( $input_data['template_type'] ) : '',
            'template_for'  => isset( $input_data['template_for'] )  ? sanitize_text_field( $input_data['template_for'] ) : '',
            'event_ids'     => isset( $input_data['event_ids'] )     ? array_map( 'intval', (array) $input_data['event_ids'] ) : [],
            'preview_event_id' => isset( $input_data['preview_event_id'] ) ? intval( $input_data['preview_event_id'] ) : null,
        ];
    }

    /**
     * Validate event IDs for selected scope
     *
     * @param string $template_for
     * @param array $event_ids
     * @return bool|WP_Error
     */
    private function validate_event_ids_for_scope( $template_for, $event_ids ) {
        if ( 'selected_events' === $template_for && empty( $event_ids ) ) {
            return new WP_Error(
                'missing_event_ids',
                __( 'Event IDs are required when template_for is selected_events', 'eventin' ),
                ['status' => 422]
            );
        }

        return true;
    }

    /**
     * Execute template assignment based on scope
     *
     * @param array $data
     * @return array|WP_Error
     */
    private function execute_template_assignment( $data ) {
        if ( 'all_events' === $data['template_for'] ) {
            return $this->assign_template_as_default_template( $data );
        }

        return $this->assign_template_to_selected_events( $data );
    }

    /**
     * Assign template to all events (global default)
     *
     * @param array $data
     * @return array|WP_Error
     */
    private function assign_template_as_default_template( $data ) {
        $option_key = $this->get_settings_key_for( $data['template_type'] );

        if ( ! $option_key ) {
            return new WP_Error(
                'invalid_template_type',
                __( 'Invalid template type for global assignment', 'eventin' ),
                ['status' => 422]
            );
        }

        $update_result = etn_update_option( $option_key, $data['template_id'] );

        if ( false === $update_result && etn_get_option( $option_key ) != $data['template_id'] ) {
            return new WP_Error(
                'update_failed',
                __( 'Failed to set default template. Please try again.', 'eventin' ),
                ['status' => 500]
            );
        }

        return [
            'message' => __( 'Successfully set default template for all events', 'eventin' ),
            'template_id' => $data['template_id'],
            'template_type' => $data['template_type'],
            'template_for' => $data['template_for']
        ];
    }

    /**
     * Assign template to selected events
     *
     * @param array $data
     * @return array
     */
    private function assign_template_to_selected_events( $data ) {
        $meta_key = $this->get_event_meta_key( $data['template_type'] );
        $updated_events = [];
        $failed_events = [];

        foreach ( $data['event_ids'] as $event_id ) {
            if ( $this->is_valid_event( $event_id ) ) {
                $current_template_id = get_post_meta($event_id, $meta_key, true);
                $updated = update_post_meta( $event_id, $meta_key, $data['template_id'] );

                if ( $updated || $current_template_id == $data['template_id'] ) {
                    $updated_events[] = $event_id;
                } else {
                    $failed_events[] = $event_id;
                }
            } else {
                $failed_events[] = $event_id;
            }
        }

        return [
            'message' => sprintf(
                // translators: %1$d is the number of events updated, %2$d is the number of failed updates.
                __( 'Template assignment completed. %1$d events updated, %2$d failed.', 'eventin' ),
                count( $updated_events ),
                count( $failed_events )
            ),
            'template_id' => $data['template_id'],
            'template_type' => $data['template_type'],
            'template_for' => $data['template_for'],
            'updated_events' => $updated_events,
            'failed_events' => $failed_events
        ];
    }

    /**
     * Get global option key for template type
     *
     * @param string $template_type
     * @return string
     */
    private function get_settings_key_for( $template_type ) {
        $option_keys = [
            'event'  => 'event_layout',
            'ticket' => 'attendee_ticket_style',
            'speaker' => 'speaker_template',
            'certificate' => 'certificate_template',
        ];

        return isset( $option_keys[$template_type] ) ? $option_keys[$template_type] : '';
    }

    /**
     * Get event meta key for template type
     *
     * @param string $template_type
     * @return string
     */
    private function get_event_meta_key( $template_type ) {
        $meta_keys = [
            'event'  => 'event_layout',
            'ticket' => 'ticket_template',
        ];

        return isset( $meta_keys[$template_type] ) ? $meta_keys[$template_type] : '';
    }

    /**
     * Empty all event_layout/ticket_template meta values where event_layout = 1156 for 'etn' posts.
     */
    private function etn_empty_template_meta($meta_key,$template_id) {
        $args = [
            'post_type'      => 'etn',
            'posts_per_page' => -1,
            'post_status'    => 'any',
            'meta_query'     => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                [
                    'key'     => $meta_key,
                    'value'   => $template_id,
                    'compare' => '=',
                ],
            ],
            'fields' => 'ids',
        ];

        $posts = get_posts( $args );

        if ( empty( $posts ) ) {
            return; // Nothing to update
        }

        // Step 2: Loop through posts and update meta
        foreach ( $posts as $post_id ) {
            update_post_meta( $post_id, 'event_layout', '' ); // empty value
        }
    }


    /**
     * Check if event ID is valid
     *
     * @param int $event_id
     * @return bool
     */
    private function is_valid_event( $event_id ) {
        $post = get_post( $event_id );
        return $post && 'etn' === $post->post_type;
    }

    /**
     * Update template preview event
     *
     * @param int $template_id
     * @param int $preview_event_id
     * @return void
     */
    private function update_template_preview( $template_id, $preview_event_id ) {
        $template = new TemplateModel( $template_id );
        $template->update( ['preview_event_id' => $preview_event_id] );
    }

    /**
     * Select default template (simpler legacy method)
     *
     * @param   WP_Rest_Request  $request  [$request description]
     *
     * @return  WP_Rest_Response | WP_Error
     */
    public function select_default_template( $request ) {
        $input_data = json_decode( $request->get_body(), true );
        $id = isset( $input_data['id'] ) ? sanitize_text_field( $input_data['id'] ) : '';
        $type = isset( $input_data['type'] ) ? sanitize_text_field( $input_data['type'] ) : '';

        if ( empty( $id ) ) {
            return new WP_Error(
                'missing_id',
                __( 'Template ID is required', 'eventin' ),
                ['status' => 422]
            );
        }

        if ( ! $type ) {
            return new WP_Error(
                'missing_type',
                __( 'Template type is required', 'eventin' ),
                ['status' => 422]
            );
        }

        $allowed_types = ['event', 'ticket', 'speaker'];
        if ( ! in_array( $type, $allowed_types ) ) {
            return new WP_Error(
                'invalid_type',
                __( 'Template type must be either "event" or "ticket"', 'eventin' ),
                ['status' => 422]
            );
        }

        // Check if this is a static template (non-numeric ID)
        $is_static_template = ! is_numeric( $id );

        if ( $is_static_template ) {
            // Validate static template ID
            if ( ! $this->is_valid_static_template( $id, $type ) ) {
                return new WP_Error(
                    'invalid_id',
                    __( 'Invalid template id', 'eventin' ),
                    ['status' => 422]
                );
            }
        } else {
            // Validate database template (post)
            $post = get_post( $id );

            if ( ! $post ) {
                return new WP_Error(
                    'invalid_id',
                    __( 'Invalid template id', 'eventin' ),
                    ['status' => 422]
                );
            }

            if ( 'etn-template' !== $post->post_type ) {
                return new WP_Error(
                    'invalid_id',
                    __( 'Invalid template id', 'eventin' ),
                    ['status' => 422]
                );
            }
        }

        // Set the template as default using etn_update_option based on type
        if ( 'event' === $type ) {
            $update_result = etn_update_option( 'event_layout', $id );
        } elseif ( 'ticket' === $type ) {
            $update_result = etn_update_option( 'attendee_ticket_style', $id );
        } elseif ( 'speaker' === $type ) {
            $update_result = etn_update_option( 'speaker_template', $id );
        }

        if ( false === $update_result ) {
            return new WP_Error(
                'update_failed',
                __( 'Failed to set default template. Please try again.', 'eventin' ),
                ['status' => 500]
            );
        }

        $response = [
            'message' => __( 'Successfully set default template', 'eventin' ),
            'template_id' => $id,
            'template_type' => $type
        ];

        return rest_ensure_response( $response );
    }

    /**
     * Get assign template parameters
     *
     * @return array
     */
    public function get_assign_template_validation_params() {
        return array(
            'template_id' => array(
                'required'    => true,
                'type'        => 'integer',
                'description' => __( 'Template ID to assign', 'eventin' ),
            ),
            'template_type' => array(
                'required'    => true,
                'type'        => 'string',
                'description' => __( 'Template type (event, ticket, speaker)', 'eventin' ),
                'enum'        => array( 'event', 'ticket', 'speaker', 'certificate' ),
            ),
            'template_for' => array(
                'required'    => true,
                'type'        => 'string',
                'description' => __( 'Template assignment scope', 'eventin' ),
                'enum'        => array( 'all_events', 'selected_events' ),
            ),
            'event_ids' => array(
                'required'    => false,
                'type'        => 'array',
                'description' => __( 'Array of event IDs for selected_events scope', 'eventin' ),
                'items'       => array(
                    'type' => 'integer',
                ),
            ),
            'preview_event_id' => array(
                'required'    => false,
                'type'        => 'integer',
                'description' => __( 'Preview event ID for the template', 'eventin' ),
            ),
        );
    }

    /**
     * Check if a given ID is a valid static template
     *
     * @param string $id Template ID to validate
     * @param string $type Template type (event, ticket, speaker)
     * @return bool True if valid static template, false otherwise
     */
    private function is_valid_static_template( $id, $type ) {
        $static_templates = etn_get_static_templates_by_type( $type );

        foreach ( $static_templates as $template ) {
            if ( isset( $template['id'] ) && $template['id'] === $id ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get events using a specific template
     *
     * @since 4.0.43
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function get_template_events( $request ) {
        $template_id = intval( $request->get_param( 'id' ) );

        // Verify template exists
        $post = get_post( $template_id );

        if ( ! $post ) {
            return new WP_Error( 'invalid_id', __( 'Invalid template id', 'eventin' ), ['status' => 422] );
        }

        if ( 'etn-template' !== $post->post_type ) {
            return new WP_Error( 'invalid_id', __( 'Invalid template id', 'eventin' ), ['status' => 422] );
        }

        // Query events with this template
        $args = [
            'post_type'      => 'etn',
            'post_status'    => 'any',
            'posts_per_page' => -1,
            'fields'         => 'ids',
            'meta_query'     => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                [
                    'key'     => 'event_layout',
                    'value'   => $template_id,
                    'compare' => '=',
                ],
            ],
        ];

        $event_ids = get_posts( $args );
        $events    = [];

        foreach ( $event_ids as $event_id ) {
            $events[] = [
                'id'   => $event_id,
                'name' => get_the_title( $event_id ),
            ];
        }

        $response = [
            'template_id' => $template_id,
            'events'      => $events,
        ];

        return rest_ensure_response( $response );
    }
}
