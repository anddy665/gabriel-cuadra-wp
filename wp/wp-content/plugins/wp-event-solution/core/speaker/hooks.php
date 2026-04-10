<?php

namespace Etn\Core\Speaker;

use \Etn\Core\Speaker\Pages\Speaker_single_post;
use Etn\Core\Speaker\Views\Parts\TemplateHooks as PartsTemplateHooks;
use TemplateHooks;

defined( 'ABSPATH' ) || exit;

class Hooks {

    use \Etn\Traits\Singleton;

    public $cpt;
    public $action;
    public $base;
    public $speaker;
    public $category;
    public $settings;
    public $spaeker_action;
    
    public function Init() {

        $this->category = new Category();

        add_filter('author_link', [$this, 'custom_speaker_link'], 10, 3);
        add_action('init', [$this, 'speaker_rewrite_rules']);


        add_filter( 'user_row_actions', [$this, 'user_row_action_organizer'], 10, 2 );
        add_filter( 'user_row_actions', [$this, 'user_row_action_speaker'], 10, 2 );

        add_action( 'admin_init', [$this, 'make_speaker_organizer'] );

        add_filter( 'users_list_table_query_args', [ $this, 'hide_speakers_from_users' ] );
    }  

    /**
     * Add a rewrite rule to enable pretty URLs for speakers.
     *
     * By default, WordPress uses `author` as the slug for the author archive. This
     * function adds a rewrite rule that allows the slug to be customized via the
     * `etn_event_options` option.
     *
     * @since 1.0.0
     * @access public
     */
    public function speaker_rewrite_rules() {
        $settings_options = get_option('etn_event_options'); 
        if (!empty($settings_options['speaker_slug'])) {
            $slug = sanitize_title($settings_options['speaker_slug']);
            add_rewrite_rule("^$slug/([^/]+)/?", 'index.php?author_name=$matches[1]', 'top');
        }
    }
    
    /**
     * Change the author slug to 'speakers'
     *
     * @param string $link The link to the author page
     * @param int $author_id The ID of the author
     * @param string $author_nicename The nicename of the author
     * @return string The modified link
     */
    public function custom_speaker_link($link, $author_id, $author_nicename) {  
        $settings_options = get_option('etn_event_options'); 
        if (!empty($settings_options['speaker_slug'])) {
            $slug = sanitize_title($settings_options['speaker_slug']);
            
            // Get the user object
            $user = get_userdata($author_id);
    
            // Check if the user has the role 'etn-speaker' or 'etn-organizer'
            if ($user && (in_array('etn-speaker', (array) $user->roles) || in_array('etn-organizer', (array) $user->roles))) {
                // Set base for speakers and organizers
                $link = home_url("/$slug/" . $author_nicename);
            }
        }
        return $link;
    }


    /**
     * Add or remove organizer role in user row actions
     *
     * @param array $actions
     * @param WP_User $user_object
     * @return array
     */
    public function user_row_action_organizer($actions, $user_object) {
        $is_organizer = in_array( 'etn-organizer', $user_object->roles );
        $button_text = $is_organizer ? esc_html__( 'Remove from Organizer', 'eventin' ) : esc_html__( 'Make Organizer', 'eventin' );
        $action = $is_organizer ? 'remove_organizer' : 'make_organizer';

        $actions['organizer'] = "<a class='etn-organizer' href='" . wp_nonce_url("users.php?action=$action&amp;users=$user_object->ID", 'bulk-users') . "'>" . $button_text . '</a>';

        return $actions;
    }

    /**
     * Add or remove speaker role in user row actions
     *
     * @param array $actions
     * @param WP_User $user_object
     * @return array
     */
    public function user_row_action_speaker($actions, $user_object) {
        
        $is_speaker = in_array( 'etn-speaker', $user_object->roles );
        $button_text = $is_speaker ? esc_html__( 'Remove from Speaker', 'eventin' ) : esc_html__( 'Make Speaker', 'eventin' );
        $action = $is_speaker ? 'remove_speaker' : 'make_speaker';

        $actions['speaker'] = "<a class='etn-speaker' href='" . wp_nonce_url("users.php?action=$action&amp;users=$user_object->ID", 'bulk-users') . "'>" . $button_text . '</a>';

        return $actions;
    }

    /**
     * Handle make or remove organizer and speaker actions
     *
     * @return void
     */
    public function make_speaker_organizer() { 

        $action  = isset( $_GET['action'] ) ? sanitize_text_field( wp_unslash( $_GET['action'] ) ) : '';
        $user_id = isset( $_GET['users'] ) ? intval( $_GET['users'] ) : 0;
        $nonce   = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
        $user    = get_userdata( $user_id );
    
        if ( !wp_verify_nonce( $nonce, 'bulk-users' ) ) {
            return;
        }

        $capability = current_user_can( 'manage_options' );
        if( !$capability ) return;

        if ( 'make_speaker' === $action ) {
            $user->add_role( 'etn-speaker' );
            update_user_meta( $user->ID, 'etn_speaker_website_email', $user->user_email );
        }

        if ( 'remove_speaker' === $action ) {
            $user->remove_role( 'etn-speaker' );
        }

        if ( 'make_organizer' === $action ) {
            $user->add_role( 'etn-organizer' );
            update_user_meta( $user->ID, 'etn_speaker_website_email', $user->user_email );
        }

        if ( 'remove_organizer' === $action ) {
            $user->remove_role( 'etn-organizer' );
        }
    }
    
    /**
     * Hihde users from user list table
     *
     * @param   array  $query_args  [$query_args description]
     *
     * @return  array
     */
    public function hide_speakers_from_users( $query_args ) {
        $args = [
            'role__in'    => ['etn-speaker', 'etn-organizer'],
            'meta_query'  => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
                [
                    'key'     => 'hide_user',
                    'value'   => '1', // Check if hide_user is true
                    'compare' => '='
                ]
            ],
            'fields'      => 'ID', // Return only user IDs
            'number'      => -1,   // Retrieve all matching users
        ];

        $users = get_users( $args ); // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude
        $hidden_users = $users;

        $query_args['exclude'] = $hidden_users; // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude

        return $query_args;
    }
}
