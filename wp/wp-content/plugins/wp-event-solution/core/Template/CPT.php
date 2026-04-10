<?php

namespace Eventin\Template;

defined( 'ABSPATH' ) || exit;

/**
 * Template custom post type
 */
class CPT {

    /**
     * Register post type
     *
     * @return  void
     */
    public function register_post_type() {
        $labels = [
            'name'                  => __( 'ETN Templates', 'eventin' ),
            'singular_name'         => __( 'ETN Template', 'eventin' ),
            'menu_name'             => __( 'ETN Templates', 'eventin' ),
            'name_admin_bar'        => __( 'ETN Template', 'eventin' ),
            'add_new'               => __( 'Add New', 'eventin' ),
            'add_new_item'          => __( 'Add New ETN Template', 'eventin' ),
            'edit_item'             => __( 'Edit ETN Template', 'eventin' ),
            'new_item'              => __( 'New ETN Template', 'eventin' ),
            'view_item'             => __( 'View ETN Template', 'eventin' ),
            'view_items'            => __( 'View ETN Templates', 'eventin' ),
            'search_items'          => __( 'Search ETN Templates', 'eventin' ),
            'not_found'             => __( 'No ETN Templates found', 'eventin' ),
            'not_found_in_trash'    => __( 'No ETN Templates found in Trash', 'eventin' ),
            'all_items'             => __( 'All ETN Templates', 'eventin' ),
            'archives'              => __( 'ETN Template Archives', 'eventin' ),
            'attributes'            => __( 'ETN Template Attributes', 'eventin' ),
            'insert_into_item'      => __( 'Insert into ETN Template', 'eventin' ),
            'uploaded_to_this_item' => __( 'Uploaded to this ETN Template', 'eventin' ),
            'featured_image'        => __( 'Featured Image', 'eventin' ),
            'set_featured_image'    => __( 'Set Featured Image', 'eventin' ),
            'remove_featured_image' => __( 'Remove Featured Image', 'eventin' ),
            'use_featured_image'    => __( 'Use as Featured Image', 'eventin' ),
            'menu_name'             => __( 'ETN Templates', 'eventin' ),
        ];
    
        $args = [
            'labels'              => $labels,
            'public'              => true, // Whether the post type is public.
            'has_archive'         => true, // Enables archive page for the post type.
            'rewrite'             => [ 'slug' => 'etn-template' ], // Custom slug for URLs.
            'supports'            => [ 'title', 'editor', 'thumbnail' ], // Supported features.
            'show_in_rest'        => true, // Enable Gutenberg editor and REST API.
            'menu_icon'           => 'dashicons-layout', // Icon for the post type.
            'capability_type'     => 'post', // Default capabilities (can be customized).
            'publicly_queryable'  => true,
            'show_ui'             => true,  // Show in Gutendberg editor in admin panel
            'show_in_menu'        => false,
            'menu_position'       => 20, // Position in admin menu.
        ];
    
        // Register the post type.
        register_post_type( 'etn-template', $args );
    }
}
