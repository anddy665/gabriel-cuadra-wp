<?php

namespace Eventin\Template;

defined( 'ABSPATH' ) || exit;

use Eventin\Interfaces\HookableInterface;

/**
 * Class EtnHomepageHooks
 * Handles ETN post type homepage functionality in WordPress Reading Settings
 */
class EtnHomepageHooks implements HookableInterface {
    
    /**
     * Register all hooks for the class
     *
     * @return void
     */
    public function register_hooks(): void {
        // Add ETN posts to the homepage dropdown in Reading Settings
        add_filter( 'get_pages', [ $this, 'add_etn_posts_to_homepage_dropdown' ], 10, 2 );
        
        // Handle the homepage display logic for ETN posts
        add_action( 'pre_get_posts', [ $this, 'modify_homepage_query_for_etn' ] );
        
        // Ensure correct template is loaded for ETN homepage
        add_action( 'template_redirect', [ $this, 'handle_etn_homepage_template' ] );
        
        // Fix canonical URLs for ETN homepage
        add_filter('redirect_canonical', [ $this, 'fix_etn_homepage_canonical' ], 10, 2);        
    }
    
    /**
     * Add ETN posts to the pages dropdown in Reading Settings
     * This makes ETN posts appear in the "Homepage" dropdown
     *
     * @param array $pages Array of page objects
     * @param array $args Arguments used to retrieve pages
     * @return array Modified array of pages including ETN posts
     */
    public function add_etn_posts_to_homepage_dropdown($pages, $args) {
        // Only modify when we're in the admin and dealing with the homepage dropdown
        if ( ! is_admin() || ! isset( $args['name'] ) || $args['name'] !== 'page_on_front' ) {
            return $pages;
        }
        
        // Get published ETN posts
        $etn_posts = get_posts([
            'post_type' => 'etn',
            'post_status' => 'publish',
            'orderby' => 'title',
            'order' => 'ASC',
        ]);
        
        // Add ETN posts to the pages array
        foreach ( $etn_posts as $post ) {
            $page_obj = new \stdClass();
            $page_obj->ID = $post->ID;
            $page_obj->post_title = $post->post_title;
            $page_obj->post_name = $post->post_name;
            $page_obj->post_type = $post->post_type;
            $page_obj->post_status = $post->post_status;
            $page_obj->post_parent = 0;
            $page_obj->menu_order = 0;
            
            $pages[] = $page_obj;
        }
        
        return $pages;
    }
    
    /**
     * Modify the main query to handle ETN post as homepage
     *
     * @param \WP_Query $query The WP_Query instance
     * @return void
     */
    public function modify_homepage_query_for_etn( \WP_Query $query ) {
        // Only modify the main query on the front page
        if ( !$query->is_main_query() || !is_front_page() ) {
            return;
        }
        
        $page_on_front = get_option('page_on_front');
        
        if ( $page_on_front ) {
            $front_page_post = get_post($page_on_front);
            
            // Check if the front page is an ETN post
            if ( $front_page_post && $front_page_post->post_type === 'etn' ) {
                // Modify query to show the specific ETN post
                $query->set('post_type', 'etn');
                $query->set('p', $page_on_front);
                $query->set('posts_per_page', 1);
                
                // Set query vars to indicate this is a singular page
                $query->is_singular = true;
                $query->is_single = true;
                $query->is_home = false;
                $query->is_front_page = true;
            }
        }
    }
    
    /**
     * Handle template loading for ETN posts used as homepage
     *
     * @return void
     */
    public function handle_etn_homepage_template() {
        if ( ! is_front_page() ) {
            return;
        }
        
        $page_on_front = get_option( 'page_on_front' );
        
        if ( ! $page_on_front ) {
            return;
        }
        
        $front_page_post = get_post( $page_on_front );
        
        if ( $front_page_post && $front_page_post->post_type === 'etn') {
            // Define template hierarchy for ETN homepage
            $template_hierarchy = [
                'single-etn-' . $front_page_post->post_name . '.php',
                'single-etn.php',
                'etn-homepage.php', // Custom homepage template for ETN
                'single.php',
                'index.php'
            ];
            
            foreach ( $template_hierarchy as $template ) {
                $located_template = locate_template( $template );
                if ( $located_template ) {
                    include( $located_template );
                    exit;
                }
            }
        }
    }
    
    /**
     * Fix canonical URLs for ETN posts used as homepage
     *
     * @param string $redirect_url The redirect URL
     * @param string $requested_url The requested URL
     * @return string Modified redirect URL
     */
    public function fix_etn_homepage_canonical( $redirect_url, $requested_url ) {
        if ( ! is_front_page() ) {
            return $redirect_url;
        }
        
        $page_on_front = get_option( 'page_on_front' );
        
        if ( $page_on_front ) {
            $front_page_post = get_post($page_on_front);
            
            if ( $front_page_post && $front_page_post->post_type === 'etn' ) {
                return home_url('/');
            }
        }
        
        return $redirect_url;
    }
    
    /**
     * Check if an ETN post is currently set as homepage
     *
     * @return bool|int Returns the ETN post ID if set as homepage, false otherwise
     */
    public function is_etn_homepage() {
        $page_on_front = get_option( 'page_on_front' );
        
        if ( $page_on_front ) {
            $front_page_post = get_post( $page_on_front );
            
            if ( $front_page_post && $front_page_post->post_type === 'etn' ) {
                return $page_on_front;
            }
        }
        
        return false;
    }
    
    /**
     * Get the current homepage ETN post object
     *
     * @return \WP_Post|null Returns the ETN post object if set as homepage, null otherwise
     */
    public function get_homepage_etn_post() {
        $etn_homepage_id = $this->is_etn_homepage();
        
        if ( $etn_homepage_id ) {
            return get_post( $etn_homepage_id );
        }
        
        return null;
    }
}