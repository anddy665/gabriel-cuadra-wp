<?php

/**
 * Speaker Api Class
 *
 * @package Eventin\Speaker
 */
namespace Eventin\Speaker\Api;

defined( 'ABSPATH' ) || exit;

use Eventin\Speaker\SpeakerExporter;
use Eventin\Speaker\SpeakerImporter;
use Etn\Core\Speaker\User_Model;
use WP_Error;
use WP_User_Query;
use WP_REST_Controller;
use WP_REST_Server;
use WP_User;

/**
 * Speaker Controller Class
 */
class SpeakerController extends WP_REST_Controller {
    
    /**
     * Meta Prefix key
     *
     * @var string
     */
    public $meta_prefix  = 'etn_speaker_';
    
    /**
     * Constructor for SpeakerController
     *
     * @return void
     */
    public function __construct() {
        $this->namespace = 'eventin/v2';
        $this->rest_base = 'speakers';
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
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [$this, 'create_item'],
                'permission_callback' => [$this, 'get_item_permissions_check'],
            ],
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [$this, 'delete_items'],
                'permission_callback' => [$this, 'delete_item_permissions_check'],
            ],
        ] );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<id>[\d]+)',
            array(
                'args' => array(
                    'id' => array(
                        'description' => __( 'Unique identifier for the post.', 'eventin' ),
                        'type'        => 'integer',
                    ),
                ),
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( $this, 'get_item' ),
                    'permission_callback' => array( $this, 'get_item_permissions_check' ),
                    'args'                => $this->get_collection_params(),
                ),
                array(
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => array( $this, 'update_item' ),
                    'permission_callback' => array( $this, 'update_item_permissions_check' ),
                    'args'                => $this->get_collection_params(),
                ),
                array(
                    'methods'             => WP_REST_Server::DELETABLE,
                    'callback'            => array( $this, 'delete_item' ),
                    'permission_callback' => array( $this, 'delete_item_permissions_check' ),
                    'args'                => $this->get_collection_params(),
                ),
            ),
        );

        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<id>[\d]+)' . '/clone',
            array(
                'args' => array(
                    'id' => array(
                        'description' => __( 'Unique identifier for the post.', 'eventin' ),
                        'type'        => 'integer',
                    ),
                ),
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( $this, 'clone_item' ),
                    'permission_callback' => array( $this, 'get_item_permissions_check' ),
                    'args'                => $this->get_collection_params(),
                ),

                // 'allow_batch' => $this->allow_batch,
                'schema' => array( $this, 'get_item_schema' ),
            ),
        );

        register_rest_route( $this->namespace, $this->rest_base . '/export', [
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [$this, 'export_items'],
                'permission_callback' => [$this, 'export_item_permissions_check'],
            ]
        ] );

        register_rest_route( $this->namespace, $this->rest_base . '/import', [
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [$this, 'import_items'],
                'permission_callback' => [$this, 'import_item_permissions_check'],
            ]
        ] );

        register_rest_route( $this->namespace, 'organizers/export', [
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [$this, 'export_organizers'],
                'permission_callback' => [$this, 'export_item_permissions_check'],
            ]
        ] );

        register_rest_route( $this->namespace, 'organizers/import', [
            [
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => [$this, 'import_organizers'],
                'permission_callback' => [$this, 'import_item_permissions_check'],
            ]
        ] );
    }

    /**
     * Check if a given request has access to get items.
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|boolean
     */
    public function get_item_permissions_check( $request ) {
        return current_user_can( 'etn_manage_organizer' )
        || current_user_can( 'etn_manage_event' );
    }

    /**
     * Get a collection of items.
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function get_items( $request ) {

        $per_page = ! empty( $request['per_page'] ) ? intval( $request['per_page'] ) : -1;
        $paged    = ! empty( $request['paged'] ) ? intval( $request['paged'] ) : 1;
        $type     = ! empty( $request['category'] ) ?  $request['category']  : [];
        $group    = ! empty( $request['speaker_group'] ) ? $request['speaker_group'] : '';
        $search   = ! empty( $request['search'] ) ?   $request['search'] : 0;

        $user_ids = self::get_etn_user_role( $type );

        if ( $group ) {
            $group_user_ids = $this->get_user_ids_by_group($group);
            $user_ids       = ! empty( $group_user_ids ) ? $group_user_ids : [-1];
        }

        if ( $type ) {
            $role_name = 'etn-' . strtolower( $type );
            $user_ids = $this->get_user_ids_by_role( $role_name );
        }
        
        $offset = ( $paged - 1 ) * $per_page;
        $exclude_ids = [];


        // Get the admin user ID to potentially exclude
        $admin_user = get_user_by( 'login', 'admin' );
        if ( $admin_user ) {
            // Check if the admin has the 'etn_speaker_category' meta_key
            $admin_meta_value = get_user_meta( $admin_user->ID, 'etn_speaker_category', true );
            if ( empty( $admin_meta_value ) ) {
                $exclude_ids[] = $admin_user->ID;
            }
        }

        $args = [
            'role__in' => [ 'etn-speaker', 'etn-organizer' ],
            'number'        => $per_page,
            'offset'        => $offset,
            'exclude'       => $exclude_ids, // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
        ];
        
        if ( ! empty( $user_ids ) ) {
            $args['include'] = $user_ids;
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            $args['meta_query'] = [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                [
                    'key'   => 'author',
                    'value' => get_current_user_id(),
                    'compare' => '='
                ]
            ];
        }

        // Search query across multiple fields
        if ( $search ) {
            $args['meta_query'][] = [
                'relation' => 'OR',
                [
                    'key'     => 'etn_company_name',
                    'value'   => $search,
                    'compare' => 'LIKE'
                ],
                [
                    'key'     => 'etn_speaker_summery',
                    'value'   => $search,
                    'compare' => 'LIKE'
                ],
                [
                    'key'     => 'etn_speaker_title',
                    'value'   => $search,
                    'compare' => 'LIKE'
                ],
                [
                    'key'     => 'etn_speaker_designation',
                    'value'   => $search,
                    'compare' => 'LIKE'
                ], 
                [
                    'key'     => 'etn_speaker_website_email',
                    'value'   => $search,
                    'compare' => 'LIKE'
                ]
            ];

            if ( ! current_user_can( 'manage_options' ) ) {
                $args['meta_query'][] = [
                    'key'   => 'author',
                    'value' => get_current_user_id(),
                    'compare' => '='
                ]; 
            }
        }
        
        $events = [];

        $item_query   = new WP_User_Query( $args );
        $query_result = $item_query->get_results();
        $total_posts  = $item_query->get_total();

        foreach ( $query_result as $user ) {
            $speaker   = new User_Model( $user->ID );
            $post_data = $speaker->get_data( $user->ID );

            $speaker_groups = [];

            // Check if speaker_group is an array (multiple categories)
            if ( is_array( $post_data['speaker_group'] ) ) {
                foreach ( $post_data['speaker_group'] as $group_id ) {
                    $category = get_term_by( 'term_id', $group_id, 'etn_speaker_category' );
                    if ( isset( $category->name ) && ! empty( $category->name ) ) {
                        $speaker_groups[] = $category->name;
                    }
                }
            } else { // If only a single category is provided
                $category = get_term_by( 'term_id', $post_data['speaker_group'], 'etn_speaker_category' );
                if ( isset( $category->name ) && ! empty( $category->name ) ) {
                    $speaker_groups[] = $category->name;
                }
            }
        
            // Assign the names back to speaker_group
            if ( ! empty( $speaker_groups ) ) {
                $post_data['speaker_group'] = $speaker_groups;
            }

            $events[] = $this->prepare_response_for_collection( $post_data );
        }

        $args        = apply_filters( 'eventin_speaker_query_args', $args, $events );
        $total_pages = $per_page > 0 ? ceil( $total_posts / $per_page ) : 1;

        $data = [
            'total_items' => $total_posts,
            'total_pages' => $total_pages,
            'items'       => $events,
        ];

        $response = rest_ensure_response( $data );
        $response->header( 'X-WP-Total', $total_posts );
        $response->header( 'X-WP-TotalPages', $total_pages );

        return $response;
    }

    /**
     * Get users by rol name
     *
     * @param   array  $roles  User roles that need search
     *
     * @return  []              User ids for searching results
     */
    private function get_user_ids_by_role( $roles = [] ) {
        if ( empty( $roles ) ) {
            return [];
        }
    
        $args = [
            'role__in' => (array) $roles,
            'fields'   => 'ID', // Only fetch user IDs
        ];
    
        $user_query = new WP_User_Query($args);
        return $user_query->get_results();
    }

    /**
     * Get one item from the collection.
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function get_item( $request ) {
        $id   = intval( $request['id'] );
        $user = new User_Model( $id );

        $item = $user->get_data( $request['id'] );

        $response = rest_ensure_response( $item );

        return $response;
    }    
    
    /**
     * Get one item from the collection.
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function clone_item( $request ) {
        $id   = intval( $request['id'] );
        $user = new User_Model( $id );

        //get data for clone
        $clone = $user->clone_data( $request['id'] );

        $created = $user->create( $clone );

        if ( ! $created ) {
            return new WP_Error( 'create_error', __( 'Speaker can not create from clone id. Please try again', 'eventin' ), ['status' => 409] );
        }

        $item = User_Model::instance()->get_data( $created );

        $response = rest_ensure_response( $item );

        return $response;
    }

    /**
     * Create one item from the collection.
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function create_item( $request ) {
        $data = $this->prepare_item_for_database( $request );
		
        if ( is_wp_error( $data ) ) {
            return $data;
        }

        $assign_role = $this->assign_role_for_existing_user( $data );

        if ( is_wp_error( $assign_role ) ) {
            return $assign_role;
        }

        if ( $assign_role ) {
            $user = get_user_by( 'email', $data['etn_speaker_website_email'] );
            if ( $user ) {
                $user_id = $user->ID;
            }

            $item = User_Model::instance()->get_data( $user_id );

            $response = rest_ensure_response( $item );
            $response->set_status( 201 );

            return $response;
        }

        $speaker = new User_Model();

        $created = $speaker->create( $data );

        if ( is_wp_error( $created ) ) {
            return new WP_Error( 'create_error', $created->get_error_message(), ['status' => 409] );
        }

        update_user_meta( $created, 'author', get_current_user_id() );

        $item = User_Model::instance()->get_data( $created );

        do_action( 'eventin_speaker_created', new User_Model( $created ), $request );

        $response = rest_ensure_response( $item );
        $response->set_status( 201 );

        return $response;
    }

    /**
     * Check if a given request has access to create items.
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|boolean
     */
    public function create_item_permissions_check( $request ) {
        return current_user_can( 'etn_manage_organizer' )
                    || current_user_can( 'etn_manage_event' );
    }

    /**
     * Update one item from the collection.
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function update_item($request){
        $target_user_id = (int)$request->get_param('id');
        $current_user = wp_get_current_user();
        $target_user = get_userdata($target_user_id);
        
        // Validate target user exists
        if ( ! $target_user ) {
            return new WP_Error('user_not_found', __( 'User does not exist', 'eventin' ), array('status' => 404));
        }
        
        // only allow to edit `speaker` or `organizer`
        if ( ! current_user_can( 'edit_user', $target_user_id ) ) {
            return new WP_Error('forbidden', __( 'Forbidden', 'eventin' ), array('status' => 403));
        }
        
        // only allow to edit `speaker` or `organizer`
        if (
            ! in_array('etn-speaker', (array)$target_user->roles, true) &&
            ! in_array('etn-organizer', (array)$target_user->roles, true)
        ) {
            return new WP_Error('forbidden', __( 'Forbidden', 'eventin' ), array('status' => 403));
        }
        
        
        $data = $this->prepare_item_for_database($request);
        
        if (is_wp_error($data)) {
            return $data;
        }
        
        $speaker = new User_Model($request['id']);
        
        $user = get_user_by('email', $data['etn_speaker_website_email']);
        
        if ($user && !empty($speaker->get_speaker_website_email()) && $speaker->get_speaker_website_email() != $data['etn_speaker_website_email']) {
            
            $assign_role = $this->assign_role_for_existing_user($data,true);
            
            if (is_wp_error($assign_role)) {
                return $assign_role;
            }
            
            if ($assign_role) {
                $response = [
                    'message' => __('The email you provided is exist and assign speaker or organizer role', 'eventin')
                ];
                
                return rest_ensure_response($response);
            }
        }
        
        $updated = $speaker->update($data);
        
        if (!$updated) {
            return new WP_Error('update_error', __('Speaker can not updated. Please try again', 'eventin'), ['status' => 409]);
        }
        
        $item = User_Model::instance()->get_data($updated);
        
        do_action('eventin_speaker_update', $speaker, $request);
        
        $response = rest_ensure_response($item);
        
        return $response;
    }

    /**
     * Check if a given request has access to create items.
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|boolean
     */
    public function update_item_permissions_check( $request ) {
        return current_user_can( 'etn_manage_organizer' )
                    || current_user_can( 'etn_manage_event' );
    }

    /**
     * Delete one item from the collection.
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function delete_item( $request ) {
        $id = intval( $request['id'] );

        $post = get_post( $id );

        if ( is_wp_error( $post ) ) {
            return $post;
        }

        $speaker = new User_Model( $id );

        do_action( 'eventin_speaker_before_delete', $speaker );

        $deleted  = $speaker->delete();
        $response = new \WP_REST_Response();
        $response->set_data(
            array(
                'deleted'  => true,
            )
        );

        if ( ! $deleted ) {
            return new WP_Error(
                'rest_cannot_delete',
                __( 'The speaker cannot be deleted.', 'eventin' ),
                array( 'status' => 500 )
            );
        }

        do_action( 'eventin_speaker_deleted', $id );

        return $response;
    }

    /**
     * Delete multiple items from the collection.
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function delete_items( $request ) {
        $ids = ! empty( $request['ids'] ) ? $request['ids'] : [];

        if ( ! $ids ) {
            return new WP_Error(
                'rest_cannot_delete',
                __( 'Speaker ids can not be empty.', 'eventin' ),
                array( 'status' => 400 )
            );
        }
        $count = 0;

        foreach ( $ids as $id ) {
            $speaker = new User_Model( $id );
            $previous = User_Model::instance()->get_data( $id );
        
            if ( ! array_intersect( ['speaker', 'organizer'], $previous['category'] ) ) {
                return new WP_Error( 'rest_cannot_delete', __( 'The speaker cannot be deleted.', 'eventin' ), [ 'status' => 500 ] );
            }
        
            $user = new WP_User( $id );
            $user_roles = $user->roles;
            $allowed_roles = ['speaker', 'organizer'];
            $hide_user = get_user_meta( $id, 'hide_user', true );

            $has_only_allowed_roles = empty(array_diff( $user_roles, $allowed_roles )) 
                                    && !empty(array_intersect( $user_roles, $allowed_roles ));

            if ( ! $has_only_allowed_roles ) {
                if ( $hide_user == 1 ) update_user_meta( $id, 'hide_user', '' ); 
                $user->remove_role( 'etn-speaker' );
                $user->remove_role( 'etn-organizer' );
            } else {
                $speaker->delete();
            }

        
            $count++;
        }
        


        if ( $count == 0 ) {
            return new WP_Error(
                'rest_cannot_delete',
                __( 'Speaker cannot be deleted.', 'eventin' ),
                array( 'status' => 500 )
            );
        }

        // translators: %1$d is the number of speakers deleted, %2$d is the total number of speakers selected.
        $message = sprintf( __( '%1$d speakers are deleted of %2$d', 'eventin' ), $count, count( $ids ) );

        return rest_ensure_response( $message );
    }

    /**
     * Delete one item from the collection.
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function delete_item_permissions_check( $request ) {
        return current_user_can( 'etn_manage_organizer' )
                    || current_user_can( 'etn_manage_event' );
    }

    /**
     * Prepare the item for create or update operation.
     *
     * @param WP_REST_Request $request Request object.
     * @return WP_Error|array $prepared_item
     */
    protected function prepare_item_for_database( $request ) {
        $prepared_data = [];
        $input_data    = json_decode( $request->get_body(), true );

        if ( ! empty( $input_data['id'] ) ) {
            $prepared_data['id'] = intval( $input_data['id'] );
        }

        if ( ! empty( $input_data['name'] ) ) {
            $prepared_data['etn_speaker_title'] = sanitize_text_field( $input_data['name'] );
            $prepared_data['display_name']      = sanitize_text_field( $input_data['name'] );
        }
        

        if ( ! empty( $input_data['email'] ) ) {
            $prepared_data['etn_speaker_website_email'] = sanitize_text_field( $input_data['email'] );
            $prepared_data['user_login']                = sanitize_email( $input_data['email'] );
        }


        if ( ! empty( $input_data['social'] ) ) {
            $prepared_data['etn_speaker_social'] = $input_data['social'] ;
        }
        
        if ( ! empty( $input_data['organizer_bio'] ) ) {
            $prepared_data['organizer_bio'] = sanitize_text_field($input_data['organizer_bio']);
        }
        
        if ( ! empty( $input_data['phone'] ) ) {
            $prepared_data['phone'] = sanitize_text_field($input_data['phone']);
        }


        if ( ! empty( $input_data['category'] ) && is_array( $input_data['category'] ) ) {
            $allowed_categories = ['speaker', 'organizer'];
            $filtered_categories = array_intersect( $input_data['category'], $allowed_categories );
        
            if ( ! empty( $filtered_categories ) ) {
                $prepared_data['etn_speaker_category'] = $filtered_categories;
            } else {
                unset( $prepared_data['etn_speaker_category'] ); // Or you can leave it out entirely
            }
        }       
        
        if ( ! empty( $input_data['speaker_group'] ) ) {
            $prepared_data['etn_speaker_group'] =  $input_data['speaker_group'] ? json_encode($input_data['speaker_group']) : json_encode([]);
        }        
        
            
        //non mandatory field
        $prepared_data['date']                      =  $input_data['date'] ? $input_data['date'] : gmdate("Y-m-d H:i:s");
        $prepared_data['etn_speaker_designation']   = ! empty( $input_data['designation'] ) ? sanitize_text_field( $input_data['designation'] ) : '';
        $prepared_data['etn_company_name']          = ! empty( $input_data['company_name'] ) ? sanitize_text_field( $input_data['company_name'] ) : '';
        $prepared_data['etn_speaker_url']           = ! empty( $input_data['company_url'] ) ? sanitize_url( $input_data['company_url'] ) : '';
        $prepared_data['etn_speaker_summery']       = ! empty( $input_data['summary'] ) ? wp_kses_post( $input_data['summary'] ) : '';
        $prepared_data['image']                     = ! empty( $input_data['image'] ) ? sanitize_url( $input_data['image'] ) : '';
        $prepared_data['image_id']                  = ! empty( $input_data['image_id'] ) ? intval( $input_data['image_id'] ) : attachment_url_to_postid( $input_data['image'] );
        $prepared_data['etn_speaker_company_logo']  = ! empty( $input_data['company_logo'] ) ? sanitize_url( $input_data['company_logo'] ) : '';
        $prepared_data['etn_company_logo_id']       = ! empty( $input_data['company_logo_id'] ) ? intval( $input_data['company_logo_id'] ): attachment_url_to_postid( $input_data['company_logo'] );


        if ( isset( $input_data['hide_user'] ) ) {
            $prepared_data['hide_user'] = $input_data['hide_user'];
        }
        
        return $prepared_data;
    }

    public static function get_etn_user_role( $category = null ) {
        $user_ids = [];
        
        $users = get_users( array( // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
            'meta_key' => 'etn_speaker_category', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
            'meta_compare' => 'EXISTS'
        ));


        //taxonomy details
        $prepared_args = array(
            'taxonomy'   => 'etn_speaker_category',
            'hide_empty' => false,
        );

        $term_name = [];

        $taxonomy = get_terms( $prepared_args );
        if ( ! empty( $taxonomy ) ) {
            foreach ( $taxonomy as $term ) {
                $term_name[] = strtolower( $term->name );
            }
        }

        foreach ( $users as $user ) {
            $meta_value = get_user_meta( $user->ID, 'etn_speaker_category', true );
            $meta_value = empty($meta_value)?[]:$meta_value;
            $meta_value = !is_array($meta_value)?[$meta_value]:$meta_value;
            
            if ( $category ) {
                // Check if any of the categories are in the serialized meta value
                if ( in_array( $category, etn_safe_decode( $meta_value ) ) ) {
                    $user_ids[] = $user->ID;
                }
            }
            
        }
        return $user_ids;
    }

    public function get_user_ids_by_group( $group ) {
        if ( ! $group ) {
            return [];
        }
    
        // Initialize an array to store matching user IDs.
        $matching_user_ids = [];
    
        // Step 1: Get all users with either 'etn_speaker_group' or 'etn_speaker_speaker_group' meta key.
        $user_query = new WP_User_Query([
            'meta_query' => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                'relation' => 'OR',
                [
                    'key'     => 'etn_speaker_group',
                    'compare' => 'EXISTS',
                ],
                [
                    'key'     => 'etn_speaker_speaker_group',
                    'compare' => 'EXISTS',
                ],
            ],
            'fields' => ['ID'],
        ]);
    
        if ( ! empty( $user_query->get_results() ) ) {
            foreach ( $user_query->get_results() as $user ) {
                $user_id = $user->ID;
    
                // Step 2: Fetch the serialized meta values
                $speaker_group = json_decode(get_user_meta($user_id, 'etn_speaker_group', true));
                $speaker_speaker_group = get_user_meta($user_id, 'etn_speaker_speaker_group', true);
    
                // Step 3: Check if the provided $group exists in either of the serialized arrays
                if ( (is_array( $speaker_group ) && in_array( $group, $speaker_group ) ) ||
                    (is_array( $speaker_speaker_group ) && in_array( $group, $speaker_speaker_group ) ) ) {
                    $matching_user_ids[] = $user_id;
                }
            }
        }
    
        return $matching_user_ids;
    }
    
    /**
     * Export items
     *
     * @param   WP_Rest_Request  $request  [$request description]
     *
     * @return  json
     */
    public function export_items( $request ) {
        $format = ! empty( $request['format'] ) ? sanitize_text_field( $request['format'] ) : '';
        $ids    = ! empty( $request['ids'] ) ? $request['ids'] : '';

        if ( ! $format ) {
            return new WP_Error( 'format_error', __( 'Invalid data format', 'eventin' ) );
        }

        if ( ! $ids ) {
            $ids = User_Model::get_ids( ['role__in' => ['etn-speaker']] );
        }

        $exporter = new SpeakerExporter();
        $response = $exporter->export( $ids, $format );

        if ( is_wp_error( $response ) ) {
            return $response;
        }
    }

    /**
     * Check the permissions for export items
     *
     * @param   WP_Rest_Request  $request
     *
     * @return  bool
     */
    public function export_item_permissions_check( $request ) {
        return current_user_can( 'etn_manage_organizer' )
                    || current_user_can( 'etn_manage_event' );
    }

    public function import_items( $request ) {
        $data = $request->get_file_params();
        $file = ! empty( $data['speaker_import'] ) ? $data['speaker_import'] : '';

        if ( ! $file ) {
            return new WP_Error( 'empty_file', __( 'You must provide a valid file.', 'eventin' ), ['status' => 409] );
        }

        $importer = new SpeakerImporter();
        $result   = $importer->import( $file, 'etn-speaker' );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        return rest_ensure_response( ['message' => __( 'Successfully imported speakers', 'eventin' )] );
    }

    /**
     * Check permission for the import speakers
     *
     * @param   WP_Rest_Request  $request  [$request description]
     *
     * @return  bool
     */
    public function import_item_permissions_check( $request ) {
	    return current_user_can( 'etn_manage_organizer' ) || current_user_can( 'etn_manage_event' );
    }

    /**
     * Export only organizers (etn-organizer role)
     *
     * @param   WP_REST_Request  $request
     * @return  void
     */
    public function export_organizers( $request ) {
        $format = ! empty( $request['format'] ) ? sanitize_text_field( $request['format'] ) : '';
        $ids    = ! empty( $request['ids'] ) ? $request['ids'] : '';

        if ( ! $format ) {
            return new WP_Error( 'format_error', __( 'Invalid data format', 'eventin' ) );
        }

        if ( ! $ids ) {
            $ids = User_Model::get_ids( ['role__in' => ['etn-organizer']] );
        }

        $exporter = new SpeakerExporter();
        $exporter->set_file_name( 'organizer-data' );
        $response = $exporter->export( $ids, $format );

        if ( is_wp_error( $response ) ) {
            return $response;
        }
    }

    /**
     * Import and force all rows as organizers (etn-organizer role)
     *
     * @param   WP_REST_Request  $request
     * @return  WP_REST_Response|WP_Error
     */
    public function import_organizers( $request ) {
        $data = $request->get_file_params();
        $file = ! empty( $data['organizer_import'] ) ? $data['organizer_import'] : '';

        if ( ! $file ) {
            return new WP_Error( 'empty_file', __( 'You must provide a valid file.', 'eventin' ), ['status' => 409] );
        }

        $importer = new SpeakerImporter();
        $result   = $importer->import( $file, 'etn-organizer' );

        if ( is_wp_error( $result ) ) {
            return $result;
        }

        return rest_ensure_response( ['message' => __( 'Successfully imported organizers', 'eventin' )] );
    }

    /**
     * Assign role for existing user
     *
     * @param   string  $email  [$email description]
     * @param   array  $roles  [$roles description]
     *
     * @return  array          [return description]
     */
    private function assign_role_for_existing_user( $data, $is_for_update=false ) {
        $email  = $data['etn_speaker_website_email'];
        $roles  = $data['etn_speaker_category'];
        $groups = $data['etn_speaker_group'];

        $user = get_user_by( 'email', $email );

        if ( ! $user ) {
            return false;
        }

        $updated_roles = [];

        if ( is_array( $roles ) ) {
            foreach ( $roles as $role ) {
                $role_name = 'etn-' . strtolower( $role );
                
                $updated_roles[] = $role_name;
            }
        }

        $exists_with_role = empty( array_diff( $updated_roles, $user->roles ) );

        if ( $exists_with_role && !$is_for_update ) {
            return new WP_Error( 'organizer_speaker_exists', __( 'Speaker or Organizer already exists', 'eventin' ), ['status' => 422] );
        }

        foreach ( $updated_roles as $role ) {
            if ( ! in_array( $role, $user->roles ) ) {
                $user->add_role( $role );
            }
        }

        foreach ( $data as $key => $value ) {
            if ( 'etn_speaker_category' === $key ) {
                // Derive existing categories from saved meta and from current WP roles.
                $existing = get_user_meta( $user->ID, 'etn_speaker_category', true );
                $existing = is_array( $existing ) ? $existing : [];

                $role_to_category = [ 'etn-organizer' => 'organizer', 'etn-speaker' => 'speaker' ];
                foreach ( $user->roles as $role ) {
                    if ( isset( $role_to_category[ $role ] ) ) {
                        $existing[] = $role_to_category[ $role ];
                    }
                }

                $value = array_values( array_unique( array_merge( $existing, (array) $value ) ) );
            }
            update_user_meta( $user->ID, $key, $value );
        }

        update_user_meta( $user->ID, 'author', get_current_user_id() );

        return true;
    }
}
