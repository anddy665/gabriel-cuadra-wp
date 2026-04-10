<?php

namespace Eventin\Blocks;

defined( 'ABSPATH' ) || exit;

use Eventin\Interfaces\HookableInterface;

class BlockLegacySupportHooks implements HookableInterface {
    /**
     * Register all hooks
     *
     * @return  void
     */
    public function register_hooks(): void {
        $wp_version = get_bloginfo( 'version' );

        if ( version_compare( $wp_version, '5.8', '>=' ) ) {
            add_filter( 'block_categories_all', [ $this, 'etn_block_category' ], 10, 2 );
        } else {
            add_filter( 'block_categories', [ $this, 'etn_block_category' ], 10, 2 );
        }

        // Hook: Block assets.
        add_action( 'enqueue_block_editor_assets', [ $this, 'etn_block_assets' ] );
    }

    /**
     * Register Eventin block category.
     *
     * @param array   $categories Existing categories.
     * @param \WP_Post $post      Current post.
     *
     * @return array
     */
    public function etn_block_category( $categories, $post ) {
        return array_merge(
            $categories,
            [
                [
                    'slug'  => 'eventin-blocks',
                    'title' => __( 'Eventin', 'eventin' ),
                ],
            ]
        );
    }

    /**
     * Enqueue block assets.
     *
     * @return void
     */
    public function etn_block_assets() {
        // Register block editor script for backend.
        wp_enqueue_script( 'eventin-block-js' );

        if ( function_exists( 'wp_set_script_translations' ) ) {
            wp_set_script_translations( 'eventin-block-js', 'eventin' );
        }

        wp_enqueue_style( 'eventin-block-editor-style-css' );
        wp_enqueue_style( 'eventin-calendar-block-editor-style' );

        // WP Localized globals. Use dynamic PHP stuff in JavaScript via `cgbGlobal` object.
        wp_localize_script(
            'eventin-block-js',
            'tsGlobal',
            [
                'pluginDirPath'  => plugin_dir_path( __DIR__ ),
                'pluginDirUrl'   => plugin_dir_url( __DIR__ ),
                'etn_pro_active' => class_exists( 'Wpeventin_Pro' ) ? true : false,
            ]
        );
    }
}