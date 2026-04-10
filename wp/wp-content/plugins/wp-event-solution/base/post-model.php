<?php
/**
 * Class Post Model
 *
 * @package Eventin
 */
namespace Etn\Base;

use Exception;
use WP_Query;

/**
 * Class Post Model
 */
abstract class Post_Model {
    /**
     * Store post type
     *
     * @var string
     */
    protected $post_type;

    /**
     * Store meta prefix
     *
     * @var string
     */
    protected $meta_prefix = '';

    /**
     * Store model data
     *
     * @var array
     */
    protected $data = [];

    /**
     * Store id
     *
     * @var integer
     */
    public $id;

    /**
     * Cache for get_data() result to avoid repeated meta queries on each property access.
     *
     * @var array|null
     */
    private $_cached_data = null;

    /**
     * Constructor for Post Model Class
     *
     * @return  void
     */
    public function __construct( $post = 0 ) {
        if ( $post instanceof self ) {
            $this->id = $post->id;
        } elseif ( ! empty( $post->id ) ) {
            $this->id = $post->id;
        } elseif ( is_numeric( $post ) && $post > 0 ) {
            $this->id = $post;
        }
    }

    /**
     * Get property of the object
     *
     * @param   string  $key
     *
     * @return  string
     */
    public function __get( $key ) {
        if ( ! isset( $this->data[$key] ) ) {
            throw new Exception( esc_html__( 'Undefined property', 'eventin' ) );
        }

        if ( null === $this->_cached_data ) {
            $this->_cached_data = $this->get_data();
        }

        return $this->_cached_data[ $key ] ?? null;
    }

    /**
     * Magic method for calling a instance method statically
     *
     * @param   string  $method       [$method description]
     * @param   array  $arguments  [$arguments description]
     *
     * @return  mixed
     */
    public static function __callStatic( $method, $arguments ) {
        if ( ! method_exists( new self, $method ) ) {
            throw new Exception( esc_html__( 'Call to undefined method', 'eventin' ) );
        }

        call_user_func( $method, $arguments );
    }

    /**
     * Create post
     *
     * @param   array  $args
     *
     * @return  mixed
     */
    public function create( $args = [] ) {
        $defaults = [
            'post_type'   => $this->post_type,
            'post_status' => 'draft',
            'post_author' => get_current_user_id(),
	        'menu_order'   => !empty($args['_etn_buddy_group_id']) ? $args['_etn_buddy_group_id'] : 0,
        ];

        $args    = wp_parse_args( $args, $defaults );
        $post_id = wp_insert_post( $args );

        if ( ! is_wp_error( $post_id ) ) {
            $this->id = $post_id;
            $this->update_meta( $args );
            return true;
        }

        return false;
    }
	
	
	public function create_and_return_post_id( $args = [] ) {
		$defaults = [
			'post_type'   => $this->post_type,
			'post_status' => 'draft',
			'post_author' => get_current_user_id(),
		];
		
		$args    = wp_parse_args( $args, $defaults );
		$post_id = wp_insert_post( $args );
		
		if ( ! is_wp_error( $post_id ) ) {
			$this->id = $post_id;
			$this->update_meta( $args );
			
			return $post_id;
		}
		
		return false;
	}

    /**
     * Update post
     *
     * @param   array  $args
     *
     * @return  bool
     */
    public function update( $args = [] ) {
        $defaults = [
            'ID'        => $this->id,
            'post_type' => $this->post_type,
        ];

        $args    = wp_parse_args( $args, $defaults );
        $post_id = wp_update_post( $args );

        if ( !is_wp_error( $post_id ) ) {
            $this->id = $post_id;
            $this->update_meta( $args );
            $this->_cached_data = null;
            return true;
        }

        return false;
    }

    /**
     * Delete post
     *
     * @return  bool
     */
    public function delete() {
        return wp_delete_post( $this->id );
    }

    /**
     * Put the post in trash
     *
     * @return  bool
     */
    

    /**
     * Update post meta
     *
     * @param   array  $data
     *
     * @return  void
     */
    public function update_meta( $data = [] ) {
        foreach ( $data as $key => $value ) {
            if ( array_key_exists( $key, $this->data ) ) {
                $meta_key = $this->meta_prefix . $key;
                update_post_meta( $this->id, $meta_key, $value );
            }
        }
    }

    /**
     * Assign post terms
     *
     * @param   Array  $terms
     *
     * @return mixed
     */
    public function assign_post_terms( $taxonomy, $new_terms = [] ) {
        // Update event categories.
        $terms = get_the_terms( $this->id, $taxonomy );
        $terms = $terms ? array_column( $terms, 'term_id' ) : [];

        if ( $terms ) {
            wp_remove_object_terms( $this->id, $terms, $taxonomy );
        }

        wp_set_post_terms( $this->id, $new_terms, $taxonomy, true );
    }

    /**
     * Get post term ids
     *
     * @param   string  $taxonomy  Taxonomy of the post
     *
     * @return  array   Return term ids
     */
    public function get_term_ids( $taxonomy ) {
        $terms = get_the_terms( $this->id, $taxonomy );
        $terms = $terms ? array_column( $terms, 'term_id' ) : [];

        return $terms;
    }

    /**
     * Get data
     *
     * @return  array
     */
    public function get_data() {
        $response_data = [
            'id' => $this->id,
        ];

        foreach ( $this->data as $key => $value ) {
            $meta_key = $this->meta_prefix . $key;

            $meta_value = get_post_meta( $this->id, $meta_key, true );

            $response_data[$key] = $meta_value;
        }

        return $response_data;
    }

    /**
     * Get all posts
     *
     * @param   array  $args
     *
     * @return  array
     */
    public function all( $args = [] ) {
        $defaults = [
            'post_type'      => $this->post_type,
            'post_status'    => 'publish',
            'posts_per_page' => 20,
            'paged'          => 1,
        ];

        $args = wp_parse_args( $args, $defaults );

        $query = new WP_Query( $args );

        return [
            'items' => $query->posts,
            'total' => $query->found_posts,
        ];
    }

    /**
     * Clone an object
     *
     * @param   integer  $id
     *
     * @return  Object
     */
    public function clone () {
        $post                 = get_post( $this->id );
        $data                 = $this->get_data();
        $data['post_title']   = $post->post_title;
        $data['post_content'] = $post->post_content;
        $data['post_status']  = 'draft';
        $data['is_clone'] = true;
	    
	    $this->create( $data );
	    
	    //$newly_created_post_id = $this->create_and_return_post_id($data);
	    $this->copy_elementor_meta_data($post->ID, $this->id);
	    
	    
	    set_post_thumbnail( $this->id, get_post_meta( $this->id, 'event_banner_id', true ) );

        return $this;
    }
	
	
	/**
	 * @desc 
	 *
	 * @param $post_id<int|string>
	 * @param $newly_created_post_id<int|string>
	 * @return void
	 */
	public function copy_elementor_meta_data($post_id, $newly_created_post_id): void
	{
		$is_elementor_editor = get_post_meta($post_id, '_elementor_edit_mode', true);
		if ( class_exists('Elementor\Plugin') && ($is_elementor_editor === "builder") ) {
			$elementor_data = get_post_meta($post_id, '_elementor_data', true);
			update_post_meta($newly_created_post_id, "_elementor_data", $elementor_data);
		}
	}

    /**
     * Get post ids
     *
     * @param   array  $args  [$args description]
     *
     * @return  []             [return description]
     */
    public function get_ids( $args = [] ) {
        $defaults = [
            'post_type'      => $this->post_type,
            'post_status'    => 'any',
            'posts_per_page' =>  -1,
            'fields'         => 'ids'
        ];

        $args  = wp_parse_args( $args, $defaults );

        if ( ! current_user_can( 'manage_options' ) ) {
            $args['author'] = get_current_user_id(); 
        }

        $posts = get_posts( $args );

        return $posts;
    }
}
